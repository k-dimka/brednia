<?php
$code = http_response_code() ?: 500;
$messages = [
    400 => 'Некорректный запрос. Даже бред должен быть оформлен правильно.',
    401 => 'Сюда нельзя без авторизации. Зарегистрируйтесь и возвращайтесь.',
    403 => 'Доступ запрещён. Возможно, вы не тот, за кого себя выдаёте.',
    404 => 'Страница не найдена. Как и смысл жизни.',
    429 => 'Слишком много запросов. Даже бессмысленная аналитика имеет лимиты.',
    500 => 'Внутренняя ошибка сервера. Бред достиг критической массы.',
    502 => 'Сервер временно не в себе. Ушёл думать о вечном.',
    503 => 'Сервис временно недоступен. Еноты взяли выходной.',
    504 => 'Сервер думал слишком долго и уснул. Попробуйте позже.',
];

$message = $messages[$code] ?? 'Что-то пошло не так. Но мы не знаем, что именно.';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка <?= $code ?> — Бредня.ру</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="/templates/img/favicon.svg">
    <style>
        :root {
            --bg: #0B0E14;
            --text: #FFB000;
            --accent: #00FF41;
            --error: #FF3333;
            --border: #2A2A2A;
            --text-dim: #8B7340;
            --bg-card: #0F131C;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 65, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 20px;
        }

        .error-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 120px;
            color: var(--error);
            text-shadow: 0 0 60px rgba(255, 51, 51, 0.5);
            line-height: 1;
            margin-bottom: 16px;
        }

        .error-title {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--text-dim);
            margin-bottom: 24px;
        }

        .terminal {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px 24px;
            max-width: 500px;
            margin: 0 auto 32px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            color: var(--text-dim);
            text-align: left;
            line-height: 1.8;
        }

        .terminal .prompt {
            color: var(--accent);
        }

        .terminal .output {
            color: var(--text);
        }

        .btn-primary {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 12px 28px;
            font-size: 14px;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--accent);
            color: var(--bg);
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.4);
        }

        .footer {
            margin-top: 40px;
            font-size: 11px;
            color: var(--text-dim);
            opacity: 0.6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-code"><?= $code ?></div>
        <div class="error-title"><?= $code >= 500 ? 'Ошибка сервера' : 'Ошибка запроса' ?></div>

        <div class="terminal">
            <span class="prompt">kbu@analytics</span>:<span class="output">~</span>$ ./status --check<br>
            <span class="output"><?= htmlspecialchars($message) ?></span><br>
            <span style="color:var(--text-dim);">exit code: <?= $code ?></span>
        </div>

        <a href="/" class="btn-primary">Вернуться на главную</a>

        <div class="footer">
            Бредня.ру — даже ошибки здесь со смыслом
        </div>
    </div>
</body>

</html>