<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, BarChart3, LineChart, PiggyBank, Settings } from 'lucide-vue-next';
import { dashboard, yearly, analytics, assets, settings } from '@/routes';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

const { isCurrentUrl } = useCurrentUrl();

const navItems: NavItem[] = [
    {
        title: 'Yearly',
        href: yearly(),
        icon: BarChart3,
    },
    {
        title: 'Analytics',
        href: analytics(),
        icon: LineChart,
    },
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Assets',
        href: assets(),
        icon: PiggyBank,
    },
    {
        title: 'Settings',
        href: settings(),
        icon: Settings,
    },
];
</script>

<template>
    <nav
        class="fixed left-4 right-4 z-50 rounded-[2rem] border border-border/40 bg-background/80 shadow-2xl focus:outline-none backdrop-blur-xl supports-[backdrop-filter]:bg-background/60 shadow-black/20 dark:shadow-black/50 md:hidden"
        style="bottom: calc(1rem + env(safe-area-inset-bottom));"
    >
        <div class="flex items-center justify-around px-2 pt-2 pb-2">
            <Link
                v-for="item in navItems"
                :key="item.title"
                :href="item.href"
                class="relative flex flex-1 flex-col items-center justify-center gap-1 py-1.5 px-1 text-[10px] font-medium transition-all"
                :class="
                    isCurrentUrl(item.href)
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'
                "
            >
                <div v-show="isCurrentUrl(item.href)" class="absolute -top-2 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.8)]" />
                <component :is="item.icon" class="h-6 w-6 stroke-[1.5]" />
                <span class="truncate">{{ item.title }}</span>
            </Link>
        </div>
    </nav>
</template>
