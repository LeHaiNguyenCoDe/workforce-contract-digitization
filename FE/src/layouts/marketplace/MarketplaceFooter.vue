<template>
  <footer class="bg-white border-t border-slate-100 pt-16 pb-8">
    <div class="container-mkp">
      <!-- Footer Top: Grid Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
        <!-- Brand & About -->
        <div>
          <img src="@/assets/marketplace/images/wood-logo-dark.svg" alt="Logo" class="h-8 mb-6" />
          <p class="text-slate-500 text-sm leading-relaxed mb-6">
            Condimentum adipiscing vel neque dis nam parturient orci at scelerisque. 
            Quis habitasse massa elementum rhoncus nisl.
          </p>
          <div class="flex items-center gap-4">
            <span class="text-sm font-bold uppercase tracking-widest text-slate-800">Subscribe us</span>
          </div>
          <div class="flex items-center gap-3 mt-4">
            <a href="#" v-for="icon in socialIcons" :key="icon.class" 
               class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-primary hover:text-white transition-all">
              <i :class="icon.class"></i>
            </a>
          </div>
        </div>

        <!-- Links Columns -->
        <div>
          <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">Categories</h4>
          <ul class="space-y-4">
            <li v-for="cat in ['Smartphones', 'Laptops', 'Hardware', 'Cameras', 'Headphones', 'Bathroom']" :key="cat">
              <a href="#" class="text-sm text-slate-500 hover:text-primary transition-colors">{{ cat }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">Useful Links</h4>
          <ul class="space-y-4">
            <li v-for="link in ['Promotions', 'Stores', 'Our contacts', 'Delivery & Return', 'Outlet']" :key="link">
              <a href="#" class="text-sm text-slate-500 hover:text-primary transition-colors">{{ link }}</a>
            </li>
          </ul>
        </div>

        <div>
          <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">Useful Links</h4>
          <ul class="space-y-4">
            <li v-for="link in ['Blog', 'Our contacts', 'Promotions', 'Stores', 'Delivery & Return']" :key="link">
              <a href="#" class="text-sm text-slate-500 hover:text-primary transition-colors">{{ link }}</a>
            </li>
          </ul>
        </div>
      </div>

      <!-- App Download Section -->
      <div class="bg-slate-50 rounded-2xl p-8 flex flex-col md:flex-row items-center justify-between gap-6 mb-12">
        <div>
          <h3 class="text-lg font-bold text-slate-800 mb-2">Download App on Mobile:</h3>
          <p class="text-sm text-slate-500 italic">15% discount on your first purchase</p>
        </div>
        <div class="flex items-center gap-4">
          <a href="#" class="transition-transform hover:-translate-y-1">
            <img src="@/assets/marketplace/images/uploads/2022/12/google-play.svg" alt="Google Play" class="h-10" />
          </a>
          <a href="#" class="transition-transform hover:-translate-y-1">
            <img src="@/assets/marketplace/images/uploads/2022/12/app-store.svg" alt="App Store" class="h-10" />
          </a>
        </div>
      </div>

      <!-- Copyright & Payment -->
      <div class="flex flex-col md:flex-row items-center justify-between pt-8 border-t border-slate-100 gap-6">
        <p class="text-[11px] text-slate-400 uppercase tracking-widest font-bold">
          Based on <span class="text-slate-800">WoodMart</span> theme 2024 <span class="text-slate-800">WooCommerce Themes</span>.
        </p>
        <div>
          <img src="@/assets/marketplace/images/payments.png" alt="Payments" class="h-4 opacity-70 grayscale hover:grayscale-0 transition-all cursor-pointer" />
        </div>
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
export default {
  name: 'MarketplaceFooter'
}
</script>

<script setup lang="ts">
const socialIcons = [
  { class: 'fab fa-facebook-f' },
  { class: 'fab fa-twitter' },
  { class: 'fab fa-pinterest-p' },
  { class: 'fab fa-linkedin-in' },
  { class: 'fab fa-telegram-plane' }
]
</script>
