<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Model\Attribute\AttributeItem;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class AttributeItemType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'AttributeItem',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (AttributeItem $item): string => $item->id(),
                ],
                'displayValue' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AttributeItem $item): string => $item->displayValue(),
                ],
                'value' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AttributeItem $item): string => $item->value(),
                ],
            ],
        ]);
    }
}
