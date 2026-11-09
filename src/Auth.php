<?php

class Auth
{
    /**
     * Проверяет, авторизован ли пользователь.
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Требует авторизацию. Если пользователь не вошёл — редирект на /login.
     */
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Возвращает ID текущего пользователя или null.
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Попытка входа. Возвращает true при успехе, false при ошибке.
     */
    public static function login(Database $db, string $email, string $password): bool
    {
        $user = $db->fetch(
            'SELECT id, password_hash FROM users WHERE email = :email',
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user_id'] = (int) $user['id'];

        // Обновляем дату последнего входа
        $db->query(
            'UPDATE users SET last_login_at = NOW() WHERE id = :id',
            ['id' => $user['id']]
        );

        return true;
    }

    /**
     * Регистрация нового пользователя.
     * Возвращает true при успехе, строку с ошибкой при провале.
     */
    public static function register(Database $db, string $email, string $password): string|bool
    {
        // Валидация email
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Некорректный email';
        }

        // Валидация пароля
        if (strlen($password) < 6) {
            return 'Пароль должен быть не менее 6 символов';
        }

        // Проверка на существование
        $exists = $db->fetch(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email]
        );
        if ($exists) {
            return 'Пользователь с таким email уже существует';
        }

        // Создаём
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->query(
            'INSERT INTO users (email, password_hash, balance) VALUES (:email, :hash, 0)',
            ['email' => $email, 'hash' => $hash]
        );

        // Авторизуем сразу
        $_SESSION['user_id'] = (int) $db->lastInsertId();

        return true;
    }

    /**
     * Выход.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
}
