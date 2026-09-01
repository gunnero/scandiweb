import { act, render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useEffect } from 'react';
import { describe, expect, it, vi } from 'vitest';
import { CartProvider, useCart } from '../context/CartContext';
import type { Order, Product } from '../types/catalog';
import { CartOverlay } from './CartOverlay';

const createOrder = vi.fn();

vi.mock('../api/catalog', () => ({
  createOrder: (...arguments_: unknown[]) => createOrder(...arguments_),
}));

const product: Product = {
  id: 'apple-iphone-12-pro',
  name: 'iPhone 12 Pro',
  description: '<p>Phone</p>',
  categoryName: 'tech',
  brand: 'Apple',
  inStock: true,
  gallery: ['iphone-main.jpg'],
  prices: [{ amount: 999.99, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
    {
      id: 'Capacity',
      name: 'Capacity',
      type: 'text',
      items: [
        { id: '256GB', displayValue: '256GB', value: '256GB' },
        { id: '512GB', displayValue: '512GB', value: '512GB' },
      ],
    },
    {
      id: 'Color',
      name: 'Color',
      type: 'swatch',
      items: [
        { id: 'Black', displayValue: 'Black', value: '#000000' },
        { id: 'Green', displayValue: 'Green', value: '#44FF03' },
      ],
    },
  ],
};

const selectedAttributes = {
  Capacity: '512GB',
  Color: 'Green',
};

function OpenCartWithProduct() {
  const { addItem, setOpen } = useCart();

  useEffect(() => {
    addItem(product, selectedAttributes);
    setOpen(true);
  }, [addItem, setOpen]);

  return <CartOverlay />;
}

describe('CartOverlay order payload', () => {
  it('keeps the cart until createOrder resolves, then clears it after success', async () => {
    let resolveOrder!: (order: Order) => void;
    const pendingOrder = new Promise<Order>((resolve) => {
      resolveOrder = resolve;
    });
    createOrder.mockReturnValueOnce(pendingOrder);
    const user = userEvent.setup();

    render(
      <CartProvider>
        <OpenCartWithProduct />
      </CartProvider>,
    );

    await user.click(screen.getByRole('button', { name: 'PLACE ORDER' }));

    expect(createOrder).toHaveBeenCalledTimes(1);
    expect(createOrder).toHaveBeenCalledWith([
      {
        productId: 'apple-iphone-12-pro',
        quantity: 1,
        selectedAttributes: [
          { attributeId: 'Capacity', itemId: '512GB' },
          { attributeId: 'Color', itemId: 'Green' },
        ],
      },
    ]);
    expect(screen.getByAltText('iPhone 12 Pro')).toBeVisible();
    expect(screen.queryByText('Your cart is empty.')).not.toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'PLACING ORDER…' })).toBeDisabled();

    await act(async () => {
      resolveOrder({
        id: '42',
        total: 999.99,
        status: 'pending',
        createdAt: '2026-09-02 12:00:00',
      });
      await pendingOrder;
    });

    await waitFor(() => {
      expect(screen.getByText('Your cart is empty.')).toBeVisible();
    });
    expect(screen.queryByAltText('iPhone 12 Pro')).not.toBeInTheDocument();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$0.00');
    expect(screen.getByRole('button', { name: 'PLACE ORDER' })).toBeDisabled();
  });
});
