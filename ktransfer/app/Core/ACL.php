<?php
declare(strict_types=1);
namespace App\Core;

use PDO;
use Throwable;

class ACL {
    public static function userHasPermission(int $userId, string $permissionCode): bool
    {
        $db = DB::connection();

        if (!self::permissionsAreSeeded($db)) {
            return true;
        }

        // Admin y superadmin tienen acceso total sin depender del seed de permisos.
        if (self::userHasRole($userId, 'superadmin') || self::userHasRole($userId, 'admin')) {
            return true;
        }

        if ($permissionCode === 'bookings.create' && self::userHasPermission($userId, 'bookings.manage')) {
            return true;
        }

        $sql = "
            SELECT COUNT(*) as count
            FROM user_roles ur
            INNER JOIN role_permissions rp ON rp.role_id = ur.role_id
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id
              AND p.code = :permission_code
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'permission_code' => $permissionCode,
        ]);

        $row = $stmt->fetch();
        return is_array($row) && ((int) ($row['count'] ?? 0)) > 0;
    }

    public static function currentUserCan(string $permissionCode): bool
    {
        $userId = Auth::id();
        if ($userId === null) {
            return false;
        }

        return self::userHasPermission($userId, $permissionCode);
    }

    public static function userHasRole(int $userId, string $roleCode): bool
    {
        $db = DB::connection();

        $sql = "
            SELECT COUNT(*) as count
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
              AND r.code = :role_code
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'role_code' => $roleCode,
        ]);

        $row = $stmt->fetch();
        return is_array($row) && ((int) ($row['count'] ?? 0)) > 0;
    }

    public static function currentUserHasRole(string $roleCode): bool
    {
        $userId = Auth::id();
        if ($userId === null) {
            return false;
        }

        return self::userHasRole($userId, $roleCode);
    }

    public static function getUserPermissions(int $userId): array
    {
        $db = DB::connection();

        $sql = "
            SELECT DISTINCT p.code, p.description
            FROM user_roles ur
            INNER JOIN role_permissions rp ON rp.role_id = ur.role_id
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id
            ORDER BY p.code ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    private static function permissionsAreSeeded(PDO $db): bool
    {
        try {
            $roles = (int) $db->query('SELECT COUNT(*) AS count FROM roles')->fetch()['count'];
            $permissions = (int) $db->query('SELECT COUNT(*) AS count FROM permissions')->fetch()['count'];
            $rolePermissions = (int) $db->query('SELECT COUNT(*) AS count FROM role_permissions')->fetch()['count'];
        } catch (Throwable) {
            return false;
        }

        return $roles > 0 && $permissions > 0 && $rolePermissions > 0;
    }
}
