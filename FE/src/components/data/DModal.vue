<script setup lang="ts">
import { onMounted, onUnmounted, computed, onUpdated, onBeforeUnmount } from 'vue'

interface Props {
  modelValue: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
  closable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  title: '',
  size: 'md',
  closable: true
})

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  close: []
}>()

const show = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const sizeClasses: Record<string, string> = {
  sm: 'dmodal-sm',
  md: 'dmodal-md',
  lg: 'dmodal-lg',
  xl: 'dmodal-xl',
  full: 'dmodal-full'
}

const handleClose = () => {
  if (props.closable) {
    show.value = false
    emit('close')
  }
}

const handleEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.closable) {
    handleClose()
  }
}

const updateBodyOverflow = () => {
  document.body.style.overflow = props.modelValue ? 'hidden' : ''
}

onUpdated(updateBodyOverflow)
onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  updateBodyOverflow()
})
onBeforeUnmount(() => document.removeEventListener('keydown', handleEscape))
onUnmounted(() => { document.body.style.overflow = '' })
</script>

<template>
  <Teleport to="body">
    <Transition name="dmodal-fade">
      <div v-if="show" class="dmodal-overlay" @click.self="handleClose">
        <!-- Modal Container -->
        <div class="dmodal-container" :class="sizeClasses[size]">
          <!-- Header -->
          <div class="dmodal-header">
            <h5 class="dmodal-title">
              <slot name="title">{{ title }}</slot>
            </h5>
            <button v-if="closable" type="button" class="dmodal-close" @click="handleClose">
              <i class="ri-close-line"></i>
            </button>
          </div>
          
          <!-- Body -->
          <div class="dmodal-body">
            <slot></slot>
          </div>
          
          <!-- Footer -->
          <div v-if="$slots.footer" class="dmodal-footer">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style>
/* Modal Overlay */
.dmodal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 20px;
}

/* Modal Container */
.dmodal-container {
  background: #fff;
  border-radius: 8px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  animation: dmodal-scale-in 0.2s ease-out;
}

/* Size variants */
.dmodal-sm { max-width: 360px; }
.dmodal-md { max-width: 520px; }
.dmodal-lg { max-width: 720px; }
.dmodal-xl { max-width: 960px; }
.dmodal-full { max-width: 90vw; }

/* Modal Header */
.dmodal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e9ebec;
  flex-shrink: 0;
}

.dmodal-title {
  font-size: 16px;
  font-weight: 600;
  color: #212529;
  margin: 0;
}

.dmodal-close {
  background: none;
  border: none;
  font-size: 20px;
  color: #878a99;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  transition: all 0.2s;
}

.dmodal-close:hover {
  background-color: #f3f6f9;
  color: #495057;
}

/* Modal Body */
.dmodal-body {
  padding: 24px;
  flex: 1;
  overflow-y: auto;
}

/* Modal Footer */
.dmodal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e9ebec;
  flex-shrink: 0;
}

/* Form elements inside modal */
.dmodal-body .form-group {
  margin-bottom: 16px;
}

.dmodal-body .form-group:last-child {
  margin-bottom: 0;
}

.dmodal-body .form-label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #212529;
  margin-bottom: 8px;
}

.dmodal-body .form-control,
.dmodal-body .form-select {
  width: 100%;
  padding: 10px 14px;
  font-size: 13px;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.dmodal-body .form-control::placeholder {
  color: #adb5bd;
}

.dmodal-body .form-control:focus,
.dmodal-body .form-select:focus {
  outline: none;
  border-color: #0ab39c;
  box-shadow: 0 0 0 3px rgba(10, 179, 156, 0.1);
}

/* Footer Buttons */
.dmodal-footer .btn-close-modal,
.dmodal-footer .btn-light {
  padding: 10px 20px;
  font-size: 13px;
  font-weight: 500;
  color: #495057;
  background-color: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.dmodal-footer .btn-close-modal:hover,
.dmodal-footer .btn-light:hover {
  background-color: #f3f6f9;
  border-color: #adb5bd;
}

.dmodal-footer .btn-submit,
.dmodal-footer .btn-success {
  padding: 10px 20px;
  font-size: 13px;
  font-weight: 500;
  color: #fff;
  background-color: #0ab39c;
  border: 1px solid #0ab39c;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.dmodal-footer .btn-submit:hover,
.dmodal-footer .btn-success:hover {
  background-color: #099885;
  border-color: #099885;
}

.dmodal-footer .btn-submit:disabled,
.dmodal-footer .btn-success:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Animation */
@keyframes dmodal-scale-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Transition */
.dmodal-fade-enter-active,
.dmodal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.dmodal-fade-enter-from,
.dmodal-fade-leave-to {
  opacity: 0;
}

.dmodal-fade-enter-from .dmodal-container,
.dmodal-fade-leave-to .dmodal-container {
  transform: scale(0.95);
}
</style>
