<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface Employee {
    id: number
    employee_code: string
    name: string
    email?: string
    phone?: string
    department?: string
    position?: string
    status: 'active' | 'inactive' | 'on_leave'
    base_salary: number
}

const employees = ref<Employee[]>([])
const isLoading = ref(true)
const showModal = ref(false)
const form = ref({ name: '', email: '', phone: '', department: '', position: '', base_salary: 0 })

const fetchEmployees = async () => {
    isLoading.value = true
    try {
        const res = await httpClient.get('admin/employees')
        employees.value = res.data.data?.data || []
    } catch (e) { console.error(e) }
    finally { isLoading.value = false }
}

const createEmployee = async () => {
    try {
        await httpClient.post('admin/employees', form.value)
        showModal.value = false
        form.value = { name: '', email: '', phone: '', department: '', position: '', base_salary: 0 }
        fetchEmployees()
    } catch (e) { console.error(e) }
}

const checkIn = async (id: number) => {
    try {
        await httpClient.post(`admin/employees/${id}/check-in`)
        alert('Check-in thành công!')
    } catch (e) { console.error(e) }
}

const getStatusColor = (s: string) => ({
    active: 'bg-success/20 text-success',
    inactive: 'bg-slate-500/20 text-slate-400',
    on_leave: 'bg-warning/20 text-warning',
}[s] || 'bg-slate-500/20 text-slate-400')

const formatCurrency = (n: number) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(n)

onMounted(fetchEmployees)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Quản lý nhân viên</h1>
            <button @click="showModal = true" class="px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90">
                + Thêm nhân viên
            </button>
        </div>

        <div v-if="isLoading" class="card p-8 text-center text-slate-400">Đang tải...</div>
        <div v-else-if="!employees.length" class="card p-8 text-center text-slate-400">Chưa có nhân viên</div>
        
        <div v-else class="card overflow-hidden">
            <table class="w-full">
                <thead class="bg-dark-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Mã</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Họ tên</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Phòng ban</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Chức vụ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Lương</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400 uppercase">Trạng thái</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-400 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="emp in employees" :key="emp.id" class="hover:bg-dark-700/50">
                        <td class="px-4 py-3 font-mono text-primary">{{ emp.employee_code }}</td>
                        <td class="px-4 py-3 text-white">{{ emp.name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ emp.department || '-' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ emp.position || '-' }}</td>
                        <td class="px-4 py-3 font-medium gradient-text">{{ formatCurrency(emp.base_salary) }}</td>
                        <td class="px-4 py-3">
                            <span :class="['px-2 py-1 text-xs rounded-full', getStatusColor(emp.status)]">
                                {{ emp.status === 'active' ? 'Đang làm' : emp.status === 'on_leave' ? 'Nghỉ phép' : 'Nghỉ việc' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="checkIn(emp.id)" class="px-3 py-1 text-sm rounded bg-success/20 text-success hover:bg-success/30">
                                Check-in
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="w-full max-w-md bg-dark-800 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Thêm nhân viên</h3>
                <div class="space-y-4">
                    <input v-model="form.name" placeholder="Họ tên *" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <input v-model="form.email" placeholder="Email" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <input v-model="form.phone" placeholder="Số điện thoại" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <div class="grid grid-cols-2 gap-4">
                        <input v-model="form.department" placeholder="Phòng ban" class="px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                        <input v-model="form.position" placeholder="Chức vụ" class="px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    </div>
                    <input v-model.number="form.base_salary" type="number" placeholder="Lương cơ bản" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showModal = false" class="px-4 py-2 rounded-lg bg-dark-600 text-slate-300">Hủy</button>
                    <button @click="createEmployee" class="px-4 py-2 rounded-lg bg-primary text-white">Thêm</button>
                </div>
            </div>
        </div>
    </div>
</template>
