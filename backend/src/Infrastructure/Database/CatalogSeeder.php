<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use JsonException;
use PDO;
use RuntimeException;
use Throwable;

final class CatalogSeeder
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /** @throws JsonException */
    public function seed(string $dataFile): void
    {
        $contents = file_get_contents($dataFile);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Could not read %s.', $dataFile));
        }

        $document = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $catalog = $document['data'] ?? null;

        if (!is_array($catalog)) {
            throw new RuntimeException('The catalog data is malformed.');
        }

        $this->connection->beginTransaction();

        try {
            $this->clearCatalog();
            $categoryIds = $this->insertCategories($catalog['categories'] ?? []);
            $this->insertProducts($catalog['products'] ?? [], $categoryIds);
            $this->connection->commit();
        } catch (Throwable $error) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $error;
        }
    }

    private function clearCatalog(): void
    {
        $this->connection->exec('DELETE FROM product_attribute_items');
        $this->connection->exec('DELETE FROM product_attribute_sets');
        $this->connection->exec('DELETE FROM product_gallery');
        $this->connection->exec('DELETE FROM product_prices');
        $this->connection->exec('DELETE FROM products');
        $this->connection->exec('DELETE FROM categories');
    }

    /**
     * @param mixed $categories
     * @return array<string, int>
     */
    private function insertCategories(mixed $categories): array
    {
        if (!is_array($categories)) {
            throw new RuntimeException('The category list is malformed.');
        }

        $statement = $this->connection->prepare(
            'INSERT INTO categories (name, type_name, position)
             VALUES (:name, :type_name, :position)'
        );
        $ids = [];

        foreach (array_values($categories) as $position => $category) {
            $statement->execute([
                'name' => $category['name'],
                'type_name' => $category['__typename'] ?? 'Category',
                'position' => $position,
            ]);
            $ids[$category['name']] = (int) $this->connection->lastInsertId();
        }

        return $ids;
    }

    /**
     * @param mixed $products
     * @param array<string, int> $categoryIds
     */
    private function insertProducts(mixed $products, array $categoryIds): void
    {
        if (!is_array($products)) {
            throw new RuntimeException('The product list is malformed.');
        }

        $statement = $this->connection->prepare(
            'INSERT INTO products (
                public_id,
                category_id,
                product_type,
                name,
                in_stock,
                description,
                brand,
                position
             ) VALUES (
                :public_id,
                :category_id,
                :product_type,
                :name,
                :in_stock,
                :description,
                :brand,
                :position
             )'
        );

        foreach (array_values($products) as $position => $product) {
            $categoryId = $categoryIds[$product['category']] ?? null;

            if ($categoryId === null) {
                throw new RuntimeException('A product references an unknown category.');
            }

            $attributes = $product['attributes'] ?? [];
            $statement->execute([
                'public_id' => $product['id'],
                'category_id' => $categoryId,
                'product_type' => $attributes === [] ? 'simple' : 'configurable',
                'name' => $product['name'],
                'in_stock' => $product['inStock'] ? 1 : 0,
                'description' => $product['description'],
                'brand' => $product['brand'],
                'position' => $position,
            ]);
            $productId = (int) $this->connection->lastInsertId();
            $this->insertGallery($productId, $product['gallery'] ?? []);
            $this->insertPrices($productId, $product['prices'] ?? []);
            $this->insertAttributes($productId, $attributes);
        }
    }

    /** @param mixed $gallery */
    private function insertGallery(int $productId, mixed $gallery): void
    {
        if (!is_array($gallery)) {
            throw new RuntimeException('A product gallery is malformed.');
        }

        $statement = $this->connection->prepare(
            'INSERT INTO product_gallery (product_id, url, position)
             VALUES (:product_id, :url, :position)'
        );

        foreach (array_values($gallery) as $position => $url) {
            $statement->execute([
                'product_id' => $productId,
                'url' => $url,
                'position' => $position,
            ]);
        }
    }

    /** @param mixed $prices */
    private function insertPrices(int $productId, mixed $prices): void
    {
        if (!is_array($prices)) {
            throw new RuntimeException('A product price list is malformed.');
        }

        $statement = $this->connection->prepare(
            'INSERT INTO product_prices (
                product_id,
                amount,
                currency_label,
                currency_symbol
             ) VALUES (
                :product_id,
                :amount,
                :currency_label,
                :currency_symbol
             )'
        );

        foreach ($prices as $price) {
            $statement->execute([
                'product_id' => $productId,
                'amount' => number_format((float) $price['amount'], 2, '.', ''),
                'currency_label' => $price['currency']['label'],
                'currency_symbol' => $price['currency']['symbol'],
            ]);
        }
    }

    /** @param mixed $attributes */
    private function insertAttributes(int $productId, mixed $attributes): void
    {
        if (!is_array($attributes)) {
            throw new RuntimeException('A product attribute list is malformed.');
        }

        $setStatement = $this->connection->prepare(
            'INSERT INTO product_attribute_sets (
                product_id,
                external_id,
                name,
                type,
                position
             ) VALUES (
                :product_id,
                :external_id,
                :name,
                :type,
                :position
             )'
        );
        $itemStatement = $this->connection->prepare(
            'INSERT INTO product_attribute_items (
                attribute_set_id,
                external_id,
                display_value,
                value,
                position
             ) VALUES (
                :attribute_set_id,
                :external_id,
                :display_value,
                :value,
                :position
             )'
        );

        foreach (array_values($attributes) as $setPosition => $attribute) {
            $setStatement->execute([
                'product_id' => $productId,
                'external_id' => $attribute['id'],
                'name' => $attribute['name'],
                'type' => $attribute['type'],
                'position' => $setPosition,
            ]);
            $attributeSetId = (int) $this->connection->lastInsertId();

            foreach (array_values($attribute['items']) as $itemPosition => $item) {
                $itemStatement->execute([
                    'attribute_set_id' => $attributeSetId,
                    'external_id' => $item['id'],
                    'display_value' => $item['displayValue'],
                    'value' => $item['value'],
                    'position' => $itemPosition,
                ]);
            }
        }
    }
}
