<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Model\Money;
use App\Model\Price;
use App\Model\Product\AbstractProduct;
use App\Model\Product\ProductFactory;
use App\Repository\ProductRepositoryInterface;
use PDO;

final class PdoProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
        private readonly ProductFactory $factory
    ) {
    }

    public function all(): array
    {
        $statement = $this->connection->query($this->baseQuery() . ' ORDER BY products.position ASC');

        return $this->hydrateAll($statement->fetchAll());
    }

    public function inCategory(string $categoryName): array
    {
        $statement = $this->connection->prepare(
            $this->baseQuery() . ' WHERE categories.name = :category ORDER BY products.position ASC'
        );
        $statement->execute(['category' => $categoryName]);

        return $this->hydrateAll($statement->fetchAll());
    }

    public function find(string $id): ?AbstractProduct
    {
        return $this->findById($id, false);
    }

    public function findForUpdate(string $id): ?AbstractProduct
    {
        return $this->findById($id, true);
    }

    private function findById(string $id, bool $forUpdate): ?AbstractProduct
    {
        $query = $this->baseQuery() . ' WHERE products.public_id = :id';
        $query .= $forUpdate ? ' FOR UPDATE' : '';
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    private function baseQuery(): string
    {
        return 'SELECT products.id, products.public_id, products.product_type, products.name,
                       products.in_stock, products.description, products.brand,
                       categories.name AS category_name
                FROM products
                INNER JOIN categories ON categories.id = products.category_id';
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<AbstractProduct>
     */
    private function hydrateAll(array $rows): array
    {
        return array_map(
            fn (array $row): AbstractProduct => $this->hydrate($row),
            $rows
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): AbstractProduct
    {
        return $this->factory->create(
            $row['product_type'],
            $row['public_id'],
            $row['name'],
            (bool) $row['in_stock'],
            $row['description'],
            $row['category_name'],
            $row['brand'],
            $this->gallery((int) $row['id']),
            $this->prices((int) $row['id'])
        );
    }

    /** @return list<string> */
    private function gallery(int $productId): array
    {
        $statement = $this->connection->prepare(
            'SELECT url FROM product_gallery WHERE product_id = :product_id ORDER BY position ASC, id ASC'
        );
        $statement->execute(['product_id' => $productId]);

        return array_map(
            static fn (array $row): string => $row['url'],
            $statement->fetchAll()
        );
    }

    /** @return list<Price> */
    private function prices(int $productId): array
    {
        $statement = $this->connection->prepare(
            'SELECT amount, currency_label, currency_symbol
             FROM product_prices
             WHERE product_id = :product_id
             ORDER BY id ASC'
        );
        $statement->execute(['product_id' => $productId]);

        return array_map(
            static fn (array $row): Price => new Price(
                Money::fromDecimal((string) $row['amount']),
                $row['currency_label'],
                $row['currency_symbol']
            ),
            $statement->fetchAll()
        );
    }
}
