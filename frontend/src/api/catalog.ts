import { GraphQLClient, gql } from 'graphql-request';
import type {
  Category,
  Order,
  OrderItemInput,
  Product,
} from '../types/catalog';
import { resolveGraphqlEndpoint } from './endpoint';

const endpoint = resolveGraphqlEndpoint(
  import.meta.env.VITE_GRAPHQL_ENDPOINT,
  window.location.origin,
);

const client = new GraphQLClient(endpoint, {
  headers: {
    Accept: 'application/json',
  },
});

const PRODUCT_FIELDS = gql`
  fragment ProductFields on Product {
    id
    name
    description
    categoryName
    brand
    inStock
    gallery
    prices {
      amount
      currencyLabel
      currencySymbol
    }
    attributes {
      id
      name
      type
      items {
        id
        displayValue
        value
      }
    }
  }
`;

const CATEGORIES_QUERY = gql`
  query Categories {
    categories {
      id
      name
    }
  }
`;

const PRODUCTS_BY_CATEGORY_QUERY = gql`
  ${PRODUCT_FIELDS}
  query ProductsByCategory($categoryName: String!) {
    productsByCategory(categoryName: $categoryName) {
      ...ProductFields
    }
  }
`;

const PRODUCT_QUERY = gql`
  ${PRODUCT_FIELDS}
  query Product($id: ID!) {
    product(id: $id) {
      ...ProductFields
    }
  }
`;

const CREATE_ORDER_MUTATION = gql`
  mutation CreateOrder($items: [OrderItemInput!]!) {
    createOrder(items: $items) {
      id
      total
      status
      createdAt
    }
  }
`;

const assertList = <T>(value: T[] | undefined, name: string): T[] => {
  if (!Array.isArray(value)) {
    throw new Error(`The GraphQL API returned an invalid ${name} response.`);
  }

  return value;
};

export const fetchCategories = async (): Promise<Category[]> => {
  const response = await client.request<{ categories: Category[] }>(CATEGORIES_QUERY);
  return assertList(response.categories, 'categories');
};

export const fetchProductsByCategory = async (categoryName: string): Promise<Product[]> => {
  const response = await client.request<{ productsByCategory: Product[] }>(
    PRODUCTS_BY_CATEGORY_QUERY,
    { categoryName },
  );

  return assertList(response.productsByCategory, 'products');
};

export const fetchProduct = async (id: string): Promise<Product | null> => {
  const response = await client.request<{ product: Product | null }>(PRODUCT_QUERY, { id });
  return response.product;
};

export const createOrder = async (items: OrderItemInput[]): Promise<Order> => {
  const response = await client.request<{ createOrder: Order }>(CREATE_ORDER_MUTATION, { items });

  if (!response.createOrder) {
    throw new Error('The order could not be created.');
  }

  return response.createOrder;
};
