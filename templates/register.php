<?php
$pageTitle = 'Регистрация';
$noindex = true;
ob_start();
?>

<div class="card" style="max-width:400px; margin:40px auto;">
    <div class="card-label">🆕 Регистрация</div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="/register" method="POST" id="register-form">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-input"
                placeholder="your@email.ru" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Пароль (минимум 6 символов)</label>
            <input type="password" name="password" id="password" class="form-input"
                placeholder="Придумайте пароль" required minlength="6">
        </div>
        <div class="form-group">
            <label>Подтвердите пароль</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-input"
                placeholder="Повторите пароль" required minlength="6">
        </div>
        <div id="password-error" class="error-message" style="display:none;">
            Пароли не совпадают
        </div>

        <div style="position:absolute; left:-9999px;" aria-hidden="true">
            <label for="website">Оставьте это поле пустым</label>
            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="btn-primary" style="width:100%;">Зарегистрироваться</button>
    </form>

    <p style="text-align:center; margin-top:16px; font-size:13px; color:var(--text-dim);">
        Уже есть аккаунт? <a href="/login" style="color:var(--accent);">Войти</a>
    </p>
</div>

<script>
    document.getElementById('register-form').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;
        const errorEl = document.getElementById('password-error');
        _tmr.push({
            type: 'reachGoal',
            id: 3781012,
            goal: 'register'
        });
        if (pass !== confirm) {
            e.preventDefault();
            errorEl.style.display = 'block';
            return false;
        }
        errorEl.style.display = 'none';
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>