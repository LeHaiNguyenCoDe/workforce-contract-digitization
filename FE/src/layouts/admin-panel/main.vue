<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useLayoutStore } from '@/stores/layout';

import Vertical from "./vertical.vue";
import Horizontal from "./horizontal.vue";

const layoutStore = useLayoutStore();

const layoutType = computed(() => layoutStore.layoutType);

onMounted(() => {
    // document.querySelector("html").setAttribute('dir', 'rtl');
});
</script>

<template>
    <div>
        <Vertical v-if="layoutType === 'vertical' || layoutType === 'semibox'" :layout="layoutType">
            <slot />
        </Vertical>

        <Horizontal v-if="layoutType === 'horizontal'" :layout="layoutType">
            <slot />
        </Horizontal>
    </div>
</template>
