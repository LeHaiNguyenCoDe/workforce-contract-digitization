<script setup lang="ts">
import { computed } from 'vue'

interface Column {
  key: string
  label: string
  width?: string
  class?: string
  align?: 'left' | 'center' | 'right'
  sortable?: boolean
}

interface Props {
  columns: Column[]
  data: any[]
  loading?: boolean
  emptyText?: string
  rowKey?: string
  hoverable?: boolean
  striped?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  emptyText: 'Không có dữ liệu',
  rowKey: 'id',
  hoverable: true,
  striped: false
})

const emit = defineEmits<{
  'row-click': [item: any, index: number]
}>()

const getAlignClass = (align?: string) => {
  if (align === 'center') return 'text-center'
  if (align === 'right') return 'text-end'
  return 'text-start'
}
</script>

<template>
  <div class="dtable-wrapper">
    <!-- Table Container -->
    <div class="dtable-container">
      <!-- Loading Overlay -->
      <div v-if="loading" class="dtable-loading-overlay">
        <div class="dtable-spinner"></div>
      </div>

      <table class="dtable">
        <thead class="dtable-head">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              :style="col.width ? { width: col.width } : {}"
              :class="['dtable-th', getAlignClass(col.align), col.class]"
            >
              <span class="dtable-th-content">
                {{ col.label }}
                <span v-if="col.sortable !== false && col.key !== 'selection'" class="dtable-sort-icon">⇅</span>
              </span>
            </th>
            <th v-if="$slots.actions" class="dtable-th text-center">
              Action <span class="dtable-sort-icon">⇅</span>
            </th>
          </tr>
        </thead>
        <tbody class="dtable-body">
          <tr
            v-for="(item, index) in data"
            :key="item[rowKey] || index"
            :class="[
              'dtable-row',
              hoverable && 'dtable-row-hoverable',
              striped && index % 2 === 1 && 'dtable-row-striped'
            ]"
            @click="emit('row-click', item, index)"
          >
            <td
              v-for="col in columns"
              :key="col.key"
              :class="['dtable-td', getAlignClass(col.align), col.class]"
            >
              <slot :name="`cell-${col.key}`" :item="item" :value="item[col.key]" :index="index">
                <span class="dtable-cell-default">{{ item[col.key] ?? '-' }}</span>
              </slot>
            </td>
            <td v-if="$slots.actions" class="dtable-td text-center">
              <slot name="actions" :item="item" :index="index"></slot>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty -->
      <div v-if="!data.length && !loading" class="dtable-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" 
          fill="none" stroke="currentColor" stroke-width="1" class="dtable-empty-icon">
          <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
          <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
        </svg>
        <p class="dtable-empty-text">{{ emptyText }}</p>
      </div>
    </div>

    <!-- Footer slot -->
    <div v-if="$slots.footer && data.length" class="dtable-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<style>
/* DTable Wrapper */
.dtable-wrapper {
  overflow: hidden;
  border-radius: 0;
  background-color: #fff;
}

/* Table Container */
.dtable-container {
  overflow-x: auto;
  position: relative;
  min-height: 200px;
}

/* Loading Overlay */
.dtable-loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
  backdrop-filter: blur(1px);
}

.dtable-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e9ebec;
  border-top-color: #0ab39c;
  border-radius: 50%;
  animation: dtable-spin 0.8s linear infinite;
}

@keyframes dtable-spin {
  to { transform: rotate(360deg); }
}

/* Table */
.dtable {
  width: 100%;
  margin-bottom: 0;
  border-collapse: collapse;
}

/* Table Header */
.dtable-head {
  background-color: #f3f6f9;
  position: sticky;
  top: 0;
  z-index: 10;
}

.dtable-th {
  padding: 12px 16px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  color: #878a99;
  white-space: nowrap;
  border-bottom: 1px solid #e9ebec;
  letter-spacing: 0.5px;
}

.dtable-th-content {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.dtable-sort-icon {
  opacity: 0.4;
  font-size: 10px;
  font-weight: normal;
}

/* Table Body */
.dtable-body {
  background-color: #fff;
}

.dtable-row {
  transition: background-color 0.15s ease;
  border-bottom: 1px solid #f0f0f0;
}

.dtable-row:last-child {
  border-bottom: none;
}

.dtable-row-hoverable:hover {
  background-color: #fafbfc;
  cursor: pointer;
}

.dtable-row-striped {
  background-color: #fafbfc;
}

.dtable-td {
  padding: 16px;
  vertical-align: middle;
  font-size: 13px;
  color: #495057;
}

.dtable-cell-default {
  color: #495057;
}

/* Empty State */
.dtable-empty {
  padding: 64px 0;
  text-align: center;
}

.dtable-empty-icon {
  margin: 0 auto 16px;
  color: #ced4da;
}

.dtable-empty-text {
  color: #878a99;
  font-size: 14px;
  margin: 0;
}

/* Footer */
.dtable-footer {
  border-top: 1px solid #e9ebec;
  background-color: #fff;
}

/* Checkbox Styling */
.dtable .form-check-input {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border-color: #ced4da;
  cursor: pointer;
}

.dtable .form-check-input:checked {
  background-color: #405189;
  border-color: #405189;
}

.dtable .form-check-input:focus {
  box-shadow: 0 0 0 0.2rem rgba(64, 81, 137, 0.25);
}

/* Product Image in Table */
.dtable .product-img-wrapper {
  width: 48px;
  height: 48px;
  background-color: #fff;
  border-radius: 6px;
  border: 1px solid #e9ebec;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.dtable .product-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

/* Action Button */
.dtable .action-btn {
  width: 32px;
  height: 32px;
  background-color: #e8f3ff;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #405189;
  font-size: 16px;
  transition: all 0.2s ease;
  border: none;
}

.dtable .action-btn:hover {
  background-color: #d6e9ff;
}

/* Rating Display */
.dtable .rating-display {
  display: inline-flex;
  align-items: center;
  font-size: 13px;
}

.dtable .rating-display i {
  font-size: 12px;
  color: #f7b84b;
}

/* Published Date */
.dtable .published-date {
  font-size: 13px;
  color: #495057;
}

.dtable .published-date .text-muted {
  color: #878a99 !important;
  font-size: 11px;
}

/* Price styling */
.dtable .text-primary {
  color: #405189 !important;
}

/* Alignment utilities */
.text-start { text-align: left; }
.text-center { text-align: center; }
.text-end { text-align: right; }
</style>
