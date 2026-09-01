<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Infrastructure\Database\ConnectionFactory;
use App\Infrastructure\Persistence\PdoAttributeRepository;
use App\Infrastructure\Persistence\PdoCategoryRepository;
use App\Infrastructure\Persistence\PdoProductRepository;
use App\Model\Attribute\AttributeFactory;
use App\Model\Product\ProductFactory;
use PHPUnit\Framework\TestCase;

final class CatalogRepositoryTest extends TestCase
{
    public function testSeededCatalogMatchesTheProvidedData(): void
    {
        $connection = ConnectionFactory::create();
        $categories = (new PdoCategoryRepository($connection))->all();
        $products = new PdoProductRepository($connection, new ProductFactory());
        $attributes = new PdoAttributeRepository($connection, new AttributeFactory());

        self::assertSame(['all', 'clothes', 'tech'], array_map(
            static fn ($category): string => $category->name(),
            $categories
        ));
        self::assertCount(8, $products->byCategory('all'));
        self::assertCount(2, $products->byCategory('clothes'));
        self::assertCount(6, $products->byCategory('tech'));
        self::assertCount(3, $attributes->forProduct('apple-imac-2021'));
        self::assertNull($products->find('does-not-exist'));
    }
}
