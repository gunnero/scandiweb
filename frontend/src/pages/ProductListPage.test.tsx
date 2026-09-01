import { render, screen, waitFor } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { CartProvider } from '../context/CartContext';
import { CatalogProvider, useCatalog } from '../context/CatalogContext';
import type { Product } from '../types/catalog';
import { ProductListPage } from './ProductListPage';

const catalogApi = vi.hoisted(() => ({
  fetchCategories: vi.fn(),
  fetchProductsByCategory: vi.fn(),
}));

vi.mock('../api/catalog', () => catalogApi);

const returnedProduct: Product = {
  id: 'oak-table',
  name: 'Oak Table',
  description: '<p>A solid oak table.</p>',
  categoryName: 'home decor',
  brand: 'Woodworks',
  inStock: true,
  gallery: ['oak-table.jpg'],
  prices: [{ amount: 499.9, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [],
};

function ActiveCategoryProbe() {
  const { activeCategoryName } = useCatalog();

  return <output data-testid="active-category-probe">{activeCategoryName}</output>;
}

describe('ProductListPage category routing', () => {
  beforeEach(() => {
    catalogApi.fetchCategories.mockReset();
    catalogApi.fetchProductsByCategory.mockReset();
    catalogApi.fetchCategories.mockResolvedValue([{ id: 'home-decor', name: 'home decor' }]);
    catalogApi.fetchProductsByCategory.mockResolvedValue([returnedProduct]);
  });

  it('decodes the route category, activates it, and renders products returned for it', async () => {
    render(
      <MemoryRouter initialEntries={['/category/home%20decor']}>
        <CatalogProvider>
          <CartProvider>
            <Routes>
              <Route
                path="/category/:categoryName"
                element={
                  <>
                    <ProductListPage />
                    <ActiveCategoryProbe />
                  </>
                }
              />
            </Routes>
          </CartProvider>
        </CatalogProvider>
      </MemoryRouter>,
    );

    await waitFor(() => {
      expect(catalogApi.fetchProductsByCategory).toHaveBeenCalledOnce();
      expect(catalogApi.fetchProductsByCategory).toHaveBeenCalledWith('home decor');
    });

    expect(await screen.findByTestId('active-category-probe')).toHaveTextContent('home decor');
    expect(await screen.findByTestId('product-oak-table')).toBeVisible();
    expect(screen.getByRole('heading', { name: 'Oak Table' })).toBeVisible();
  });
});
