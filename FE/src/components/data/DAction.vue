<script setup lang="ts">
interface Props {
  icon: 'view' | 'edit' | 'delete' | 'approve' | 'reject' | 'complete'
  title?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false
})

const emit = defineEmits<{
  click: [e: Event]
}>()

const iconConfig: Record<string, { class: string; icon: string }> = {
  view: {
    class: 'bg-slate-600/20 text-slate-400 hover:bg-slate-600/40',
    icon: 'eye'
  },
  edit: {
    class: 'bg-info/10 text-info hover:bg-info/20',
    icon: 'pencil'
  },
  delete: {
    class: 'bg-error/10 text-error hover:bg-error/20',
    icon: 'trash'
  },
  approve: {
    class: 'bg-success/10 text-success hover:bg-success/20',
    icon: 'check'
  },
  reject: {
    class: 'bg-error/10 text-error hover:bg-error/20',
    icon: 'x'
  },
  cancel: {
    class: 'bg-error/10 text-error hover:bg-error/20',
    icon: 'x'
  },
  complete: {
    class: 'bg-primary/10 text-primary hover:bg-primary/20',
    icon: 'check'
  },
  inventory: {
    class: 'bg-info/10 text-info hover:bg-info/20',
    icon: 'package'
  },
  tracking: {
    class: 'bg-primary/10 text-primary hover:bg-primary/20',
    icon: 'truck'
  }
}

const config = iconConfig[props.icon] || iconConfig.view

const getIconUrl = (iconName: string) => {
  // Map internal icon names to asset names if necessary
  const nameMap: Record<string, string> = {
    eye: 'eye',
    pencil: 'pencil',
    trash: 'trash',
    check: 'check',
    x: 'x',
    package: 'package',
    truck: 'truck'
  }
  return new URL(`../../assets/admin/icons/${nameMap[iconName] || iconName}.svg`, import.meta.url).href
}

const handleClick = (e: Event) => {
  if (!props.disabled) {
    emit('click', e)
  }
}
</script>

<template>
  <button
    type="button"
    @click.stop="handleClick"
    :disabled="disabled"
    :title="title"
    class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
    :class="[config.class, disabled && 'opacity-50 cursor-not-allowed']"
  >
    <img :src="getIconUrl(config.icon)" class="w-4 h-4 brightness-0 invert opacity-70" :alt="icon" />
  </button>
</template>
