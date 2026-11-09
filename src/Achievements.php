<?php

class Achievements
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Проверяет и выдаёт ачивки на основе данных анализа
     */
    public function checkAndUnlock(int $userId, array $dashboardData, int $analysisId): array
    {
        $unlocked = [];
        $allAchievements = $this->db->fetchAll(
            'SELECT * FROM achievements WHERE is_active = true'
        );


        foreach ($allAchievements as $ach) {
            if ($ach['code'] === 'answer_42') {
                file_put_contents(
                    __DIR__ . '/achievements-debug.txt',
                    date('Y-m-d H:i:s') . " Data: " . json_encode($dashboardData, JSON_UNESCAPED_UNICODE) . "\nCondition: " . $ach['condition_json'] . "\n\n",
                    FILE_APPEND
                );
            }
            // Проверяем, не получил ли пользователь уже эту ачивку
            $existing = $this->db->fetch(
                'SELECT id FROM user_achievements WHERE user_id = :uid AND achievement_id = :aid',
                ['uid' => $userId, 'aid' => $ach['id']]
            );
            if ($existing) continue;

            if ($this->checkCondition($ach, $dashboardData)) {
                $this->db->query(
                    'INSERT INTO user_achievements (user_id, achievement_id, analysis_id) 
                     VALUES (:uid, :aid, :aid_analysis)',
                    [
                        'uid' => $userId,
                        'aid' => $ach['id'],
                        'aid_analysis' => $analysisId
                    ]
                );
                $unlocked[] = $ach;
            }
        }

        return $unlocked;
    }

    /**
     * Проверка условия ачивки
     */
    private function checkCondition(array $achievement, array $data): bool
    {
        $condition = json_decode($achievement['condition_json'], true);
        $type = $achievement['condition_type'];

        switch ($type) {
            case 'field_value':
                return $this->checkFieldValue($condition, $data);

            case 'text_contains':
                return $this->checkTextContains($condition, $data);

            default:
                return false;
        }
    }

    private function checkFieldValue(array $condition, array $data): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (!$field || !isset($data[$field])) return false;

        $actual = $data[$field];

        // Извлекаем значение из вложенных массивов (например, kbu.kbu)
        if (strpos($field, '.') !== false) {
            $parts = explode('.', $field);
            $actual = $data;
            foreach ($parts as $part) {
                if (!isset($actual[$part])) return false;
                $actual = $actual[$part];
            }
        }

        switch ($operator) {
            case '>':
                return $actual > $value;
            case '<':
                return $actual < $value;
            case '=':
                return $actual == $value;
            case '>=':
                return $actual >= $value;
            case '<=':
                return $actual <= $value;
            case 'between':
                return $actual >= $value[0] && $actual <= $value[1];
            default:
                return false;
        }
    }

    private function checkTextContains(array $condition, array $data): bool
    {
        $field = $condition['field'] ?? null;
        $actual = $data[$field] ?? '';

        if (!$field || $actual === '') return false;

        // Проверка на массив значений (старый формат)
        $values = $condition['values'] ?? [];
        if (!empty($values)) {
            foreach ($values as $val) {
                if (mb_stripos($actual, $val) !== false) {
                    return true;
                }
            }
        }

        // Проверка на одиночное значение (новый формат для text_contains)
        $value = $condition['value'] ?? '';
        if ($value !== '' && mb_stripos($actual, $value) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Получить все ачивки пользователя
     */
    public function getUserAchievements(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, ua.unlocked_at, ua.analysis_id
             FROM user_achievements ua
             JOIN achievements a ON a.id = ua.achievement_id
             WHERE ua.user_id = :uid
             ORDER BY ua.unlocked_at DESC',
            ['uid' => $userId]
        );
    }

    /**
     * Получить количество ачивок пользователя
     */
    public function getAchievementsCount(int $userId): int
    {
        $row = $this->db->fetch(
            'SELECT COUNT(*) as cnt FROM user_achievements WHERE user_id = :uid',
            ['uid' => $userId]
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Получить общее количество возможных ачивок
     */
    public function getTotalAchievementsCount(): int
    {
        $row = $this->db->fetch(
            'SELECT COUNT(*) as cnt FROM achievements WHERE is_active = true'
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Проверить, есть ли у пользователя ачивка
     */
    public function hasAchievement(int $userId, string $code): bool
    {
        $row = $this->db->fetch(
            'SELECT 1 FROM user_achievements ua 
             JOIN achievements a ON a.id = ua.achievement_id 
             WHERE ua.user_id = :uid AND a.code = :code',
            ['uid' => $userId, 'code' => $code]
        );
        return $row !== null;
    }

    /**
     * Получить последние полученные ачивки
     */
    public function getLatestAchievements(int $userId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT a.*, ua.unlocked_at
             FROM user_achievements ua
             JOIN achievements a ON a.id = ua.achievement_id
             WHERE ua.user_id = :uid
             ORDER BY ua.unlocked_at DESC
             LIMIT :limit',
            ['uid' => $userId, 'limit' => $limit]
        );
    }

    /**
     * Получить данные для дашборда ачивок
     */
    public function getAchievementsDashboard(int $userId): array
    {
        $userAchievements = $this->getUserAchievements($userId);
        $total = $this->getTotalAchievementsCount();
        $unlocked = count($userAchievements);

        // Группировка по категориям
        $categories = [
            'common' => ['name' => 'Обычные', 'icon' => '📌', 'achievements' => []],
            'rare' => ['name' => 'Редкие', 'icon' => '🔮', 'achievements' => []],
            'epic' => ['name' => 'Эпические', 'icon' => '⚡', 'achievements' => []],
            'secret' => ['name' => 'Секретные', 'icon' => '🤫', 'achievements' => []],
            'easter_egg' => ['name' => 'Пасхалки', 'icon' => '🥚', 'achievements' => []],
        ];

        foreach ($userAchievements as $ach) {
            $cat = $ach['category'] ?? 'common';
            if (!isset($categories[$cat])) {
                $categories[$cat] = ['name' => ucfirst($cat), 'icon' => '🎯', 'achievements' => []];
            }
            $categories[$cat]['achievements'][] = $ach;
        }

        return [
            'total' => $total,
            'unlocked' => $unlocked,
            'progress' => $total > 0 ? round(($unlocked / $total) * 100, 1) : 0,
            'by_category' => $categories,
            'latest' => $this->getLatestAchievements($userId, 3),
        ];
    }

    /**
     * Подготовка данных для проверки ачивок из дашборда
     */
    public function extractAchievementData(array $dashboardData): array
    {
        $data = [];

        $data['kbu'] = $dashboardData['kbu']['kbu'] ?? 0;
        $data['enot_value'] = $dashboardData['kbu']['enot_value'] ?? 0;
        $data['enot_max'] = $dashboardData['kbu']['enot_max'] ?? 17;
        $data['secret_level'] = $dashboardData['secret']['level'] ?? '';
        $data['secret_name'] = $dashboardData['secret']['name'] ?? '';
        $data['secret_title'] = $dashboardData['secret']['title'] ?? '';
        $data['action'] = $dashboardData['compare']['action'] ?? '';
        $data['character'] = $dashboardData['compare']['character_name'] ?? '';
        $data['reaction'] = $dashboardData['compare']['reaction'] ?? '';
        $data['prediction'] = $dashboardData['prediction']['text'] ?? '';

        // Пасхалка: 42
        if (($dashboardData['input'] ?? '') == 42) {
            $data['character'] = 'Дуглас Адамс';
            $data['prediction'] = 'полотенце';
            $data['secret_level'] = 'БЕТА';
        }

        $segments = $dashboardData['chart']['segments'] ?? [];
        foreach ($segments as $seg) {
            $key = 'category_' . strtolower(str_replace(' ', '_', $seg['name']));
            $data[$key] = $seg['percent'] ?? 0;
        }

        if (count($segments) === 5) {
            $equal = true;
            foreach ($segments as $seg) {
                if (abs(($seg['percent'] ?? 0) - 20) > 5) {
                    $equal = false;
                    break;
                }
            }
            $data['all_categories_equal'] = $equal;
        }

        return $data;
    }
}
