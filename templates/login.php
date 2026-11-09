<?php
$pageTitle = 'Вход';
$noindex = true;
ob_start();
?>

<div class="card" style="max-width:400px; margin:40px auto;">
    <div class="card-label">🔐 Вход в систему</div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="/login" method="POST">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-input"
                placeholder="your@email.ru" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" class="form-input"
                placeholder="Не менее 6 символов" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;">Войти</button>
    </form>

    <p style="text-align:center; margin-top:16px; font-size:13px; color:var(--text-dim);">
        Нет аккаунта? <a href="/register" style="color:var(--accent);">Зарегистрироваться</a>
    </p>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>