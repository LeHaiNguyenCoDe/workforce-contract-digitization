export interface Category {
  id: string | number;
  name: string;
  icon?: string;
  image?: string;
  count?: number;
}

export interface Product {
  id: string | number;
  name: string;
  category: string;
  price: number;
  originalPrice?: number;
  rating: number;
  image: string;
  inStock: boolean;
  isHot?: boolean;
  isNew?: boolean;
  sku: string;
  brand?: string;
  colors?: string[];
  specs?: Record<string, string>;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface Article {
  id: string | number;
  title: string;
  excerpt: string;
  image: string;
  category: string;
  date: string;
}

export interface Store {
  name: string;
  address: string;
}

export interface MarketplaceState {
  categories: Category[];
  products: Product[];
  articles: Article[];
  stores: Store[];
}
