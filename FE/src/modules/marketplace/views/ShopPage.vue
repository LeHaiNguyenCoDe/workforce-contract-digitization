<template>
  <div class="shop-page marketplace-wrapper pt-10 pb-20">
    <div class="container-mkp">
      <div class="flex flex-col lg:flex-row gap-10">
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-[280px] shrink-0">
          <div class="sticky top-[110px] space-y-10">
            <!-- Categories Widget -->
            <div class="widget">
              <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6 pb-2 border-b-2 border-primary w-fit">
                Categories
              </h4>
              <ul class="space-y-3">
                <li>
                  <a 
                    href="#" 
                    class="text-sm font-medium transition-colors"
                    :class="!productStore.activeCategory ? 'text-primary' : 'text-slate-500 hover:text-primary'"
                    @click.prevent="productStore.setCategory(null)"
                  >
                    All Products
                  </a>
                </li>
                <li v-for="cat in productStore.categories" :key="cat.id">
                  <a 
                    href="#" 
                    class="text-sm font-medium transition-colors flex justify-between items-center"
                    :class="productStore.activeCategory === cat.name ? 'text-primary' : 'text-slate-500 hover:text-primary'"
                    @click.prevent="productStore.setCategory(cat.name)"
                  >
                    <span>{{ cat.name }}</span>
                    <span class="text-[10px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded-full">{{ cat.count || 0 }}</span>
                  </a>
                </li>
              </ul>
            </div>

            <!-- Price Filter Widget (Mock) -->
            <div class="widget">
              <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6 pb-2 border-b-2 border-primary w-fit">
                Filter by price
              </h4>
              <div class="space-y-4">
                <input type="range" class="w-full h-1 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-primary" />
                <div class="flex items-center justify-between text-sm font-bold text-slate-700">
                  <span>Price: 0 ₫ — 50.000.000 ₫</span>
                  <button class="text-primary hover:underline">Filter</button>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow">
          <!-- Shop Toolbar -->
          <div class="flex items-center justify-between mb-10 bg-slate-50 p-4 rounded-xl">
            <div class="text-sm text-slate-500 font-medium">
              Showing <span class="text-slate-900 font-bold">{{ productStore.filteredProducts.length }}</span> products
            </div>
            
            <div class="flex items-center gap-6">
              <select 
                class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer"
                v-model="productStore.sortBy"
              >
                <option value="default">Default sorting</option>
                <option value="rating">Sort by average rating</option>
                <option value="latest">Sort by latest</option>
                <option value="price-low">Sort by price: low to high</option>
                <option value="price-high">Sort by price: high to low</option>
              </select>

              <div class="flex items-center gap-2 border-l pl-6 border-slate-200">
                <button class="w-8 h-8 flex items-center justify-center text-primary"><i class="fas fa-th-large"></i></button>
                <button class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-primary"><i class="fas fa-list"></i></button>
              </div>
            </div>
          </div>

          <!-- Product Grid -->
          <div v-if="productStore.loading" class="py-20 text-center">
            <div class="animate-spin text-primary text-4xl mb-4"><i class="fas fa-spinner"></i></div>
            <p class="text-slate-500 font-bold uppercase tracking-widest">Loading products...</p>
          </div>

          <div v-else-if="productStore.filteredProducts.length > 0" 
               class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            <ProductCard 
              v-for="product in productStore.filteredProducts" 
              :key="product.id" 
              :product="product" 
            />
          </div>

          <!-- Empty State -->
          <div v-else class="py-20 text-center">
            <div class="text-slate-200 text-6xl mb-6"><i class="fas fa-search"></i></div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">No products found</h3>
            <p class="text-slate-500">Try adjusting your filters or search query.</p>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useProductStore } from '../stores/products'
import ProductCard from '../components/common/ProductCard.vue'

const productStore = useProductStore()

onMounted(() => {
  productStore.fetchCategories()
  productStore.fetchProducts()
})
</script>

<style lang="scss" scoped>
.widget {
  background: white;
}
</style>
