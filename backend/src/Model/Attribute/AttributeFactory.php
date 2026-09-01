<?php

declare(strict_types=1);

namespace App\Model\Attribute;

use App\Exception\UserInputException;

final class AttributeFactory
{
    /** @var array<string, class-string<AbstractAttribute>> */
    private const TYPES = [
        'text' => TextAttribute::class,
        'swatch' => SwatchAttribute::class,
    ];

    /** @param list<AttributeItem> $items */
    public function create(string $type, string $id, string $name, array $items): AbstractAttribute
    {
        $className = self::TYPES[$type] ?? throw new UserInputException(
            sprintf('Unsupported attribute type "%s".', $type)
        );

        return new $className($id, $name, $items);
    }
}
