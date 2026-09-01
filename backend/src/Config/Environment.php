<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class Environment
{
    public static function required(string $name): string
    {
        $value = self::read($name);

        if ($value === null || trim($value) === '') {
            throw new RuntimeException(sprintf('%s must be configured.', $name));
        }

        return $value;
    }

    public static function withDefault(string $name, string $default): string
    {
        $value = self::read($name);

        return $value === null || trim($value) === '' ? $default : $value;
    }

    private static function read(string $name): ?string
    {
        $processValue = getenv($name);

        if (is_string($processValue)) {
            return $processValue;
        }

        $value = $_ENV[$name] ?? $_SERVER[$name] ?? null;

        return is_string($value) ? $value : null;
    }
}
