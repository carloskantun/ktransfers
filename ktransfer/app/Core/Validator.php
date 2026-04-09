<?php
declare(strict_types=1);
namespace App\Core;

class Validator {
    public static function required(array $input, array $fields): array
    {
        $errors = [];

        foreach ($fields as $field) {
            $value = $input[$field] ?? null;
            if ($value === null || (is_string($value) && trim($value) === '')) {
                $errors[$field] = 'Required field.';
            }
        }

        return $errors;
    }

    public static function in(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }
}
