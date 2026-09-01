<?php

declare(strict_types=1);

use App\Infrastructure\Database\CatalogSeeder;
use App\Infrastructure\Database\ConnectionFactory;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$attempts = 30;
$connection = null;

while ($attempts > 0) {
    try {
        $connection = ConnectionFactory::create();
        break;
    } catch (PDOException $error) {
        $attempts--;

        if ($attempts === 0) {
            throw $error;
        }

        sleep(2);
    }
}

if (!$connection instanceof PDO) {
    throw new RuntimeException('The database connection could not be initialized.');
}

$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');

if ($schema === false) {
    throw new RuntimeException('The database schema could not be read.');
}

$connection->exec($schema);
$categoryCount = (int) $connection->query('SELECT COUNT(*) FROM categories')->fetchColumn();

if ($categoryCount === 0) {
    (new CatalogSeeder($connection))->seed(dirname(__DIR__) . '/data/data.json');
    fwrite(STDOUT, "Catalog initialized.\n");
}
