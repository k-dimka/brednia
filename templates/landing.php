<?php
$pageTitle = 'шуточная аналитика чисел | Персональный дашборд';
ob_start();
?>

<div class="text-center mb-24" style="padding: 40px 0 20px;">
    <h1 style="font-family:'JetBrains Mono',monospace; font-size:36px; color:var(--accent); letter-spacing:3px; margin-bottom:12px;">
        БРЕДНЯ.РУ
    </h1>
    <h2 style="font-size:18px; color:var(--text-dim); max-width:500px; margin:0 auto; line-height:1.6;">
        Числа знают о вас больше, чем вы думаете.
    </h2>
    <h3 style="font-size:18px; color:var(--text-dim); max-width:500px; margin:0 auto; line-height:1.6;">
        Получите персональный дашборд и узнайте ответ на главный вопрос жизни.
    </h3>
</div>

<div class="card text-center" style="max-width:500px; margin:0 auto;">
    <div class="card-label">Получите анализ числа</div>
    <form id="analyze-form" action="/analyze" method="POST" style="display:flex; gap:12px; flex-wrap:wrap; justify-content:center;">
        <input type="text" name="value" class="form-input"
            placeholder="Введите значимую цифру..."
            style="flex:1; min-width:200px; font-size:20px; letter-spacing:2px;"
            value="42" required>
        <button type="submit" class="btn-primary" id="analyze-btn">▶ Анализ</button>
    </form>
    <p style="font-size:14px; color:var(--text-dim); margin-top:12px;">
        Внутри тебя ждут ачивки за достижения
    </p>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:32px;" class="mb-24">
    <div class="card" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:8px;">🦝</div>
        <div style="font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--text-dim);">
            Уровень счастья в енотах
        </div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:8px;">📡</div>
        <div style="font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--text-dim);">
            Прогноз на каждый день
        </div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:8px;">🥧</div>
        <div style="font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--text-dim);">
            Распределение ресурсов
        </div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:8px;">🕵️</div>
        <div style="font-family:'JetBrains Mono',monospace; font-size:13px; color:var(--text-dim);">
            Тайное имя в параллельной вселенной
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>