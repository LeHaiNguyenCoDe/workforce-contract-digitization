import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import httpClient from '@/plugins/api/httpClient'
import type { HomeBanner } from './types'
import type { Product } from '../products/types'
import type { CategoryWithProducts } from '../categories/types'
import type { Promotion } from '../promotions/types'

export const useHomeStore = defineStore('landing-home', () => {
  // State
  const featuredProducts = ref<Product[]>([])
  const newProducts = ref<Product[]>([])
  const categories = ref<CategoryWithProducts[]>([])
  const promotions = ref<Promotion[]>([])
  const banners = ref<HomeBanner[]>([])
  const isLoading = ref(false)
  const categoriesSymbolicNames = {
    1: 'https://ladora.com.vn/wp-content/uploads/2019/05/binh-gom-trang-tri-bg021-3.jpg',
    2: 'https://vungdecor.com/wp-content/uploads/2022/10/binh-gom-trang-tri-Vung-Decor10.jpg',
    3: 'https://bizweb.dktcdn.net/100/400/560/products/z4544943015781-2f0075e3e216d9a6399fc773d1533632.jpg?v=1690254500157',
    4: 'https://phapduyen.com/wp-content/uploads/2024/10/00-1a-1.jpg',
    5: 'https://ecocare.vn/wp-content/uploads/2019/03/do-gia-dung-thong-minh-4-800x800.jpg',
    6: 'https://naty.vn/wp-content/uploads/2023/06/qua-tang-tan-gia-4.jpg',
    7: 'https://battrangceramics.com/User_folder_upload/admin/images/2021/gom-tam-linh-aug/loc-linh-tam-hop-ty-dau-suu-mau-xanh-ngoc-cao-31cm3.jpg',
    8: 'https://flexdecor.vn/wp-content/uploads/2022/11/binh-gom-trang-tri-phong-cach-nhat-ban-jfs9297-1.jpg',
    9: 'https://gomsubaokhanh.vn/media/product/1_hu_sanh_dung_gao_20_kg_tai_loc_dang_tru__cg_004_20_2.jpg',
    10: 'https://quatangcaominh.com/wp-content/uploads/2024/12/hop-qua-tam-linh-1024x576.jpg',
    11: 'https://gomtruongan.vn/uploads/products/02062020012026/tranh-gom-su-op-tuong-canh-dong-que_02062020012026.jpg',
    12: 'https://vuongomviet.com/public/uploads/Tintuc/tuong-gom-su-1.jpg'
  }

  // Getters
  const hasFeaturedProducts = computed(() => featuredProducts.value.length > 0)
  const hasPromotions = computed(() => promotions.value.length > 0)

  // Actions
  async function fetchHomeData() {
    isLoading.value = true
    try {
      // Fetch all home data in parallel
      const [productsRes, categoriesRes, promotionsRes] = await Promise.all([
        httpClient.get<any>('/frontend/products', { params: { per_page: 8, featured: true } }),
        httpClient.get<any>('/frontend/categories'),
        httpClient.get<any>('/frontend/promotions', { params: { per_page: 4 } })
      ])

      const productsData = productsRes.data as any
      featuredProducts.value = productsData?.data?.data || productsData?.data || []

      const categoriesData = categoriesRes.data as any
      categories.value = Array.isArray(categoriesData?.data) ? categoriesData.data : []

      const promotionsData = promotionsRes.data as any
      promotions.value = promotionsData?.data?.data || promotionsData?.data || []
    } catch (error) {
      console.error('Failed to fetch home data:', error)
    } finally {
      isLoading.value = false
    }
  }

  function reset() {
    featuredProducts.value = []
    newProducts.value = []
    categories.value = []
    promotions.value = []
    banners.value = []
  }

  return {
    featuredProducts,
    newProducts,
    categories,
    promotions,
    banners,
    isLoading,
    hasFeaturedProducts,
    hasPromotions,
    fetchHomeData,
    categoriesSymbolicNames,
    reset
  }
})
