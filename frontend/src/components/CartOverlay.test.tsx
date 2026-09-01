import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useEffect } from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { CartProvider, useCart } from '../context/CartContext';
import type { Product } from '../types/catalog';
import { CartOverlay } from './CartOverlay';

const createOrder = vi.fn();

vi.mock('../api/catalog', () => ({
  createOrder: (...arguments_: unknown[]) => createOrder(...arguments_),
}));

const product: Product = {
  id: 'iphone',
  name: 'iPhone',
  description: '<p>Phone</p>',
  categoryName: 'tech',
  brand: 'Apple',
  inStock: true,
  gallery: ['main.jpg', 'second.jpg'],
  prices: [{ amount: 1000.76, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
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

const defaultSelection = { Color: 'Black' };

function OpenCart({
  cartProduct = product,
  selectedAttributes = defaultSelection,
}: {
  cartProduct?: Product;
  selectedAttributes?: Record<string, string>;
}) {
  const { addItem, setOpen } = useCart();

  useEffect(() => {
    addItem(cartProduct, selectedAttributes);
    setOpen(true);
  }, [addItem, cartProduct, selectedAttributes, setOpen]);

  return <CartOverlay />;
}

describe('CartOverlay', () => {
  beforeEach(() => {
    createOrder.mockReset();
    createOrder.mockResolvedValue({
      id: '1',
      total: 1000.76,
      status: 'pending',
      createdAt: '2026-09-01 12:00:00',
    });
  });

  it('shows every option, marks the selected option, and clears only after checkout', async () => {
    const user = userEvent.setup();
    render(
      <CartProvider>
        <OpenCart />
      </CartProvider>,
    );

    expect(screen.getByTestId('cart-item-attribute-color')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-color-black-selected')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-color-green')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$1000.76');
    expect(screen.getByAltText('iPhone')).toHaveAttribute('src', 'main.jpg');

    await user.click(screen.getByRole('button', { name: 'PLACE ORDER' }));

    expect(createOrder).toHaveBeenCalledTimes(1);
    expect(screen.getByText('Your cart is empty.')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$0.00');
    expect(screen.getByRole('button', { name: 'PLACE ORDER' })).toBeDisabled();
  });

  it('preserves the cart when the GraphQL mutation fails', async () => {
    createOrder.mockRejectedValue(new Error('Checkout is unavailable.'));
    const user = userEvent.setup();
    render(
      <CartProvider>
        <OpenCart />
      </CartProvider>,
    );

    await user.click(screen.getByRole('button', { name: 'PLACE ORDER' }));

    expect(screen.getByText('Checkout is unavailable.')).toBeVisible();
    expect(screen.getByAltText('iPhone')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$1000.76');
  });

  it('renders every backend attribute and formats the total with its backend currency', () => {
    const productWithThreeAttributes: Product = {
      ...product,
      id: 'imac',
      name: 'iMac',
      prices: [{ amount: 1688.03, currencyLabel: 'EUR', currencySymbol: '€' }],
      attributes: [
        ...product.attributes,
        {
          id: 'Capacity',
          name: 'Capacity',
          type: 'text',
          items: [{ id: '256GB', displayValue: '256GB', value: '256GB' }],
        },
        {
          id: 'Touch ID',
          name: 'Touch ID in keyboard',
          type: 'text',
          items: [{ id: 'Yes', displayValue: 'Yes', value: 'Yes' }],
        },
      ],
    };

    render(
      <CartProvider>
        <OpenCart
          cartProduct={productWithThreeAttributes}
          selectedAttributes={{ Color: 'Black', Capacity: '256GB', 'Touch ID': 'Yes' }}
        />
      </CartProvider>,
    );

    expect(screen.getByTestId('cart-item-attribute-color')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-capacity')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-touch-id-in-keyboard')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('€1688.03');
  });
});
