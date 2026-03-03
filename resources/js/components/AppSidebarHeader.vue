<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Moon, Sun } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import { useAppearance } from '@/composables/useAppearance';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { appearance, updateAppearance } = useAppearance();

const toggleTheme = () => {
    const newTheme = appearance.value === 'dark' ? 'light' : 'dark';
    updateAppearance(newTheme);
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- Theme Toggle Button -->
        <button
            @click="toggleTheme"
            class="inline-flex items-center justify-center rounded-md p-2 text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-neutral-800"
            :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`"
        >
            <Sun v-if="appearance === 'dark'" class="h-5 w-5" />
            <Moon v-else class="h-5 w-5" />
        </button>
    </header>
</template>
