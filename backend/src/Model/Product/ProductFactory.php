<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Exception\UserInputException;
use App\Model\Price;

final class ProductFactory
{
    /** @var array<string, class-string<AbstractProduct>> */
    private const TYPES = [
        'simple' => SimpleProduct::class,
        'configurable' => ConfigurableProduct::class,
    ];

    /**
     * @param list<string> $gallery
     * @param list<Price> $prices
     */
    public function create(
        string $type,
        string $id,
        string $name,
        bool $inStock,
        string $description,
        string $categoryName,
        string $brand,
        array $gallery,
        array $prices
    ): AbstractProduct {
        $className = self::TYPES[$type] ?? throw new UserInputException(
            sprintf('Unsupported product type "%s".', $type)
        );

        return new $className(
            $id,
            $name,
            $inStock,
            $description,
            $categoryName,
            $brand,
            $gallery,
            $prices
        );
    }
}
