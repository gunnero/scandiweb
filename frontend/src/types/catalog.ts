export interface Category {
  id: string;
  name: string;
}

export interface AttributeItem {
  id: string;
  displayValue: string;
  value: string;
}

export interface ProductAttribute {
  id: string;
  name: string;
  type: 'text' | 'swatch' | string;
  items: AttributeItem[];
}

export interface Price {
  amount: number;
  currencyLabel: string;
  currencySymbol: string;
}

export interface Product {
  id: string;
  name: string;
  description: string;
  categoryName: string;
  brand: string;
  inStock: boolean;
  gallery: string[];
  prices: Price[];
  attributes: ProductAttribute[];
}

export interface SelectedAttributeInput {
  attributeId: string;
  itemId: string;
}

export interface OrderItemInput {
  productId: string;
  quantity: number;
  selectedAttributes: SelectedAttributeInput[];
}

export interface Order {
  id: string;
  total: number;
  status: string;
  createdAt: string;
}

export interface CartItem {
  variantId: string;
  product: Product;
  selectedAttributes: Record<string, string>;
  quantity: number;
}
