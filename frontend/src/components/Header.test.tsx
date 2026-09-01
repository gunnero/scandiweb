import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useEffect } from 'react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { CartProvider, useCart } from '../context/CartContext';
import type { Product } from '../types/catalog';
import { Header } from './Header';

vi.mock('../context/CatalogContext', () => ({
  useCatalog: () => ({
    activeCategoryName: null,
    categories: [],
  }),
}));

const product: Product = {
  id: 'test-product',
  name: 'Test product',
  description: '<p>Test product</p>',
  categoryName: 'all',
  brand: 'Test brand',
  inStock: true,
  gallery: ['test-product.jpg'],
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

function SeedCart() {
  const { addItem } = useCart();

  useEffect(() => {
    addItem(product, { Size: 'Small' });
    addItem(product, { Size: 'Small' });
    addItem(product, { Size: 'Large' });
  }, [addItem]);

  return null;
}

function renderHeader({ withCartItems = false } = {}) {
  return render(
    <MemoryRouter>
      <CartProvider>
        {withCartItems && <SeedCart />}
        <Header />
      </CartProvider>
    </MemoryRouter>,
  );
}

describe('Header cart button', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it('uses the required test id, hides the zero-count bubble, and toggles the cart', async () => {
    const user = userEvent.setup();
    const { container } = renderHeader();
    const cartButton = screen.getByTestId('cart-btn');

    expect(cartButton).toBeVisible();
    expect(cartButton).toHaveAttribute('aria-expanded', 'false');
    expect(container.querySelector('.cart-count')).not.toBeInTheDocument();

    await user.click(cartButton);
    expect(cartButton).toHaveAttribute('aria-expanded', 'true');

    await user.click(cartButton);
    expect(cartButton).toHaveAttribute('aria-expanded', 'false');
  });

  it('shows a bubble with the total quantity across cart variants', async () => {
    const { container } = renderHeader({ withCartItems: true });
    const bubble = await screen.findByText('3');

    expect(bubble).toHaveClass('cart-count');
    expect(container.querySelectorAll('.cart-count')).toHaveLength(1);
    expect(screen.getByTestId('cart-btn')).toHaveAccessibleName(/Open cart, 3/i);
  });
});
