import type { Price } from '../types/catalog';

export const toKebabCase = (value: string): string =>
  value
    .normalize('NFKD')
    .toLowerCase()
    .trim()
    .replace(/[\s_]+/g, '-')
    .replace(/[^a-z0-9#-]/g, '')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');

export const formatPrice = (price?: Price): string => {
  if (!price) {
    return '$0.00';
  }

  return `${price.currencySymbol}${Number(price.amount).toFixed(2)}`;
};

export const buildVariantId = (
  productId: string,
  selectedAttributes: Record<string, string>,
): string => {
  const selection = Object.entries(selectedAttributes).sort(([left], [right]) =>
    left.localeCompare(right),
  );

  return JSON.stringify([productId, selection]);
};
