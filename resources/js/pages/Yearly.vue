<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { yearly } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { formatCurrency } from '@/utils/currency';

interface Stat {
    label: string;
    value: number;
}

interface MonthlyDataItem {
    month: string;
    income: number;
    expenses: number;
    net: number;
}

interface Props {
    yearlyStats: Stat[];
    monthlyData: MonthlyDataItem[];
    currentYear: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Yearly',
        href: yearly(),
    },
];

const displayStats = computed(() =>
    props.yearlyStats.map(stat => ({
        label: stat.label,
        value: formatCurrency(stat.value),
    }))
);

const displayMonthlyData = computed(() =>
    props.monthlyData.map(item => ({
        month: item.month,
        income: formatCurrency(item.income),
        expenses: formatCurrency(item.expenses),
        net: formatCurrency(item.net),
    }))
);
</script>

<template>
    <Head title="Yearly" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Yearly Overview</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Full year {{ props.currentYear }} financial summary</p>
            </div>

            <!-- Year Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:gap-6">
                <div
                    v-for="stat in displayStats"
                    :key="stat.label"
                    class="surface-card p-4 lg:p-5 overflow-hidden relative"
                >
                    <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">{{ stat.label }}</p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">{{ stat.value }}</p>
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div class="surface-card p-5 lg:p-6">
                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Monthly Breakdown</h2>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans">Income, expenses, and net income for each month in {{ props.currentYear }}</p>

                <!-- Table -->
                <div class="overflow-x-auto mt-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border/30">
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Month</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Income</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Expenses</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in displayMonthlyData"
                                :key="index"
                                class="border-b border-border/30 hover:bg-accent/20 transition-colors"
                            >
                                <td class="px-4 py-3 text-sm font-semibold text-foreground font-sans">{{ row.month }}</td>
                                <td class="px-4 py-3 text-right text-sm font-mono text-foreground">{{ row.income }}</td>
                                <td class="px-4 py-3 text-right text-sm font-mono text-foreground">{{ row.expenses }}</td>
                                <td class="px-4 py-3 text-right text-sm font-mono font-semibold text-foreground">{{ row.net }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
