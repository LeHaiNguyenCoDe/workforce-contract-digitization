<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { emailService } from '@/plugins/api'
import type { EmailCampaign, EmailTemplate } from '@/plugins/api/services/EmailService'

// State
const campaigns = ref<EmailCampaign[]>([])
const templates = ref<EmailTemplate[]>([])
const isLoading = ref(true)
const isModalOpen = ref(false)
const isSending = ref(false)

const form = ref<Partial<EmailCampaign>>({
    name: '',
    subject: '',
    content_html: '',
    status: 'draft'
})

// Fetch data
const fetchData = async () => {
    isLoading.value = true
    try {
        const [campaignsRes, templatesRes] = await Promise.all([
            emailService.getCampaigns(),
            emailService.getTemplates()
        ])
        campaigns.value = campaignsRes.items || []
        templates.value = templatesRes || []
    } catch (error) {
        console.error('Failed to fetch email data:', error)
    } finally {
        isLoading.value = false
    }
}

// Actions
const saveCampaign = async () => {
    isSending.value = true
    try {
        if (form.value.id) {
            await emailService.updateCampaign(form.value.id, form.value)
        } else {
            await emailService.createCampaign(form.value)
        }
        await fetchData()
        isModalOpen.value = false
        resetForm()
    } catch (error) {
        console.error('Failed to save campaign:', error)
    } finally {
        isSending.value = false
    }
}

const deleteCampaign = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa chiến dịch này?')) return
    try {
        await emailService.deleteCampaign(id)
        await fetchData()
    } catch (error) {
        console.error('Failed to delete campaign:', error)
    }
}

const sendCampaign = async (id: number) => {
    if (!confirm('Bạn muốn bắt đầu gửi chiến dịch này ngay bây giờ?')) return
    try {
        await emailService.sendCampaign(id)
        await fetchData()
    } catch (error) {
        console.error('Failed to send campaign:', error)
    }
}

const openEditModal = (campaign: EmailCampaign) => {
    form.value = { ...campaign }
    isModalOpen.value = true
}

const resetForm = () => {
    form.value = {
        name: '',
        subject: '',
        content_html: '',
        status: 'draft'
    }
}

const getStatusColor = (status: string) => {
    const colors: any = {
        draft: 'bg-slate-500/20 text-slate-400',
        scheduled: 'bg-info/20 text-info',
        sending: 'bg-warning/20 text-warning',
        sent: 'bg-success/20 text-success',
        failed: 'bg-error/20 text-error'
    }
    return colors[status] || 'bg-slate-500/20 text-slate-400'
}

onMounted(fetchData)
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Chiến dịch Email</h1>
                <p class="text-slate-400">Gửi thông báo và chương trình khuyến mãi đến khách hàng</p>
            </div>
            <button @click="isModalOpen = true; resetForm()" class="btn-primary flex items-center gap-2">
                <span>➕</span> Tạo chiến dịch
            </button>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="card bg-gradient-to-br from-indigo-500/10 to-transparent border-indigo-500/20">
                <div class="text-sm text-slate-400 mb-1">Tổng chiến dịch</div>
                <div class="text-2xl font-bold text-white">{{ campaigns.length }}</div>
            </div>
            <div class="card bg-gradient-to-br from-success/10 to-transparent border-success/20">
                <div class="text-sm text-slate-400 mb-1">Đã gửi</div>
                <div class="text-2xl font-bold text-success">{{ campaigns.filter(c => c.status === 'sent').length }}</div>
            </div>
            <div class="card bg-gradient-to-br from-warning/10 to-transparent border-warning/20">
                <div class="text-sm text-slate-400 mb-1">Đang chờ/Lên lịch</div>
                <div class="text-2xl font-bold text-warning">{{ campaigns.filter(c => ['draft', 'scheduled'].includes(c.status)).length }}</div>
            </div>
            <div class="card bg-gradient-to-br from-error/10 to-transparent border-error/20">
                <div class="text-sm text-slate-400 mb-1">Thất bại</div>
                <div class="text-2xl font-bold text-error">{{ campaigns.filter(c => c.status === 'failed').length }}</div>
            </div>
        </div>

        <!-- Campaigns Table -->
        <div class="card p-0 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-dark-700/50 text-slate-400 text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Chiến dịch</th>
                        <th class="px-6 py-4 font-semibold">Tiêu đề</th>
                        <th class="px-6 py-4 font-semibold">Trạng thái</th>
                        <th class="px-6 py-4 font-semibold">Ngày tạo</th>
                        <th class="px-6 py-4 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="campaign in campaigns" :key="campaign.id" class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-white">{{ campaign.name }}</div>
                            <div class="text-xs text-slate-500">Bởi {{ campaign.user?.name || 'Admin' }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ campaign.subject }}</td>
                        <td class="px-6 py-4">
                            <span :class="['px-2 py-1 rounded-full text-xs font-bold uppercase', getStatusColor(campaign.status)]">
                                {{ campaign.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ new Date(campaign.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button v-if="campaign.status === 'draft'" @click="sendCampaign(campaign.id)" 
                                    class="p-2 hover:bg-success/20 text-success rounded-lg transition-colors" title="Gửi ngay">
                                    🚀
                                </button>
                                <button @click="openEditModal(campaign)" class="p-2 hover:bg-primary/20 text-primary rounded-lg transition-colors">
                                    ✏️
                                </button>
                                <button @click="deleteCampaign(campaign.id)" class="p-2 hover:bg-error/20 text-error rounded-lg transition-colors">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!isLoading && campaigns.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500 italic">
                            Chưa có chiến dịch nào được tạo
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="card w-full max-w-4xl max-h-[90vh] overflow-y-auto bg-dark-800 border-white/10 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white">{{ form.id ? 'Cập nhật chiến dịch' : 'Tạo chiến dịch mới' }}</h2>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-white text-2xl">×</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Tên chiến dịch</label>
                        <input v-model="form.name" type="text" placeholder="Ví dụ: Khuyến mãi Tết 2024" 
                            class="w-full bg-dark-700 border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Tiêu đề Email</label>
                        <input v-model="form.subject" type="text" placeholder="Nhập tiêu đề khách hàng sẽ thấy" 
                            class="w-full bg-dark-700 border-white/10 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-primary outline-none">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Chọn mẫu (Templates)</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="template in templates" :key="template.id"
                            @click="form.content_html = template.body; form.subject = template.subject"
                            class="p-4 rounded-xl border border-white/5 hover:border-primary/50 hover:bg-primary/5 cursor-pointer transition-all">
                            <div class="text-sm font-bold text-white mb-1">{{ template.name }}</div>
                            <div class="text-xs text-slate-500 italic">{{ template.type }}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nội dung Email (HTML)</label>
                    <textarea v-model="form.content_html" rows="12" placeholder="Nhập nội dung HTML hoặc chọn mẫu ở trên"
                        class="w-full bg-dark-700 border-white/10 rounded-xl px-4 py-3 text-white font-mono text-sm focus:ring-2 focus:ring-primary outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <button @click="isModalOpen = false" class="px-6 py-2.5 rounded-xl border border-white/10 text-slate-400 hover:bg-white/5">
                        Hủy
                    </button>
                    <button @click="saveCampaign" :disabled="isSending" 
                        class="px-8 py-2.5 rounded-xl bg-primary text-white font-bold hover:shadow-lg hover:shadow-primary/30 active:scale-95 transition-all">
                        {{ isSending ? 'Đang lưu...' : 'Lưu chiến dịch' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-primary {
    @apply px-4 py-2 bg-primary hover:bg-primary-light text-white rounded-xl font-bold transition-all active:scale-95;
}
</style>
