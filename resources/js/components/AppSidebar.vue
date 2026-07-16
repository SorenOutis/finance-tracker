<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, LineChart, CalendarClock, CreditCard, Wallet, History, ReceiptText, Tags, CalendarDays, Download, PiggyBank } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, analytics } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'History',
        href: '/history',
        icon: History,
    },
    {
        title: 'Wallets',
        href: '/wallets',
        icon: CreditCard,
    },
    {
        title: 'Bills',
        href: '/bills',
        icon: CalendarClock,
    },
    {
        title: 'Expenses',
        href: '/expenses',
        icon: ReceiptText,
    },
    {
        title: 'Income',
        href: '/incomes',
        icon: Wallet,
    },
    {
        title: 'Analytics',
        href: analytics(),
        icon: LineChart,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: Tags,
    },
    {
        title: 'Calendar',
        href: '/calendar',
        icon: CalendarDays,
    },
    {
        title: 'Envelopes',
        href: '/envelopes',
        icon: PiggyBank,
    },
    {
        title: 'Import',
        href: '/import',
        icon: Download,
    },

];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
