import { render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import App from './App';

const catalogApi = vi.hoisted(() => ({
  fetchCategories: vi.fn(),
  fetchProductsByCategory: vi.fn(),
}));

vi.mock('./api/catalog', () => ({
  createOrder: vi.fn(),
  fetchCategories: catalogApi.fetchCategories,
  fetchProductsByCategory: catalogApi.fetchProductsByCategory,
}));

describe('App default category route', () => {
  beforeEach(() => {
    window.history.replaceState(null, '', '/');
    window.scrollTo = vi.fn();
    catalogApi.fetchCategories.mockReset();
    catalogApi.fetchProductsByCategory.mockReset();
  });

  it('redirects the root route to the first category returned by the catalog', async () => {
    catalogApi.fetchCategories.mockResolvedValue([
      { id: 'first', name: 'first category' },
      { id: 'second', name: 'second category' },
    ]);
    catalogApi.fetchProductsByCategory.mockResolvedValue([]);

    render(<App />);

    expect(
      await screen.findByRole('heading', { name: 'first category' }),
    ).toBeVisible();
    await waitFor(() => {
      expect(window.location.pathname).toBe('/first%20category');
    });
    expect(catalogApi.fetchProductsByCategory).toHaveBeenCalledWith('first category');
    expect(catalogApi.fetchProductsByCategory).not.toHaveBeenCalledWith(
      'second category',
    );
  });
});
