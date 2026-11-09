<?php
$pageTitle = 'Публичный дашборд — Бредня.ру';
$d = $data['dashboard'];

$db = $GLOBALS['db'];
$dashboard = new Dashboard($db);
ob_start();
?>

<?= $dashboard->renderPublicHTML($data) ?>

<div class="card" style="background:var(--bg-card); border:1px solid var(--accent); border-radius:6px; padding:24px; text-align:center; margin-top:24px;">
    <p style="color:var(--text); font-size:15px; margin-bottom:12px;">
        Хотите такой же анализ для своего числа?
    </p>
    <p style="color:var(--text-dim); font-size:13px; margin-bottom:16px;">
        Зарегистрируйтесь и получите персональный дашборд.
    </p>
    <a href="/register" class="btn-primary" style="display:inline-block; text-decoration:none; font-size:14px; padding:12px 28px;">
        Зарегистрироваться
    </a>
</div>

<?php
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анализ числа <?= htmlspecialchars($data['input']) ?> — Бредня.ру</title>
    <meta name="description" content="Персональный дашборд для числа <?= htmlspecialchars($data['input']) ?>: КБУ <?= $d['kbu']['kbu'] ?>/100, счастье в енотах <?= $d['kbu']['enot_value'] ?>/17, прогноз на день и сравнение с <?= htmlspecialchars($d['compare']['character_name']) ?>. Сгенерируйте свой за 5₽ на Бредня.ру.">
    <meta name="keywords" content="анализ числа, дашборд, КБУ, коэффициент бесполезного успеха, Бредня">
    <link rel="canonical" href="https://brednia.ru/p/<?= $data['public_hash'] ?>">
    <meta property="og:title" content="Анализ числа <?= htmlspecialchars($data['input']) ?> — Бредня.ру">
    <meta property="og:description" content="КБУ <?= $d['kbu']['kbu'] ?>/100, счастье в енотах <?= $d['kbu']['enot_value'] ?>/17. Узнайте свой ответ на главный вопрос жизни за 5₽.">
    <meta property="og:url" content="https://brednia.ru/p/<?= $data['public_hash'] ?>">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/svg+xml" href="/templates/img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
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
            flex-direction: column;
            align-items: center;
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
            width: 100%;
            max-width: 900px;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .btn-primary {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 14px 28px;
            font-size: 15px;
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

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .card-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-dim);
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="margin-bottom:10px;">
            <a href="/" style="text-decoration:none; display:flex; align-items:center; gap:16px;">
                <img src="/templates/img/logo.svg" alt="Бредня.ру" style="height:48px; width:auto;">
                <h1 style="font-family:'JetBrains Mono',monospace; font-size:32px; color:var(--accent); letter-spacing:3px; margin:0;">
                    Бредн.ру
                </h1>
            </a>
        </div>

        <?= $content ?>
    </div>
</body>

</html>