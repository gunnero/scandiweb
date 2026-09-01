<?php

declare(strict_types=1);

use App\Infrastructure\Database\CatalogSeeder;
use App\Infrastructure\Database\ConnectionFactory;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$connection = ConnectionFactory::create();
$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');

if ($schema === false) {
    throw new RuntimeException('The database schema could not be read.');
}

$connection->exec($schema);
(new CatalogSeeder($connection))->seed(dirname(__DIR__) . '/data/data.json');

fwrite(STDOUT, "Catalog database seeded successfully.\n");
