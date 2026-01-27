<script setup lang="ts">
import { ref } from 'vue'
import httpClient from '@/plugins/api/httpClient'

const isExporting = ref(false)
const isImporting = ref(false)
const importFile = ref<File | null>(null)
const importResult = ref<{ imported: number; errors: string[] } | null>(null)

const exportProducts = async () => {
    isExporting.value = true
    try {
        const res = await httpClient.get('admin/import-export/export/products')
        const data = res.data.data
        downloadCSV(data.headers, data.data, 'products_export.csv')
    } catch (e) { console.error(e) }
    finally { isExporting.value = false }
}

const exportOrders = async () => {
    isExporting.value = true
    try {
        const res = await httpClient.get('admin/import-export/export/orders')
        const data = res.data.data
        downloadCSV(data.headers, data.data, 'orders_export.csv')
    } catch (e) { console.error(e) }
    finally { isExporting.value = false }
}

const downloadCSV = (headers: string[], rows: any[], filename: string) => {
    const csv = [
        headers.join(','),
        ...rows.map(row => Object.values(row).map((v: any) => `"${v}"`).join(','))
    ].join('\n')
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = filename
    link.click()
}

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    importFile.value = target.files?.[0] || null
}

const importProducts = async () => {
    if (!importFile.value) return
    isImporting.value = true
    try {
        const formData = new FormData()
        formData.append('file', importFile.value)
        const res = await httpClient.post('admin/import-export/import/products', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        importResult.value = res.data.data
        importFile.value = null
    } catch (e) { console.error(e) }
    finally { isImporting.value = false }
}

const downloadTemplate = async (type: string) => {
    try {
        const res = await httpClient.get(`admin/import-export/template/${type}`)
        const data = res.data.data
        downloadCSV(data.headers, [data.sample], `${type}_template.csv`)
    } catch (e) { console.error(e) }
}
</script>

<template>
    <div>
        <h1 class="text-2xl font-bold text-white mb-6">Import / Export Dữ liệu</h1>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Export -->
            <div class="card">
                <h3 class="text-lg font-bold text-white mb-4">📤 Export dữ liệu</h3>
                <div class="space-y-3">
                    <button @click="exportProducts" :disabled="isExporting"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-dark-700 hover:bg-dark-600 text-white transition-colors">
                        <span>Xuất danh sách sản phẩm</span>
                        <span class="text-sm text-slate-400">CSV</span>
                    </button>
                    <button @click="exportOrders" :disabled="isExporting"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-dark-700 hover:bg-dark-600 text-white transition-colors">
                        <span>Xuất danh sách đơn hàng</span>
                        <span class="text-sm text-slate-400">CSV</span>
                    </button>
                </div>
            </div>

            <!-- Import -->
            <div class="card">
                <h3 class="text-lg font-bold text-white mb-4">📥 Import dữ liệu</h3>
                <div class="space-y-4">
                    <div class="flex gap-2">
                        <button @click="downloadTemplate('products')" class="text-sm text-primary hover:underline">
                            Tải template sản phẩm
                        </button>
                        <button @click="downloadTemplate('customers')" class="text-sm text-primary hover:underline">
                            Tải template khách hàng
                        </button>
                    </div>
                    
                    <div class="border-2 border-dashed border-white/10 rounded-lg p-6 text-center">
                        <input type="file" accept=".csv,.xlsx,.xls" @change="handleFileChange" class="hidden" id="importFile">
                        <label for="importFile" class="cursor-pointer">
                            <div class="text-slate-400 mb-2">
                                {{ importFile ? importFile.name : 'Chọn file CSV hoặc Excel' }}
                            </div>
                            <span class="px-4 py-2 rounded-lg bg-dark-600 text-white text-sm">Chọn file</span>
                        </label>
                    </div>
                    
                    <button v-if="importFile" @click="importProducts" :disabled="isImporting"
                        class="w-full px-4 py-2 rounded-lg bg-primary text-white font-medium disabled:opacity-50">
                        {{ isImporting ? 'Đang import...' : 'Import sản phẩm' }}
                    </button>

                    <div v-if="importResult" class="p-4 rounded-lg" 
                        :class="importResult.errors.length ? 'bg-warning/10' : 'bg-success/10'">
                        <div class="text-white mb-2">Đã import {{ importResult.imported }} sản phẩm</div>
                        <div v-if="importResult.errors.length" class="text-sm text-error space-y-1">
                            <div v-for="(err, i) in importResult.errors.slice(0, 5)" :key="i">{{ err }}</div>
                            <div v-if="importResult.errors.length > 5">...và {{ importResult.errors.length - 5 }} lỗi khác</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
