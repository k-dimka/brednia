<?php

class Dashboard
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function generate(int $userId, string $inputValue): int
    {
        if ($inputValue == 42) {
            return $this->generate42($userId);
        }

        $kbu = $this->generateKbuBlock($userId);
        $compare = $this->generateCompareBlock($inputValue);
        $chart = $this->generateChartBlock();
        $prediction = $this->getPrediction();
        $secret = $this->generateSecretBlock();

        $dashboard = [
            'input' => $inputValue,
            'kbu' => $kbu,
            'compare' => $compare,
            'chart' => $chart,
            'prediction' => $prediction,
            'secret' => $secret,
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        $publicHash = substr(md5(random_bytes(16)), 0, 12);

        // Сохраняем анализ
        $this->db->query(
            'INSERT INTO analyses (user_id, input_value, dashboard_json, public_hash)
         VALUES (:uid, :input, :json, :hash)',
            [
                'uid' => $userId,
                'input' => $inputValue,
                'json' => json_encode($dashboard, JSON_UNESCAPED_UNICODE),
                'hash' => $publicHash,
            ]
        );
        $analysisId = (int) $this->db->lastInsertId();

        // ===== Проверка ачивок =====
        $achievements = new Achievements($this->db);
        $achievementData = $achievements->extractAchievementData($dashboard);
        $unlocked = $achievements->checkAndUnlock($userId, $achievementData, $analysisId);
        $achievementData = $achievements->extractAchievementData($dashboard);

        $unlocked = $achievements->checkAndUnlock($userId, $achievementData, $analysisId);

        if (!empty($unlocked)) {
            // Добавляем в сессию для отображения на странице
            $_SESSION['new_achievements'] = array_merge(
                $_SESSION['new_achievements'] ?? [],
                $unlocked
            );
        }

        return $analysisId;
    }

    public function getById(int $id, int $userId): ?array
    {
        $row = $this->db->fetch(
            'SELECT * FROM analyses WHERE id = :id AND user_id = :uid',
            ['id' => $id, 'uid' => $userId]
        );
        if (!$row) return null;

        return [
            'id' => $row['id'],
            'input' => $row['input_value'],
            'dashboard' => json_decode($row['dashboard_json'], true),
            'public_hash' => $row['public_hash'],
            'created_at' => $row['created_at'],
        ];
    }

    public function getByPublicHash(string $hash): ?array
    {
        $row = $this->db->fetch(
            'SELECT * FROM analyses WHERE public_hash = :hash',
            ['hash' => $hash]
        );
        if (!$row) return null;

        return [
            'id' => $row['id'],
            'input' => $row['input_value'],
            'dashboard' => json_decode($row['dashboard_json'], true),
            'public_hash' => $row['public_hash'],  // ← добавить
            'created_at' => $row['created_at'],
        ];
    }

    public function getHistory(int $userId, int $limit = 20): array
    {
        $rows = $this->db->fetchAll(
            'SELECT id, input_value, public_hash, dashboard_json, created_at
         FROM analyses
         WHERE user_id = :uid
         ORDER BY created_at DESC
         LIMIT :limit',
            ['uid' => $userId, 'limit' => $limit]
        );

        return array_map(function ($row) {
            $d = json_decode($row['dashboard_json'], true);
            return [
                'id' => $row['id'],
                'input_value' => $row['input_value'],
                'public_hash' => $row['public_hash'],
                'created_at' => $row['created_at'],
                'kbu' => $d['kbu']['kbu'] ?? '—',
                'dynamics' => $d['kbu']['dynamics'] ?? '',
            ];
        }, $rows);
    }

    private function generateKbuBlock($userId): array
    {
        $kbu = rand(0, 100);

        // Ищем предыдущий КБУ из последнего анализа этого пользователя
        $prevRow = $this->db->fetch(
            'SELECT dashboard_json FROM analyses WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1',
            ['uid' => $userId]
        );

        $prevKbu = null;
        if ($prevRow) {
            $prevDashboard = json_decode($prevRow['dashboard_json'], true);
            $prevKbu = $prevDashboard['kbu']['kbu'] ?? null;
        }

        // Если нет предыдущего — случайный
        if ($prevKbu === null) {
            $prevKbu = 0;
        }
        $diff = $kbu - $prevKbu;

        if ($diff > 5) {
            $dynamics = 'растёт ▲';
            $dynamicsType = 'up';
        } elseif ($diff < -5) {
            $dynamics = 'падает ▼';
            $dynamicsType = 'down';
        } else {
            $dynamics = 'без изменений ▬';
            $dynamicsType = 'flat';
        }

        $percentile = rand(30, 99);
        $enotValue = round($kbu / 6 + 1, 1);
        $enotMax = 17.0;

        return [
            'kbu' => $kbu,
            'prev_kbu' => $prevKbu,
            'dynamics' => $dynamics,
            'dynamics_type' => $dynamicsType,
            'percentile' => $percentile,
            'enot_value' => $enotValue,
            'enot_max' => $enotMax,
            'enot_percent' => round(($enotValue / $enotMax) * 100, 1),
        ];
    }

    private function generateCompareBlock(string $input): array
    {
        $char = $this->getRandomRow('characters');
        $template = $this->getRandomRow('compare_templates');

        // Приводим ввод к числу (если возможно), иначе используем длину строки
        $numericValue = is_numeric($input) ? (float) $input : strlen($input);

        $grechkaKg = max(1, round($numericValue * (rand(5, 50) / 100)));
        $catsCount = max(0.1, round($numericValue * (rand(1, 15) / 1000), 1));
        $month = max(1, round($numericValue / 30));
        $napoleon = max(1, round($numericValue / 3, 1));


        $actionText = $this->getRandomRow('actions')['text'];
        $reactionText = $this->getRandomRow('reactions')['text'];

        // Текст шаблона
        $text = $template['template'];
        $text = str_replace('{name}', $char['name'], $text);
        $text = str_replace('{input}', $input, $text);
        $text = str_replace('{percent}', (string) rand(10, 300), $text);
        $text = str_replace('{action}', $this->getRandomRow('actions')['text'], $text);
        $text = str_replace('{item}', $this->getRandomRow('items')['text'], $text);
        $text = str_replace('{reaction}', $this->getRandomRow('reactions')['text'], $text);
        $text = str_replace('{count}', (string) rand(2, 50), $text);

        // На случай старых шаблонов с {comment} — убираем, если остался
        $text = str_replace('{comment}', '', $text);

        // 30% шанс на случайный комментарий в конце
        if (rand(1, 100) <= 30) {
            $comment = $this->getRandomRow('comments')['text'];
            $text .= ' ' . $comment;
        }

        return [
            'character_name' => $char['name'],
            'character_desc' => $char['description'],
            'analysis_text' => $text,
            'grechka_kg' => $grechkaKg,
            'month' => $month,
            'cats_count' => $catsCount,
            'action' => $actionText,
            'reaction' => $reactionText,
            'napoleon' => $napoleon
        ];
    }

    private function generateChartBlock(): array
    {
        $allCategories = $this->db->fetchAll(
            'SELECT name FROM chart_categories WHERE is_active = true'
        );

        if (count($allCategories) < 5) {
            return ['segments' => [], 'note' => 'Недостаточно категорий'];
        }

        shuffle($allCategories);
        $selected = array_slice($allCategories, 0, 5);
        $percentages = $this->distributePercentages(5);

        $colors = ['#00FF41', '#FFB000', '#FF3333', '#8B7340', '#4A90D9'];
        $segments = [];
        foreach ($selected as $i => $cat) {
            $segments[] = [
                'name' => $cat['name'],
                'percent' => $percentages[$i],
                'color' => $colors[$i % count($colors)],
            ];
        }

        return ['segments' => $segments];
    }

    private function getPrediction(): array
    {
        $dayOfYear = (int) date('z') + 1;
        $row = $this->db->fetch(
            'SELECT text FROM predictions WHERE is_active = true ORDER BY RANDOM() LIMIT 1'
        );

        if (!$row) {
            $row = $this->getRandomRow('predictions');
        }

        return [
            'day_of_year' => $dayOfYear,
            'text' => $row['text'],
        ];
    }

    private function generateSecretBlock(): array
    {
        return [
            'name' => $this->getRandomRow('secret_names')['name'],
            'title' => $this->getRandomRow('secret_titles')['title'],
            'level' => $this->getRandomRow('secret_levels')['level'],
        ];
    }

    private function getRandomRow(string $table): array
    {
        $row = $this->db->fetch(
            "SELECT * FROM {$table} WHERE is_active = true ORDER BY RANDOM() LIMIT 1"
        );
        return $row ?: [];
    }

    private function distributePercentages(int $count): array
    {
        $parts = [];
        $remaining = 100;
        for ($i = 0; $i < $count - 1; $i++) {
            $max = $remaining - ($count - $i - 1) * 5;
            $min = max(5, (int) floor($remaining / ($count - $i)));
            $val = rand($min, $max);
            $parts[] = $val;
            $remaining -= $val;
        }
        $parts[] = $remaining;
        shuffle($parts);
        return $parts;
    }

    /**
     * Возвращает дашборд по slug (public_hash), только если принадлежит пользователю.
     */
    public function getBySlug(string $slug, int $userId): ?array
    {
        $row = $this->db->fetch(
            'SELECT * FROM analyses WHERE public_hash = :hash AND user_id = :uid',
            ['hash' => $slug, 'uid' => $userId]
        );
        if (!$row) return null;

        return [
            'id' => $row['id'],
            'input' => $row['input_value'],
            'dashboard' => json_decode($row['dashboard_json'], true),
            'public_hash' => $row['public_hash'],
            'created_at' => $row['created_at'],
        ];
    }

    /**
     * Генерирует HTML дашборда из массива данных.
     */
    public function renderHTML(array $data): string
    {
        $d = $data['dashboard'];
        $hash = $data['public_hash'];

        $dynamicsColor = match ($d['kbu']['dynamics_type']) {
            'up' => 'var(--accent)',
            'down' => 'var(--error)',
            default => 'var(--text-dim)',
        };

        ob_start();
?>
        <div id="dashboard-container">

            <!-- Заголовок анализа -->
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-family:'JetBrains Mono',monospace; font-size:11px; text-transform:uppercase; letter-spacing:2px; color:var(--text-dim); margin-bottom:4px;">
                    Анализ числа
                </div>
                <div style="font-family:'JetBrains Mono',monospace; font-size:28px; color:var(--accent); letter-spacing:3px;">
                    <?= htmlspecialchars($data['input']) ?>
                </div>
                <div style="font-size:11px; color:var(--text-dim); margin-top:4px;">
                    <?= date('d.m.Y H:i', strtotime($data['created_at'])) ?>
                </div>
            </div>

            <!-- Прогноз на сегодня -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:20px 24px; margin-bottom:16px;">
                <div class="card-label">Прогноз на сегодня · день №<?= $d['prediction']['day_of_year'] ?></div>
                <p style="font-size:15px; line-height:1.7; padding-left:20px; border-left:2px solid var(--accent); margin:0;">
                    <?= htmlspecialchars($d['prediction']['text']) ?>
                </p>
            </div>

            <!-- Ряд: КБУ + Тайное имя -->
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">

                <!-- КБУ -->
                <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px; position:relative;">
                    <div class="card-corner-badge">[ LIVE ]</div>
                    <div style="display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap;">
                        <div style="flex-shrink:0;">
                            <div style="font-family:'JetBrains Mono',monospace; font-size:80px; color:var(--accent); line-height:1; text-shadow:0 0 40px rgba(0,255,65,0.5);">
                                <?= $d['kbu']['kbu'] ?>
                            </div>
                            <div style="font-family:'JetBrains Mono',monospace; font-size:10px; color:var(--text-dim); text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                                Коэффициент<br>Бесполезного<br>Успеха
                            </div>
                        </div>
                        <div style="flex:1; min-width:140px;">
                            <div style="font-size:13px; color:var(--text-dim); line-height:2.2;">
                                <div>предыдущий: <span style="color:var(--text); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['prev_kbu'] ?></span></div>
                                <div>динамика: <span style="color:<?= $dynamicsColor ?>; font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['dynamics'] ?></span></div>
                                <div style="position:relative;">
                                    <span class="tooltip-trigger" onclick="toggleTooltip(event)" style="border-bottom:1px dashed var(--text-dim); cursor:pointer;">
                                        процентиль: <span style="color:var(--text); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['percentile'] ?></span>
                                        <span class="tooltip-box" style="display:none; position:absolute; bottom:140%; left:0; background:#1a1f2b; border:1px solid var(--accent); color:var(--text); padding:10px 14px; font-size:11px; font-family:'Inter',sans-serif; border-radius:4px; white-space:normal; width:240px; z-index:100; box-shadow:0 4px 20px rgba(0,0,0,0.7); pointer-events:none;">
                                            Вы <?= $d['kbu']['percentile'] ?>-й из 100. Остальные <?= 100 - $d['kbu']['percentile'] ?> ещё более случайны.
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <div style="width:100%; height:5px; background:var(--border); border-radius:3px; margin-top:14px; overflow:hidden;">
                                <div style="width:<?= $d['kbu']['kbu'] ?>%; height:100%; background:var(--accent); border-radius:3px; box-shadow:0 0 10px rgba(0,255,65,0.4);"></div>
                            </div>
                            <div style="margin-top:12px; font-size:12px; color:var(--text-dim); display:flex; align-items:center; gap:8px;">
                                <span style="font-size:30px;">🦝</span>
                                <span>Счастье в енотах:</span>
                                <span style="color:var(--accent); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['enot_value'] ?>/<?= $d['kbu']['enot_max'] ?></span>
                                <div style="flex:1; height:5px; background:var(--border); border-radius:3px; overflow:hidden;">
                                    <div style="width:<?= $d['kbu']['enot_percent'] ?>%; height:100%; background:linear-gradient(90deg, #8B7340, var(--accent)); border-radius:3px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Тайное имя -->
                <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:20px; position:relative;">
                    <div class="card-corner-badge">[ CLASSIFIED ]</div>
                    <div class="card-label">Тайное имя</div>
                    <div style="font-family:'JetBrains Mono',monospace; font-size:12px; line-height:1.8;">
                        <div style="display:flex; align-items:baseline; gap:8px;">
                            <span style=" color:var(--text-dim); flex-shrink:0;">ИМЯ:</span>
                            <span style="color:var(--accent); font-size:15px;"><?= htmlspecialchars($d['secret']['name']) ?></span>
                        </div>
                        <div style="display:flex; align-items:baseline; gap:8px;">
                            <span style=" color:var(--text-dim); flex-shrink:0;">ТИТУЛ:</span>
                            <span style="color:var(--text); font-size:13px;"><?= htmlspecialchars($d['secret']['title']) ?></span>
                        </div>
                        <div style="display:flex; align-items:baseline; gap:8px;">
                            <span style=" color:var(--text-dim); flex-shrink:0;">УРОВЕНЬ:</span>
                            <span style="color:var(--text); font-size:13px;"><?= htmlspecialchars($d['secret']['level']) ?></span>
                        </div>
                    </div>
                    <div style="display:inline-block; margin-top:12px; padding:3px 10px; border:1px solid var(--accent); border-radius:3px; font-family:'JetBrains Mono',monospace; font-size:9px; color:var(--accent); letter-spacing:1px;">
                        ● АКТИВЕН
                    </div>
                </div>
            </div>

            <!-- Сравнительная аналитика -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px; margin-bottom:16px; position:relative;">
                <div class="card-corner-badge">[ ANALYTICS ]</div>
                <div class="card-label">Сравнительная аналитика числа <?= htmlspecialchars($data['input']) ?></div>

                <!-- Метрики -->
                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В гречке:</span>
                        <span style="color:var(--text);"><?= $d['compare']['grechka_kg'] ?> кг</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В котах:</span>
                        <span style="color:var(--text);"><?= $d['compare']['cats_count'] ?> усатых ед.</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В месяцах:</span>
                        <span style="color:var(--text);"><?= round($d['compare']['month']) ?> мес.</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В Наполеонах:</span>
                        <span style="color:var(--text);"><?= round($d['compare']['cats_count'] / 3, 1) ?> Нп</span>
                    </div>
                </div>

                <!-- Персонаж и анализ -->
                <div style="margin-bottom:8px;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:15px; color:var(--accent);">
                        <?= htmlspecialchars(mb_ucfirst($d['compare']['character_name'])) ?>
                    </span>
                    <span style="font-size:12px; color:var(--text-dim);">
                        — <?= htmlspecialchars($d['compare']['character_desc']) ?>
                    </span>
                </div>
                <div style="font-size:14px; color:var(--text); line-height:1.6; padding:14px 18px; background:rgba(0,255,65,0.04); border-left:2px solid var(--accent); border-radius:0 4px 4px 0;">
                    <?= htmlspecialchars(mb_ucfirst($d['compare']['analysis_text'])) ?>
                </div>
            </div>

            <!-- Диаграмма: Распределение ресурсов -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px; margin-bottom:16px; position:relative;">
                <div class="card-corner-badge">[ CHART ]</div>
                <div class="card-label">Куда уходят ваши ресурсы</div>
                <p style="font-size:12px; color:var(--text-dim); margin-bottom:16px;">На основе введённого числа мы определили, как распределяются ваши жизненные силы. В процентах от общего потенциала.</p>
                <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap; justify-content:center;">
                    <svg width="200" height="200" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="90" fill="none" stroke="var(--border)" stroke-width="18" />
                        <?php
                        $angle = -90;
                        foreach ($d['chart']['segments'] as $seg):
                            $sliceAngle = ($seg['percent'] / 100) * 360;
                            $endAngle = $angle + $sliceAngle;
                            $startRad = deg2rad($angle);
                            $endRad = deg2rad($endAngle);
                            $x1 = 100 + 86 * cos($startRad);
                            $y1 = 100 + 86 * sin($startRad);
                            $x2 = 100 + 86 * cos($endRad);
                            $y2 = 100 + 86 * sin($endRad);
                            $largeArc = $sliceAngle > 180 ? 1 : 0;
                        ?>
                            <path d="M 100 100 L <?= $x1 ?> <?= $y1 ?> A 86 86 0 <?= $largeArc ?> 1 <?= $x2 ?> <?= $y2 ?> Z"
                                fill="<?= $seg['color'] ?>" stroke="#0B0E14" stroke-width="2" />
                        <?php $angle = $endAngle;
                        endforeach; ?>
                    </svg>
                    <div style="font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-dim); line-height:2;">
                        <?php foreach ($d['chart']['segments'] as $seg): ?>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:10px;height:10px;border-radius:2px;background:<?= $seg['color'] ?>;flex-shrink:0;"></div>
                                <span><?= htmlspecialchars($seg['name']) ?></span>
                                <span style="color:var(--accent); margin-left:auto;"><?= $seg['percent'] ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Шеринг -->
            <div style="text-align:center; margin-top:20px;">
                <p style="font-size:11px; color:var(--text-dim); margin-bottom:8px;">Публичная ссылка:</p>
                <div style="display:flex; align-items:center; gap:8px; justify-content:center;">
                    <input type="text" readonly value="https://brednia.ru/p/<?= $hash ?>"
                        id="public-link-input"
                        style="background:var(--bg); border:1px solid var(--border); color:var(--accent); padding:10px 14px; font-size:13px; font-family:'JetBrains Mono',monospace; max-width:360px; width:100%; border-radius:4px; outline:none;"
                        onclick="this.select()">
                    <button onclick="copyToClipboard('https://brednia.ru/p/<?= $hash ?>', 'copy-btn')"
                        id="copy-btn"
                        style="background:transparent; border:1px solid var(--border); color:var(--text-dim); padding:8px 10px; border-radius:4px; cursor:pointer; transition:all 0.3s; flex-shrink:0;"
                        onmouseenter="this.style.borderColor='var(--accent)';this.style.color='var(--accent)';"
                        onmouseleave="this.style.borderColor='var(--border)';this.style.color='var(--text-dim)';"
                        title="Скопировать ссылку">
                        <svg style="width:14px;height:14px;display:inline;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Возвращает массив случайных фраз для стадии мышления.
     */
    public function getThinkingPhrases(): array
    {
        $all = $this->db->fetchAll(
            'SELECT text FROM thinking_phrases WHERE is_active = true ORDER BY RANDOM()'
        );

        $phrases = array_column($all, 'text');

        // Берём 5-9 случайных + финальная фраза
        $count = min(count($phrases), rand(5, 9));
        $selected = array_slice($phrases, 0, $count);
        $selected[] = 'Готово';

        return $selected;
    }

    /**
     * Проверяет, был ли уже анализ этого числа сегодня.
     */
    public function hasAnalysisToday(int $userId, string $input): bool
    {
        $row = $this->db->fetch(
            'SELECT id FROM analyses 
         WHERE user_id = :uid 
           AND input_value = :input 
           AND created_at::date = CURRENT_DATE
         LIMIT 1',
            ['uid' => $userId, 'input' => $input]
        );
        return $row !== null;
    }

    public function renderPublicHTML(array $data): string
    {
        // Копия renderHTML, но без:
        // - кнопки «Ещё анализ»
        // - блока с публичной ссылкой и кнопкой копирования
        // В остальном — тот же дашборд

        $d = $data['dashboard'];
        $dynamicsColor = match ($d['kbu']['dynamics_type']) {
            'up' => 'var(--accent)',
            'down' => 'var(--error)',
            default => 'var(--text-dim)',
        };

        ob_start();
    ?>
        <div id="dashboard-container">
            <!-- Заголовок -->
            <div style="text-align:center; margin-bottom:20px;">
                <div style="font-family:'JetBrains Mono',monospace; font-size:11px; text-transform:uppercase; letter-spacing:2px; color:var(--text-dim); margin-bottom:4px;">Анализ числа</div>
                <div style="font-family:'JetBrains Mono',monospace; font-size:28px; color:var(--accent); letter-spacing:3px;"><?= htmlspecialchars($data['input']) ?></div>
                <div style="font-size:11px; color:var(--text-dim); margin-top:4px;"><?= date('d.m.Y H:i', strtotime($data['created_at'])) ?></div>
            </div>

            <!-- КБУ + Тайное имя -->
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">
                <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px;">
                    <div class="card-label">Коэффициент Бесполезного Успеха</div>
                    <div style="display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap;">
                        <div>
                            <div style="font-family:'JetBrains Mono',monospace; font-size:80px; color:var(--accent); line-height:1; text-shadow:0 0 40px rgba(0,255,65,0.5);"><?= $d['kbu']['kbu'] ?></div>
                        </div>
                        <div style="flex:1; min-width:140px;">
                            <div style="font-size:13px; color:var(--text-dim); line-height:2.2;">
                                <div>вчера: <span style="color:var(--text); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['prev_kbu'] ?></span></div>
                                <div>динамика: <span style="color:<?= $dynamicsColor ?>; font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['dynamics'] ?></span></div>
                                <div>процентиль: <span style="color:var(--text); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['percentile'] ?></span></div>
                            </div>
                            <div style="width:100%; height:5px; background:var(--border); border-radius:3px; margin-top:14px; overflow:hidden;">
                                <div style="width:<?= $d['kbu']['kbu'] ?>%; height:100%; background:var(--accent); border-radius:3px;"></div>
                            </div>
                            <div style="margin-top:12px; font-size:12px; color:var(--text-dim); display:flex; align-items:center; gap:8px;">
                                <span style="font-size:30px;">🦝</span>
                                <span>Счастье в енотах:</span>
                                <span style="color:var(--accent); font-family:'JetBrains Mono',monospace;"><?= $d['kbu']['enot_value'] ?>/<?= $d['kbu']['enot_max'] ?></span>
                                <div style="flex:1; height:5px; background:var(--border); border-radius:3px; overflow:hidden;">
                                    <div style="width:<?= $d['kbu']['enot_percent'] ?>%; height:100%; background:linear-gradient(90deg, #8B7340, var(--accent)); border-radius:3px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:20px;">
                    <div class="card-label">Тайное имя</div>
                    <div style="font-family:'JetBrains Mono',monospace; font-size:12px; line-height:1.8;">
                        <div style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--text-dim); width:55px; text-align:right;">ИМЯ:</span><span style="color:var(--accent); font-size:15px;"><?= htmlspecialchars($d['secret']['name']) ?></span></div>
                        <div style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--text-dim); width:55px; text-align:right;">ТИТУЛ:</span><span style="color:var(--text); font-size:13px;"><?= htmlspecialchars($d['secret']['title']) ?></span></div>
                        <div style="display:flex; align-items:baseline; gap:8px;"><span style="color:var(--text-dim); width:55px; text-align:right;">УРОВЕНЬ:</span><span style="color:var(--text); font-size:13px;"><?= htmlspecialchars($d['secret']['level']) ?></span></div>
                    </div>
                    <div style="display:inline-block; margin-top:12px; padding:3px 10px; border:1px solid var(--accent); border-radius:3px; font-family:'JetBrains Mono',monospace; font-size:9px; color:var(--accent); letter-spacing:1px;">● АКТИВЕН</div>
                </div>
            </div>

            <!-- Прогноз -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:20px 24px; margin-bottom:16px;">
                <div class="card-label">Прогноз на день №<?= $d['prediction']['day_of_year'] ?></div>
                <p style="font-size:15px; line-height:1.7; padding-left:20px; border-left:2px solid var(--accent); margin:0;"><?= htmlspecialchars($d['prediction']['text']) ?></p>
            </div>

            <!-- Диаграмма -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px; margin-bottom:16px;">
                <div class="card-label">Куда уходят ваши ресурсы</div>
                <p style="font-size:12px; color:var(--text-dim); margin-bottom:16px;">На основе введённого числа мы определили, как распределяются ваши жизненные силы.</p>
                <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap; justify-content:center;">
                    <svg width="200" height="200" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="90" fill="none" stroke="var(--border)" stroke-width="18" />
                        <?php $angle = -90;
                        foreach ($d['chart']['segments'] as $seg):
                            $sliceAngle = ($seg['percent'] / 100) * 360;
                            $endAngle = $angle + $sliceAngle;
                            $x1 = 100 + 86 * cos(deg2rad($angle));
                            $y1 = 100 + 86 * sin(deg2rad($angle));
                            $x2 = 100 + 86 * cos(deg2rad($endAngle));
                            $y2 = 100 + 86 * sin(deg2rad($endAngle));
                            $largeArc = $sliceAngle > 180 ? 1 : 0;
                        ?>
                            <path d="M 100 100 L <?= $x1 ?> <?= $y1 ?> A 86 86 0 <?= $largeArc ?> 1 <?= $x2 ?> <?= $y2 ?> Z" fill="<?= $seg['color'] ?>" stroke="#0B0E14" stroke-width="2" />
                        <?php $angle = $endAngle;
                        endforeach; ?>
                    </svg>
                    <div style="font-family:'JetBrains Mono',monospace; font-size:12px; color:var(--text-dim); line-height:2;">
                        <?php foreach ($d['chart']['segments'] as $seg): ?>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:10px;height:10px;border-radius:2px;background:<?= $seg['color'] ?>;"></div>
                                <span><?= htmlspecialchars($seg['name']) ?></span>
                                <span style="color:var(--accent); margin-left:auto;"><?= $seg['percent'] ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Сравнительная аналитика -->
            <div class="card" style="background:var(--bg-card); border:1px solid var(--border); border-radius:6px; padding:24px; margin-bottom:16px;">
                <div class="card-label">Сравнительная аналитика числа <?= htmlspecialchars($data['input']) ?></div>
                <div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В гречке:</span>
                        <span style="color:var(--text);"><?= $d['compare']['grechka_kg'] ?> кг</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В котах:</span>
                        <span style="color:var(--text);"><?= $d['compare']['cats_count'] ?> усатых ед.</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В месяцах:</span>
                        <span style="color:var(--text);"><?= round($d['compare']['month']) ?> мес.</span>
                    </div>
                    <div style="background:rgba(255,176,0,0.06); border:1px solid rgba(255,176,0,0.25); border-radius:4px; padding:8px 14px; font-family:'JetBrains Mono',monospace; font-size:12px;">
                        <span style="color:var(--text-dim);">В Наполеонах:</span>
                        <span style="color:var(--text);"><?= round($d['compare']['napoleon']) ?> Нп</span>
                    </div>
                </div>
                <div style="margin-bottom:8px;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:15px; color:var(--accent);"><?= htmlspecialchars(mb_ucfirst($d['compare']['character_name'])) ?></span>
                    <span style="font-size:12px; color:var(--text-dim);">— <?= htmlspecialchars($d['compare']['character_desc']) ?></span>
                </div>
                <div style="font-size:14px; color:var(--text); line-height:1.6; padding:14px 18px; background:rgba(0,255,65,0.04); border-left:2px solid var(--accent); border-radius:0 4px 4px 0;"><?= htmlspecialchars(mb_ucfirst($d['compare']['analysis_text'])) ?></div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }

    //пасхалочки
    private function generate42(int $userId): int
    {
        $dashboard = [
            'input' => '42',
            'kbu' => [
                'kbu' => 42,
                'prev_kbu' => 42,
                'dynamics' => 'без изменений ▬',
                'dynamics_type' => 'flat',
                'percentile' => 42,
                'enot_value' => 4.2,
                'enot_max' => 17.0,
                'enot_percent' => 24.7,
            ],
            'compare' => [
                'character_name' => 'Дуглас Адамс',
                'character_desc' => 'Автор "Автостопом по галактике"',
                'analysis_text' => 'Ответ на главный вопрос жизни, вселенной и всего такого — 42. Ваше число идеально. Не требуйте большего.',
                'grechka_kg' => 42,
                'month' => 42,
                'cats_count' => 4.2,
                'napoleon' => 4.2,
                'action' => 'понял(а) смысл жизни',
                'reaction' => 'абсолютный восторг',
            ],
            'chart' => [
                'segments' => [
                    ['name' => 'Смысл жизни', 'percent' => 42, 'color' => '#00FF41'],
                    ['name' => 'Вселенная', 'percent' => 42, 'color' => '#FFB000'],
                    ['name' => 'Всё остальное', 'percent' => 8, 'color' => '#FF3333'],
                    ['name' => 'Еноты', 'percent' => 4, 'color' => '#8B7340'],
                    ['name' => 'Чай', 'percent' => 4, 'color' => '#4A90D9'],
                ],
            ],
            'prediction' => [
                'day_of_year' => (int) date('z') + 1,
                'text' => 'Сегодня — день, когда вы уже знаете ответ. Осталось понять вопрос. Носите с собой полотенце.',
            ],
            'secret' => [
                'name' => 'Форд Префект',
                'title' => 'Путешественник-автостопщик',
                'level' => 'БЕТА',
            ],
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        $publicHash = substr(md5(random_bytes(16)), 0, 12);

        $this->db->query(
            'INSERT INTO analyses (user_id, input_value, dashboard_json, public_hash)
         VALUES (:uid, :input, :json, :hash)',
            [
                'uid' => $userId,
                'input' => '42',
                'json' => json_encode($dashboard, JSON_UNESCAPED_UNICODE),
                'hash' => $publicHash,
            ]
        );

        $analysisId = (int) $this->db->lastInsertId();

        // Проверка ачивок для пасхалки 42
        $achievements = new Achievements($this->db);
        $achievementData = $achievements->extractAchievementData($dashboard);
        $unlocked = $achievements->checkAndUnlock($userId, $achievementData, $analysisId);

        if (!empty($unlocked)) {
            $_SESSION['new_achievements'] = array_merge(
                $_SESSION['new_achievements'] ?? [],
                $unlocked
            );
        }

        return $analysisId;
    }
}
