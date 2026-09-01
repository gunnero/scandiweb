<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Bootstrap\ApplicationFactory;
use App\Infrastructure\Database\ConnectionFactory;
use GraphQL\GraphQL;
use PHPUnit\Framework\TestCase;

final class GraphQLCatalogTest extends TestCase
{
    public function testCategorySubtypesPreserveThePublicCatalogContract(): void
    {
        $result = GraphQL::executeQuery(
            ApplicationFactory::schema(ConnectionFactory::create()),
            <<<'GRAPHQL'
                query CategoryCatalog {
                    categories {
                        __typename
                        id
                        name
                    }
                    all: productsByCategory(categoryName: "all") { id }
                    clothes: productsByCategory(categoryName: "clothes") { id }
                    tech: productsByCategory(categoryName: "tech") { id }
                    missing: productsByCategory(categoryName: "missing") { id }
                }
                GRAPHQL
        )->toArray();

        self::assertArrayNotHasKey('errors', $result);
        self::assertSame(
            ['all', 'clothes', 'tech'],
            array_column($result['data']['categories'], 'name')
        );
        self::assertSame(
            ['Category'],
            array_values(array_unique(array_column($result['data']['categories'], '__typename')))
        );
        self::assertCount(8, $result['data']['all']);
        self::assertCount(2, $result['data']['clothes']);
        self::assertCount(6, $result['data']['tech']);
        self::assertSame([], $result['data']['missing']);
    }
}
