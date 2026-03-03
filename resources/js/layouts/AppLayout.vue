<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { onMounted } from 'vue';
import { useLocalStorage } from '@/plugins/localStorage';
import { usePage } from '@inertiajs/vue3';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Save current page to local storage
onMounted(() => {
    useLocalStorage.setCurrentPage(page.url);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
