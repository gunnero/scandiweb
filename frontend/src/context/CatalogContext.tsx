import {
  createContext,
  type PropsWithChildren,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import { fetchCategories } from '../api/catalog';
import type { Category } from '../types/catalog';

interface CatalogContextValue {
  categories: Category[];
  isLoading: boolean;
  error: string | null;
  activeCategoryName: string | null;
  setActiveCategoryName: (categoryName: string) => void;
  reload: () => Promise<void>;
}

const CatalogContext = createContext<CatalogContextValue | null>(null);

export function CatalogProvider({ children }: PropsWithChildren) {
  const [categories, setCategories] = useState<Category[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeCategoryName, setActiveCategoryName] = useState<string | null>(null);

  const reload = useCallback(async () => {
    setIsLoading(true);
    setError(null);

    try {
      setCategories(await fetchCategories());
    } catch (requestError) {
      setCategories([]);
      setError(
        requestError instanceof Error
          ? requestError.message
          : 'The catalog could not be loaded.',
      );
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    void reload();
  }, [reload]);

  const value = useMemo(
    () => ({
      categories,
      isLoading,
      error,
      activeCategoryName,
      setActiveCategoryName,
      reload,
    }),
    [activeCategoryName, categories, error, isLoading, reload],
  );

  return <CatalogContext.Provider value={value}>{children}</CatalogContext.Provider>;
}

export function useCatalog(): CatalogContextValue {
  const context = useContext(CatalogContext);

  if (!context) {
    throw new Error('useCatalog must be used inside CatalogProvider.');
  }

  return context;
}
