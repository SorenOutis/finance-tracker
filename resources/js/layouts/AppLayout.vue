<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { onMounted, ref } from 'vue';
import { useLocalStorage } from '@/plugins/localStorage';
import { usePage } from '@inertiajs/vue3';
import gsap from 'gsap';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const mainContent = ref<HTMLElement | null>(null);

// Save current page to local storage and animate entry
onMounted(() => {
    useLocalStorage.setCurrentPage(page.url);

    // Global entry animation for a premium feel
    if (mainContent.value) {
        gsap.from(mainContent.value.querySelectorAll('.surface-card, .animate-fade-up, h1, p'), {
            y: 30,
            opacity: 0,
            duration: 0.8,
            stagger: 0.1,
            ease: 'expo.out',
            clearProps: 'all',
        });
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div ref="mainContent" class="contents">
            <slot />
        </div>
    </AppLayout>
</template>
