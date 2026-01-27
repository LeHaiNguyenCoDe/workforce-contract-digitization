<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-6 mb-6">
       <!-- Header -->
       <div class="flex items-center gap-3 mb-5">
           <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center">
               <i class="ri-question-answer-line text-white text-lg"></i>
           </div>
           <div>
               <h3 class="text-lg font-bold text-gray-900">Hỏi và Đáp</h3>
               <p class="text-xs text-gray-500">Đặt câu hỏi để được hỗ trợ nhanh chóng</p>
           </div>
       </div>
       
       <!-- Input Box -->
       <div class="bg-blue-50 rounded-xl p-4 mb-6 border border-blue-100">
           <div class="flex gap-4 items-start">
               <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <i class="ri-user-line text-white text-xl"></i>
               </div>
               <div class="flex-1">
                   <div class="font-semibold text-gray-800 text-sm mb-1">Bạn có thắc mắc về sản phẩm?</div>
                   <div class="text-xs text-gray-500 mb-3">Chúng tôi sẽ phản hồi trong thời gian sớm nhất. Giờ làm việc: 8:00 - 22:00 hàng ngày.</div>
                   <div class="flex gap-2">
                       <input type="text" 
                              placeholder="Nhập câu hỏi của bạn..."
                              class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-white transition-all" />
                       <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition-all">
                           Gửi <i class="ri-send-plane-fill ml-1"></i>
                       </button>
                   </div>
               </div>
           </div>
       </div>
       
       <!-- List -->
       <div class="space-y-4">
            <div v-for="(question, i) in mockQuestions" :key="i" class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100/70 transition-colors">
                 <div class="flex gap-3 mb-2">
                      <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 text-white shadow-sm"
                           :class="avatarColors[i % avatarColors.length]">
                          {{ question.name.charAt(0) }}
                      </div>
                      <div class="flex-1">
                           <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm">{{ question.name }}</span>
                                <span class="text-xs text-gray-400 font-normal">
                                    <i class="ri-time-line"></i> {{ question.time }}
                                </span>
                           </div>
                           <div class="text-sm text-gray-700 mt-1.5 leading-relaxed">
                               {{ question.content }}
                           </div>
                           
                           <!-- Reply if exists -->
                           <div v-if="question.reply" class="mt-3 pl-3 border-l-2 border-blue-200 bg-blue-50/50 rounded-r-lg p-3">
                               <div class="flex items-center gap-2 mb-1">
                                   <span class="bg-blue-600 text-white text-[10px] px-1.5 py-0.5 rounded font-medium">Admin</span>
                                   <span class="text-xs text-gray-400">{{ question.replyTime }}</span>
                               </div>
                               <p class="text-sm text-gray-700">{{ question.reply }}</p>
                           </div>
                           
                           <button class="text-xs text-blue-600 font-medium mt-2 hover:text-blue-800 transition-colors">
                               <i class="ri-reply-line"></i> Trả lời
                           </button>
                      </div>
                 </div>
            </div>
            
            <div class="text-center pt-3">
                 <button class="text-sm text-gray-500 hover:text-blue-600 transition-colors font-medium px-4 py-2 rounded-lg hover:bg-blue-50">
                     Xem tất cả câu hỏi <i class="ri-arrow-right-s-line"></i>
                 </button>
            </div>
       </div>
  </div>
</template>

<script setup lang="ts">
const avatarColors = [
    'bg-blue-500',
    'bg-blue-600',
    'bg-blue-700',
    'bg-gray-600',
    'bg-gray-700',
]

const mockQuestions = [
    {
        name: 'Minh Tuấn',
        content: 'Sản phẩm này có hỗ trợ bảo hành mở rộng không ạ?',
        time: '2 giờ trước',
        reply: 'Chào bạn, sản phẩm có hỗ trợ mua thêm gói bảo hành mở rộng 1-2 năm tùy theo nhu cầu của bạn nhé!',
        replyTime: '1 giờ trước'
    },
    {
        name: 'Lan Anh',
        content: 'Cho mình hỏi màu trắng còn hàng không?',
        time: '5 giờ trước',
        reply: null,
        replyTime: null
    },
    {
        name: 'Hoàng Nam',
        content: 'Giao hàng đến Đà Nẵng mất bao lâu vậy shop?',
        time: '1 ngày trước',
        reply: 'Dạ ship Đà Nẵng từ 2-3 ngày làm việc bạn nhé!',
        replyTime: '23 giờ trước'
    },
]
</script>
