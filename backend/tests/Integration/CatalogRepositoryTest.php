<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Infrastructure\Database\ConnectionFactory;
use App\Infrastructure\Persistence\PdoAttributeRepository;
use App\Infrastructure\Persistence\PdoCategoryRepository;
use App\Infrastructure\Persistence\PdoProductRepository;
use App\Model\Attribute\AttributeFactory;
use App\Model\Category\CategoryFactory;
use App\Model\Product\ProductFactory;
use PHPUnit\Framework\TestCase;

final class CatalogRepositoryTest extends TestCase
{
    public function testSeededCatalogMatchesTheProvidedData(): void
    {
        $connection = ConnectionFactory::create();
        $categoryRepository = new PdoCategoryRepository($connection, new CategoryFactory());
        $categories = $categoryRepository->all();
        $products = new PdoProductRepository($connection, new ProductFactory());
        $attributes = new PdoAttributeRepository($connection, new AttributeFactory());

        self::assertSame(['all', 'clothes', 'tech'], array_map(
            static fn ($category): string => $category->name(),
            $categories
        ));
        self::assertCount(8, $categoryRepository->findByName('all')?->productsFrom($products) ?? []);
        self::assertCount(2, $categoryRepository->findByName('clothes')?->productsFrom($products) ?? []);
        self::assertCount(6, $categoryRepository->findByName('tech')?->productsFrom($products) ?? []);
        self::assertNull($categoryRepository->findByName('does-not-exist'));
        self::assertCount(3, $attributes->forProduct('apple-imac-2021'));
        self::assertNull($products->find('does-not-exist'));
    }
}
