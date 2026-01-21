<script setup lang="ts">
import { ref } from 'vue'

const activeIndex = ref<number | null>(0)

const faqs = [
  { question: 'iPhone 17 Pro pin bao nhiêu?', answer: 'iPhone 17 Pro sở hữu viên pin dung lượng 3274 mAh, cho thời gian sử dụng xem video lên đến 23 giờ, nghe nhạc lên đến 75 giờ.' },
  { question: 'iPhone 17 Pro màn hình bao nhiêu inch?', answer: 'iPhone 17 Pro có màn hình Super Retina XDR OLED kích thước 6.1 inch, độ phân giải 2556 x 1179 pixel.' },
  { question: 'iPhone 17 Pro có mấy màu?', answer: 'iPhone 17 Pro có 4 tùy chọn màu sắc: Titan Đen, Titan Trắng, Titan Xanh và Titan Tự nhiên.' },
  { question: 'iPhone 17 Pro khi nào ra mắt?', answer: 'Dòng iPhone 17 series dự kiến sẽ được Apple ra mắt vào tháng 9 năm 2025.' }
]

const toggle = (index: number) => {
  activeIndex.value = activeIndex.value === index ? null : index
}
</script>

<template>
  <div class="mb-6">
      <h3 class="font-bold text-gray-800 text-lg mb-4">Câu hỏi thường gặp</h3>
      <div class="space-y-2">
           <div v-for="(faq, index) in faqs" :key="index" class="bg-gray-50 rounded-lg overflow-hidden transition-all duration-300">
                <button @click="toggle(index)" class="w-full flex justify-between items-center px-4 py-3 text-sm font-bold text-gray-800 hover:bg-gray-100 transition-colors text-left">
                    <span>{{ faq.question }}</span>
                    <i class="ri-arrow-down-s-line text-lg transition-transform duration-300" :class="{ 'rotate-180': activeIndex === index }"></i>
                </button>
                <div v-show="activeIndex === index" class="px-4 pb-3 text-sm text-gray-600 leading-relaxed border-t border-gray-100">
                    {{ faq.answer }}
                </div>
           </div>
      </div>
  </div>
</template>
