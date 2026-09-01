<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Model\Category\AbstractCategory;
use App\Model\Category\CategoryFactory;
use App\Repository\CategoryRepositoryInterface;
use PDO;

final class PdoCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
        private readonly CategoryFactory $factory
    ) {
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name FROM categories ORDER BY position ASC, id ASC'
        );

        return array_map(
            fn (array $row): AbstractCategory => $this->hydrate($row),
            $statement->fetchAll()
        );
    }

    public function findByName(string $name): ?AbstractCategory
    {
        $statement = $this->connection->prepare(
            'SELECT id, name FROM categories WHERE name = :name'
        );
        $statement->execute(['name' => $name]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): AbstractCategory
    {
        return $this->factory->create((string) $row['id'], $row['name']);
    }
}
