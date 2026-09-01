import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import type { Product } from '../types/catalog';
import { CartProvider, useCart } from './CartContext';

const product: Product = {
  id: 'jacket',
  name: 'Jacket',
  description: '<p>Jacket</p>',
  categoryName: 'clothes',
  brand: 'Brand',
  inStock: true,
  gallery: ['main.jpg', 'second.jpg'],
  prices: [{ amount: 10, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
    {
      id: 'Size',
      name: 'Size',
      type: 'text',
      items: [
        { id: 'Small', displayValue: 'Small', value: 'S' },
        { id: 'Large', displayValue: 'Large', value: 'L' },
      ],
    },
  ],
};

function Harness() {
  const { items, itemCount, total, addItem, increment, decrement } = useCart();

  return (
    <>
      <button type="button" onClick={() => addItem(product, { Size: 'Small' })}>
        Add small
      </button>
      <button type="button" onClick={() => addItem(product, { Size: 'Large' })}>
        Add large
      </button>
      <button type="button" onClick={() => items[0] && increment(items[0].variantId)}>
        Increment first
      </button>
      <button type="button" onClick={() => items[0] && decrement(items[0].variantId)}>
        Decrement first
      </button>
      <output data-testid="lines">{items.length}</output>
      <output data-testid="count">{itemCount}</output>
      <output data-testid="total">{total.toFixed(2)}</output>
    </>
  );
}

describe('CartProvider', () => {
  it('merges identical variants, separates different variants, and removes at one', async () => {
    const user = userEvent.setup();
    render(
      <CartProvider>
        <Harness />
      </CartProvider>,
    );

    await user.click(screen.getByRole('button', { name: 'Add small' }));
    await user.click(screen.getByRole('button', { name: 'Add small' }));
    expect(screen.getByTestId('lines')).toHaveTextContent('1');
    expect(screen.getByTestId('count')).toHaveTextContent('2');
    expect(screen.getByTestId('total')).toHaveTextContent('20.00');

    await user.click(screen.getByRole('button', { name: 'Add large' }));
    expect(screen.getByTestId('lines')).toHaveTextContent('2');
    expect(screen.getByTestId('count')).toHaveTextContent('3');

    await user.click(screen.getByRole('button', { name: 'Decrement first' }));
    await user.click(screen.getByRole('button', { name: 'Decrement first' }));
    expect(screen.getByTestId('lines')).toHaveTextContent('1');
    expect(screen.getByTestId('count')).toHaveTextContent('1');
  });
});
