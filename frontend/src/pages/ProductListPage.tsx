import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { fetchProductsByCategory } from '../api/catalog';
import { AsyncState } from '../components/AsyncState';
import { ProductCard } from '../components/ProductCard';
import { useCatalog } from '../context/CatalogContext';
import type { Product } from '../types/catalog';

export function ProductListPage() {
  const { categoryName = '' } = useParams();
  const decodedCategoryName = decodeURIComponent(categoryName);
  const { setActiveCategoryName } = useCatalog();
  const [products, setProducts] = useState<Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [requestKey, setRequestKey] = useState(0);

  useEffect(() => {
    setActiveCategoryName(decodedCategoryName);
  }, [decodedCategoryName, setActiveCategoryName]);

  useEffect(() => {
    let active = true;
    setIsLoading(true);
    setError(null);

    fetchProductsByCategory(decodedCategoryName)
      .then((result) => {
        if (active) {
          setProducts(result);
        }
      })
      .catch((requestError: unknown) => {
        if (active) {
          setProducts([]);
          setError(
            requestError instanceof Error
              ? requestError.message
              : 'Products could not be loaded.',
          );
        }
      })
      .finally(() => {
        if (active) {
          setIsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, [decodedCategoryName, requestKey]);

  if (isLoading) {
    return <AsyncState title="Loading products" isLoading />;
  }

  if (error) {
    return (
      <AsyncState
        title="We could not load this category"
        message={error}
        actionLabel="Try again"
        onAction={() => setRequestKey((current) => current + 1)}
      />
    );
  }

  return (
    <section className="catalog-page">
      <h1>{decodedCategoryName}</h1>
      {products.length > 0 ? (
        <div className="product-grid">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      ) : (
        <p className="empty-category">There are no products in this category.</p>
      )}
    </section>
  );
}
