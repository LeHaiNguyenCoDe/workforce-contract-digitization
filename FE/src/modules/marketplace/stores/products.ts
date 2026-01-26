import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Product, Category } from '../types';
import { productService } from '../services/productService';

export const useProductStore = defineStore('marketplace-products', () => {
  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const currentProduct = ref<Product | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Filters State
  const activeCategory = ref<string | null>(null);
  const searchQuery = ref('');
  const sortBy = ref('default');

  const filteredProducts = computed(() => {
    let result = [...products.value];

    if (activeCategory.value) {
      result = result.filter(p => p.category === activeCategory.value);
    }

    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase();
      result = result.filter(p => 
        p.name.toLowerCase().includes(query) || 
        p.category.toLowerCase().includes(query)
      );
    }

    // Sorting
    switch (sortBy.value) {
      case 'price-low':
        result.sort((a, b) => a.price - b.price);
        break;
      case 'price-high':
        result.sort((a, b) => b.price - a.price);
        break;
      case 'rating':
        result.sort((a, b) => b.rating - a.rating);
        break;
    }

    return result;
  });

  const fetchProducts = async () => {
    loading.value = true;
    error.value = null;
    try {
      products.value = await productService.getProducts();
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch products';
    } finally {
      loading.value = false;
    }
  };

  const bestOffers = ref<Product[]>([]);
  const newGoods = ref<Product[]>([]);

  const fetchHomePageData = async () => {
    loading.value = true;
    try {
      const [offers, goods, cats] = await Promise.all([
        productService.getBestOffers(),
        productService.getNewGoods(),
        productService.getCategories()
      ]);
      bestOffers.value = offers;
      newGoods.value = goods;
      categories.value = cats;
    } catch (err: any) {
      console.error('Failed to fetch home page data', err);
    } finally {
      loading.value = false;
    }
  };

  const fetchCategories = async () => {
    try {
      categories.value = await productService.getCategories();
    } catch (err: any) {
      console.error('Failed to fetch categories:', err);
    }
  };

  const fetchProduct = async (id: string | number) => {
    loading.value = true;
    error.value = null;
    try {
      // First check if we already have it
      let existing = products.value.find(p => p.id.toString() === id.toString());
      if (!existing) {
         // Also check in bestOffers/newGoods just in case
         existing = [...bestOffers.value, ...newGoods.value].find(p => p.id.toString() === id.toString());
      }
      
      if (existing) {
        currentProduct.value = existing;
      } else {
        currentProduct.value = (await productService.getProductById(id)) || null;
      }
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch product';
    } finally {
      loading.value = false;
    }
  };

  const setCategory = (categoryName: string | null) => {
    activeCategory.value = categoryName;
  };

  const setSearch = (query: string) => {
    searchQuery.value = query;
  };

  const setSort = (sort: string) => {
    sortBy.value = sort;
  };

  return {
    products,
    categories,
    bestOffers,
    newGoods,
    currentProduct,
    loading,
    error,
    activeCategory,
    searchQuery,
    sortBy,
    filteredProducts,
    fetchProducts,
    fetchHomePageData,
    fetchCategories,
    fetchProduct,
    setCategory,
    setSearch,
    setSort
  };
});
