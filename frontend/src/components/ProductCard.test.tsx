import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import { describe, expect, it } from 'vitest';
import { CartProvider, useCart } from '../context/CartContext';
import type { Product } from '../types/catalog';
import { ProductCard } from './ProductCard';

const product: Product = {
  id: 'shoe',
  name: 'Great Shoe',
  description: '<p>Shoe</p>',
  categoryName: 'clothes',
  brand: 'Brand',
  inStock: true,
  gallery: ['main.jpg'],
  prices: [{ amount: 144.69, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
    {
      id: 'Size',
      name: 'Size',
      type: 'text',
      items: [
        { id: '40', displayValue: '40', value: '40' },
        { id: '41', displayValue: '41', value: '41' },
      ],
    },
  ],
};

function CartCount() {
  const { itemCount, items } = useCart();
  return (
    <output data-testid="cart-probe">
      {itemCount}:{items[0]?.selectedAttributes.Size ?? 'none'}
    </output>
  );
}

const renderCard = (item: Product) =>
  render(
    <MemoryRouter>
      <CartProvider>
        <ProductCard product={item} />
        <CartCount />
      </CartProvider>
    </MemoryRouter>,
  );

describe('ProductCard', () => {
  it('keeps an out-of-stock card linked to its PDP and removes Quick Shop', () => {
    renderCard({ ...product, inStock: false });

    expect(screen.getByRole('link', { name: /Great Shoe/ })).toHaveAttribute(
      'href',
      '/product/shoe',
    );
    expect(screen.getByText('OUT OF STOCK')).toBeVisible();
    expect(screen.queryByRole('button', { name: /Quick shop/ })).not.toBeInTheDocument();
  });

  it('Quick Shop adds the first value from every attribute', async () => {
    const user = userEvent.setup();
    renderCard(product);

    await user.click(screen.getByRole('button', { name: 'Quick shop Great Shoe' }));

    expect(screen.getByTestId('cart-probe')).toHaveTextContent('1:40');
  });
});
