<?php

declare(strict_types=1);

namespace App\Config;

final class ApplicationConfig
{
    public static function debugEnabled(): bool
    {
        return filter_var(Environment::withDefault('APP_DEBUG', '0'), FILTER_VALIDATE_BOOL);
    }

    /** @return list<string> */
    public static function allowedOrigins(): array
    {
        $origins = Environment::withDefault('APP_ALLOWED_ORIGIN', 'http://localhost:3000');

        return array_values(
            array_filter(
                array_map('trim', explode(',', $origins)),
                static fn (string $origin): bool => $origin !== ''
            )
        );
    }
}
