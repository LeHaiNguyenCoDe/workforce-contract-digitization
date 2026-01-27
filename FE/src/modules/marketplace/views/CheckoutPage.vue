<template>
  <div class="checkout-page marketplace-wrapper pt-10 pb-20">
    <div class="container-mkp">
      <h2 class="text-3xl font-black mb-10 text-slate-900">Checkout</h2>

      <div class="flex flex-col lg:flex-row gap-16">
        <!-- Billing Details -->
        <div class="flex-grow">
          <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-8 pb-3 border-b-2 border-primary w-fit">
            Billing details
          </h4>
          
          <form class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="flex flex-col gap-2">
                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">First name <span class="text-red-500">*</span></label>
                <input type="text" class="checkout-input" required />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Last name <span class="text-red-500">*</span></label>
                <input type="text" class="checkout-input" required />
              </div>
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Company name (optional)</label>
              <input type="text" class="checkout-input" />
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Country / Region <span class="text-red-500">*</span></label>
              <select class="checkout-input">
                <option value="VN">Vietnam</option>
                <option value="USA">United States</option>
              </select>
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Street address <span class="text-red-500">*</span></label>
              <input type="text" placeholder="House number and street name" class="checkout-input mb-3" required />
              <input type="text" placeholder="Apartment, suite, unit, etc. (optional)" class="checkout-input" />
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Town / City <span class="text-red-500">*</span></label>
              <input type="text" class="checkout-input" required />
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Phone <span class="text-red-500">*</span></label>
              <input type="tel" class="checkout-input" required />
            </div>

            <div class="flex flex-col gap-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Email address <span class="text-red-500">*</span></label>
              <input type="email" class="checkout-input" required />
            </div>

            <div class="pt-6">
              <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-6">Additional information</h4>
              <div class="flex flex-col gap-2">
                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Order notes (optional)</label>
                <textarea rows="4" placeholder="Notes about your order, e.g. special notes for delivery." class="checkout-input !rounded-2xl"></textarea>
              </div>
            </div>
          </form>
        </div>

        <!-- Order Summary -->
        <aside class="w-full lg:w-[450px] shrink-0">
          <div class="bg-white rounded-3xl p-8 border-4 border-slate-50 relative">
            <h4 class="text-sm font-bold uppercase tracking-widest text-slate-800 mb-8 pb-3 border-b border-slate-100">
              Your order
            </h4>
            
            <table class="w-full mb-8">
              <thead>
                <tr class="text-[11px] uppercase tracking-widest text-slate-400 font-bold">
                  <th class="text-left pb-4">Product</th>
                  <th class="text-right pb-4">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in cartStore.items" :key="item.product.id">
                  <td class="py-4 text-sm">
                    <span class="text-slate-600">{{ item.product.name }}</span>
                    <strong class="ml-2 text-slate-900">× {{ item.quantity }}</strong>
                  </td>
                  <td class="py-4 text-right font-bold text-slate-800 text-sm">
                    {{ formatPrice(item.product.price * item.quantity) }}
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="border-t border-slate-200">
                  <td class="py-5 font-bold text-slate-800 text-sm uppercase tracking-widest">Subtotal</td>
                  <td class="py-5 text-right font-black text-slate-900 text-sm">{{ formatPrice(cartStore.totalPrice) }}</td>
                </tr>
                <tr class="border-t border-slate-100">
                  <td class="py-5 font-bold text-slate-800 text-sm uppercase tracking-widest">Shipping</td>
                  <td class="py-5 text-right text-slate-500 text-xs">Free shipping</td>
                </tr>
                <tr class="border-t-2 border-slate-900">
                  <td class="py-6 font-black text-slate-900 text-lg uppercase tracking-widest">Total</td>
                  <td class="py-6 text-right font-black text-primary text-2xl">{{ formatPrice(cartStore.totalPrice) }}</td>
                </tr>
              </tfoot>
            </table>

            <!-- Payment Methods -->
            <div class="bg-slate-50 rounded-2xl p-6 space-y-4 mb-8">
               <div class="flex items-start gap-3">
                 <input type="radio" name="payment" id="bacs" checked class="mt-1 accent-primary" />
                 <div>
                   <label for="bacs" class="text-sm font-bold text-slate-800 block mb-2">Direct bank transfer</label>
                   <p class="text-[12px] text-slate-500 leading-relaxed italic">Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.</p>
                 </div>
               </div>
               <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
                 <input type="radio" name="payment" id="cod" class="accent-primary" />
                 <label for="cod" class="text-sm font-bold text-slate-800">Cash on delivery</label>
               </div>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed mb-8">
              Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <a href="#" class="text-primary hover:underline">privacy policy</a>.
            </p>

            <button class="w-full btn-mkp btn-mkp-primary h-14 rounded-full text-lg shadow-xl shadow-primary/20">
              Place order
            </button>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from '../stores/cart'
import { formatPrice } from '../helpers/format'

const cartStore = useCartStore()
</script>

<style lang="scss" scoped>
.checkout-input {
  height: 3rem;
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  border-radius: 9999px;
  border-width: 1px;
  border-color: rgb(226, 232, 240);
  font-size: 0.875rem;
  line-height: 1.25rem;
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;

  &:focus {
    border-color: var(--mkp-primary);
    outline: 2px solid transparent;
    outline-offset: 2px;
  }
}
</style>
