<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useSessionStorage } from '@vueuse/core';
import { onMounted, ref, computed } from 'vue';
import LoadingScreen from '@/components/LoadingScreen.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { useLocalStorage } from '@/plugins/localStorage';
import type { BreadcrumbItem } from '@/types';


type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Page transition key — changes on every navigation to restart CSS slide-in animation
const pageKey = computed(() => page.url);

// Only show loading screen once per session
const showLoading = useSessionStorage('has-seen-loading', false);
const isLoadingVisible = ref(!showLoading.value);

const onLoadingComplete = () => {
    showLoading.value = true;
    isLoadingVisible.value = false;
};


// Save current page to local storage
onMounted(() => {
    useLocalStorage.setCurrentPage(page.url);
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <LoadingScreen v-if="isLoadingVisible" @complete="onLoadingComplete" />
        <div class="contents page-enter" :key="pageKey">
            <slot />
        </div>
    </AppLayout>
    <ToastNotification />
</template>

<style scoped>
/**
 * Slide-in transition for page navigation.
 * The :key binding on the wrapper div causes Vue to recreate the element
 * whenever the page URL changes, re-triggering this animation.
 */
.page-enter {
    animation: page-slide-in 0.4s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

@keyframes page-slide-in {
    0% {
        opacity: 0;
        transform: translateX(24px) translateY(4px);
    }
    100% {
        opacity: 1;
        transform: translateX(0) translateY(0);
    }
}
</style>
