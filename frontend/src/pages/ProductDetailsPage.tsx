import parse from 'html-react-parser';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import { fetchProduct } from '../api/catalog';
import { AsyncState } from '../components/AsyncState';
import { useCart } from '../context/CartContext';
import { useCatalog } from '../context/CatalogContext';
import type { Product } from '../types/catalog';
import { formatPrice, toKebabCase } from '../utils/format';

export function ProductDetailsPage() {
  const { id = '' } = useParams();
  const productId = decodeURIComponent(id);
  const { addItem, setOpen } = useCart();
  const { setActiveCategoryName } = useCatalog();
  const [product, setProduct] = useState<Product | null>(null);
  const [selectedAttributes, setSelectedAttributes] = useState<Record<string, string>>({});
  const [activeImage, setActiveImage] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [requestKey, setRequestKey] = useState(0);

  useEffect(() => {
    let active = true;
    setIsLoading(true);
    setError(null);

    fetchProduct(productId)
      .then((result) => {
        if (active) {
          setProduct(result);
          if (result) {
            setActiveCategoryName(result.categoryName);
          }
          setSelectedAttributes({});
          setActiveImage(0);
        }
      })
      .catch((requestError: unknown) => {
        if (active) {
          setProduct(null);
          setError(
            requestError instanceof Error
              ? requestError.message
              : 'The product could not be loaded.',
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
  }, [productId, requestKey, setActiveCategoryName]);

  const allAttributesSelected = useMemo(
    () =>
      product?.attributes.every((attribute) => Boolean(selectedAttributes[attribute.id])) ?? false,
    [product, selectedAttributes],
  );

  if (isLoading) {
    return <AsyncState title="Loading product" isLoading />;
  }

  if (error) {
    return (
      <AsyncState
        title="We could not load this product"
        message={error}
        actionLabel="Try again"
        onAction={() => setRequestKey((current) => current + 1)}
      />
    );
  }

  if (!product) {
    return <AsyncState title="Product not found" message="This product is no longer available." />;
  }

  const canAddToCart = product.inStock && allAttributesSelected;

  const previousImage = () => {
    setActiveImage((current) => (current - 1 + product.gallery.length) % product.gallery.length);
  };

  const nextImage = () => {
    setActiveImage((current) => (current + 1) % product.gallery.length);
  };

  const handleAddToCart = () => {
    if (!canAddToCart) {
      return;
    }

    addItem(product, selectedAttributes);
    setOpen(true);
  };

  return (
    <article className="product-details-page">
      <section className="product-gallery" data-testid="product-gallery" aria-label="Product gallery">
        <div className="gallery-thumbnails" aria-label="Product images">
          {product.gallery.map((image, index) => (
            <button
              className={`gallery-thumbnail${index === activeImage ? ' gallery-thumbnail-active' : ''}`}
              type="button"
              key={`${image}-${index}`}
              aria-label={`Show image ${index + 1} of ${product.gallery.length}`}
              aria-current={index === activeImage ? 'true' : undefined}
              onClick={() => setActiveImage(index)}
            >
              <img src={image} alt="" />
            </button>
          ))}
        </div>

        <div className="gallery-main">
          <img src={product.gallery[activeImage]} alt={product.name} />
          {product.gallery.length > 1 && (
            <>
              <button
                className="gallery-arrow gallery-arrow-left"
                type="button"
                aria-label="Previous product image"
                onClick={previousImage}
              >
                <ChevronLeft aria-hidden="true" />
              </button>
              <button
                className="gallery-arrow gallery-arrow-right"
                type="button"
                aria-label="Next product image"
                onClick={nextImage}
              >
                <ChevronRight aria-hidden="true" />
              </button>
            </>
          )}
        </div>
      </section>

      <section className="product-information">
        <p className="product-brand">{product.brand}</p>
        <h1>{product.name}</h1>

        {product.attributes.map((attribute) => (
          <fieldset
            className="product-attribute"
            data-testid={`product-attribute-${toKebabCase(attribute.name)}`}
            key={attribute.id}
          >
            <legend>{attribute.name}:</legend>
            <div className="product-attribute-options">
              {attribute.items.map((option) => {
                const selected = selectedAttributes[attribute.id] === option.id;
                const isSwatch = attribute.type === 'swatch';

                return (
                  <button
                    className={`product-option${isSwatch ? ' product-option-swatch' : ''}${
                      selected ? ' product-option-selected' : ''
                    }`}
                    type="button"
                    key={option.id}
                    aria-label={`${attribute.name}: ${option.displayValue}`}
                    aria-pressed={selected}
                    style={isSwatch ? { backgroundColor: option.value } : undefined}
                    title={option.displayValue}
                    onClick={() =>
                      setSelectedAttributes((current) => ({
                        ...current,
                        [attribute.id]: option.id,
                      }))
                    }
                  >
                    {!isSwatch && option.value}
                  </button>
                );
              })}
            </div>
          </fieldset>
        ))}

        <div className="product-price">
          <p>PRICE:</p>
          <strong>{formatPrice(product.prices[0])}</strong>
        </div>

        <button
          className="primary-button add-to-cart-button"
          type="button"
          data-testid="add-to-cart"
          disabled={!canAddToCart}
          onClick={handleAddToCart}
        >
          {product.inStock ? 'ADD TO CART' : 'OUT OF STOCK'}
        </button>

        <div className="product-description" data-testid="product-description">
          {parse(product.description)}
        </div>
      </section>
    </article>
  );
}
