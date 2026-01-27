<template>
  <div class="cart-page marketplace-wrapper pt-10 pb-20">
    <div class="container-mkp">
      <h2 class="text-3xl font-black mb-10 text-slate-900">Shopping Cart</h2>

      <div v-if="cartStore.items.length > 0" class="flex flex-col lg:flex-row gap-10">
        <!-- Cart Table -->
        <div class="flex-grow overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 text-[11px] uppercase tracking-widest text-slate-400 font-bold">
                <th class="pb-4 font-bold">Product</th>
                <th class="pb-4 font-bold">Price</th>
                <th class="pb-4 font-bold text-center">Quantity</th>
                <th class="pb-4 font-bold text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in cartStore.items" :key="item.product.id" class="group">
                <td class="py-6">
                  <div class="flex items-center gap-6">
                    <button 
                      @click="cartStore.removeFromCart(item.product.id)"
                      class="text-slate-300 hover:text-red-500 transition-colors"
                    >
                      <i class="fas fa-times text-xs"></i>
                    </button>
                    <div class="w-20 h-20 bg-slate-50 rounded-lg p-2 overflow-hidden shrink-0">
                      <img :src="item.product.image" :alt="item.product.name" class="w-full h-full object-contain" />
                    </div>
                    <router-link :to="'/marketplace/product/' + item.product.id" class="text-slate-800 font-bold hover:text-primary transition-colors text-sm">
                      {{ item.product.name }}
                    </router-link>
                  </div>
                </td>
                <td class="py-6 font-bold text-slate-600 text-sm">
                  {{ formatPrice(item.product.price) }}
                </td>
                <td class="py-6">
                  <div class="flex items-center justify-center">
                    <div class="flex items-center border border-slate-200 rounded-full h-10 px-2">
                       <button 
                         @click="cartStore.updateQuantity(item.product.id, item.quantity - 1)"
                         class="w-6 text-slate-400 hover:text-primary transition-colors text-xs"
                       >
                         <i class="fas fa-minus"></i>
                       </button>
                       <input 
                         type="number" 
                         v-model.number="item.quantity" 
                         @change="handleQuantityChange(item)"
                         class="w-10 text-center text-sm font-bold border-none focus:ring-0 px-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                       />
                       <button 
                         @click="cartStore.updateQuantity(item.product.id, item.quantity + 1)"
                         class="w-6 text-slate-400 hover:text-primary transition-colors text-xs"
                       >
                         <i class="fas fa-plus"></i>
                       </button>
                    </div>
                  </div>
                </td>
                <td class="py-6 text-right font-black text-primary text-sm">
                  {{ formatPrice(item.product.price * item.quantity) }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Cart Actions -->
          <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <input 
                type="text" 
                placeholder="Coupon code" 
                class="h-11 px-6 rounded-full border border-slate-200 focus:border-primary focus:outline-none text-sm min-w-[200px]"
              />
              <button class="btn-mkp btn-mkp-outline h-11 rounded-full whitespace-nowrap">Apply coupon</button>
            </div>
            <button 
              @click="cartStore.clearCart()"
              class="text-sm font-bold text-slate-400 hover:text-red-500 transition-colors uppercase tracking-widest"
            >
              Clear shopping cart
            </button>
          </div>
        </div>

        <!-- Order Summary -->
        <aside class="w-full lg:w-[380px] shrink-0">
          <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100">
            <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-8 pb-3 border-b border-slate-200">
              Cart Total
            </h4>
            
            <div class="space-y-4 mb-8">
              <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="font-bold text-slate-800">{{ formatPrice(cartStore.totalPrice) }}</span>
              </div>
              <div class="flex justify-between items-start text-sm">
                <span class="text-slate-500">Shipping</span>
                <div class="text-right">
                  <div class="font-bold text-slate-800">Free shipping</div>
                  <p class="text-[11px] text-slate-400 mt-1">Shipping options will be updated during checkout.</p>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-slate-200 mb-8">
              <span class="font-bold text-slate-800 text-lg uppercase tracking-widest">Total</span>
              <span class="font-black text-2xl text-primary">{{ formatPrice(cartStore.totalPrice) }}</span>
            </div>

            <router-link 
              to="/marketplace/checkout" 
              class="w-full btn-mkp btn-mkp-primary h-14 rounded-full text-lg shadow-lg shadow-primary/20"
            >
              Proceed to checkout
            </router-link>
            
            <div class="mt-6 flex items-center justify-center gap-2 text-slate-400">
              <i class="fas fa-lock text-[10px]"></i>
              <span class="text-[11px] font-bold uppercase tracking-widest">Secure Checkout</span>
            </div>
          </div>
        </aside>
      </div>

      <!-- Empty Cart State -->
      <div v-else class="py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-xl">
          <i class="fas fa-shopping-basket text-primary text-3xl opacity-20"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-900 mb-4">Your cart is currenty empty.</h3>
        <p class="text-slate-500 mb-10 max-w-md mx-auto">
          Before proceed to checkout you must add some products to your shopping cart. 
          You will find a lot of interesting products on our "Shop" page.
        </p>
        <router-link 
          to="/marketplace/shop" 
          class="btn-mkp btn-mkp-primary h-14 px-10 rounded-full text-lg shadow-lg shadow-primary/20"
        >
          Return to shop
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '../stores/cart'
import { formatPrice } from '../helpers/format'

const cartStore = useCartStore()

const handleQuantityChange = (item: any) => {
  if (item.quantity <= 0) {
    cartStore.removeFromCart(item.product.id)
  }
}
</script>
