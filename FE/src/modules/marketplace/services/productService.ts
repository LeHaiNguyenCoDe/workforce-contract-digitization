import type { Product, Category } from '../types';
import { BEST_OFFER_PRODUCTS, NEW_GOODS_PRODUCTS, POPULAR_CATEGORIES } from '../configs';

const MOCK_DELAY = 500;

export class ProductService {
  /**
   * Fetch all products (simulated)
   */
  async getProducts(): Promise<Product[]> {
    return new Promise((resolve) => {
      setTimeout(() => {
        // Combine all mock products to mimic a full catalog
        const allProducts = [...BEST_OFFER_PRODUCTS, ...NEW_GOODS_PRODUCTS];
        resolve(allProducts);
      }, MOCK_DELAY);
    });
  }

  async getBestOffers(): Promise<Product[]> {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(BEST_OFFER_PRODUCTS);
      }, MOCK_DELAY);
    });
  }

  async getNewGoods(): Promise<Product[]> {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(NEW_GOODS_PRODUCTS);
      }, MOCK_DELAY);
    });
  }

  /**
   * Fetch a single product by ID
   */
  async getProductById(id: string | number): Promise<Product | undefined> {
    return new Promise((resolve) => {
      setTimeout(() => {
        const allProducts = [...BEST_OFFER_PRODUCTS, ...NEW_GOODS_PRODUCTS];
        const product = allProducts.find((p) => p.id.toString() === id.toString());
        resolve(product);
      }, MOCK_DELAY);
    });
  }

  /**
   * Fetch categories
   */
  async getCategories(): Promise<Category[]> {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(POPULAR_CATEGORIES);
      }, MOCK_DELAY);
    });
  }
}

export const productService = new ProductService();
