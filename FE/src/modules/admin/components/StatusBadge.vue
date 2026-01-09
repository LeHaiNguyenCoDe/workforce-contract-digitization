<script setup lang="ts">
import { useI18n } from 'vue-i18n'

interface Props {
  status: string
  text?: string
  type?: 'return' | 'order' | 'user' | 'custom'
  customLabels?: Record<string, string>
  customClasses?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  type: 'custom'
})

const { t } = useI18n()

// Preset label keys mapped to i18n translation keys
const presetLabelKeys: Record<string, Record<string, string>> = {
  return: {
    pending: 'common.pendingApproval',
    approved: 'common.approved',
    rejected: 'common.rejected',
    receiving: 'common.receiving',
    completed: 'common.completed',
    cancelled: 'common.cancelled'
  },
  order: {
    pending: 'common.pending',
    processing: 'common.processingOrder',
    shipped: 'common.shipped',
    delivered: 'common.delivered',
    cancelled: 'common.cancelled'
  },
  user: {
    active: 'common.active',
    inactive: 'common.inactive',
    banned: 'common.banned'
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
  // If custom text prop provided, use it directly
  if (props.text) {
    return props.text
  }
  if (props.customLabels?.[props.status]) {
    return props.customLabels[props.status]
  }
  if (props.type !== 'custom' && presetLabelKeys[props.type]?.[props.status]) {
    return t(presetLabelKeys[props.type][props.status])
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
