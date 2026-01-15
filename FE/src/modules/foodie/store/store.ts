import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { 
  CategoryItem, 
  Product, 
  Restaurant,
  Banner
} from '../types'

export const useFoodieHomeStore = defineStore('foodie-home', () => {
  // State
  const banners = ref<Banner[]>([])
  const categories = ref<CategoryItem[]>([])
  const popularDrinks = ref<Product[]>([])
  const restaurants = ref<Restaurant[]>([])
  const partyFavourites = ref<Product[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Getters
  const hasCategories = computed(() => categories.value.length > 0)
  const hasPopularDrinks = computed(() => popularDrinks.value.length > 0)
  const hasRestaurants = computed(() => restaurants.value.length > 0)
  const hasPartyFavourites = computed(() => partyFavourites.value.length > 0)

  // Actions
  async function fetchHomeData() {
    if (isLoading.value) return
    
    isLoading.value = true
    error.value = null
    
    try {
      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 500))
      
      // Mock Data
      categories.value = [
        { id: 1, name: 'Vases', image: 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=400&h=400&fit=crop' },
        { id: 2, name: 'Plates', image: 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=400&h=400&fit=crop' },
        { id: 3, name: 'Bowls', image: 'https://images.unsplash.com/photo-1567057419565-4349c49d8a04?w=400&h=400&fit=crop' },
        { id: 4, name: 'Cups', image: 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=400&h=400&fit=crop' },
        { id: 5, name: 'Planters', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=400&h=400&fit=crop' },
        { id: 6, name: 'Planters', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=400&h=400&fit=crop' },
        { id: 7, name: 'Planters', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=400&h=400&fit=crop' },
      ]

      const mockProducts: Product[] = [
        { id: 1, name: 'Rustic Clay Vase', brand: 'Earth & One', price: 45.00, size: 'Medium', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.8 },
        { id: 2, name: 'Modern White Planter', brand: 'Studio Zen', price: 32.50, size: 'Large', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.9 },
        { id: 3, name: 'Speckled Dinner Set', brand: 'Artisan Table', price: 120.00, size: '12 Piece', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.7 },
        { id: 4, name: 'Handmade Coffee Mug', brand: 'Morning Clay', price: 24.00, size: '12oz', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.6 },
        { id: 5, name: 'Blue Glaze Bowl', brand: 'Ocean Ceramics', price: 28.00, size: 'Medium', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.5 },
        { id: 6, name: 'Terracotta Jug', brand: 'Earth & One', price: 38.00, size: '1L', alcohol: '', image: 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=600&h=600&fit=crop', rating: 4.4 },
      ]

      popularDrinks.value = mockProducts
      partyFavourites.value = mockProducts

      restaurants.value = [
        { id: 1, name: "Sunset Clay Studio", image: 'https://images.unsplash.com/photo-1520408222757-6f9f95d87d5d?w=800&h=600&fit=crop' },
        { id: 2, name: "The Potters Wheel", image: 'https://images.unsplash.com/photo-1520408222757-6f9f95d87d5d?w=800&h=600&fit=crop' },
        { id: 3, name: 'Urban Ceramics', image: 'https://images.unsplash.com/photo-1520408222757-6f9f95d87d5d?w=800&h=600&fit=crop' },
      ]
      
    } catch (err) {
      console.error('Failed to fetch foodie home data:', err)
      error.value = 'Failed to load home data'
    } finally {
      isLoading.value = false
    }
  }

  function reset() {
    banners.value = []
    categories.value = []
    popularDrinks.value = []
    restaurants.value = []
    partyFavourites.value = []
    error.value = null
  }

  return {
    banners,
    categories,
    popularDrinks,
    restaurants,
    partyFavourites,
    isLoading,
    error,
    hasCategories,
    hasPopularDrinks,
    hasRestaurants,
    hasPartyFavourites,
    fetchHomeData,
    reset
  }
})
