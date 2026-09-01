import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { CartProvider, useCart } from '../context/CartContext';
import { CatalogProvider } from '../context/CatalogContext';
import type { Product } from '../types/catalog';
import { ProductDetailsPage } from './ProductDetailsPage';

const catalogApi = vi.hoisted(() => ({
  fetchCategories: vi.fn(),
  fetchProduct: vi.fn(),
}));

vi.mock('../api/catalog', () => catalogApi);

const product: Product = {
  id: 'trail-runner',
  name: 'Trail Runner',
  description:
    '<p>Built for <strong>comfort</strong> &amp; speed.</p><ul><li>Lightweight</li></ul>',
  categoryName: 'clothes',
  brand: 'Active',
  inStock: true,
  gallery: ['front.jpg', 'back.jpg', 'detail.jpg'],
  prices: [{ amount: 129.9, currencyLabel: 'USD', currencySymbol: '$' }],
  attributes: [
    {
      id: 'Size',
      name: 'Size',
      type: 'text',
      items: [
        { id: 'S', displayValue: 'Small', value: 'S' },
        { id: 'M', displayValue: 'Medium', value: 'M' },
      ],
    },
    {
      id: 'Color',
      name: 'Color',
      type: 'swatch',
      items: [
        { id: 'Red', displayValue: 'Red', value: '#ff0000' },
        { id: 'Blue', displayValue: 'Blue', value: '#0000ff' },
      ],
    },
  ],
};

function CartProbe() {
  const { isOpen, itemCount, items } = useCart();
  const item = items[0];

  return (
    <output data-testid="cart-probe">
      {`${isOpen}:${itemCount}:${item?.selectedAttributes.Size ?? 'none'}:${
        item?.selectedAttributes.Color ?? 'none'
      }`}
    </output>
  );
}

function renderProductDetails() {
  return render(
    <MemoryRouter initialEntries={['/product/trail-runner']}>
      <CatalogProvider>
        <CartProvider>
          <Routes>
            <Route
              path="/product/:id"
              element={
                <>
                  <ProductDetailsPage />
                  <CartProbe />
                </>
              }
            />
          </Routes>
        </CartProvider>
      </CatalogProvider>
    </MemoryRouter>,
  );
}

describe('ProductDetailsPage', () => {
  beforeEach(() => {
    catalogApi.fetchCategories.mockReset();
    catalogApi.fetchProduct.mockReset();
    catalogApi.fetchCategories.mockResolvedValue([{ id: 'clothes', name: 'clothes' }]);
    catalogApi.fetchProduct.mockResolvedValue(product);
  });

  it('renders required details and adds the fully configured product to an open cart', async () => {
    const user = userEvent.setup();
    renderProductDetails();

    expect(await screen.findByRole('heading', { name: 'Trail Runner' })).toBeVisible();
    expect(catalogApi.fetchProduct).toHaveBeenCalledOnce();
    expect(catalogApi.fetchProduct).toHaveBeenCalledWith('trail-runner');
    expect(screen.getByText('$129.90')).toBeVisible();

    const description = screen.getByTestId('product-description');
    expect(description.querySelector('p')).toHaveTextContent('Built for comfort & speed.');
    expect(description.querySelector('strong')).toHaveTextContent('comfort');
    expect(description.querySelector('li')).toHaveTextContent('Lightweight');
    expect(description).not.toHaveTextContent('<p>');

    const sizeAttribute = screen.getByTestId('product-attribute-size');
    const colorAttribute = screen.getByTestId('product-attribute-color');
    const addToCart = screen.getByTestId('add-to-cart');

    expect(sizeAttribute).toHaveTextContent('Size:');
    expect(colorAttribute).toHaveTextContent('Color:');
    expect(addToCart.nextElementSibling).toBe(description);
    expect(addToCart).toBeDisabled();
    expect(screen.getByTestId('cart-probe')).toHaveTextContent('false:0:none:none');

    await user.click(within(sizeAttribute).getByRole('button', { name: 'Size: Medium' }));
    expect(addToCart).toBeDisabled();
    expect(within(sizeAttribute).getByRole('button', { name: 'Size: Medium' })).toHaveAttribute(
      'aria-pressed',
      'true',
    );

    const redSwatch = within(colorAttribute).getByRole('button', { name: 'Color: Red' });
    await user.click(redSwatch);
    expect(redSwatch).toHaveAttribute('aria-pressed', 'true');
    expect(redSwatch).toHaveStyle({ backgroundColor: '#ff0000' });
    expect(addToCart).toBeEnabled();

    await user.click(addToCart);

    expect(screen.getByTestId('cart-probe')).toHaveTextContent('true:1:M:Red');
  });

  it('shows every gallery image and changes the main image with thumbnails and arrows', async () => {
    const user = userEvent.setup();
    renderProductDetails();

    const gallery = await screen.findByTestId('product-gallery');
    const mainImage = within(gallery).getByRole('img', { name: 'Trail Runner' });
    const firstThumbnail = within(gallery).getByRole('button', {
      name: 'Show image 1 of 3',
    });
    const secondThumbnail = within(gallery).getByRole('button', {
      name: 'Show image 2 of 3',
    });
    const thirdThumbnail = within(gallery).getByRole('button', {
      name: 'Show image 3 of 3',
    });

    expect(mainImage).toHaveAttribute('src', 'front.jpg');
    expect(firstThumbnail).toHaveAttribute('aria-current', 'true');
    expect(within(firstThumbnail).getByRole('presentation')).toHaveAttribute('src', 'front.jpg');
    expect(within(secondThumbnail).getByRole('presentation')).toHaveAttribute('src', 'back.jpg');
    expect(within(thirdThumbnail).getByRole('presentation')).toHaveAttribute('src', 'detail.jpg');
    expect(within(gallery).getByRole('button', { name: 'Previous product image' })).toBeVisible();
    expect(within(gallery).getByRole('button', { name: 'Next product image' })).toBeVisible();

    await user.click(secondThumbnail);
    expect(mainImage).toHaveAttribute('src', 'back.jpg');
    expect(secondThumbnail).toHaveAttribute('aria-current', 'true');

    await user.click(within(gallery).getByRole('button', { name: 'Next product image' }));
    expect(mainImage).toHaveAttribute('src', 'detail.jpg');

    await user.click(within(gallery).getByRole('button', { name: 'Previous product image' }));
    expect(mainImage).toHaveAttribute('src', 'back.jpg');
  });

  it('never enables Add to Cart for an out-of-stock product', async () => {
    catalogApi.fetchProduct.mockResolvedValue({ ...product, inStock: false });
    const user = userEvent.setup();
    renderProductDetails();

    const addToCart = await screen.findByTestId('add-to-cart');
    expect(addToCart).toBeDisabled();
    expect(addToCart).toHaveTextContent('OUT OF STOCK');

    await user.click(screen.getByRole('button', { name: 'Size: Small' }));
    await user.click(screen.getByRole('button', { name: 'Color: Blue' }));

    expect(addToCart).toBeDisabled();
    expect(screen.getByTestId('cart-probe')).toHaveTextContent('false:0:none:none');
  });
});
