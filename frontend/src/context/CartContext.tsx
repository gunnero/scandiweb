import {
  createContext,
  type PropsWithChildren,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import type { CartItem, Product } from '../types/catalog';
import { buildVariantId } from '../utils/format';

const STORAGE_KEY = 'scandiweb-cart';

interface CartContextValue {
  items: CartItem[];
  isOpen: boolean;
  itemCount: number;
  total: number;
  addItem: (product: Product, selectedAttributes: Record<string, string>) => void;
  increment: (variantId: string) => void;
  decrement: (variantId: string) => void;
  clear: () => void;
  setOpen: (isOpen: boolean) => void;
}

const CartContext = createContext<CartContextValue | null>(null);

const restoreCart = (): CartItem[] => {
  try {
    const stored = localStorage.getItem(STORAGE_KEY);
    const parsed = stored ? JSON.parse(stored) : [];
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
};

export function CartProvider({ children }: PropsWithChildren) {
  const [items, setItems] = useState<CartItem[]>(restoreCart);
  const [isOpen, setOpen] = useState(false);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
  }, [items]);

  const addItem = useCallback(
    (product: Product, selectedAttributes: Record<string, string>) => {
      const variantId = buildVariantId(product.id, selectedAttributes);

      setItems((currentItems) => {
        const existingItem = currentItems.find((item) => item.variantId === variantId);

        if (existingItem) {
          return currentItems.map((item) =>
            item.variantId === variantId ? { ...item, quantity: item.quantity + 1 } : item,
          );
        }

        return [
          ...currentItems,
          {
            variantId,
            product,
            selectedAttributes,
            quantity: 1,
          },
        ];
      });
    },
    [],
  );

  const increment = useCallback((variantId: string) => {
    setItems((currentItems) =>
      currentItems.map((item) =>
        item.variantId === variantId ? { ...item, quantity: item.quantity + 1 } : item,
      ),
    );
  }, []);

  const decrement = useCallback((variantId: string) => {
    setItems((currentItems) =>
      currentItems.flatMap((item) => {
        if (item.variantId !== variantId) {
          return [item];
        }

        return item.quantity > 1 ? [{ ...item, quantity: item.quantity - 1 }] : [];
      }),
    );
  }, []);

  const clear = useCallback(() => setItems([]), []);

  const itemCount = useMemo(
    () => items.reduce((sum, item) => sum + item.quantity, 0),
    [items],
  );
  const total = useMemo(
    () =>
      items.reduce(
        (sum, item) => sum + Number(item.product.prices[0]?.amount ?? 0) * item.quantity,
        0,
      ),
    [items],
  );

  const value = useMemo(
    () => ({
      items,
      isOpen,
      itemCount,
      total,
      addItem,
      increment,
      decrement,
      clear,
      setOpen,
    }),
    [addItem, clear, decrement, increment, isOpen, itemCount, items, total],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart(): CartContextValue {
  const context = useContext(CartContext);

  if (!context) {
    throw new Error('useCart must be used inside CartProvider.');
  }

  return context;
}
