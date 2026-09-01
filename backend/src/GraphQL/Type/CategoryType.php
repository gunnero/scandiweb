<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Model\Category;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CategoryType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Category',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (Category $category): string => $category->id(),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (Category $category): string => $category->name(),
                ],
            ],
        ]);
    }
}
