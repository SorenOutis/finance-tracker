<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Calendar, TrendingUp, TrendingDown, DollarSign, ArrowUpRight, ArrowDownRight } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { useOnboarding } from '@/composables/useOnboarding';
import OnboardingTour from '@/components/OnboardingTour.vue';
import { Bar as BarChart } from 'vue-chartjs';
import { useChartDefaults } from '@/composables/useChartDefaults';
import { usePageLoading } from '@/composables/usePageLoading';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { yearly } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';

ChartJS.register(...registerables);

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

const { isLoading } = usePageLoading(500);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Yearly',
        href: yearly(),
    },
];

const { chartColors, barChartOptions } = useChartDefaults();

const getStatIcon = (label: string) => {
    switch (label.toLowerCase()) {
        case 'year income': return TrendingUp;
        case 'year expenses': return TrendingDown;
        case 'year net': return DollarSign;
        default: return DollarSign;
    }
};

const getStatTint = (label: string, value: number) => {
    switch (label.toLowerCase()) {
        case 'year income': return 'stat-tint-income';
        case 'year expenses': return 'stat-tint-expense';
        case 'year net': return value >= 0 ? 'stat-tint-positive' : 'stat-tint-negative';
        default: return '';
    }
};

const getStatColorClass = (label: string, value: number) => {
    if (label.toLowerCase() === 'year net') {
        return value >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]';
    }
    return 'text-foreground';
};

const displayStats = computed(() =>
    props.yearlyStats.map(stat => ({
        label: stat.label,
        value: formatCurrency(stat.value),
        rawValue: stat.value,
        icon: getStatIcon(stat.label),
        tint: getStatTint(stat.label, stat.value),
        colorClass: getStatColorClass(stat.label, stat.value),
    }))
);

// Best/worst performing months
const bestMonth = computed(() => {
    const withData = props.monthlyData.filter(m => m.income > 0 || m.expenses > 0);
    if (withData.length === 0) return null;
    return withData.reduce((best, m) => m.net > best.net ? m : best, withData[0]);
});

const worstMonth = computed(() => {
    const withData = props.monthlyData.filter(m => m.income > 0 || m.expenses > 0);
    if (withData.length === 0) return null;
    return withData.reduce((worst, m) => m.net < worst.net ? m : worst, withData[0]);
});

// Monthly chart data
const monthlyChart = computed(() => {
    return {
        labels: props.monthlyData.map(m => m.month.substring(0, 3)),
        datasets: [
            {
                label: 'Income',
                data: props.monthlyData.map(m => m.income),
                backgroundColor: chartColors.gain,
                borderRadius: 4,
                borderSkipped: false,
            },
            {
                label: 'Expenses',
                data: props.monthlyData.map(m => m.expenses),
                backgroundColor: chartColors.loss,
                borderRadius: 4,
                borderSkipped: false,
            }
        ]
    };
});

// Max value for the bar scale in table
const maxAmount = computed(() => {
    let max = 0;
    for (const m of props.monthlyData) {
        max = Math.max(max, m.income, m.expenses);
    }
    return max || 1;
});

// Onboarding
const page = usePage();
const seenTour = useStorage('seen-tour-yearly', false, sessionStorage);
const tour = useOnboarding([
    { title: 'Yearly Overview', description: 'View your complete annual financial summary with income, expenses, and net totals.', targetSelector: '.tour-yearly-hero' },
    { title: 'Monthly Comparison', description: 'Compare income vs expenses across all months with an interactive bar chart.', targetSelector: '.tour-yearly-chart' },
    { title: 'Monthly Breakdown', description: 'Dive into detailed month-by-month data with income, expenses, and net for each period.' },
]);

onMounted(() => {
    if (!seenTour.value && !(page.props.auth?.user as any)?.is_onboarded) {
        setTimeout(() => {
            tour.showOnboarding.value = true;
            setTimeout(tour.updateTooltipPosition, 300);
        }, 500);
    }
});
</script>

<template>
    <Head title="Yearly" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template v-if="isLoading">
            <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 p-4 sm:p-8 pb-24 md:pb-8 animate-pulse">
                <div class="rounded-[24px] bg-card border border-border/50 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <Skeleton class="h-6 w-6 rounded-lg" />
                                <Skeleton class="h-3 w-20" />
                            </div>
                            <Skeleton class="h-7 w-48 mb-2" />
                            <Skeleton class="h-4 w-72" />
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="p-4 lg:p-5 rounded-[20px] border border-border/40 bg-card flex items-center gap-4">
                        <Skeleton class="h-10 w-10 rounded-xl shrink-0" />
                        <div>
                            <Skeleton class="h-3 w-20 mb-1" />
                            <Skeleton class="h-6 w-28 mb-1" />
                            <Skeleton class="h-4 w-20" />
                        </div>
                    </div>
                    <div class="p-4 lg:p-5 rounded-[20px] border border-border/40 bg-card flex items-center gap-4">
                        <Skeleton class="h-10 w-10 rounded-xl shrink-0" />
                        <div>
                            <Skeleton class="h-3 w-20 mb-1" />
                            <Skeleton class="h-6 w-28 mb-1" />
                            <Skeleton class="h-4 w-20" />
                        </div>
                    </div>
                </div>
                <div class="p-5 lg:p-6 rounded-[20px] border border-border/40 bg-card">
                    <Skeleton class="h-5 w-48 mb-1" />
                    <Skeleton class="h-3 w-64 mb-6" />
                    <Skeleton class="h-[350px] w-full rounded-xl" />
                </div>
                <div class="p-5 lg:p-6 rounded-[20px] border border-border/40 bg-card">
                    <Skeleton class="h-5 w-48 mb-1" />
                    <Skeleton class="h-3 w-64 mb-6" />
                    <div class="space-y-3">
                        <div v-for="i in 5" :key="i" class="flex items-center gap-4 py-2 border-b border-border/20">
                            <Skeleton class="h-4 w-24" />
                            <Skeleton class="h-4 w-20 ml-auto" />
                            <Skeleton class="h-4 w-20" />
                            <Skeleton class="h-4 w-20" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header Section (Compact Hero) -->
            <div :class="['relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 mb-1.5 sm:mb-2 animate-fade-up stagger-1 tour-yearly-hero', tour.highlightClass(1)]">
                <!-- Background abstract shapes -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary">
                                <Calendar class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Yearly</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                            Annual <span class="text-primary">Overview</span>
                        </h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                            Full financial summary for <span class="text-foreground font-bold">{{ props.currentYear }}</span>.
                            You've earned <span class="text-[--fin-gain] font-bold">{{ displayStats.find(s => s.label.toLowerCase() === 'year income')?.value }}</span>
                            and spent <span class="text-[--fin-loss] font-bold">{{ displayStats.find(s => s.label.toLowerCase() === 'year expenses')?.value }}</span>
                            with a net of <span :class="['font-bold', (displayStats.find(s => s.label.toLowerCase() === 'year net')?.rawValue ?? 0) >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">
                                {{ displayStats.find(s => s.label.toLowerCase() === 'year net')?.value }}
                            </span>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Best/Worst Month -->
            <div v-if="bestMonth || worstMonth" class="grid gap-4 md:grid-cols-2 animate-fade-up stagger-4">
                <div v-if="bestMonth" class="surface-card p-4 lg:p-5 flex items-center gap-4">
                    <div class="p-2.5 rounded-xl bg-[--fin-gain]/10 shrink-0">
                        <ArrowUpRight class="h-5 w-5 text-[--fin-gain]" />
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Best Month</p>
                        <p class="text-lg font-bold text-foreground font-sans mt-0.5">{{ bestMonth.month }}</p>
                        <p class="text-sm font-mono text-[--fin-gain]">+{{ formatCurrency(bestMonth.net) }} net</p>
                    </div>
                </div>
                <div v-if="worstMonth" class="surface-card p-4 lg:p-5 flex items-center gap-4">
                    <div class="p-2.5 rounded-xl bg-[--fin-loss]/10 shrink-0">
                        <ArrowDownRight class="h-5 w-5 text-[--fin-loss]" />
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Worst Month</p>
                        <p class="text-lg font-bold text-foreground font-sans mt-0.5">{{ worstMonth.month }}</p>
                        <p class="text-sm font-mono" :class="worstMonth.net >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">
                            {{ worstMonth.net >= 0 ? '+' : '' }}{{ formatCurrency(worstMonth.net) }} net
                        </p>
                    </div>
                </div>
            </div>

            <!-- Monthly Bar Chart -->
            <div :class="['surface-card p-5 lg:p-6 animate-fade-up stagger-5 tour-yearly-chart', tour.highlightClass(2)]">
                <div class="flex items-center gap-2 mb-1">
                    <div class="p-1.5 rounded-lg bg-primary/8">
                        <Calendar class="h-4 w-4 text-primary" />
                    </div>
                    <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Monthly Comparison</h2>
                </div>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans ml-9">Income vs expenses for each month in {{ props.currentYear }}</p>

                <div v-if="props.monthlyData.some(m => m.income > 0 || m.expenses > 0)" class="relative mt-6" style="height: 350px; position: relative;">
                    <BarChart
                        :key="JSON.stringify(monthlyChart)"
                        :data="monthlyChart"
                        :options="barChartOptions"
                        style="position: relative; height: 100%;"
                    />
                </div>
                <div v-else class="mt-6 flex h-80 flex-col items-center justify-center">
                    <Calendar class="h-12 w-12 text-muted-foreground/20 mb-3" />
                    <p class="text-sm text-muted-foreground font-sans">No data available for {{ props.currentYear }}</p>
                </div>
            </div>

            <!-- Monthly Breakdown Table -->
            <div class="surface-card p-5 lg:p-6 animate-fade-up stagger-5">
                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Monthly Breakdown</h2>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans mb-6">Detailed income, expenses, and net income for each month in {{ props.currentYear }}</p>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border/50">
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Month</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Income</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Expenses</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans">Net</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-muted-foreground uppercase tracking-[0.08em] font-sans hidden md:table-cell w-48">Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in props.monthlyData"
                                :key="index"
                                :class="[
                                    'border-b border-border/20 transition-all duration-200 hover:bg-accent/20',
                                    row.income === 0 && row.expenses === 0 ? 'opacity-40' : ''
                                ]"
                            >
                                <td class="px-4 py-3.5 text-sm font-semibold text-foreground font-sans">{{ row.month }}</td>
                                <td class="px-4 py-3.5 text-right text-sm font-mono text-[--fin-gain]">{{ formatCurrency(row.income) }}</td>
                                <td class="px-4 py-3.5 text-right text-sm font-mono text-[--fin-loss]">{{ formatCurrency(row.expenses) }}</td>
                                <td :class="['px-4 py-3.5 text-right text-sm font-mono font-bold', row.net >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">
                                    {{ row.net >= 0 ? '+' : '' }}{{ formatCurrency(row.net) }}
                                </td>
                                <td class="px-4 py-3.5 hidden md:table-cell">
                                    <div v-if="row.income > 0 || row.expenses > 0" class="flex items-center gap-1">
                                        <div class="h-1.5 rounded-full bg-[--fin-gain]" :style="{ width: (row.income / maxAmount * 100) + '%', minWidth: '2px' }"></div>
                                        <div class="h-1.5 rounded-full bg-[--fin-loss]" :style="{ width: (row.expenses / maxAmount * 100) + '%', minWidth: '2px' }"></div>
                                    </div>
                                    <div v-else class="h-1.5 w-full rounded-full bg-muted/30"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </template>
    </AppLayout>

    <OnboardingTour
        :show="tour.showOnboarding.value"
        :step="tour.onboardingStep.value"
        :total="tour.totalSteps"
        :current-step="tour.currentStep.value"
        :tooltip-style="tour.tooltipStyle.value"
        :tooltip-side="tour.tooltipSide.value"
        @next="tour.nextStep()"
        @prev="tour.prevStep()"
        @close="tour.completeOnboarding()"
    />
</template>
