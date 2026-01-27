<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface Appointment {
    id: number
    title: string
    description?: string
    start_at: string
    end_at?: string
    type: 'meeting' | 'call' | 'visit'
    status: 'scheduled' | 'completed' | 'cancelled'
    customer?: { name: string }
    staff?: { name: string }
}

const appointments = ref<Appointment[]>([])
const isLoading = ref(true)
const currentDate = ref(new Date())
const showModal = ref(false)
const form = ref({ title: '', start_at: '', type: 'meeting', description: '' })

const currentMonth = computed(() => currentDate.value.toLocaleDateString('vi-VN', { month: 'long', year: 'numeric' }))

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear()
    const month = currentDate.value.getMonth()
    const firstDay = new Date(year, month, 1)
    const lastDay = new Date(year, month + 1, 0)
    const days = []
    
    // Padding for first week
    for (let i = 0; i < firstDay.getDay(); i++) {
        days.push(null)
    }
    
    for (let d = 1; d <= lastDay.getDate(); d++) {
        days.push(new Date(year, month, d))
    }
    return days
})

const fetchAppointments = async () => {
    isLoading.value = true
    try {
        const year = currentDate.value.getFullYear()
        const month = currentDate.value.getMonth()
        const start = new Date(year, month, 1).toISOString().split('T')[0]
        const end = new Date(year, month + 1, 0).toISOString().split('T')[0]
        
        const res = await httpClient.get(`admin/appointments?start_date=${start}&end_date=${end}`)
        appointments.value = res.data.data || []
    } catch (e) { console.error(e) }
    finally { isLoading.value = false }
}

const getAppointmentsForDay = (date: Date | null) => {
    if (!date) return []
    const dateStr = date.toISOString().split('T')[0]
    return appointments.value.filter(a => a.start_at.startsWith(dateStr))
}

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1)
    fetchAppointments()
}

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1)
    fetchAppointments()
}

const createAppointment = async () => {
    try {
        await httpClient.post('admin/appointments', form.value)
        showModal.value = false
        form.value = { title: '', start_at: '', type: 'meeting', description: '' }
        fetchAppointments()
    } catch (e) { console.error(e) }
}

const getTypeColor = (t: string) => ({
    meeting: 'bg-primary',
    call: 'bg-info',
    visit: 'bg-warning',
}[t] || 'bg-slate-500')

const isToday = (date: Date | null) => {
    if (!date) return false
    const today = new Date()
    return date.toDateString() === today.toDateString()
}

onMounted(fetchAppointments)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Lịch hẹn</h1>
            <button @click="showModal = true" class="px-4 py-2 rounded-lg bg-primary text-white font-medium">+ Thêm lịch hẹn</button>
        </div>

        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <button @click="prevMonth" class="p-2 rounded-lg hover:bg-dark-600 text-slate-400">←</button>
                <h2 class="text-lg font-medium text-white capitalize">{{ currentMonth }}</h2>
                <button @click="nextMonth" class="p-2 rounded-lg hover:bg-dark-600 text-slate-400">→</button>
            </div>

            <div class="grid grid-cols-7 gap-1 mb-2 text-center text-xs text-slate-500">
                <div>CN</div><div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div>
            </div>

            <div v-if="isLoading" class="py-20 text-center text-slate-400">Đang tải...</div>
            
            <div v-else class="grid grid-cols-7 gap-1">
                <div v-for="(day, i) in daysInMonth" :key="i"
                    class="min-h-24 p-1 rounded-lg"
                    :class="day ? (isToday(day) ? 'bg-primary/10 border border-primary/30' : 'bg-dark-700') : ''">
                    <template v-if="day">
                        <div class="text-right text-xs mb-1" :class="isToday(day) ? 'text-primary font-bold' : 'text-slate-500'">
                            {{ day.getDate() }}
                        </div>
                        <div class="space-y-1">
                            <div v-for="apt in getAppointmentsForDay(day).slice(0, 2)" :key="apt.id"
                                :class="['text-[10px] px-1.5 py-0.5 rounded text-white truncate', getTypeColor(apt.type)]">
                                {{ apt.title }}
                            </div>
                            <div v-if="getAppointmentsForDay(day).length > 2" class="text-[10px] text-slate-500">
                                +{{ getAppointmentsForDay(day).length - 2 }} khác
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="w-full max-w-md bg-dark-800 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Thêm lịch hẹn</h3>
                <div class="space-y-4">
                    <input v-model="form.title" placeholder="Tiêu đề *" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <input v-model="form.start_at" type="datetime-local" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <select v-model="form.type" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                        <option value="meeting">Họp</option>
                        <option value="call">Gọi điện</option>
                        <option value="visit">Thăm KH</option>
                    </select>
                    <textarea v-model="form.description" placeholder="Ghi chú" rows="2" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white"></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showModal = false" class="px-4 py-2 rounded-lg bg-dark-600 text-slate-300">Hủy</button>
                    <button @click="createAppointment" class="px-4 py-2 rounded-lg bg-primary text-white">Tạo</button>
                </div>
            </div>
        </div>
    </div>
</template>
