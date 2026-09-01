<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Application\PlaceOrderService;
use App\Config\ApplicationConfig;
use App\Controller\GraphQLController;
use App\GraphQL\Resolver\AttributeResolver;
use App\GraphQL\Resolver\CategoryResolver;
use App\GraphQL\Resolver\OrderResolver;
use App\GraphQL\Resolver\ProductResolver;
use App\GraphQL\SchemaFactory;
use App\Infrastructure\Persistence\PdoAttributeRepository;
use App\Infrastructure\Persistence\PdoCategoryRepository;
use App\Infrastructure\Persistence\PdoOrderRepository;
use App\Infrastructure\Persistence\PdoProductRepository;
use App\Infrastructure\Persistence\PdoTransactionManager;
use App\Model\Attribute\AttributeFactory;
use App\Model\Category\CategoryFactory;
use App\Model\Product\ProductFactory;
use GraphQL\Type\Schema;
use PDO;

final class ApplicationFactory
{
    public static function graphQLController(PDO $connection): GraphQLController
    {
        return new GraphQLController(
            self::schema($connection),
            ApplicationConfig::debugEnabled()
        );
    }

    public static function schema(PDO $connection): Schema
    {
        $attributeRepository = new PdoAttributeRepository(
            $connection,
            new AttributeFactory()
        );
        $categoryRepository = new PdoCategoryRepository($connection, new CategoryFactory());
        $productRepository = new PdoProductRepository($connection, new ProductFactory());
        $placeOrder = new PlaceOrderService(
            $productRepository,
            $attributeRepository,
            new PdoOrderRepository($connection),
            new PdoTransactionManager($connection)
        );
        return (new SchemaFactory(
            new CategoryResolver($categoryRepository),
            new ProductResolver($productRepository, $categoryRepository),
            new AttributeResolver($attributeRepository),
            new OrderResolver($placeOrder)
        ))->create();
    }
}
