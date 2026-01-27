<script setup lang="ts">
const props = defineProps<{
  errors?: Record<string, string>
  readonly?: boolean
}>()

const manufacturerName = defineModel<string>('manufacturerName', { default: '' })
const manufacturerBrand = defineModel<string>('manufacturerBrand', { default: '' })
const stockQuantity = defineModel<number>('stockQuantity', { default: 0 })
const price = defineModel<number>('price', { default: 0 })
const discountPercentage = defineModel<number>('discountPercentage', { default: 0 })
const ordersCount = defineModel<number>('ordersCount', { default: 0 })
</script>

<template>
  <BCard no-body class="border-0 shadow-sm mb-4">
    <BCardHeader class="bg-transparent border-bottom px-0">
      <BTabs class="nav-tabs-custom card-header-tabs border-bottom-0 ps-3">
        <BTab title="Thông tin chung" active />
        <BTab title="Meta Data" />
      </BTabs>
    </BCardHeader>
    <BCardBody class="p-4">
      <BRow class="g-3">
        <BCol md="6">
          <label class="form-label fs-13 text-muted mb-2">Tên nhà sản xuất</label>
          <input type="text" class="form-control" v-model="manufacturerName" :disabled="readonly" placeholder="Nhập tên nhà sản xuất" />
        </BCol>
        <BCol md="6">
          <label class="form-label fs-13 text-muted mb-2">Thương hiệu</label>
          <input type="text" class="form-control" v-model="manufacturerBrand" :disabled="readonly" placeholder="Nhập thương hiệu" />
        </BCol>
        <BCol md="3">
          <label class="form-label fs-13 text-muted mb-2">Số lượng tồn</label>
          <input type="number" class="form-control" v-model="stockQuantity" :disabled="readonly" placeholder="0" />
        </BCol>
        <BCol md="3">
          <label class="form-label fs-13 text-muted mb-2">Giá niêm yết</label>
          <div class="input-group has-validation">
            <span class="input-group-text">$</span>
            <input type="number" class="form-control" :class="{'is-invalid': errors?.price}" v-model="price" :disabled="readonly" placeholder="0.00" />
            <div v-if="errors?.price" class="invalid-feedback">{{ errors.price }}</div>
          </div>
        </BCol>
        <BCol md="3">
          <label class="form-label fs-13 text-muted mb-2">Giảm giá</label>
          <div class="input-group">
            <span class="input-group-text">%</span>
            <input type="number" class="form-control" v-model="discountPercentage" :disabled="readonly" placeholder="0" />
          </div>
        </BCol>
        <BCol md="3">
          <label class="form-label fs-13 text-muted mb-2">Đơn hàng</label>
          <input type="number" class="form-control" v-model="ordersCount" :disabled="readonly" placeholder="0" />
        </BCol>
      </BRow>
    </BCardBody>
  </BCard>
</template>

<style scoped>
.nav-tabs-custom .nav-link {
  border: none;
  font-weight: 500;
  color: var(--vz-secondary-color);
  position: relative;
  padding: 1rem 1.5rem;
}

.nav-tabs-custom .nav-link.active {
  color: var(--vz-primary);
  background-color: transparent;
}

.nav-tabs-custom .nav-link.active::after {
  content: "";
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: var(--vz-primary);
}
</style>
