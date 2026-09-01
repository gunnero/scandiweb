<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\PlaceOrderService;
use App\Model\Attribute\AttributeFactory;
use App\Model\Attribute\AttributeItem;
use App\Model\Money;
use App\Model\Order\Order;
use App\Model\Price;
use App\Model\Product\ProductFactory;
use App\Repository\AttributeRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Repository\TransactionManagerInterface;
use PHPUnit\Framework\TestCase;

final class PlaceOrderServiceTest extends TestCase
{
    public function testOrderPersistsEverySelectedAttributeAndServerCalculatedTotal(): void
    {
        $product = (new ProductFactory())->create(
            'configurable',
            'apple-imac-2021',
            'iMac 2021',
            true,
            'The new iMac!',
            'tech',
            'Apple',
            ['imac.jpg'],
            [new Price(Money::fromDecimal('1688.03'), 'USD', '$')]
        );
        $attributeFactory = new AttributeFactory();
        $attributes = [
            $attributeFactory->create(
                'text',
                'Capacity',
                'Capacity',
                [new AttributeItem('256GB', '256GB', '256GB')]
            ),
            $attributeFactory->create(
                'text',
                'Touch ID in keyboard',
                'Touch ID in keyboard',
                [new AttributeItem('Yes', 'Yes', 'Yes')]
            ),
            $attributeFactory->create(
                'text',
                'With USB 3 ports',
                'With USB 3 ports',
                [new AttributeItem('No', 'No', 'No')]
            ),
        ];

        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->method('findForUpdate')->with('apple-imac-2021')->willReturn($product);
        $attributesRepository = $this->createMock(AttributeRepositoryInterface::class);
        $attributesRepository->method('forProduct')->willReturn($attributes);
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects(self::once())
            ->method('create')
            ->with(
                self::callback(static fn (Money $total): bool => $total->toDecimal() === '3376.06'),
                'USD',
                self::callback(
                    static fn (array $lines): bool =>
                        count($lines) === 1 && count($lines[0]->selectedAttributes()) === 3
                )
            )
            ->willReturn(new Order('42', Money::fromDecimal('3376.06'), 'pending', '2026-09-01 12:00:00'));
        $transactions = $this->createMock(TransactionManagerInterface::class);
        $transactions->method('run')->willReturnCallback(static fn (callable $operation): mixed => $operation());

        $service = new PlaceOrderService(
            $products,
            $attributesRepository,
            $orders,
            $transactions
        );
        $order = $service->place([
            [
                'productId' => 'apple-imac-2021',
                'quantity' => 2,
                'selectedAttributes' => [
                    ['attributeId' => 'Capacity', 'itemId' => '256GB'],
                    ['attributeId' => 'Touch ID in keyboard', 'itemId' => 'Yes'],
                    ['attributeId' => 'With USB 3 ports', 'itemId' => 'No'],
                ],
            ],
        ]);

        self::assertSame('42', $order->id());
        self::assertSame('3376.06', $order->total()->toDecimal());
    }
}
