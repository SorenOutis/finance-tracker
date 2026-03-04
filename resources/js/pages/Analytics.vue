<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { analytics } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { formatCurrency } from '@/utils/currency';
import { useChartDefaults } from '@/composables/useChartDefaults';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Line as LineChart, Bar as BarChart, Doughnut as DoughnutChart } from 'vue-chartjs';

ChartJS.register(...registerables);

interface TrendItem {
    month: string;
    income: number;
    expenses: number;
}

interface CategoryItem {
    name: string;
    amount: number;
    percentage: number;
}

interface Props {
    trendData: TrendItem[];
    categoryData: CategoryItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Analytics',
        href: analytics(),
    },
];

const { chartColors, lineChartOptions, barChartOptions, doughnutChartOptions, isDark } = useChartDefaults();

// Income vs Expenses Trend Chart
const incomeExpensesChart = computed(() => {
    if (!props.trendData || props.trendData.length === 0) {
        return {
            labels: [],
            datasets: []
        };
    }
    
    const labels = props.trendData.map((d: any) => d.month);
    const incomeData = props.trendData.map((d: any) => d.income);
    const expensesData = props.trendData.map((d: any) => d.expenses);
    
    return {
        labels: labels,
        datasets: [
            {
                label: 'Income',
                data: incomeData,
                borderColor: chartColors.teal,
                backgroundColor: chartColors.tealMuted,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: chartColors.teal,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            },
            {
                label: 'Expenses',
                data: expensesData,
                borderColor: chartColors.loss,
                backgroundColor: chartColors.lossMuted,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: chartColors.loss,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
            }
        ]
    };
});

// Monthly Net Income Chart
const netIncomeChart = computed(() => {
    if (!props.trendData || props.trendData.length === 0) {
        return {
            labels: [],
            datasets: []
        };
    }
    
    return {
        labels: props.trendData.map(d => d.month),
        datasets: [{
            label: 'Net Income',
            data: props.trendData.map(d => {
                const net = d.income - d.expenses;
                return net >= 0 ? net : 0;
            }),
            backgroundColor: props.trendData.map(d => d.income - d.expenses >= 0 ? chartColors.gain : chartColors.loss),
            borderRadius: 6,
            borderSkipped: false,
        }]
    };
});

// Expenses by Category Chart
const categoryChart = computed(() => {
    if (!props.categoryData || props.categoryData.length === 0) {
        return {
            labels: [],
            datasets: []
        };
    }
    
    const categoryColors = [
        chartColors.teal,
        chartColors.azure,
        chartColors.amber,
        chartColors.slate,
        chartColors.loss,
    ];
    
    return {
        labels: props.categoryData.map(c => c.name),
        datasets: [{
            data: props.categoryData.map(c => c.amount),
            backgroundColor: props.categoryData.map((_, i) => categoryColors[i % categoryColors.length]),
            borderColor: isDark.value ? 'oklch(0.269 0 0)' : 'oklch(0.922 0 0)',
            borderWidth: 2,
            hoverOffset: 4,
        }]
    };
});

// Top category
const topCategory = computed(() => {
    if (props.categoryData && props.categoryData.length > 0) {
        return props.categoryData[0];
    }
    return null;
});
</script>

<template>
    <Head title="Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Analytics</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Track your financial trends and insights</p>
            </div>

            <!-- Income vs Expenses Trend -->
            <div class="surface-card p-5 lg:p-6">
                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Income vs Expenses Trend</h2>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans">Monthly comparison of income and expenses</p>

                <div v-if="props.trendData && props.trendData.length > 0" class="relative mt-6" style="height: 400px; position: relative;">
                    <LineChart
                        :key="JSON.stringify(incomeExpensesChart)"
                        :data="incomeExpensesChart"
                        :options="lineChartOptions"
                        style="position: relative; height: 100%;"
                    />
                </div>
                <div v-else class="mt-6 flex h-96 items-center justify-center text-sm text-muted-foreground">
                    No data available
                </div>
            </div>

            <!-- Monthly Net Income -->
            <div class="surface-card p-5 lg:p-6">
                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Monthly Net Income</h2>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans">Your earnings after expenses</p>

                <div v-if="props.trendData.length > 0" class="relative mt-6" style="height: 400px; position: relative;">
                    <BarChart
                        :key="JSON.stringify(netIncomeChart)"
                        :data="netIncomeChart"
                        :options="barChartOptions"
                        style="position: relative; height: 100%;"
                    />
                </div>
                <div v-else class="mt-6 flex h-96 items-center justify-center text-sm text-muted-foreground">
                    No data available
                </div>
            </div>

            <!-- Expenses by Category -->
            <div class="surface-card p-5 lg:p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expenses by Category</h2>
                        <p class="mt-0.5 text-[11px] text-muted-foreground font-sans">Breakdown of your spending patterns</p>
                    </div>
                    <div v-if="topCategory" class="text-right">
                        <p class="text-[11px] text-muted-foreground font-sans">Top Category</p>
                        <p class="text-lg lg:text-2xl font-bold text-foreground font-mono mt-1">{{ formatCurrency(topCategory.amount) }}</p>
                    </div>
                </div>

                <!-- Category Chart -->
                <div v-if="props.categoryData.length > 0" class="relative mt-6" style="height: 400px; position: relative;">
                    <DoughnutChart
                        :key="JSON.stringify(categoryChart)"
                        :data="categoryChart"
                        :options="doughnutChartOptions"
                        style="position: relative; height: 100%;"
                    />
                </div>

                <div v-else class="mt-6 flex h-96 items-center justify-center text-sm text-muted-foreground">
                    No category data available
                </div>
            </div>
        </div>
    </AppLayout>
</template>
