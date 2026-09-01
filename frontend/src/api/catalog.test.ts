import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { Order, OrderItemInput } from '../types/catalog';

const { requestMock } = vi.hoisted(() => ({
  requestMock: vi.fn(),
}));

vi.mock('graphql-request', () => ({
  GraphQLClient: class MockGraphQLClient {
    request = requestMock;
  },
  gql: (parts: TemplateStringsArray, ...values: unknown[]) =>
    parts.reduce(
      (document, part, index) =>
        `${document}${index > 0 ? String(values[index - 1]) : ''}${part}`,
      '',
    ),
}));

import { createOrder } from './catalog';

describe('createOrder', () => {
  beforeEach(() => {
    requestMock.mockReset();
  });

  it('sends the CreateOrder mutation with its items and returns the created order', async () => {
    const items: OrderItemInput[] = [
      {
        productId: 'apple-iphone-12-pro',
        quantity: 2,
        selectedAttributes: [
          { attributeId: 'Capacity', itemId: '256GB' },
          { attributeId: 'Color', itemId: 'Green' },
        ],
      },
    ];
    const order: Order = {
      id: 'order-42',
      total: 1999.98,
      status: 'PLACED',
      createdAt: '2026-09-02T12:00:00+00:00',
    };
    requestMock.mockResolvedValueOnce({ createOrder: order });

    await expect(createOrder(items)).resolves.toEqual(order);

    expect(requestMock).toHaveBeenCalledTimes(1);
    const [document, variables] = requestMock.mock.calls[0] as [string, unknown];
    const normalizedDocument = document.replace(/\s+/g, ' ').trim();

    expect(variables).toEqual({ items });
    expect(normalizedDocument).toContain(
      'mutation CreateOrder($items: [OrderItemInput!]!)',
    );
    expect(normalizedDocument).toMatch(
      /createOrder\(items: \$items\) \{ id total status createdAt \}/,
    );
  });

  it('rejects an empty mutation result', async () => {
    requestMock.mockResolvedValueOnce({ createOrder: null });

    await expect(createOrder([])).rejects.toThrow('The order could not be created.');
  });
});
