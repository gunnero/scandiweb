<?php

declare(strict_types=1);

namespace App\Model\Category;

use InvalidArgumentException;

final class CategoryFactory
{
    /** @var array<string, class-string<AbstractCategory>> */
    private const TYPES = [
        'all' => AllCategory::class,
    ];

    public function create(string $id, string $name): AbstractCategory
    {
        if (trim($id) === '' || trim($name) === '') {
            throw new InvalidArgumentException('Categories require an identifier and name.');
        }

        $categoryClass = self::TYPES[$name] ?? NamedCategory::class;

        return new $categoryClass($id, $name);
    }
}
