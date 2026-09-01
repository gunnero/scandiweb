<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Model\Category;
use App\Repository\CategoryRepositoryInterface;
use PDO;

final class PdoCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name FROM categories ORDER BY position ASC, id ASC'
        );

        return array_map(
            static fn (array $row): Category => new Category((string) $row['id'], $row['name']),
            $statement->fetchAll()
        );
    }
}
