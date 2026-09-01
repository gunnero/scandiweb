import { describe, expect, it } from 'vitest';
import { resolveGraphqlEndpoint } from './endpoint';

describe('resolveGraphqlEndpoint', () => {
  it('resolves the default endpoint against the current origin', () => {
    expect(resolveGraphqlEndpoint(undefined, 'http://localhost:3000')).toBe(
      'http://localhost:3000/graphql',
    );
  });

  it('preserves an absolute endpoint for split frontend and API hosting', () => {
    expect(
      resolveGraphqlEndpoint(
        'https://api.example.com/graphql',
        'https://shop.example.com',
      ),
    ).toBe('https://api.example.com/graphql');
  });
});
