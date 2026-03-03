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
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Year Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    v-for="stat in displayStats"
                    :key="stat.label"
                    class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white"
                >
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ stat.label }}</p>
                    <p class="text-4xl font-bold">{{ stat.value }}</p>
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                <h2 class="mb-2 text-xl font-bold">Monthly Breakdown - {{ props.currentYear }}</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Income, expenses, and net for each month</p>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                <th class="px-4 py-3 text-left text-sm font-semibold">Month</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">Income</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">Expenses</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in displayMonthlyData"
                                :key="index"
                                class="border-b border-gray-300 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-900"
                            >
                                <td class="px-4 py-3 text-sm">{{ row.month }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ row.income }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ row.expenses }}</td>
                                <td class="px-4 py-3 text-right text-sm">{{ row.net }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
