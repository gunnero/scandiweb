<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Config\Environment;
use PDO;

final class ConnectionFactory
{
    public static function create(): PDO
    {
        $host = Environment::required('DB_HOST');
        $port = Environment::required('DB_PORT');
        $database = Environment::required('DB_NAME');
        $username = Environment::required('DB_USER');
        $password = Environment::required('DB_PASSWORD');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        return new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );
    }
}
