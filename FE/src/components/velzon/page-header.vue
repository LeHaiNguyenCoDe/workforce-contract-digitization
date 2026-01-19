<script setup lang="ts">
const props = defineProps<{
  title: string;
  pageTitle: string;
}>();
</script>

<template>
  <BRow>
    <BCol cols="12">
      <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
        <h4 class="mb-sm-0">{{ props.title }}</h4>
        <div class="page-title-right">
          <ol class="breadcrumb m-0">
            <li class="breadcrumb-item">
              <a href="javascript: void(0);">{{ props.pageTitle }}</a>
            </li>
            <li class="breadcrumb-item active">{{ props.title }}</li>
          </ol>
        </div>
      </div>
    </BCol>
  </BRow>
</template>
