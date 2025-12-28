<script setup lang="ts">
interface Props {
  status: string
  type?: 'return' | 'order' | 'user' | 'custom'
  customLabels?: Record<string, string>
  customClasses?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  type: 'custom'
})

// Preset labels
const presetLabels: Record<string, Record<string, string>> = {
  return: {
    pending: 'Chờ duyệt',
    approved: 'Đã duyệt',
    rejected: 'Từ chối',
    receiving: 'Đang nhận',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  },
  order: {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    shipped: 'Đang giao',
    delivered: 'Đã giao',
    cancelled: 'Đã hủy'
  },
  user: {
    active: 'Hoạt động',
    inactive: 'Không HĐ',
    banned: 'Bị khóa'
  }
}

// Preset classes
const presetClasses: Record<string, Record<string, string>> = {
  return: {
    pending: 'bg-warning/10 text-warning',
    approved: 'bg-info/10 text-info',
    rejected: 'bg-error/10 text-error',
    receiving: 'bg-primary/10 text-primary',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-slate-500/10 text-slate-400'
  },
  order: {
    pending: 'bg-warning/10 text-warning',
    processing: 'bg-info/10 text-info',
    shipped: 'bg-primary/10 text-primary',
    delivered: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error'
  },
  user: {
    active: 'bg-success/10 text-success',
    inactive: 'bg-slate-500/10 text-slate-400',
    banned: 'bg-error/10 text-error'
  }
}

const getLabel = (): string => {
  if (props.customLabels?.[props.status]) {
    return props.customLabels[props.status]
  }
  if (props.type !== 'custom' && presetLabels[props.type]?.[props.status]) {
    return presetLabels[props.type][props.status]
  }
  return props.status
}

const getClass = (): string => {
  if (props.customClasses?.[props.status]) {
    return props.customClasses[props.status]
  }
  if (props.type !== 'custom' && presetClasses[props.type]?.[props.status]) {
    return presetClasses[props.type][props.status]
  }
  return 'bg-slate-500/10 text-slate-400'
}
</script>

<template>
  <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium', getClass()]">
    <slot>{{ getLabel() }}</slot>
  </span>
</template>
