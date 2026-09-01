<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Model\Attribute\AbstractAttribute;
use App\Model\Attribute\AttributeFactory;
use App\Model\Attribute\AttributeItem;
use App\Repository\AttributeRepositoryInterface;
use PDO;

final class PdoAttributeRepository implements AttributeRepositoryInterface
{
    public function __construct(
        private readonly PDO $connection,
        private readonly AttributeFactory $factory
    ) {
    }

    public function forProduct(string $productId): array
    {
        $setStatement = $this->connection->prepare(
            'SELECT attribute_sets.id, attribute_sets.external_id, attribute_sets.name, attribute_sets.type
             FROM product_attribute_sets AS attribute_sets
             INNER JOIN products ON products.id = attribute_sets.product_id
             WHERE products.public_id = :product_id
             ORDER BY attribute_sets.position ASC, attribute_sets.id ASC'
        );
        $setStatement->execute(['product_id' => $productId]);

        return array_map(
            fn (array $row): AbstractAttribute => $this->factory->create(
                $row['type'],
                $row['external_id'],
                $row['name'],
                $this->items((int) $row['id'])
            ),
            $setStatement->fetchAll()
        );
    }

    /** @return list<AttributeItem> */
    private function items(int $attributeSetId): array
    {
        $statement = $this->connection->prepare(
            'SELECT external_id, display_value, value
             FROM product_attribute_items
             WHERE attribute_set_id = :attribute_set_id
             ORDER BY position ASC, id ASC'
        );
        $statement->execute(['attribute_set_id' => $attributeSetId]);

        return array_map(
            static fn (array $row): AttributeItem => new AttributeItem(
                $row['external_id'],
                $row['display_value'],
                $row['value']
            ),
            $statement->fetchAll()
        );
    }
}
