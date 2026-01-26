import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Product, CartItem } from '../types';

export const useCartStore = defineStore('marketplace-cart', () => {
  const items = ref<CartItem[]>([]);

  const totalItems = computed(() => {
    return items.value.reduce((acc, item) => acc + item.quantity, 0);
  });

  const totalPrice = computed(() => {
    return items.value.reduce((acc, item) => acc + (item.product.price * item.quantity), 0);
  });

  const addToCart = (product: Product, quantity: number = 1) => {
    const existingItem = items.value.find(item => item.product.id === product.id);
    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      items.value.push({ product, quantity });
    }
  };

  const removeFromCart = (productId: string | number) => {
    const index = items.value.findIndex(item => item.product.id === productId);
    if (index !== -1) {
      items.value.splice(index, 1);
    }
  };

  const updateQuantity = (productId: string | number, quantity: number) => {
    const item = items.value.find(item => item.product.id === productId);
    if (item) {
      if (quantity <= 0) {
        removeFromCart(productId);
      } else {
        item.quantity = quantity;
      }
    }
  };

  const clearCart = () => {
    items.value = [];
  };

  return {
    items,
    totalItems,
    totalPrice,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart
  };
});
