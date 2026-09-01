import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import type { CartItem, Product } from '../types/catalog';
import { buildVariantId } from '../utils/format';
import { CartProvider, useCart } from './CartContext';

const STORAGE_KEY = 'scandiweb-cart';

const product: Product = {
  id: 'persisted-jacket',
  name: 'Persisted Jacket',
  description: '<p>A jacket restored from storage.</p>',
  categoryName: 'clothes',
  brand: 'Brand',
  inStock: true,
  gallery: ['main.jpg'],
  prices: [{ amount: 12.5, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
    {
      id: 'Size',
      name: 'Size',
      type: 'text',
      items: [{ id: 'Small', displayValue: 'Small', value: 'S' }],
    },
  ],
};

const storedItem: CartItem = {
  variantId: buildVariantId(product.id, { Size: 'Small' }),
  product,
  selectedAttributes: { Size: 'Small' },
  quantity: 2,
};

function PersistenceHarness() {
  const { items, itemCount, total, increment, clear } = useCart();

  return (
    <>
      <button type="button" onClick={() => items[0] && increment(items[0].variantId)}>
        Increment restored item
      </button>
      <button type="button" onClick={clear}>
        Clear cart
      </button>
      <output data-testid="lines">{items.length}</output>
      <output data-testid="count">{itemCount}</output>
      <output data-testid="total">{total.toFixed(2)}</output>
    </>
  );
}

describe('CartProvider localStorage persistence', () => {
  it('restores a stored cart and persists subsequent quantity changes', async () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify([storedItem]));
    const user = userEvent.setup();
    const firstRender = render(
      <CartProvider>
        <PersistenceHarness />
      </CartProvider>,
    );

    expect(screen.getByTestId('lines')).toHaveTextContent('1');
    expect(screen.getByTestId('count')).toHaveTextContent('2');
    expect(screen.getByTestId('total')).toHaveTextContent('25.00');

    await user.click(screen.getByRole('button', { name: 'Increment restored item' }));

    await waitFor(() => {
      const persistedItems = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]') as CartItem[];
      expect(persistedItems[0]?.quantity).toBe(3);
    });

    firstRender.unmount();
    render(
      <CartProvider>
        <PersistenceHarness />
      </CartProvider>,
    );

    expect(screen.getByTestId('count')).toHaveTextContent('3');
    expect(screen.getByTestId('total')).toHaveTextContent('37.50');
  });

  it('does not restore stale items after the cart is cleared', async () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify([storedItem]));
    const user = userEvent.setup();
    const firstRender = render(
      <CartProvider>
        <PersistenceHarness />
      </CartProvider>,
    );

    await user.click(screen.getByRole('button', { name: 'Clear cart' }));

    await waitFor(() => {
      expect(JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]')).toEqual([]);
    });

    firstRender.unmount();
    render(
      <CartProvider>
        <PersistenceHarness />
      </CartProvider>,
    );

    expect(screen.getByTestId('lines')).toHaveTextContent('0');
    expect(screen.getByTestId('count')).toHaveTextContent('0');
    expect(screen.getByTestId('total')).toHaveTextContent('0.00');
  });
});
