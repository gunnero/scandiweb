<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Model\Attribute\AbstractAttribute;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class AttributeType extends ObjectType
{
    public function __construct(AttributeItemType $itemType)
    {
        parent::__construct([
            'name' => 'Attribute',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (AbstractAttribute $attribute): string => $attribute->id(),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractAttribute $attribute): string => $attribute->name(),
                ],
                'type' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractAttribute $attribute): string => $attribute->type(),
                ],
                'items' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($itemType))),
                    'resolve' => static fn (AbstractAttribute $attribute): array => $attribute->items(),
                ],
            ],
        ]);
    }
}
