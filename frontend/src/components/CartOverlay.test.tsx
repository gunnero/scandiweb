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

function OpenCartWithVariants() {
  const { addItem, setOpen } = useCart();

  useEffect(() => {
    addItem(product, { Color: 'Black' });
    addItem(product, { Color: 'Green' });
    addItem(product, { Color: 'Green' });
    setOpen(true);
  }, [addItem, setOpen]);

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
    expect(screen.getByTestId('cart-item-attribute-color-black-selected').tagName).toBe('SPAN');
    expect(screen.getByTestId('cart-item-amount-increase')).toBeEnabled();
    expect(screen.getByTestId('cart-item-amount')).toHaveTextContent('1');
    expect(screen.getByTestId('cart-item-amount-decrease')).toBeEnabled();
    expect(screen.getByRole('heading', { name: 'iPhone' })).toBeVisible();
    expect(screen.getByText('My Bag,').parentElement).toHaveTextContent('My Bag, 1 Item');
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$1000.76');
    expect(screen.getByAltText('iPhone')).toHaveAttribute('src', 'main.jpg');

    await user.click(screen.getByRole('button', { name: 'PLACE ORDER' }));

    expect(createOrder).toHaveBeenCalledTimes(1);
    expect(screen.getByText('Your cart is empty.')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('$0.00');
    expect(screen.getByRole('button', { name: 'PLACE ORDER' })).toBeDisabled();
  });

  it('separates different variants, merges identical variants, and applies quantity controls', async () => {
    const user = userEvent.setup();
    render(
      <CartProvider>
        <OpenCartWithVariants />
      </CartProvider>,
    );

    expect(screen.getAllByRole('article')).toHaveLength(2);
    expect(screen.getAllByTestId('cart-item-amount').map((element) => element.textContent)).toEqual([
      '1',
      '2',
    ]);
    expect(screen.getByText('My Bag,').parentElement).toHaveTextContent('My Bag, 3 Items');

    await user.click(screen.getAllByRole('button', { name: /Increase iPhone quantity/ })[0]);
    expect(screen.getAllByTestId('cart-item-amount')[0]).toHaveTextContent('2');
    expect(screen.getByText('My Bag,').parentElement).toHaveTextContent('My Bag, 4 Items');

    await user.click(screen.getAllByRole('button', { name: /Decrease iPhone quantity/ })[0]);
    await user.click(screen.getAllByRole('button', { name: /Decrease iPhone quantity/ })[0]);
    expect(screen.getAllByRole('article')).toHaveLength(1);
    expect(screen.getByText('My Bag,').parentElement).toHaveTextContent('My Bag, 2 Items');
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
          items: [{ id: 'capacity-256', displayValue: '256GB', value: '256GB' }],
        },
        {
          id: 'Touch ID',
          name: 'Touch ID in keyboard',
          type: 'text',
          items: [
            { id: 'touch-yes', displayValue: 'Yes', value: 'Yes' },
            { id: 'touch-no', displayValue: 'No', value: 'No' },
          ],
        },
      ],
    };

    render(
      <CartProvider>
        <OpenCart
          cartProduct={productWithThreeAttributes}
          selectedAttributes={{
            Color: 'Black',
            Capacity: 'capacity-256',
            'Touch ID': 'touch-yes',
          }}
        />
      </CartProvider>,
    );

    expect(screen.getByTestId('cart-item-attribute-color')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-capacity')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-capacity-256gb-selected')).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-touch-id-in-keyboard')).toBeVisible();
    expect(
      screen.getByTestId('cart-item-attribute-touch-id-in-keyboard-yes-selected'),
    ).toBeVisible();
    expect(screen.getByTestId('cart-item-attribute-touch-id-in-keyboard-no')).toBeVisible();
    expect(screen.getByTestId('cart-total')).toHaveTextContent('€1688.03');
  });
});
