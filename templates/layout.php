<?php
$isAuthenticated = Auth::check();
if ($isAuthenticated) {
    $db = $GLOBALS['db'];
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бредня.ру — <?= $pageTitle ?></title>
    <?php if (!empty($noindex)): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <meta name="description"
        content="Введите любое число и получите персональный дашборд: КБУ, прогноз на день, сравнение с Наполеоном и уровень счастья в енотах. Серьёзная аналитика про вашу никчемность за 5₽.">
    <meta name="keywords"
        content="аналитика, дашборд, шуточная аналитика, коэффициент бесполезного успеха, КБУ, прогноз, бред, развлечение">
    <link rel="icon" href="/templates/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <meta name="yandex-verification" content="b09effc20138dbf5" />
    <script type="text/javascript">
        (function(m, e, t, r, i, k, a) {
            m[i] = m[i] || function() {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=110873829', 'ym');

        ym(110873829, 'init', {
            ssr: true,
            webvisor: true,
            clickmap: true,
            ecommerce: "dataLayer",
            referrer: document.referrer,
            url: location.href,
            accurateTrackBounce: true,
            trackLinks: true
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/110873829" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>

    <!-- Top.Mail.Ru counter -->
    <script type="text/javascript">
        var _tmr = window._tmr || (window._tmr = []);
        _tmr.push({
            id: "3781012",
            type: "pageView",
            start: (new Date()).getTime()
        });
        (function(d, w, id) {
            if (d.getElementById(id)) return;
            var ts = d.createElement("script");
            ts.type = "text/javascript";
            ts.async = true;
            ts.id = id;
            ts.src = "https://top-fwz1.mail.ru/js/code.js";
            var f = function() {
                var s = d.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(ts, s);
            };
            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else {
                f();
            }
        })(document, window, "tmr-code");
    </script>
    <noscript>
        <div><img src="https://top-fwz1.mail.ru/counter?id=3781012;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div>
    </noscript>
    <!-- /Top.Mail.Ru counter -->

    <noscript>
        <div><img src="https://top-fwz1.mail.ru/counter?id=3781012;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div>
    </noscript>

    <style>
        :root {
            --bg: #0B0E14;
            --text: #FFB000;
            --accent: #00FF41;
            --error: #FF3333;
            --border: #7e7068;
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
            position: relative;
            overflow-x: hidden;
            padding: 0 6px;
            font-size: 14px;
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

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(0deg,
                    transparent, transparent 2px,
                    rgba(0, 0, 0, 0.03) 2px, rgba(0, 0, 0, 0.03) 4px);
            pointer-events: none;
            z-index: 0;
            animation: scanlines 8s linear infinite;
        }

        @keyframes scanlines {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(4px);
            }
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 100%;
            padding: 10px 6px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== TOP-BAR (МОБИЛЬНАЯ ВЕРСИЯ - БАЗОВАЯ) ===== */
        .top-bar {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            width: 100%;
            margin-bottom: 20px;
        }

        .top-bar-logo {
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .top-bar-logo img {
            height: 28px;
            width: auto;
        }

        .top-bar-logo span {
            font-family: 'JetBrains Mono', monospace;
            color: var(--accent);
            font-size: 18px;
            letter-spacing: 2px;
        }

        .top-bar-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .top-bar-buttons {
            width: 100%;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .top-bar-btn {
            font-size: 16px;
            font-family: 'JetBrains Mono', monospace;
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--text-dim);
            border-radius: 3px;
            transition: all 0.3s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: var(--text-dim);
            flex-shrink: 0;
            width: auto;
        }

        .top-bar-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 12px rgba(0, 255, 65, 0.2);
        }

        .top-bar-btn.accent {
            color: var(--accent);
        }

        .top-bar-btn.logout:hover {
            border-color: var(--error);
            color: var(--error);
        }

        .achievement-badge {
            background: var(--bg);
            color: var(--accent);
            padding: 0 6px;
            border-radius: 2px;
            font-size: 10px;
        }

        /* ===== ОСТАЛЬНЫЕ СТИЛИ ===== */
        .btn-topup,
        .btn-logout,
        .btn-login,
        .btn-register {
            background: transparent;
            border: 1px solid var(--text-dim);
            color: var(--text-dim);
            padding: 5px 10px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            cursor: pointer;
            border-radius: 3px;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            white-space: nowrap;
            text-decoration: none;
            display: inline-block;
        }

        .btn-topup:hover,
        .btn-logout:hover,
        .btn-login:hover,
        .btn-register:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 12px rgba(0, 255, 65, 0.2);
        }

        .btn-pay {
            background: transparent;
            border: 1px solid var(--text-dim);
            color: var(--text-dim);
            font-family: 'JetBrains Mono', monospace;
            padding: 10px 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 3px;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            white-space: nowrap;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;

        }

        .btn-pay:hover {
            border-color: var(--accent);
            color: var(--accent);
            box-shadow: 0 0 12px rgba(0, 255, 65, 0.2);
        }

        .btn-primary {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 12px 20px;
            font-size: 13px;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .btn-primary:hover {
            background: var(--accent);
            color: var(--bg);
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.4);
        }

        .history {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .block-history {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: border-color 0.3s;
        }

        .kbu {
            text-align: center;
            min-width: 160px;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 16px 14px;
            margin-bottom: 16px;
        }

        .card-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-dim);
            margin-bottom: 12px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 1px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--accent);
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'JetBrains Mono', monospace;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-dim);
        }

        .error-message {
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .success-message {
            background: rgba(0, 255, 65, 0.1);
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .footer-sync {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8px;
            color: var(--text-dim);
            margin-top: 32px;
            letter-spacing: 1px;
        }

        .footer-sync span {
            color: var(--accent);
        }

        .text-center {
            text-align: center;
        }

        .mb-16 {
            margin-bottom: 16px;
        }

        .mb-24 {
            margin-bottom: 24px;
        }

        .mt-16 {
            margin-top: 16px;
        }

        h1 {
            font-size: 24px !important;
        }

        h2 {
            font-size: 16px !important;
        }

        /* Скроллбар терминала */
        #terminal-header::-webkit-scrollbar {
            width: 6px;
        }

        #terminal-header::-webkit-scrollbar-track {
            background: var(--bg-card);
            border-radius: 3px;
        }

        #terminal-header::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        #terminal-header::-webkit-scrollbar-thumb:hover {
            background: var(--accent);
        }

        #terminal-header {
            scrollbar-width: thin;
            scrollbar-color: var(--border) var(--bg-card);
        }

        /* Тултип */
        .tooltip-trigger.active .tooltip-box {
            display: block !important;
        }

        @media (hover: hover) {
            .tooltip-trigger:hover .tooltip-box {
                display: block !important;
            }
        }

        /* Скрыть стрелки у input[type=number] */
        input[type="number"] {
            -webkit-appearance: none;
            -moz-appearance: textfield;
            appearance: none;
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            appearance: none;
            margin: 0;
        }

        input[type="number"]::-ms-clear,
        input[type="number"]::-ms-reveal {
            display: none;
        }

        /* Анимации для достижений */
        @keyframes glitchOverlay {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }

            20% {
                transform: scale(1.1);
            }

            40% {
                transform: scale(0.9);
            }

            60% {
                transform: scale(1.05);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes glitchCard {
            0% {
                transform: translate(10px, -10px) rotate(2deg);
            }

            10% {
                transform: translate(-10px, 10px) rotate(-2deg);
            }

            20% {
                transform: translate(5px, -5px) rotate(1deg);
            }

            30% {
                transform: translate(-5px, 5px) rotate(-1deg);
            }

            40% {
                transform: translate(3px, -3px);
            }

            50% {
                transform: translate(-3px, 3px);
            }

            60% {
                transform: translate(2px, -2px);
            }

            70% {
                transform: translate(-2px, 2px);
            }

            100% {
                transform: translate(0, 0);
            }
        }

        @keyframes glitchBorder {
            0% {
                transform: translate(-2px, 2px);
            }

            50% {
                transform: translate(2px, -2px);
            }

            100% {
                transform: translate(-2px, 2px);
            }
        }

        @keyframes glitchText {
            0% {
                transform: translate(-1px, 1px) skewX(-2deg);
            }

            25% {
                transform: translate(1px, -1px) skewX(2deg);
            }

            50% {
                transform: translate(-1px, -1px) skewX(-1deg);
            }

            75% {
                transform: translate(1px, 1px) skewX(1deg);
            }

            100% {
                transform: translate(0, 0);
            }
        }

        @keyframes spinGlitch {
            0% {
                transform: rotate(0deg) scale(0);
                opacity: 0;
            }

            50% {
                transform: rotate(720deg) scale(1.5);
                opacity: 1;
            }

            70% {
                transform: rotate(540deg) scale(0.9);
            }

            100% {
                transform: rotate(720deg) scale(1);
            }
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 0.2;
            }

            50% {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ===== ДЕСКТОПНАЯ ВЕРСИЯ (от 768px) ===== */
        @media (min-width: 768px) {
            body {
                padding: 0;
                font-size: 16px;
            }

            .container {
                padding: 20px;
                max-width: 900px;
            }

            /* TOP-BAR ДЛЯ ДЕСКТОПА */
            .top-bar {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }

            .top-bar-logo {
                justify-content: flex-start;
                flex-shrink: 0;
            }

            .top-bar-right {
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .top-bar-buttons {
                width: auto;
            }

            .top-bar-btn {
                width: auto;
                font-size: 14px;
                padding: 8px 14px;
            }

            /* ОСТАЛЬНЫЕ ДЕСКТОПНЫЕ СТИЛИ */
            .btn-primary {
                padding: 14px 28px;
                font-size: 15px;
                width: auto;
            }

            .btn-topup,
            .btn-logout,
            .btn-login,
            .btn-register {
                font-size: 11px;
                padding: 6px 12px;
            }

            .kbu {
                flex: 1;
                text-align: left;
                min-width: 160px;
            }

            .card {
                padding: 24px;
            }

            .card-label {
                font-size: 12px;
                letter-spacing: 2px;
            }

            .form-input {
                padding: 12px 16px;
                font-size: 16px;
            }

            .footer-sync {
                font-size: 10px;
            }

            h1 {
                font-size: 36px !important;
            }

            h2 {
                font-size: 22px !important;
            }
        }
    </style>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "Бредня.ру",
            "url": "https://brednia.ru",
            "description": "Шуточная аналитика чисел. Персональный дашборд за 5₽.",
            "applicationCategory": "Entertainment",
            "operatingSystem": "All"
        }
    </script>
</head>

<body>
    <div id="analysis-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:var(--bg); z-index:1000; font-family:'JetBrains Mono',monospace; padding:40px 20px; overflow-y:auto;">
        <div style="max-width:700px; margin:0 auto;">
            <div style="color:var(--text-dim); font-size:13px; margin-bottom:8px;">
                kbu@analytics:~$ ./analyze --deep --confidence=max
            </div>
            <div id="analysis-lines" style="color:var(--text-dim); font-size:13px; line-height:1.8; min-height:200px;"></div>
            <div style="color:var(--accent); font-size:13px; margin-top:8px;">
                <span id="analysis-spinner">⠋</span> <span id="analysis-status">Инициализация...</span>
            </div>
        </div>
    </div>
    <div class="container">
        <?php if ($isAuthenticated): ?>
            <div class="top-bar">
                <a href="/" class="top-bar-left" style="text-decoration:none;">
                    <img src="/templates/img/favicon.svg" alt="Бредня.ру" style="height:28px; width:auto;">
                    <span style="font-family:'JetBrains Mono',monospace; color:var(--accent); font-size:18px; letter-spacing:2px;">БРЕДНЯ</span>
                </a>
                <div class="top-bar-right">
                    <?php $ach = new Achievements($db);
                    $achCount = $ach->getAchievementsCount(Auth::id()); ?>
                    <div class="top-bar-buttons">
                        <a href="/achievements" class="top-bar-btn accent">
                            Достижения 🏆 <span class="achievement-badge"><?= $achCount ?></span>
                        </a>
                        <a href="/logout" class="top-bar-btn logout">Выйти</a>
                    </div>
                </div>
            </div>
            <!-- </div> -->
        <?php else: ?>
            <div class="top-bar" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <a href="/" style="text-decoration:none; flex-shrink:0;">
                    <div style="font-family:'JetBrains Mono',monospace; color:var(--accent); font-size:20px; letter-spacing:2px;">
                        БРЕДНЯ
                    </div>
                </a>
                <div style="display:flex; gap:8px;">
                    <a href="/login" class="btn-login">Войти</a>
                    <a href="/register" class="btn-register">Регистрация</a>
                </div>
            </div>
        <?php endif; ?>
        <?= $content ?>

        <div class="footer" style="margin-top:auto;">
            <div class="footer-sync">
                Last sync: <span id="syncTime">--:--:--</span>
            </div>
            <div style="text-align:center; margin-top:4px; font-size:10px; color:var(--text-dim); opacity:0.6;">
                <a href="/" style="color:var(--text-dim);">© БРЕДНЯ.РУ</a> · Наваял Дмитрий Калашник
            </div>
        </div>
    </div>
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('syncTime').textContent =
                now.toLocaleTimeString('ru-RU', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
        }
        setInterval(updateTime, 1000);
        updateTime();

        function disableButton() {
            const btn = document.getElementById('sbp-submit');
            if (btn.disabled) return false;
            btn.disabled = true;
            btn.textContent = '⌛ Ожидайте...';
            return true;
        }
    </script>
</body>

</html>