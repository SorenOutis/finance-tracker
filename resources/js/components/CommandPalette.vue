<script setup lang="ts">
import { ref, watch } from 'vue';
import { useMagicKeys } from '@vueuse/core';
import { router } from '@inertiajs/vue3';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import {
    LayoutDashboard,
    LineChart,
    Wallet,
    Calendar,
    User,
    PlusCircle,
    TrendingDown,
} from 'lucide-vue-next';

const open = ref(false);
const keys = useMagicKeys({
    passive: false,
    onEventFired(e) {
        if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
        }
    },
});
const cmdK = keys['Meta+K'];
const ctrlK = keys['Control+K'];

watch([cmdK, ctrlK], (v) => {
    if (v[0] || v[1]) {
        open.value = !open.value;
    }
});

const navigate = (routePath: string) => {
    open.value = false;
    router.get(routePath);
};

const triggerAction = (type: string) => {
    open.value = false;
    if (type === 'income') {
        localStorage.setItem('dashboard-income-modal', 'true');
        router.get('/dashboard');
    } else if (type === 'expense') {
        localStorage.setItem('dashboard-expense-modal', 'true');
        router.get('/dashboard');
    }
};
</script>

<template>
    <CommandDialog v-model:open="open">
        <CommandInput placeholder="Search or type a command..." />
        <CommandList>
            <CommandEmpty>No results found.</CommandEmpty>
            <CommandGroup heading="Pages">
                <CommandItem value="dashboard" @select="navigate('/dashboard')">
                    <LayoutDashboard class="mr-2 h-4 w-4" />
                    <span>Dashboard</span>
                </CommandItem>
                <CommandItem value="analytics" @select="navigate('/analytics')">
                    <LineChart class="mr-2 h-4 w-4" />
                    <span>Analytics</span>
                </CommandItem>
                <CommandItem value="assets" @select="navigate('/assets')">
                    <Wallet class="mr-2 h-4 w-4" />
                    <span>Assets & Liabilities</span>
                </CommandItem>
                <CommandItem value="yearly" @select="navigate('/yearly')">
                    <Calendar class="mr-2 h-4 w-4" />
                    <span>Yearly Overview</span>
                </CommandItem>
            </CommandGroup>
            <CommandSeparator />
            <CommandGroup heading="Quick Actions">
                <CommandItem value="add income" @select="triggerAction('income')">
                    <PlusCircle class="mr-2 h-4 w-4 text-[--fin-gain]" />
                    <span>Add Income</span>
                </CommandItem>
                <CommandItem value="add expense" @select="triggerAction('expense')">
                    <TrendingDown class="mr-2 h-4 w-4 text-[--fin-loss]" />
                    <span>Add Expense</span>
                </CommandItem>
            </CommandGroup>
            <CommandSeparator />
            <CommandGroup heading="Account">
                <CommandItem value="profile" @select="navigate('/settings/profile')">
                    <User class="mr-2 h-4 w-4" />
                    <span>Profile Settings</span>
                </CommandItem>
            </CommandGroup>
        </CommandList>
    </CommandDialog>
</template>
