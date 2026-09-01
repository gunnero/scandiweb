<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exception\UserInputException;
use App\Model\Attribute\AttributeFactory;
use App\Model\Attribute\AttributeItem;
use App\Model\Attribute\SwatchAttribute;
use App\Model\Attribute\TextAttribute;
use App\Model\Category\AllCategory;
use App\Model\Category\CategoryFactory;
use App\Model\Category\NamedCategory;
use App\Model\Money;
use App\Model\Price;
use App\Model\Product\ConfigurableProduct;
use App\Model\Product\ProductFactory;
use App\Model\Product\SimpleProduct;
use App\Repository\ProductRepositoryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PolymorphismTest extends TestCase
{
    public function testAttributeFactoryReturnsConcreteTypesWithoutTypeBranching(): void
    {
        $factory = new AttributeFactory();
        $items = [new AttributeItem('Black', 'Black', '#000000')];

        self::assertInstanceOf(TextAttribute::class, $factory->create('text', 'Size', 'Size', $items));
        self::assertInstanceOf(
            SwatchAttribute::class,
            $factory->create('swatch', 'Color', 'Color', $items)
        );
    }

    public function testProductTypesEnforceTheirOwnSelectionRules(): void
    {
        $factory = new ProductFactory();
        $price = new Price(Money::fromDecimal('10.00'), 'USD', '$');
        $simple = $factory->create(
            'simple',
            'simple',
            'Simple',
            true,
            '<p>Simple</p>',
            'tech',
            'Brand',
            ['image.jpg'],
            [$price]
        );
        $configurable = $factory->create(
            'configurable',
            'configurable',
            'Configurable',
            true,
            '<p>Configurable</p>',
            'tech',
            'Brand',
            ['image.jpg'],
            [$price]
        );
        $size = (new AttributeFactory())->create(
            'text',
            'Size',
            'Size',
            [new AttributeItem('Small', 'Small', 'S')]
        );

        self::assertInstanceOf(SimpleProduct::class, $simple);
        self::assertInstanceOf(ConfigurableProduct::class, $configurable);
        $simple->assertPurchasable([], []);
        $configurable->assertPurchasable([$size], ['Size' => 'Small']);

        $this->expectException(UserInputException::class);
        $configurable->assertPurchasable([$size], []);
    }

    public function testAttributeTypesEnforceTheirOwnValueInvariants(): void
    {
        $factory = new AttributeFactory();
        $namedValue = [new AttributeItem('Blue', 'Blue', 'blue')];

        self::assertInstanceOf(
            TextAttribute::class,
            $factory->create('text', 'Finish', 'Finish', $namedValue)
        );

        $this->expectException(InvalidArgumentException::class);
        $factory->create('swatch', 'Color', 'Color', $namedValue);
    }

    public function testCategoryTypesDelegateTheirOwnCatalogScope(): void
    {
        $factory = new CategoryFactory();
        $all = $factory->create('1', 'all');
        $named = $factory->create('2', 'tech');

        self::assertInstanceOf(AllCategory::class, $all);
        self::assertInstanceOf(NamedCategory::class, $named);

        $allProducts = $this->createMock(ProductRepositoryInterface::class);
        $allProducts->expects(self::once())->method('all')->willReturn([]);
        $allProducts->expects(self::never())->method('inCategory');
        self::assertSame([], $all->productsFrom($allProducts));

        $namedProducts = $this->createMock(ProductRepositoryInterface::class);
        $namedProducts->expects(self::never())->method('all');
        $namedProducts->expects(self::once())->method('inCategory')->with('tech')->willReturn([]);
        self::assertSame([], $named->productsFrom($namedProducts));
    }
}
