import { useEffect, useRef, useState } from 'react';
import { X } from 'lucide-react';
import { createOrder } from '../api/catalog';
import { useCart } from '../context/CartContext';
import type { CartItem } from '../types/catalog';
import { formatPrice, toKebabCase } from '../utils/format';

const itemLabel = (count: number): string => `${count} ${count === 1 ? 'Item' : 'Items'}`;

const optionTestId = (
  attributeName: string,
  value: string,
  selected: boolean,
): string => {
  const base = `cart-item-attribute-${toKebabCase(attributeName)}-${toKebabCase(value)}`;
  return selected ? `${base}-selected` : base;
};

function CartLine({
  item,
  isSubmitting,
  onIncrement,
  onDecrement,
}: {
  item: CartItem;
  isSubmitting: boolean;
  onIncrement: () => void;
  onDecrement: () => void;
}) {
  return (
    <article className="cart-line">
      <div className="cart-line-details">
        <h3>{item.product.name}</h3>
        <p className="cart-line-price">{formatPrice(item.product.prices[0])}</p>

        {item.product.attributes.map((attribute) => (
          <div
            className="cart-attribute"
            data-testid={`cart-item-attribute-${toKebabCase(attribute.name)}`}
            key={attribute.id}
          >
            <p>{attribute.name}:</p>
            <div className="cart-attribute-options" aria-label={`${attribute.name} options`}>
              {attribute.items.map((option) => {
                const selected = item.selectedAttributes[attribute.id] === option.id;
                const isSwatch = attribute.type === 'swatch';

                return (
                  <span
                    className={`cart-option${isSwatch ? ' cart-option-swatch' : ''}${
                      selected ? ' cart-option-selected' : ''
                    }`}
                    data-testid={optionTestId(attribute.name, option.displayValue, selected)}
                    key={option.id}
                    style={isSwatch ? { backgroundColor: option.value } : undefined}
                    title={option.displayValue}
                    aria-label={`${option.displayValue}${selected ? ', selected' : ''}`}
                  >
                    {!isSwatch && option.value}
                  </span>
                );
              })}
            </div>
          </div>
        ))}
      </div>

      <div className="cart-line-quantity">
        <button
          type="button"
          data-testid="cart-item-amount-increase"
          aria-label={`Increase ${item.product.name} quantity`}
          disabled={isSubmitting}
          onClick={onIncrement}
        >
          +
        </button>
        <span data-testid="cart-item-amount">{item.quantity}</span>
        <button
          type="button"
          data-testid="cart-item-amount-decrease"
          aria-label={`Decrease ${item.product.name} quantity`}
          disabled={isSubmitting}
          onClick={onDecrement}
        >
          −
        </button>
      </div>

      <img
        className="cart-line-image"
        src={item.product.gallery[0]}
        alt={item.product.name}
      />
    </article>
  );
}

export function CartOverlay() {
  const {
    items,
    isOpen,
    itemCount,
    total,
    increment,
    decrement,
    clear,
    setOpen,
  } = useCart();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [message, setMessage] = useState<string | null>(null);
  const panelRef = useRef<HTMLElement>(null);

  useEffect(() => {
    if (!isOpen) {
      return undefined;
    }

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    panelRef.current?.focus();

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && !isSubmitting) {
        setOpen(false);
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [isOpen, isSubmitting, setOpen]);

  if (!isOpen) {
    return null;
  }

  const handlePlaceOrder = async () => {
    if (items.length === 0 || isSubmitting) {
      return;
    }

    setIsSubmitting(true);
    setMessage(null);

    try {
      const order = await createOrder(
        items.map((item) => ({
          productId: item.product.id,
          quantity: item.quantity,
          selectedAttributes: Object.entries(item.selectedAttributes).map(
            ([attributeId, itemId]) => ({ attributeId, itemId }),
          ),
        })),
      );
      clear();
      setMessage(`Order #${order.id} was placed successfully.`);
    } catch (requestError) {
      setMessage(
        requestError instanceof Error
          ? requestError.message
          : 'The order could not be placed. Please try again.',
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <>
      <button
        className="cart-backdrop"
        type="button"
        aria-label="Close cart"
        onClick={() => !isSubmitting && setOpen(false)}
      />
      <section
        className="cart-overlay"
        id="cart-overlay"
        role="dialog"
        aria-modal="true"
        aria-label="Shopping cart"
        ref={panelRef}
        tabIndex={-1}
      >
        <div className="cart-overlay-header">
          <p>
            <strong>My Bag,</strong> {itemLabel(itemCount)}
          </p>
          <button
            className="cart-close"
            type="button"
            aria-label="Close cart"
            disabled={isSubmitting}
            onClick={() => setOpen(false)}
          >
            <X size={18} aria-hidden="true" />
          </button>
        </div>

        <div className="cart-lines">
          {items.length === 0 && <p className="cart-empty">Your cart is empty.</p>}
          {items.map((item) => (
            <CartLine
              item={item}
              isSubmitting={isSubmitting}
              key={item.variantId}
              onIncrement={() => increment(item.variantId)}
              onDecrement={() => decrement(item.variantId)}
            />
          ))}
        </div>

        <div className="cart-summary">
          <div className="cart-total-row">
            <strong>Total</strong>
            <strong data-testid="cart-total">${total.toFixed(2)}</strong>
          </div>
          {message && (
            <p className="cart-message" role="status">
              {message}
            </p>
          )}
          <button
            className="primary-button place-order-button"
            type="button"
            disabled={items.length === 0 || isSubmitting}
            onClick={() => void handlePlaceOrder()}
          >
            {isSubmitting ? 'PLACING ORDER…' : 'PLACE ORDER'}
          </button>
        </div>
      </section>
    </>
  );
}
