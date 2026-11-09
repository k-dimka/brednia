<?php

declare(strict_types=1);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Router.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Dashboard.php';
require_once __DIR__ . '/src/Achievements.php';
require_once __DIR__ . '/src/helpers.php';

$db = new Database();
$GLOBALS['db'] = $db;

$router = new Router();

// ============================================================================
//  ПУБЛИЧНЫЕ МАРШРУТЫ (без авторизации)
// ============================================================================

/**
 * GET / — Главная страница.
 * Для неавторизованных: лендинг.
 * Для авторизованных: личный кабинет.
 */
$router->get('/', function () use ($db) {
    if (Auth::check()) {
        require __DIR__ . '/templates/cabinet.php';
    } else {
        require __DIR__ . '/templates/landing.php';
    }
});

/**
 * GET /login — Страница входа.
 */
$router->get('/login', function () {
    require __DIR__ . '/templates/login.php';
});

/**
 * POST /login — Обработка формы входа.
 */
$router->post('/login', function () use ($db) {
    if (!empty($_POST['website'])) {
        header('Location: /');
        return;
    }
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (Auth::login($db, $email, $password)) {
        header('Location: /');
    } else {
        $error = 'Неверный email или пароль';
        require __DIR__ . '/templates/login.php';
    }
});

/**
 * GET /register — Страница регистрации.
 */
$router->get('/register', function () {
    require __DIR__ . '/templates/register.php';
});

/**
 * POST /register — Обработка формы регистрации.
 */
$router->post('/register', function () use ($db) {
    if (!empty($_POST['website'])) {
        header('Location: /');
        return;
    }
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($password !== $passwordConfirm) {
        $error = 'Пароли не совпадают';
        require __DIR__ . '/templates/register.php';
        return;
    }

    $error = Auth::register($db, $email, $password);
    if ($error === true) {
        header('Location: /');
    } else {
        require __DIR__ . '/templates/register.php';
    }
});

/**
 * GET /logout — Выход из системы.
 */
$router->get('/logout', function () {
    Auth::logout();
    header('Location: /');
});

// ============================================================================
//  ЗАЩИЩЁННЫЕ МАРШРУТЫ (требуют авторизации)
// ============================================================================

/**
 * POST /analyze — Генерация анализа (AJAX).
 * Принимает число, генерирует дашборд, возвращает HTML и публичный хеш.
 * Бесплатно, без списаний.
 */
$router->post('/analyze', function () use ($db) {
    Auth::require();
    $force = ($_POST['force'] ?? '') === 'true';
    $userId = $_SESSION['user_id'];
    $input = trim($_POST['value'] ?? '');
    $input = str_replace(',', '.', $input);

    if ($input === '' || !is_numeric($input)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'invalid_input', 'message' => 'Введите число.']);
        return;
    }

    $dashboard = new Dashboard($db);

    if (!$force && $dashboard->hasAnalysisToday($userId, $input)) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'duplicate',
            'message' => 'Сегодня уже был анализ этого числа. Показать ещё раз? Результат будет тем же.',
        ]);
        return;
    }

    $analysisId = $dashboard->generate($userId, $input);
    $data = $dashboard->getById($analysisId, $userId);

    $newAchievements = $_SESSION['new_achievements'] ?? [];
    unset($_SESSION['new_achievements']);

    header('Content-Type: application/json');
    echo json_encode([
        'html' => $dashboard->renderHTML($data),
        'public_hash' => $data['public_hash'],
        'achievements' => $newAchievements,
    ]);
});

/**
 * GET /dashboard/{slug} — Просмотр сохранённого дашборда по slug.
 */
$router->get('/dashboard/{slug}', function ($slug) use ($db) {
    Auth::require();
    $dashboard = new Dashboard($db);
    $data = $dashboard->getBySlug($slug, $_SESSION['user_id']);
    if (!$data) {
        http_response_code(404);
        require __DIR__ . '/templates/error.php';
        return;
    }
    require __DIR__ . '/templates/cabinet.php';
});

/**
 * GET /p/{hash} — Публичный дашборд.
 */
$router->get('/p/{hash}', function ($hash) use ($db) {
    $dashboard = new Dashboard($db);
    $data = $dashboard->getByPublicHash($hash);
    if (!$data) {
        http_response_code(404);
        require __DIR__ . '/templates/error.php';
        return;
    }
    require __DIR__ . '/templates/public_dashboard.php';
});

/**
 * GET /history — История анализов.
 */
$router->get('/history', function () use ($db) {
    Auth::require();
    require __DIR__ . '/templates/cabinet.php';
});

/**
 * GET /offer — Публичная оферта.
 */
$router->get('/offer', function () {
    require __DIR__ . '/templates/offer.php';
});

/**
 * GET /thinking-phrases — Случайные фразы для анимации «мышления».
 */
$router->get('/thinking-phrases', function () use ($db) {
    Auth::require();
    $dashboard = new Dashboard($db);
    header('Content-Type: application/json');
    echo json_encode($dashboard->getThinkingPhrases());
});

/**
 * GET /achievements — Страница достижений.
 */
$router->get('/achievements', function () {
    Auth::require();
    require __DIR__ . '/templates/achievements.php';
});

// ============================================================================
//  ЗАПУСК РОУТЕРА
// ============================================================================
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
