import { Link } from 'react-router-dom';
import { useCart } from '../context/CartContext';
import type { Product } from '../types/catalog';
import { formatPrice, toKebabCase } from '../utils/format';
import { ShoppingCartIcon } from './Icons';

export function ProductCard({ product }: { product: Product }) {
  const { addItem } = useCart();

  const handleQuickShop = () => {
    const defaultAttributes = Object.fromEntries(
      product.attributes.map((attribute) => [attribute.id, attribute.items[0]?.id]),
    );

    addItem(product, defaultAttributes);
  };

  return (
    <article
      className={`product-card${product.inStock ? '' : ' product-card-out-of-stock'}`}
      data-testid={`product-${toKebabCase(product.name)}`}
    >
      <Link className="product-card-link" to={`/product/${encodeURIComponent(product.id)}`}>
        <div className="product-card-image-wrap">
          <img
            className="product-card-image"
            src={product.gallery[0]}
            alt={product.name}
            loading="lazy"
          />
          {!product.inStock && <span className="stock-label">OUT OF STOCK</span>}
        </div>
        <h2>{product.name}</h2>
        <p>{formatPrice(product.prices[0])}</p>
      </Link>

      {product.inStock && (
        <button
          className="quick-shop-button"
          type="button"
          aria-label={`Quick shop ${product.name}`}
          onClick={handleQuickShop}
        >
          <ShoppingCartIcon width={22} height={22} strokeWidth={2.2} aria-hidden="true" />
        </button>
      )}
    </article>
  );
}
