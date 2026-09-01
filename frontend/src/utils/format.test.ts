import { describe, expect, it } from 'vitest';
import { buildVariantId, formatPrice, toKebabCase } from './format';

describe('format helpers', () => {
  it('creates the exact selector slugs used by Auto QA', () => {
    expect(toKebabCase('iPhone 12 Pro')).toBe('iphone-12-pro');
    expect(toKebabCase('Touch ID in keyboard')).toBe('touch-id-in-keyboard');
    expect(toKebabCase('Extra Large')).toBe('extra-large');
    expect(toKebabCase('#44FF03')).toBe('44ff03');
  });

  it('always renders two decimal places', () => {
    expect(
      formatPrice({ amount: 120.5, currencyLabel: 'USD', currencySymbol: '$' }),
    ).toBe('$120.50');
  });

  it('builds the same variant identity regardless of attribute order', () => {
    expect(buildVariantId('product', { Size: 'Small', Color: 'Black' })).toBe(
      buildVariantId('product', { Color: 'Black', Size: 'Small' }),
    );
  });
});
