<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, LineChart, CalendarClock, Settings, CreditCard } from 'lucide-vue-next';
import { dashboard, analytics, settings } from '@/routes';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

const { isCurrentUrl } = useCurrentUrl();

const navItems: NavItem[] = [
    {
        title: 'Analytics',
        href: analytics(),
        icon: LineChart,
    },
    {
        title: 'Wallets',
        href: '/wallets',
        icon: CreditCard,
    },
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Bills',
        href: '/bills',
        icon: CalendarClock,
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
        class="fixed left-6 right-6 z-50 rounded-[2.5rem] border border-white/20 dark:border-white/10 bg-background/70 shadow-[0_20px_50px_rgba(0,0,0,0.3)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.6)] focus:outline-none backdrop-blur-2xl supports-[backdrop-filter]:bg-background/40 md:hidden"
        style="bottom: calc(1.5rem + env(safe-area-inset-bottom));"
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
