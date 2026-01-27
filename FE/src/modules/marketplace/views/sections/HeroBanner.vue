<template>
  <section class="mkp-section !pt-4">
    <div class="container-mkp">
      <div class="grid grid-cols-12 gap-6">
        <!-- Main Slider (Left) -->
        <div class="col-span-12 lg:col-span-8">
          <div class="relative rounded-xl overflow-hidden h-[400px] md:h-[500px] bg-slate-900 group">
            <img src="@/assets/marketplace/images/uploads/2022/12/slide-1-opt.jpg" alt="Main Banner" class="w-full h-full object-cover opacity-80" />
            <div class="absolute inset-0 flex flex-col justify-center px-8 md:px-16 text-white max-w-xl">
              <h2 class="text-4xl md:text-5xl font-black mb-6 leading-tight !text-white">
                Discount on all Smart appliances up to 25%
              </h2>
              <p class="text-lg opacity-90 mb-8">Shop great deals on MacBook, iPad, iPhone and more.</p>
              <div>
                <button class="btn-mkp btn-mkp-primary !px-10 !py-4 rounded-md">Shop Now</button>
              </div>
            </div>
            <!-- Slider Controls -->
            <div class="absolute bottom-8 left-16 flex gap-3">
              <span class="w-2 h-2 rounded-full bg-white"></span>
              <span class="w-2 h-2 rounded-full bg-white/30"></span>
              <span class="w-2 h-2 rounded-full bg-white/30"></span>
            </div>
          </div>
        </div>

        <!-- Right Side Banners -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
          <!-- Aurora Headset (Top Right) -->
          <div class="relative rounded-xl overflow-hidden flex-1 min-h-[250px] bg-[#fdf2f2] group p-8">
            <div class="relative z-10">
              <h3 class="text-2xl font-black text-slate-800 mb-4">Aurora Headset</h3>
              <CountdownTimer targetDate="2026-12-31T23:59:59" class="mb-6" />
              <button class="btn-mkp btn-mkp-primary rounded-md">Buy Now</button>
            </div>
            <img src="@/assets/marketplace/images/uploads/2022/12/logitech-g735-1.jpg" alt="Headset" 
                 class="absolute bottom-0 right-0 w-[60%] object-contain group-hover:scale-105 transition-transform" />
          </div>

          <!-- Bottom Grid -->
          <div class="grid grid-cols-2 gap-6 h-[200px]">
             <!-- New Dual Sense -->
             <div class="relative rounded-xl overflow-hidden bg-[#eaf4fe] p-6 group">
                <div class="relative z-10">
                  <h4 class="text-lg font-black text-slate-800 mb-1">New Dual Sense</h4>
                  <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-4">For PlayStation 5</p>
                  <button class="text-xs font-bold underline decoration-primary underline-offset-4 decoration-2">View Details</button>
                </div>
                <img src="@/assets/marketplace/images/uploads/2022/12/slide-2-opt.jpg" alt="PS5"
                     class="absolute bottom-4 right-4 w-[80%] object-contain group-hover:rotate-12 transition-transform" />
             </div>
             
             <!-- Instant Cameras -->
             <div class="relative rounded-xl overflow-hidden bg-[#fff9e6] p-6 group">
                <div class="relative z-10">
                  <h4 class="text-lg font-black text-slate-800 mb-1">Instant Cameras</h4>
                  <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-4">Get photo paper as a gift</p>
                  <button class="text-xs font-bold underline decoration-primary underline-offset-4 decoration-2">View Details</button>
                </div>
                <img src="@/assets/marketplace/images/uploads/2022/12/slide-3-opt.jpg" alt="Camera"
                     class="absolute bottom-4 right-4 w-[60%] object-contain group-hover:scale-110 transition-transform" />
             </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import CountdownTimer from '../../components/common/CountdownTimer.vue'
</script>
