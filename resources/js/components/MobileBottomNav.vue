<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, BarChart3, LineChart, PiggyBank, Settings } from 'lucide-vue-next';
import { dashboard, yearly, analytics, assets, settings } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();

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

const isActive = (href: string) => {
    return page.url === href || page.url.startsWith(href + '?');
};
</script>

<template>
    <nav
        class="fixed bottom-0 left-0 right-0 border-t border-border bg-card md:hidden"
    >
        <div class="flex items-center justify-around">
            <Link
                v-for="item in navItems"
                :key="item.href"
                :href="item.href"
                class="flex flex-1 flex-col items-center justify-center gap-1 py-3 px-2 text-xs font-medium transition-colors"
                :class="
                    isActive(item.href)
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'
                "
            >
                <component :is="item.icon" class="h-5 w-5" />
                <span class="truncate">{{ item.title }}</span>
            </Link>
        </div>
    </nav>
</template>
