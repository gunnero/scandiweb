import { NavLink } from 'react-router-dom';
import brandLogo from '../assets/a-logo.svg';
import { useCart } from '../context/CartContext';
import { useCatalog } from '../context/CatalogContext';

export function Header() {
  const { activeCategoryName, categories } = useCatalog();
  const { isOpen, itemCount, setOpen } = useCart();

  return (
    <header className="site-header">
      <div className="header-content">
        <nav className="category-navigation" aria-label="Product categories">
          {categories.map((category) => {
            const destination = `/category/${encodeURIComponent(category.name)}`;
            const isActive = activeCategoryName === category.name;

            return (
              <NavLink
                className={`category-link${isActive ? ' category-link-active' : ''}`}
                data-testid={isActive ? 'active-category-link' : 'category-link'}
                key={category.id}
                to={destination}
              >
                {category.name}
              </NavLink>
            );
          })}
        </nav>

        <NavLink className="brand-mark" to="/" aria-label="Scandiweb storefront home">
          <img src={brandLogo} alt="" aria-hidden="true" />
        </NavLink>

        <button
          className="cart-trigger"
          type="button"
          data-testid="cart-btn"
          aria-label={`Open cart, ${itemCount} ${itemCount === 1 ? 'item' : 'items'}`}
          aria-controls="cart-overlay"
          aria-expanded={isOpen}
          onClick={() => setOpen(!isOpen)}
        >
          <svg viewBox="0 0 20 18" aria-hidden="true">
            <path d="M1.5 1.5h2l1.8 9.2h9.2l2-6.6H5.1M7.1 15.3a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0Zm8 0a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0Z" />
          </svg>
          {itemCount > 0 && <span className="cart-count">{itemCount}</span>}
        </button>
      </div>
    </header>
  );
}
