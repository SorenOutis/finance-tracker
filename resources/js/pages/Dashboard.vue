<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, watch, onMounted } from 'vue';
import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import { Wallet, TrendingDown, TrendingUp, DollarSign, PieChart, Plus, ArrowUpRight, ArrowDownRight, Minus } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { useChartDefaults } from '@/composables/useChartDefaults';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Line as LineChart } from 'vue-chartjs';

ChartJS.register(...registerables);

interface Entry {
    id: number;
    amount: number;
    date: string;
    category?: string;
    description?: string;
}

interface User {
    name: string;
    email: string;
}

interface DashboardData {
    totalIncome: number;
    totalExpense: number;
    netIncome: number;
    netWorth: number;
    prevMonthIncome: number;
    prevMonthExpense: number;
}

interface DailySpendingItem {
    day: number;
    amount: number;
}

interface CategoryBreakdownItem {
    category: string;
    amount: number;
    percentage: number;
}

interface Props {
    user: User;
    dashboard: DashboardData;
    incomeEntries: Entry[];
    expenseEntries: Entry[];
    dailySpending: DailySpendingItem[];
    expensesByCategory: CategoryBreakdownItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const { chartColors, isDark } = useChartDefaults();

const getYYYYMMDDFromDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getInitialDate = (): string => {
    const params = new URLSearchParams(window.location.search);
    const dateParam = params.get('date');
    return dateParam || getYYYYMMDDFromDate(new Date());
};

const selectedDate = ref(getInitialDate());
const currentMonth = computed(() => new Date(selectedDate.value));

const formatMonthYear = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const formatYearMonth = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${year}-${month}`;
};

// Month-over-month change calculations
const incomeChange = computed(() => {
    if (props.dashboard.prevMonthIncome === 0) return null;
    return ((props.dashboard.totalIncome - props.dashboard.prevMonthIncome) / props.dashboard.prevMonthIncome) * 100;
});

const expenseChange = computed(() => {
    if (props.dashboard.prevMonthExpense === 0) return null;
    return ((props.dashboard.totalExpense - props.dashboard.prevMonthExpense) / props.dashboard.prevMonthExpense) * 100;
});

const getStatTint = (label: string) => {
    switch (label) {
        case 'Total Income': return 'stat-tint-income';
        case 'Total Expenses': return 'stat-tint-expense';
        case 'Net Income': return props.dashboard.netIncome >= 0 ? 'stat-tint-positive' : 'stat-tint-negative';
        case 'Net Worth': return 'stat-tint-accent';
        default: return '';
    }
};

const getStatIcon = (label: string) => {
    switch (label) {
        case 'Total Income': return Wallet;
        case 'Total Expenses': return TrendingDown;
        case 'Net Income': return DollarSign;
        case 'Net Worth': return PieChart;
        default: return DollarSign;
    }
};

const getStatChange = (label: string) => {
    switch (label) {
        case 'Total Income': return incomeChange.value;
        case 'Total Expenses': return expenseChange.value;
        default: return null;
    }
};

const savingsRate = computed(() => {
    if (props.dashboard.totalIncome === 0) return 0;
    return ((props.dashboard.totalIncome - props.dashboard.totalExpense) / props.dashboard.totalIncome) * 100;
});

const displayStats = computed(() => [
    {
        label: 'Total Income',
        value: formatCurrency(props.dashboard.totalIncome),
        icon: getStatIcon('Total Income'),
        tint: getStatTint('Total Income'),
        change: getStatChange('Total Income'),
    },
    {
        label: 'Total Expenses',
        value: formatCurrency(props.dashboard.totalExpense),
        icon: getStatIcon('Total Expenses'),
        tint: getStatTint('Total Expenses'),
        change: getStatChange('Total Expenses'),
    },
    {
        label: 'Net Income',
        value: formatCurrency(props.dashboard.netIncome),
        icon: getStatIcon('Net Income'),
        tint: getStatTint('Net Income'),
        change: null,
    },
    {
        label: 'Net Worth',
        value: formatCurrency(props.dashboard.netWorth),
        icon: getStatIcon('Net Worth'),
        tint: getStatTint('Net Worth'),
        change: null,
    }
]);

// Animated savings ring
const animatedSavingsRate = ref(0);
const ringRadius = 54;
const ringCircumference = 2 * Math.PI * ringRadius;
const ringDashOffset = computed(() => {
    const clamped = Math.max(0, Math.min(100, animatedSavingsRate.value));
    return ringCircumference - (clamped / 100) * ringCircumference;
});

onMounted(() => {
    // Animate savings rate counter
    const target = Math.max(0, Math.min(100, savingsRate.value));
    const duration = 1200;
    const startTime = performance.now();

    const animate = (currentTime: number) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        animatedSavingsRate.value = eased * target;

        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    requestAnimationFrame(animate);
});

// Daily spending sparkline chart
const sparklineData = computed(() => {
    if (!props.dailySpending || props.dailySpending.length === 0) {
        return { labels: [], datasets: [] };
    }

    return {
        labels: props.dailySpending.map(d => d.day.toString()),
        datasets: [{
            data: props.dailySpending.map(d => d.amount),
            borderColor: chartColors.loss,
            backgroundColor: chartColors.lossMuted,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: chartColors.loss,
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
        }]
    };
});

const sparklineOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: isDark.value ? 'oklch(0.145 0 0)' : 'oklch(0.205 0 0)',
            titleColor: 'oklch(0.985 0 0)',
            bodyColor: 'oklch(0.985 0 0)',
            borderColor: isDark.value ? 'oklch(0.269 0 0)' : 'oklch(0.922 0 0)',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 10,
            displayColors: false,
            callbacks: {
                title: (items: any[]) => `Day ${items[0].label}`,
                label: (item: any) => `₱${Number(item.raw).toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
            },
        },
    },
    scales: {
        x: { display: false },
        y: {
            display: false,
            beginAtZero: true,
        },
    },
    interaction: {
        mode: 'index' as const,
        intersect: false,
    },
    animation: {
        duration: 1000,
        easing: 'easeInOutQuart' as const,
    },
}));

// Category colors for breakdown bars
const categoryBarColors = [
    chartColors.teal,
    chartColors.azure,
    chartColors.amber,
    chartColors.slate,
    chartColors.loss,
];

const getCategoryColor = (index: number) => {
    return categoryBarColors[index % categoryBarColors.length];
};

const showIncomeModal = useStorage('dashboard-income-modal', false);
const showExpenseModal = useStorage('dashboard-expense-modal', false);

// Reload page when date changes
watch(selectedDate, () => {
    router.get(
        route('dashboard'),
        { date: selectedDate.value },
        { preserveState: true, preserveScroll: true }
    );
});

const incomeForm = useForm({
    amount: '',
    date: getYYYYMMDDFormat(new Date()),
    category: '',
    description: '',
});

const expenseForm = useForm({
    amount: '',
    date: getYYYYMMDDFormat(new Date()),
    category: '',
    description: '',
});

function getYYYYMMDDFormat(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

const addIncome = () => {
    if (!incomeForm.amount) return;

    incomeForm.post('/incomes', {
        onSuccess: () => {
            incomeForm.reset();
            incomeForm.date = getYYYYMMDDFormat(new Date());
            showIncomeModal.value = false;
        },
    });
};

const addExpense = () => {
    if (!expenseForm.amount) return;

    expenseForm.post('/expenses', {
        onSuccess: () => {
            expenseForm.reset();
            expenseForm.date = getYYYYMMDDFormat(new Date());
            showExpenseModal.value = false;
        },
    });
};

const deleteEntry = (id: number, type: 'income' | 'expense') => {
    router.delete(`/${type}s/${id}`, {
        preserveScroll: true,
    });
};

// Greeting based on time of day
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div class="animate-fade-up stagger-1">
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Finance Tracker</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">{{ greeting }}, {{
                    props.user.name }}. Here's your financial overview for <span class="font-semibold text-foreground">{{ formatMonthYear(currentMonth) }}</span>.</p>
            </div>

            <!-- Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between animate-fade-up stagger-2">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-semibold text-muted-foreground font-sans">Date</label>
                    <input v-model="selectedDate" type="date"
                        class="rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:border-border focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>

                <div class="flex gap-3">
                    <button @click="showIncomeModal = true"
                        class="group flex items-center gap-2 rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-semibold text-foreground shadow-sm transition-all duration-200 hover:border-border hover:bg-accent/50 hover:shadow-md">
                        <Plus class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90" />
                        Add Income
                    </button>
                    <button @click="showExpenseModal = true"
                        class="group flex items-center gap-2 rounded-lg border border-primary bg-primary/8 px-5 py-2.5 text-sm font-bold text-primary hover:bg-primary/12 transition-all duration-200 hover:shadow-md">
                        <Plus class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90" />
                        Add Expense
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:gap-6">
                <div v-for="(stat, idx) in displayStats" :key="stat.label"
                    :class="['group surface-card p-4 lg:p-5 overflow-hidden relative hover:scale-[1.03] transition-all duration-300 hover:shadow-lg cursor-default animate-fade-up', `stagger-${idx + 1}`, stat.tint]">
                    <!-- Watermark icon -->
                    <div class="absolute top-0 right-0 w-24 h-24 opacity-[0.04] pointer-events-none transition-transform duration-500 group-hover:scale-110">
                        <component :is="stat.icon" class="size-24 -translate-y-4 translate-x-4" />
                    </div>
                    <div class="flex items-center justify-between mb-2.5">
                        <p
                            class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">
                            {{ stat.label }}
                        </p>
                        <!-- MoM change badge -->
                        <span v-if="stat.change !== null && stat.change !== undefined"
                            :class="[
                                'inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-[10px] font-bold',
                                stat.label === 'Total Expenses'
                                    ? (stat.change <= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]')
                                    : (stat.change >= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]')
                            ]">
                            <component :is="stat.label === 'Total Expenses'
                                ? (stat.change <= 0 ? ArrowDownRight : ArrowUpRight)
                                : (stat.change >= 0 ? ArrowUpRight : ArrowDownRight)"
                                class="h-3 w-3" />
                            {{ Math.abs(stat.change).toFixed(1) }}%
                        </span>
                    </div>
                    <p :class="[
                        'text-2xl lg:text-3xl font-bold font-mono tracking-tighter leading-none',
                        stat.label === 'Net Income'
                            ? (props.dashboard.netIncome >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]')
                            : 'text-foreground'
                    ]">
                        {{ stat.value }}
                    </p>
                </div>
            </div>

            <!-- Savings Rate Ring + Daily Spending Row -->
            <div class="grid gap-6 md:grid-cols-3 animate-fade-up stagger-5">
                <!-- Savings Rate Ring -->
                <div class="surface-card p-5 lg:p-6 flex flex-col items-center justify-center">
                    <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-4 font-sans">
                        Savings Rate
                    </p>
                    <div class="relative w-32 h-32">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                            <!-- background ring -->
                            <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                class="stroke-border/30" stroke-width="8" />
                            <!-- progress ring -->
                            <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                :stroke="savingsRate >= 0 ? 'oklch(0.76 0.16 162)' : 'oklch(0.62 0.22 18)'"
                                stroke-width="8"
                                stroke-linecap="round"
                                :stroke-dasharray="ringCircumference"
                                :stroke-dashoffset="ringDashOffset"
                                class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold font-mono tracking-tighter"
                                :class="savingsRate >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">
                                {{ animatedSavingsRate.toFixed(0) }}%
                            </span>
                        </div>
                    </div>
                    <p class="mt-3 text-[11px] text-muted-foreground text-center font-sans">
                        {{ savingsRate >= 20 ? 'Great job saving!' : savingsRate >= 0 ? 'Try to save more' : 'Spending exceeds income' }}
                    </p>
                </div>

                <!-- Daily Spending Sparkline -->
                <div class="surface-card p-5 lg:p-6 md:col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Daily Spending</h2>
                        <p class="text-[11px] text-muted-foreground font-sans">{{ formatMonthYear(currentMonth) }}</p>
                    </div>
                    <p class="text-[11px] text-muted-foreground font-sans mb-4">Expense distribution across the month</p>

                    <div v-if="dailySpending && dailySpending.some(d => d.amount > 0)" class="relative" style="height: 160px;">
                        <LineChart
                            :key="JSON.stringify(sparklineData)"
                            :data="sparklineData"
                            :options="sparklineOptions"
                            style="position: relative; height: 100%;"
                        />
                    </div>
                    <div v-else class="flex h-40 items-center justify-center">
                        <div class="text-center">
                            <TrendingDown class="h-8 w-8 text-muted-foreground/30 mx-auto mb-2" />
                            <p class="text-[11px] font-medium text-muted-foreground font-sans">No spending data this month</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Breakdown by Category -->
            <div v-if="expensesByCategory && expensesByCategory.length > 0" class="surface-card p-5 lg:p-6 animate-fade-up stagger-5">
                <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expense Breakdown</h2>
                <p class="mt-0.5 text-[11px] text-muted-foreground font-sans mb-6">Where your money goes this month</p>

                <div class="space-y-4">
                    <div v-for="(cat, idx) in expensesByCategory" :key="cat.category"
                        class="group">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                    :style="{ backgroundColor: getCategoryColor(idx) }"></span>
                                <span class="text-sm font-semibold text-foreground font-sans">{{ cat.category }}</span>
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
                                    backgroundColor: getCategoryColor(idx),
                                    animationDelay: (idx * 100) + 'ms'
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Entries Grid -->
            <div class="grid gap-6 md:grid-cols-2 animate-fade-up stagger-5">
                <!-- Income Section -->
                <div class="surface-card p-5 lg:p-6 relative overflow-hidden">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-[--fin-gain]/10">
                                <Wallet class="h-4 w-4 text-[--fin-gain]" />
                            </div>
                            <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Income</h2>
                        </div>
                        <span class="text-[11px] text-muted-foreground font-sans">
                            {{ formatYearMonth(currentMonth) }}
                        </span>
                    </div>

                    <div v-if="props.incomeEntries.length > 0" class="space-y-0 divide-y divide-border/30">
                        <div v-for="entry in props.incomeEntries" :key="entry.id"
                            class="entry-row group flex items-start justify-between py-3.5 transition-colors first:pt-0">
                            <div class="flex-1">
                                <p class="font-semibold text-[--fin-gain] text-[13px] font-mono">+{{
                                    formatCurrency(entry.amount) }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-muted-foreground font-sans">{{ entry.date }}</span>
                                    <span v-if="entry.category"
                                        class="inline-flex items-center text-[10px] font-semibold text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-full font-sans">{{
                                            entry.category
                                        }}</span>
                                </div>
                                <p v-if="entry.description"
                                    class="mt-1 text-xs leading-relaxed text-muted-foreground font-sans">
                                    {{ entry.description }}
                                </p>
                            </div>
                            <button @click="deleteEntry(entry.id, 'income')"
                                class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                <span class="text-sm">✕</span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="mt-6 flex flex-col items-center justify-center py-8">
                        <Wallet class="h-10 w-10 text-muted-foreground/20 mb-3" />
                        <p class="text-[11px] font-medium text-muted-foreground font-sans">No income entries this month</p>
                        <button @click="showIncomeModal = true" class="mt-3 text-[11px] font-semibold text-primary hover:underline">
                            Add your first income →
                        </button>
                    </div>
                </div>

                <!-- Expenses Section -->
                <div class="surface-card p-5 lg:p-6 relative overflow-hidden">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 rounded-lg bg-[--fin-loss]/10">
                                <TrendingDown class="h-4 w-4 text-[--fin-loss]" />
                            </div>
                            <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expenses</h2>
                        </div>
                        <span class="text-[11px] text-muted-foreground font-sans">
                            {{ formatYearMonth(currentMonth) }}
                        </span>
                    </div>

                    <div v-if="props.expenseEntries.length > 0" class="space-y-0 divide-y divide-border/30">
                        <div v-for="entry in props.expenseEntries" :key="entry.id"
                            class="entry-row group flex items-start justify-between py-3.5 transition-colors first:pt-0">
                            <div class="flex-1">
                                <p class="font-semibold text-[--fin-loss] text-[13px] font-mono">-{{
                                    formatCurrency(entry.amount) }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-muted-foreground font-sans">{{ entry.date }}</span>
                                    <span v-if="entry.category"
                                        class="inline-flex items-center text-[10px] font-semibold text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-full font-sans">{{
                                            entry.category
                                        }}</span>
                                </div>
                                <p v-if="entry.description"
                                    class="mt-1 text-xs leading-relaxed text-muted-foreground font-sans">
                                    {{ entry.description }}
                                </p>
                            </div>
                            <button @click="deleteEntry(entry.id, 'expense')"
                                class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                <span class="text-sm">✕</span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="mt-6 flex flex-col items-center justify-center py-8">
                        <TrendingDown class="h-10 w-10 text-muted-foreground/20 mb-3" />
                        <p class="text-[11px] font-medium text-muted-foreground font-sans">No expense entries this month</p>
                        <button @click="showExpenseModal = true" class="mt-3 text-[11px] font-semibold text-primary hover:underline">
                            Add your first expense →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Income Modal -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showIncomeModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="showIncomeModal = false">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
                appear>
                <div class="w-full max-w-md transform rounded-2xl surface-card p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 rounded-xl bg-[--fin-gain]/10">
                            <Wallet class="h-5 w-5 text-[--fin-gain]" />
                        </div>
                        <h2 class="text-lg font-bold leading-tight">Add Income</h2>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                            <input v-model="incomeForm.amount" type="number" placeholder="0.00"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Date</label>
                            <input v-model="incomeForm.date" type="date"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Category <span
                                    class="font-normal text-muted-foreground/70">(Optional)</span></label>
                            <input v-model="incomeForm.category" type="text" placeholder="Salary, Bonus, etc."
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Description <span
                                    class="font-normal text-muted-foreground/70">(Optional)</span></label>
                            <input v-model="incomeForm.description" type="text" placeholder="Notes"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button @click="showIncomeModal = false"
                            class="flex-1 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-accent/50">
                            Cancel
                        </button>
                        <button @click="addIncome"
                            class="flex-1 rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                            Add Income
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>

    <!-- Expense Modal -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="showExpenseModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="showExpenseModal = false">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 scale-95 translate-y-2"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
                appear>
                <div class="w-full max-w-md transform rounded-2xl surface-card p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 rounded-xl bg-[--fin-loss]/10">
                            <TrendingDown class="h-5 w-5 text-[--fin-loss]" />
                        </div>
                        <h2 class="text-lg font-bold leading-tight">Add Expense</h2>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                            <input v-model="expenseForm.amount" type="number" placeholder="0.00"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Date</label>
                            <input v-model="expenseForm.date" type="date"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Category <span
                                    class="font-normal text-muted-foreground/70">(Optional)</span></label>
                            <input v-model="expenseForm.category" type="text" placeholder="Food, Transport, Utilities, etc."
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Description <span
                                    class="font-normal text-muted-foreground/70">(Optional)</span></label>
                            <input v-model="expenseForm.description" type="text" placeholder="Notes"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button @click="showExpenseModal = false"
                            class="flex-1 rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-accent/50">
                            Cancel
                        </button>
                        <button @click="addExpense"
                            class="flex-1 rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                            Add Expense
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
