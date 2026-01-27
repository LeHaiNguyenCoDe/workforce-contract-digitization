<script setup lang="ts">
import { ref, onMounted } from 'vue'
import httpClient from '@/plugins/api/httpClient'

interface Task {
    id: number
    title: string
    description?: string
    status: 'todo' | 'in_progress' | 'review' | 'done'
    priority: 'low' | 'medium' | 'high' | 'urgent'
    due_date?: string
    assignee?: { id: number; name: string; avatar?: string }
}

interface Board {
    todo: Task[]
    in_progress: Task[]
    review: Task[]
    done: Task[]
}

const board = ref<Board>({ todo: [], in_progress: [], review: [], done: [] })
const isLoading = ref(true)
const showModal = ref(false)
const form = ref({ title: '', description: '', priority: 'medium' })
const draggedTask = ref<Task | null>(null)

const columns = [
    { key: 'todo', label: 'Việc cần làm', color: 'border-slate-500' },
    { key: 'in_progress', label: 'Đang thực hiện', color: 'border-info' },
    { key: 'review', label: 'Đang review', color: 'border-warning' },
    { key: 'done', label: 'Hoàn thành', color: 'border-success' },
]

const fetchBoard = async () => {
    isLoading.value = true
    try {
        const res = await httpClient.get('admin/tasks/board')
        board.value = res.data.data || { todo: [], in_progress: [], review: [], done: [] }
    } catch (e) { console.error(e) }
    finally { isLoading.value = false }
}

const createTask = async () => {
    try {
        await httpClient.post('admin/tasks', form.value)
        showModal.value = false
        form.value = { title: '', description: '', priority: 'medium' }
        fetchBoard()
    } catch (e) { console.error(e) }
}

const updateStatus = async (taskId: number, status: string) => {
    try {
        await httpClient.patch(`admin/tasks/${taskId}/status`, { status })
        fetchBoard()
    } catch (e) { console.error(e) }
}

const onDragStart = (task: Task) => { draggedTask.value = task }
const onDragEnd = () => { draggedTask.value = null }
const onDrop = (status: string) => {
    if (draggedTask.value && draggedTask.value.status !== status) {
        updateStatus(draggedTask.value.id, status)
    }
}

const getPriorityColor = (p: string) => ({
    low: 'text-slate-400',
    medium: 'text-info',
    high: 'text-warning',
    urgent: 'text-error',
}[p] || 'text-slate-400')

onMounted(fetchBoard)
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-white">Quản lý công việc</h1>
            <button @click="showModal = true" class="px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-primary/90">
                + Thêm task
            </button>
        </div>

        <div v-if="isLoading" class="text-center text-slate-400 py-8">Đang tải...</div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="col in columns" :key="col.key" 
                class="bg-dark-800 rounded-xl border-t-4 min-h-[400px]"
                :class="col.color"
                @dragover.prevent
                @drop="onDrop(col.key)">
                <div class="p-3 border-b border-white/10">
                    <h3 class="font-medium text-white">{{ col.label }}</h3>
                    <span class="text-xs text-slate-500">{{ board[col.key as keyof Board]?.length || 0 }} tasks</span>
                </div>
                <div class="p-2 space-y-2">
                    <div v-for="task in board[col.key as keyof Board]" :key="task.id"
                        draggable="true"
                        @dragstart="onDragStart(task)"
                        @dragend="onDragEnd"
                        class="p-3 rounded-lg bg-dark-700 cursor-move hover:bg-dark-600 transition-colors">
                        <div class="font-medium text-white text-sm">{{ task.title }}</div>
                        <div v-if="task.description" class="text-xs text-slate-500 mt-1 line-clamp-2">{{ task.description }}</div>
                        <div class="flex items-center justify-between mt-2">
                            <span :class="['text-xs font-medium', getPriorityColor(task.priority)]">
                                {{ task.priority.toUpperCase() }}
                            </span>
                            <span v-if="task.assignee" class="text-xs text-slate-400">{{ task.assignee.name }}</span>
                        </div>
                    </div>
                    <div v-if="!board[col.key as keyof Board]?.length" class="text-center text-slate-600 text-sm py-8">
                        Kéo task vào đây
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="w-full max-w-md bg-dark-800 rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Thêm task mới</h3>
                <div class="space-y-4">
                    <input v-model="form.title" placeholder="Tiêu đề *" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                    <textarea v-model="form.description" placeholder="Mô tả" rows="3" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white"></textarea>
                    <select v-model="form.priority" class="w-full px-4 py-2 rounded-lg bg-dark-700 border border-white/10 text-white">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showModal = false" class="px-4 py-2 rounded-lg bg-dark-600 text-slate-300">Hủy</button>
                    <button @click="createTask" class="px-4 py-2 rounded-lg bg-primary text-white">Tạo</button>
                </div>
            </div>
        </div>
    </div>
</template>
