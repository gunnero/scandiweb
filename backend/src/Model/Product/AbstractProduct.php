<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Exception\UserInputException;
use App\Model\Attribute\AbstractAttribute;
use App\Model\Price;

abstract class AbstractProduct
{
    /**
     * @param list<string> $gallery
     * @param list<Price> $prices
     */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly bool $inStock,
        private readonly string $description,
        private readonly string $categoryName,
        private readonly string $brand,
        private readonly array $gallery,
        private readonly array $prices
    ) {
    }

    /**
     * @param list<AbstractAttribute> $attributes
     * @param array<string, string> $selectedAttributes
     */
    abstract protected function assertSelections(array $attributes, array $selectedAttributes): void;

    /**
     * @param list<AbstractAttribute> $attributes
     * @param array<string, string> $selectedAttributes
     */
    final public function assertPurchasable(array $attributes, array $selectedAttributes): void
    {
        if (!$this->inStock) {
            throw new UserInputException(sprintf('%s is out of stock.', $this->name));
        }

        $this->assertSelections($attributes, $selectedAttributes);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function inStock(): bool
    {
        return $this->inStock;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function brand(): string
    {
        return $this->brand;
    }

    /** @return list<string> */
    public function gallery(): array
    {
        return $this->gallery;
    }

    /** @return list<Price> */
    public function prices(): array
    {
        return $this->prices;
    }

    public function price(string $currencyLabel): ?Price
    {
        foreach ($this->prices as $price) {
            if ($price->currencyLabel() === $currencyLabel) {
                return $price;
            }
        }

        return null;
    }
}
