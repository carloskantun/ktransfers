<?php
declare(strict_types=1);
namespace App\Core;

use PDO;

class Auth {
    private const SESSION_USER_KEY = '_auth_user_id';

    public static function attempt(string $email, string $password): bool
    {
        $db = DB::connection();
        $stmt = $db->prepare(
            'SELECT id, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !is_array($user)) {
            return false;
        }

        if (!(bool) ($user['is_active'] ?? false)) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER_KEY] = (int) $user['id'];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_KEY]);
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_USER_KEY]) && is_int($_SESSION[self::SESSION_USER_KEY]);
    }

    public static function id(): ?int
    {
        if (!self::check()) {
            return null;
        }

        return $_SESSION[self::SESSION_USER_KEY];
    }

    public static function user(): ?array
    {
        $userId = self::id();
        if ($userId === null) {
            return null;
        }

        $db = DB::connection();
        try {
            $stmt = $db->prepare(
                'SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.is_active,
                    u.provider_id,
                    p.name AS provider_name
                 FROM users u
                 LEFT JOIN providers p ON p.id = u.provider_id
                 WHERE u.id = :id
                 LIMIT 1'
            );
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
        } catch (\Throwable) {
            $stmt = $db->prepare(
                'SELECT id, name, email, is_active FROM users WHERE id = :id LIMIT 1'
            );
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();
        }

        return is_array($user) ? $user : null;
    }
}
