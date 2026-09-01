import { useEffect } from 'react';
import {
  BrowserRouter,
  Navigate,
  Route,
  Routes,
  useLocation,
} from 'react-router-dom';
import { AsyncState } from './components/AsyncState';
import { CartOverlay } from './components/CartOverlay';
import { Header } from './components/Header';
import { CartProvider } from './context/CartContext';
import { CatalogProvider, useCatalog } from './context/CatalogContext';
import { ProductDetailsPage } from './pages/ProductDetailsPage';
import { ProductListPage } from './pages/ProductListPage';

function ScrollToTop() {
  const { pathname } = useLocation();

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'auto' });
  }, [pathname]);

  return null;
}

function DefaultCategoryRoute() {
  const { categories, isLoading, error, reload } = useCatalog();

  if (isLoading) {
    return <AsyncState title="Loading catalog" isLoading />;
  }

  if (error) {
    return (
      <AsyncState
        title="We could not reach the catalog"
        message={error}
        actionLabel="Try again"
        onAction={() => void reload()}
      />
    );
  }

  if (categories.length === 0) {
    return <AsyncState title="The catalog is empty" />;
  }

  return <Navigate replace to={`/category/${encodeURIComponent(categories[0].name)}`} />;
}

function Storefront() {
  return (
    <BrowserRouter>
      <ScrollToTop />
      <Header />
      <CartOverlay />
      <main className="site-main">
        <Routes>
          <Route path="/" element={<DefaultCategoryRoute />} />
          <Route path="/category/:categoryName" element={<ProductListPage />} />
          <Route path="/product/:id" element={<ProductDetailsPage />} />
          <Route path="*" element={<DefaultCategoryRoute />} />
        </Routes>
      </main>
    </BrowserRouter>
  );
}

export default function App() {
  return (
    <CatalogProvider>
      <CartProvider>
        <Storefront />
      </CartProvider>
    </CatalogProvider>
  );
}
