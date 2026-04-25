<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { analytics, yearly } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { formatCurrency } from '@/utils/currency';
import { useChartDefaults } from '@/composables/useChartDefaults';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Line as LineChart, Bar as BarChart, Doughnut as DoughnutChart } from 'vue-chartjs';
import { TrendingUp, TrendingDown, BarChart3, PieChart, ArrowUpRight, ArrowDownRight, ArrowRight } from 'lucide-vue-next';
import { onMounted } from 'vue';
import gsap from 'gsap';

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

// Summary stats computed from trend data
const totalYearIncome = computed(() => props.trendData?.reduce((sum, d) => sum + d.income, 0) ?? 0);
const totalYearExpenses = computed(() => props.trendData?.reduce((sum, d) => sum + d.expenses, 0) ?? 0);
const totalYearNet = computed(() => totalYearIncome.value - totalYearExpenses.value);
const avgMonthlyExpense = computed(() => {
    const monthsWithData = props.trendData?.filter(d => d.expenses > 0).length ?? 0;
    return monthsWithData > 0 ? totalYearExpenses.value / monthsWithData : 0;
});

const summaryStats = computed(() => [
    {
        label: 'Total Income',
        value: formatCurrency(totalYearIncome.value),
        icon: TrendingUp,
        tint: 'stat-tint-income',
    },
    {
        label: 'Total Expenses',
        value: formatCurrency(totalYearExpenses.value),
        icon: TrendingDown,
        tint: 'stat-tint-expense',
    },
    {
        label: 'Net Profit',
        value: formatCurrency(totalYearNet.value),
        icon: totalYearNet.value >= 0 ? ArrowUpRight : ArrowDownRight,
        tint: totalYearNet.value >= 0 ? 'stat-tint-positive' : 'stat-tint-negative',
        colorClass: totalYearNet.value >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]',
    },
    {
        label: 'Avg Monthly Spend',
        value: formatCurrency(avgMonthlyExpense.value),
        icon: BarChart3,
        tint: 'stat-tint-accent',
    },
]);

// Best/worst months
const bestMonth = computed(() => {
    if (!props.trendData || props.trendData.length === 0) return null;
    const withNet = props.trendData.map(d => ({ ...d, net: d.income - d.expenses }));
    return withNet.reduce((best, d) => d.net > best.net ? d : best, withNet[0]);
});

const worstMonth = computed(() => {
    if (!props.trendData || props.trendData.length === 0) return null;
    const withNet = props.trendData.filter(d => d.income > 0 || d.expenses > 0).map(d => ({ ...d, net: d.income - d.expenses }));
    if (withNet.length === 0) return null;
    return withNet.reduce((worst, d) => d.net < worst.net ? d : worst, withNet[0]);
});

// Income vs Expenses Trend Chart
const incomeExpensesChart = computed(() => {
    if (!props.trendData || props.trendData.length === 0) {
        return { labels: [], datasets: [] };
    }
    
    return {
        labels: props.trendData.map(d => d.month),
        datasets: [
            {
                label: 'Income',
                data: props.trendData.map(d => d.income),
                borderColor: chartColors.teal,
                backgroundColor: (context: any) => {
                    const { ctx, chartArea } = context.chart;
                    if (!chartArea) return chartColors.tealMuted;
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, chartColors.tealMuted);
                    gradient.addColorStop(1, 'oklch(0.6 0.118 184.704 / 0.01)');
                    return gradient;
                },
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
                data: props.trendData.map(d => d.expenses),
                borderColor: chartColors.loss,
                backgroundColor: (context: any) => {
                    const { ctx, chartArea } = context.chart;
                    if (!chartArea) return chartColors.lossMuted;
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, chartColors.lossMuted);
                    gradient.addColorStop(1, 'oklch(0.62 0.22 18 / 0.01)');
                    return gradient;
                },
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
        return { labels: [], datasets: [] };
    }
    
    return {
        labels: props.trendData.map(d => d.month),
        datasets: [{
            label: 'Net Income',
            data: props.trendData.map(d => d.income - d.expenses),
            backgroundColor: props.trendData.map(d => d.income - d.expenses >= 0 ? chartColors.gain : chartColors.loss),
            borderRadius: 6,
            borderSkipped: false,
        }]
    };
});

// Expenses by Category Chart
const categoryChart = computed(() => {
    if (!props.categoryData || props.categoryData.length === 0) {
        return { labels: [], datasets: [] };
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

// Category colors for the breakdown list
const categoryBarColors = [
    chartColors.teal,
    chartColors.azure,
    chartColors.amber,
    chartColors.slate,
    chartColors.loss,
];

onMounted(() => {
    // Animate category bars
    gsap.from('.animate-bar-fill', {
        scaleX: 0,
        duration: 1.5,
        stagger: 0.1,
        ease: 'expo.out',
        delay: 0.5
    });
});
</script>

<template>
    <Head title="Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 p-4 sm:p-8 pb-28 md:pb-12">
            <!-- Header Section (Compact Hero) -->
            <div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 mb-1.5 sm:mb-2 animate-fade-up stagger-1">
                <!-- Background abstract shapes -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>

                <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary">
                                <BarChart3 class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Insights</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                            Financial <span class="text-primary">Analytics</span>
                        </h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                            This year you've earned <span class="text-[--fin-gain] font-bold">{{ formatCurrency(totalYearIncome) }}</span> and spent <span class="text-[--fin-loss] font-bold">{{ formatCurrency(totalYearExpenses) }}</span>, resulting in a net profit of <span :class="['font-bold', totalYearNet >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ formatCurrency(totalYearNet) }}</span>.
                        </p>
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-3">
                        <Link :href="yearly().url" class="group flex items-center gap-2 rounded-xl bg-card border border-border/50 px-4 py-2 text-xs font-bold text-foreground transition-all hover:bg-muted active:scale-[0.98] shadow-sm">
                            Check Yearly Report
                            <ArrowRight class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>
            </div>
            <!-- Best/Worst Month Callout -->
            <div v-if="bestMonth || worstMonth" class="grid grid-cols-2 gap-3 animate-fade-up stagger-5">
                <div v-if="bestMonth" class="surface-card premium-hover p-4 lg:p-5 flex items-center gap-4">
                    <div class="p-2.5 rounded-xl bg-[--fin-gain]/10 shrink-0">
                        <TrendingUp class="h-5 w-5 text-[--fin-gain]" />
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Best Month</p>
                        <p class="text-lg font-bold text-foreground font-sans mt-0.5">{{ bestMonth.month }}</p>
                        <p class="text-sm font-mono text-[--fin-gain]">+{{ formatCurrency(bestMonth.net) }}</p>
                    </div>
                </div>
                <div v-if="worstMonth" class="surface-card premium-hover p-4 lg:p-5 flex items-center gap-4">
                    <div class="p-2.5 rounded-xl bg-[--fin-loss]/10 shrink-0">
                        <TrendingDown class="h-5 w-5 text-[--fin-loss]" />
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Worst Month</p>
                        <p class="text-lg font-bold text-foreground font-sans mt-0.5">{{ worstMonth.month }}</p>
                        <p class="text-sm font-mono" :class="worstMonth.net >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">
                            {{ worstMonth.net >= 0 ? '+' : '' }}{{ formatCurrency(worstMonth.net) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Income vs Expenses Trend -->
            <div class="surface-card premium-hover p-5 lg:p-6 animate-fade-up stagger-5">
                <div class="flex items-center gap-2 mb-1">
                    <div class="p-1.5 rounded-lg bg-primary/8">
                        <TrendingUp class="h-4 w-4 text-primary" />
                    </div>
                    <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Income vs Expenses Trend</h2>
                </div>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans ml-9">Monthly comparison of income and expenses</p>

                <div v-if="props.trendData && props.trendData.length > 0" class="relative mt-6 h-[300px] sm:h-[400px] w-full overflow-hidden">
                    <LineChart
                        :key="JSON.stringify(incomeExpensesChart)"
                        :data="incomeExpensesChart"
                        :options="lineChartOptions"
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="mt-6 flex h-96 flex-col items-center justify-center">
                    <TrendingUp class="h-12 w-12 text-muted-foreground/20 mb-3" />
                    <p class="text-sm text-muted-foreground font-sans">No trend data available yet</p>
                    <p class="text-[11px] text-muted-foreground/70 font-sans mt-1">Start adding income and expenses to see trends</p>
                </div>
            </div>

            <!-- Monthly Net Income -->
            <div class="surface-card premium-hover p-5 lg:p-6 animate-fade-up stagger-5">
                <div class="flex items-center gap-2 mb-1">
                    <div class="p-1.5 rounded-lg bg-primary/8">
                        <BarChart3 class="h-4 w-4 text-primary" />
                    </div>
                    <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Monthly Net Income</h2>
                </div>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans ml-9">Your earnings after expenses each month</p>

                <div v-if="props.trendData && props.trendData.length > 0" class="relative mt-6 h-[300px] sm:h-[400px] w-full overflow-hidden">
                    <BarChart
                        :key="JSON.stringify(netIncomeChart)"
                        :data="netIncomeChart"
                        :options="barChartOptions"
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="mt-6 flex h-96 flex-col items-center justify-center">
                    <BarChart3 class="h-12 w-12 text-muted-foreground/20 mb-3" />
                    <p class="text-sm text-muted-foreground font-sans">No data available</p>
                </div>
            </div>

            <!-- Expenses by Category -->
            <div class="grid gap-6 md:grid-cols-2 animate-fade-up stagger-5">
                <!-- Doughnut Chart -->
                <div class="surface-card premium-hover p-5 lg:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="p-1.5 rounded-lg bg-primary/8">
                                    <PieChart class="h-4 w-4 text-primary" />
                                </div>
                                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expenses by Category</h2>
                            </div>
                            <p class="mt-0.5 text-[11px] text-muted-foreground font-sans ml-9">Breakdown of your spending patterns</p>
                        </div>
                    </div>

                    <div v-if="props.categoryData && props.categoryData.length > 0 && props.categoryData[0].amount > 0" class="relative mt-6 h-[250px] sm:h-[300px] w-full overflow-hidden">
                        <DoughnutChart
                            :key="JSON.stringify(categoryChart)"
                            :data="categoryChart"
                            :options="doughnutChartOptions"
                            class="w-full h-full"
                        />
                    </div>
                    <div v-else class="mt-6 flex h-72 flex-col items-center justify-center">
                        <PieChart class="h-12 w-12 text-muted-foreground/20 mb-3" />
                        <p class="text-sm text-muted-foreground font-sans">No category data available</p>
                    </div>
                </div>

                <!-- Category Breakdown Bars -->
                <div class="surface-card p-5 lg:p-6">
                    <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Category Ranking</h2>
                    <p class="mt-0.5 text-[11px] text-muted-foreground font-sans mb-6">Top spending categories this year</p>

                    <div v-if="props.categoryData && props.categoryData.length > 0 && props.categoryData[0].amount > 0" class="space-y-4">
                        <div v-for="(cat, idx) in props.categoryData" :key="cat.name" class="group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                        :style="{ backgroundColor: categoryBarColors[idx % categoryBarColors.length] }"></span>
                                    <span class="text-sm font-semibold text-foreground font-sans">{{ cat.name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-medium text-muted-foreground font-sans">{{ cat.percentage }}%</span>
                                    <span class="text-sm font-bold font-mono text-foreground">{{ formatCurrency(cat.amount) }}</span>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-muted/40 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full animate-bar-fill origin-left"
                                    :style="{
                                        width: cat.percentage + '%',
                                        backgroundColor: categoryBarColors[idx % categoryBarColors.length],
                                        animationDelay: (idx * 120) + 'ms'
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="flex h-72 flex-col items-center justify-center">
                        <PieChart class="h-12 w-12 text-muted-foreground/20 mb-3" />
                        <p class="text-sm text-muted-foreground font-sans">No category data yet</p>
                    </div>

                    <!-- Top category callout -->
                    <div v-if="topCategory && topCategory.amount > 0" class="mt-6 p-3 rounded-xl bg-muted/30 border border-border/30">
                        <p class="text-[11px] text-muted-foreground font-sans">Top Category</p>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm font-bold text-foreground font-sans">{{ topCategory.name }}</span>
                            <span class="text-lg font-bold font-mono text-foreground">{{ formatCurrency(topCategory.amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
