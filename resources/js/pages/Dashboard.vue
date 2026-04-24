<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, watch, onMounted } from 'vue';
import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import {
    Wallet, TrendingDown, TrendingUp, DollarSign, PieChart, Plus, ArrowUpRight, ArrowDownRight,
    Target, CalendarClock, Download, Flame, Activity, Clock, Zap, AlertCircle, Trash2, X,
    CreditCard, Smartphone, Landmark, Coins, Bitcoin, MoreHorizontal
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { useChartDefaults } from '@/composables/useChartDefaults';
import { useNumberAnimation } from '@/composables/useNumberAnimation';
import gsap from 'gsap';
import { Chart as ChartJS, registerables } from 'chart.js';
import { Line as LineChart } from 'vue-chartjs';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/components/ui/dialog';

ChartJS.register(...registerables);

interface Entry { id: number; amount: number; date: string; category?: string; description?: string; }
interface User { name: string; email: string; }
interface DashboardData {
    totalIncome: number; totalExpense: number; netIncome: number; netWorth: number;
    prevMonthIncome: number; prevMonthExpense: number;
}
interface DailySpendingItem { day: number; amount: number; }
interface CategoryBreakdownItem { category: string; amount: number; percentage: number; }
interface TransactionStats { total: number; incomeCount: number; expenseCount: number; avgExpense: number; daysNoSpending: number; }
interface ActivityItem { id: number; type: string; amount: number; category?: string; description?: string; date: string; timestamp: number; }
interface YtdData { income: number; expense: number; net: number; dayOfYear: number; daysInYear: number; }
interface GoalItem { id: number; name: string; target_amount: number; current_amount: number; deadline?: string; color: string; }
interface RecurringItem { id: number; name: string; amount: number; type: string; category?: string; frequency: string; next_due_date: string; }
interface BillItem { id: number; name: string; amount: number; next_due_date: string; days_until: number; frequency: string; }
interface AccountItem { id: number; name: string; type: string; color: string | null; balance: number; last4: string | null; holder_name: string | null; month_income: number; month_expense: number; }

interface Props {
    user: User; dashboard: DashboardData; incomeEntries: Entry[]; expenseEntries: Entry[];
    dailySpending: DailySpendingItem[]; expensesByCategory: CategoryBreakdownItem[];
    topExpense: { amount: number; category: string; description?: string; date: string } | null;
    transactionStats: TransactionStats; recentActivity: ActivityItem[]; savingsStreak: number;
    ytd: YtdData; budget: { monthly_limit: number } | null; financialGoals: GoalItem[];
    recurringTransactions: RecurringItem[]; upcomingBills: BillItem[];
    accounts: AccountItem[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard() }];
const { chartColors, isDark } = useChartDefaults();

// Date handling
const getYYYYMMDD = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
const getInitialDate = () => new URLSearchParams(window.location.search).get('date') || getYYYYMMDD(new Date());
const selectedDate = ref(getInitialDate());
const currentMonth = computed(() => new Date(selectedDate.value));
const fmtMonthYear = (d: Date) => d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
const fmtYearMonth = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;

watch(selectedDate, () => { router.get(dashboard(), { date: selectedDate.value }, { preserveState: true, preserveScroll: true }); });

// MoM changes
const incomeChange = computed(() => props.dashboard.prevMonthIncome === 0 ? null : ((props.dashboard.totalIncome - props.dashboard.prevMonthIncome) / props.dashboard.prevMonthIncome) * 100);
const expenseChange = computed(() => props.dashboard.prevMonthExpense === 0 ? null : ((props.dashboard.totalExpense - props.dashboard.prevMonthExpense) / props.dashboard.prevMonthExpense) * 100);

// Financial health score (0-100)
const healthScore = computed(() => {
    let score = 50;
    const savingsRate = props.dashboard.totalIncome > 0 ? (props.dashboard.netIncome / props.dashboard.totalIncome) * 100 : 0;
    if (savingsRate >= 20) score += 25; else if (savingsRate >= 10) score += 15; else if (savingsRate > 0) score += 5; else score -= 15;
    if (props.savingsStreak >= 3) score += 15; else if (props.savingsStreak >= 1) score += 5;
    if (props.budget && props.dashboard.totalExpense <= props.budget.monthly_limit) score += 10;
    return Math.max(0, Math.min(100, score));
});
const healthLabel = computed(() => { if (healthScore.value >= 80) return 'Excellent'; if (healthScore.value >= 60) return 'Good'; if (healthScore.value >= 40) return 'Fair'; return 'Needs Work'; });
const healthColor = computed(() => { if (healthScore.value >= 80) return 'oklch(0.76 0.16 162)'; if (healthScore.value >= 60) return 'oklch(0.68 0.14 245)'; if (healthScore.value >= 40) return 'oklch(0.76 0.14 75)'; return 'oklch(0.62 0.22 18)'; });

const savingsRate = computed(() => props.dashboard.totalIncome === 0 ? 0 : ((props.dashboard.totalIncome - props.dashboard.totalExpense) / props.dashboard.totalIncome) * 100);

const animatedIncome = useNumberAnimation(() => props.dashboard.totalIncome);
const animatedExpense = useNumberAnimation(() => props.dashboard.totalExpense);
const animatedNetIncome = useNumberAnimation(() => props.dashboard.netIncome);
const animatedNetWorth = useNumberAnimation(() => props.dashboard.netWorth);

const displayStats = computed(() => [
    { label: 'Total Income', value: formatCurrency(animatedIncome.value), icon: Wallet, tint: 'stat-tint-income', change: incomeChange.value, changeInverted: false },
    { label: 'Total Expenses', value: formatCurrency(animatedExpense.value), icon: TrendingDown, tint: 'stat-tint-expense', change: expenseChange.value, changeInverted: true },
    { label: 'Net Income', value: formatCurrency(animatedNetIncome.value), icon: DollarSign, tint: props.dashboard.netIncome >= 0 ? 'stat-tint-positive' : 'stat-tint-negative', change: null, changeInverted: false, colorClass: props.dashboard.netIncome >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]' },
    { label: 'Net Worth', value: formatCurrency(animatedNetWorth.value), icon: PieChart, tint: 'stat-tint-accent', change: null, changeInverted: false },
]);

// Animated savings ring
const animatedSR = ref(0);
const R = 54; const C = 2 * Math.PI * R;
const ringOffset = computed(() => C - (Math.max(0, Math.min(100, animatedSR.value)) / 100) * C);
// Animated health ring
const animatedHS = ref(0);
const healthOffset = computed(() => C - (Math.max(0, Math.min(100, animatedHS.value)) / 100) * C);

onMounted(() => {
    // Animate rings using GSAP
    gsap.to(animatedSR, {
        value: Math.max(0, Math.min(100, savingsRate.value)),
        duration: 1.5,
        ease: 'expo.out',
        delay: 0.2
    });

    gsap.to(animatedHS, {
        value: healthScore.value,
        duration: 1.5,
        ease: 'expo.out',
        delay: 0.4
    });
});

// Sparkline
const sparklineData = computed(() => {
    if (!props.dailySpending?.length) return { labels: [], datasets: [] };
    return { labels: props.dailySpending.map(d => d.day.toString()), datasets: [{ data: props.dailySpending.map(d => d.amount), borderColor: chartColors.loss, backgroundColor: chartColors.lossMuted, borderWidth: 2, tension: 0.4, fill: true, pointRadius: 0, pointHoverRadius: 5, pointHoverBackgroundColor: chartColors.loss, pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2 }] };
});
const sparkOpts = computed(() => ({ responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: isDark.value ? 'oklch(0.145 0 0)' : 'oklch(0.205 0 0)', titleColor: 'oklch(0.985 0 0)', bodyColor: 'oklch(0.985 0 0)', borderColor: isDark.value ? 'oklch(0.269 0 0)' : 'oklch(0.922 0 0)', borderWidth: 1, cornerRadius: 8, padding: 10, displayColors: false, callbacks: { title: (i: any[]) => `Day ${i[0].label}`, label: (i: any) => `₱${Number(i.raw).toLocaleString('en-US', { minimumFractionDigits: 2 })}` } } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } }, interaction: { mode: 'index' as const, intersect: false }, animation: { duration: 1000, easing: 'easeInOutQuart' as const } }));

const catColors = [chartColors.teal, chartColors.azure, chartColors.amber, chartColors.slate, chartColors.loss];

// Modals
const showIncomeModal = useStorage('dashboard-income-modal', false);
const showExpenseModal = useStorage('dashboard-expense-modal', false);
const showBudgetModal = ref(false);
const showGoalModal = ref(false);
const showWelcomeModal = useStorage('show-welcome-modal-v1', true, sessionStorage);
const showRecurringModal = ref(false);
const showQuickAddModal = ref(false);

// Forms
const incomeForm = useForm({ amount: '', date: getYYYYMMDD(new Date()), category: '', description: '', account_id: '' as string | number });
const expenseForm = useForm({ amount: '', date: getYYYYMMDD(new Date()), category: '', description: '', account_id: '' as string | number });

// Wallet card helpers
const walletDefColors: Record<string, string> = { gcash: '#007DFE', maya: '#00B274', paypal: '#003087', bank: '#1E3A5F', cash: '#6B7280', credit_card: '#18181B', crypto: '#F7931A', other: '#6366F1' };
const walletGradient = (type: string, color: string | null) => { const b = color || walletDefColors[type] || '#6366F1'; return `linear-gradient(135deg, ${b} 0%, ${b}CC 50%, ${b}99 100%)`; };
const walletIcon = (type: string) => ({ gcash: Smartphone, maya: Smartphone, paypal: Wallet, bank: Landmark, cash: Coins, credit_card: CreditCard, crypto: Bitcoin }[type] || MoreHorizontal);
const walletLabel = (type: string) => ({ gcash: 'GCash', maya: 'Maya', paypal: 'PayPal', bank: 'Bank', cash: 'Cash', credit_card: 'Credit Card', crypto: 'Crypto' }[type] || type);
const budgetForm = useForm({ monthly_limit: props.budget?.monthly_limit?.toString() || '' });
const goalForm = useForm({ name: '', target_amount: '', current_amount: '', deadline: '', color: '#14b8a6' });
const recurringForm = useForm({ name: '', amount: '', type: 'expense' as string, category: '', description: '', frequency: 'monthly' as string, next_due_date: getYYYYMMDD(new Date()) });

const addIncome = () => { if (!incomeForm.amount) return; incomeForm.post('/incomes', { onSuccess: () => { incomeForm.reset(); incomeForm.date = getYYYYMMDD(new Date()); showIncomeModal.value = false; } }); };
const addExpense = () => { if (!expenseForm.amount) return; expenseForm.post('/expenses', { onSuccess: () => { expenseForm.reset(); expenseForm.date = getYYYYMMDD(new Date()); showExpenseModal.value = false; } }); };
const saveBudget = () => { if (!budgetForm.monthly_limit) return; budgetForm.post('/budgets', { onSuccess: () => { showBudgetModal.value = false; } }); };
const addGoal = () => { if (!goalForm.name || !goalForm.target_amount) return; goalForm.post('/financial-goals', { onSuccess: () => { goalForm.reset(); goalForm.color = '#14b8a6'; showGoalModal.value = false; } }); };
const deleteGoal = (id: number) => { router.delete(`/financial-goals/${id}`, { preserveScroll: true }); };
const addRecurring = () => { if (!recurringForm.name || !recurringForm.amount) return; recurringForm.post('/recurring-transactions', { onSuccess: () => { recurringForm.reset(); recurringForm.type = 'expense'; recurringForm.frequency = 'monthly'; recurringForm.next_due_date = getYYYYMMDD(new Date()); showRecurringModal.value = false; } }); };
const deleteRecurring = (id: number) => { router.delete(`/recurring-transactions/${id}`, { preserveScroll: true }); };
const deleteEntry = (id: number, type: 'income' | 'expense') => { router.delete(`/${type}s/${id}`, { preserveScroll: true }); };

const exportCsv = () => { window.location.href = `/export/csv?month=${fmtYearMonth(currentMonth.value)}`; };

const greeting = computed(() => { const h = new Date().getHours(); return h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening'; });
const yearProgress = computed(() => Math.round((props.ytd.dayOfYear / props.ytd.daysInYear) * 100));
const budgetUsedPct = computed(() => props.budget ? Math.min(100, (props.dashboard.totalExpense / props.budget.monthly_limit) * 100) : 0);
</script>

<template>
<Head title="Dashboard" />
<AppLayout :breadcrumbs="breadcrumbs">
<div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">

<!-- Header + Controls -->
<div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between animate-fade-up stagger-1">
    <div>
        <h1 class="text-4xl sm:text-4xl font-extrabold leading-tight tracking-tight text-foreground">Finance Tracker</h1>
        <p class="mt-2 text-sm text-muted-foreground/80 font-medium font-sans">{{ greeting }}, {{ user.name.split(' ')[0] }}. <span class="font-bold text-foreground">{{ fmtMonthYear(currentMonth) }}</span></p>
    </div>
    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
        <div class="relative flex-1 sm:flex-initial">
            <input v-model="selectedDate" type="date" class="w-full sm:w-auto rounded-2xl border border-border/50 bg-card/20 px-4 py-2.5 text-sm font-semibold transition-all hover:bg-card/40 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/50 [&::-webkit-calendar-picker-indicator]:opacity-40" />
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="exportCsv" class="group flex items-center gap-2 rounded-2xl border border-border/50 bg-card/20 px-4 py-2.5 text-sm font-semibold text-muted-foreground transition-all hover:text-foreground hover:bg-card/40">
                <Download class="h-4 w-4 transition-transform group-hover:-translate-y-0.5" /> Export
            </button>
            <button @click="showQuickAddModal = true" class="group flex items-center gap-2 rounded-2xl bg-primary px-4 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" /> Add
            </button>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:gap-4 mt-2">
    <div v-for="(stat, idx) in displayStats" :key="stat.label"
        :class="['group p-4 sm:p-5 rounded-[24px] overflow-hidden relative border border-border/40 bg-card/20 premium-hover cursor-default animate-fade-up', `stagger-${idx+1}`, stat.tint]">
        <div class="absolute top-0 right-0 w-24 h-24 opacity-[0.03] pointer-events-none group-hover:scale-110 transition-transform duration-500">
            <component :is="stat.icon" class="size-24 -translate-y-4 translate-x-4" />
        </div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] sm:text-[11px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">{{ stat.label }}</p>
            <span v-if="stat.change != null" :class="['inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[9px] font-bold',
                stat.changeInverted ? (stat.change <= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]') : (stat.change >= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]')]">
                <component :is="stat.changeInverted ? (stat.change <= 0 ? ArrowDownRight : ArrowUpRight) : (stat.change >= 0 ? ArrowUpRight : ArrowDownRight)" class="h-2.5 w-2.5" />
                {{ Math.abs(stat.change).toFixed(1) }}%
            </span>
        </div>
        <div class="pt-1.5">
            <p :class="['text-2xl sm:text-3xl font-black font-display tracking-tight leading-none', stat.colorClass || 'text-foreground']">{{ stat.value }}</p>
        </div>
    </div>
</div>

<!-- Wallet Cards Strip -->
<div v-if="accounts?.length" class="animate-fade-up stagger-3 mt-1">
    <div class="flex items-center justify-between mb-2">
        <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">My Wallets</p>
        <a href="/wallets" class="text-[10px] font-semibold text-primary hover:underline">View all</a>
    </div>
    <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory pb-2 scrollbar-hide">
        <div v-for="account in accounts" :key="account.id"
            class="group relative flex-shrink-0 w-[220px] sm:w-[260px] snap-start overflow-hidden rounded-[18px] p-4 sm:p-5 text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 cursor-default"
            :style="{ background: walletGradient(account.type, account.color) }">
            <!-- Card pattern -->
            <div class="absolute inset-0 opacity-[0.06] pointer-events-none">
                <div class="absolute top-2 right-2 w-20 h-20 border-2 border-white rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-28 h-28 border-2 border-white rounded-full"></div>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-1.5 mb-3">
                    <div class="p-1 rounded-md bg-white/20"><component :is="walletIcon(account.type)" class="h-3 w-3" /></div>
                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-90">{{ walletLabel(account.type) }}</span>
                </div>
                <p class="text-lg sm:text-xl font-black font-mono tracking-tight leading-none">{{ formatCurrency(account.balance) }}</p>
                <div class="flex items-end justify-between mt-3">
                    <p class="text-[10px] font-semibold opacity-80">{{ account.name }}</p>
                    <p v-if="account.last4" class="text-[10px] font-mono font-bold tracking-widest opacity-70">•••• {{ account.last4 }}</p>
                </div>
                <div class="flex items-center gap-1.5 mt-2.5 pt-2 border-t border-white/15">
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.5 py-0.5 text-[9px] font-bold">
                        <ArrowDownRight class="h-2 w-2" /> {{ formatCurrency(account.month_income) }}
                    </span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.5 py-0.5 text-[9px] font-bold">
                        <ArrowUpRight class="h-2 w-2" /> {{ formatCurrency(account.month_expense) }}
                    </span>
                </div>
            </div>
        </div>
        <!-- Add wallet card -->
        <a href="/wallets" class="flex-shrink-0 w-[120px] snap-start flex flex-col items-center justify-center rounded-[18px] border-2 border-dashed border-border/40 bg-card/10 text-muted-foreground hover:border-primary/50 hover:text-primary transition-all cursor-pointer">
            <Plus class="h-5 w-5 mb-1" />
            <span class="text-[10px] font-bold">Add Wallet</span>
        </a>
    </div>
</div>

<!-- Transaction Chips + Income vs Expense Bar -->
<div class="grid gap-3 md:grid-cols-2 animate-fade-up stagger-5 mt-1">
    <!-- Transaction Stats Chips (Pills layout) -->
    <div class="p-5 rounded-[24px] border border-border/40 bg-card/20 flex flex-wrap items-center justify-center gap-2.5">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-card/40 border border-border/30 px-3.5 py-1.5 text-[12px] font-bold text-muted-foreground transition-all">
            <Activity class="h-3.5 w-3.5" /> {{ transactionStats.total }} transactions
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-card/40 border border-border/30 px-3.5 py-1.5 text-[12px] font-bold text-foreground">
            {{ transactionStats.incomeCount }} income
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-card/40 border border-border/30 px-3.5 py-1.5 text-[12px] font-bold text-foreground">
            {{ transactionStats.expenseCount }} expenses
        </span>
        <span v-if="transactionStats.avgExpense > 0" class="inline-flex items-center gap-1.5 rounded-full bg-card/40 border border-border/30 px-3.5 py-1.5 text-[12px] font-bold text-muted-foreground/80">
            Avg {{ formatCurrency(transactionStats.avgExpense) }}
        </span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-card/40 border border-border/30 px-3.5 py-1.5 text-[12px] font-bold text-muted-foreground/80">
            {{ transactionStats.daysNoSpending }} no-spend days
        </span>
    </div>

    <!-- Income vs Expense Comparison Bar -->
    <div class="surface-card premium-hover p-4">
        <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2 font-sans">Income vs Expenses</p>
        <div class="flex h-3 rounded-full overflow-hidden bg-muted/30">
            <div class="bg-[--fin-gain] transition-all duration-700 rounded-l-full"
                :style="{ width: (props.dashboard.totalIncome + props.dashboard.totalExpense > 0 ? (props.dashboard.totalIncome / (props.dashboard.totalIncome + props.dashboard.totalExpense)) * 100 : 50) + '%' }"></div>
            <div class="bg-[--fin-loss] transition-all duration-700 rounded-r-full flex-1"></div>
        </div>
        <div class="flex justify-between mt-1.5 text-[10px] font-mono font-semibold">
            <span class="text-[--fin-gain]">{{ formatCurrency(animatedIncome) }}</span>
            <span class="text-[--fin-loss]">{{ formatCurrency(animatedExpense) }}</span>
        </div>
    </div>
</div>

<!-- Rings Row: Savings + Health + Year Progress -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 animate-fade-up stagger-5">
    <!-- Savings Rate Ring -->
    <div class="surface-card p-4 flex flex-col items-center justify-center">
        <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-3 font-sans">Savings Rate</p>
        <div class="relative w-24 h-24">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" :r="R" fill="none" class="stroke-border/30" stroke-width="8" />
                <circle cx="60" cy="60" :r="R" fill="none" :stroke="savingsRate >= 0 ? 'oklch(0.76 0.16 162)' : 'oklch(0.62 0.22 18)'" stroke-width="8" stroke-linecap="round" :stroke-dasharray="C" :stroke-dashoffset="ringOffset" class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-lg font-bold font-mono tracking-tighter" :class="savingsRate >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">{{ animatedSR.toFixed(0) }}%</span>
            </div>
        </div>
        <p class="mt-2 text-[10px] text-muted-foreground text-center font-sans">{{ savingsRate >= 20 ? 'Great job!' : savingsRate >= 0 ? 'Room to improve' : 'Over budget' }}</p>
    </div>

    <!-- Health Score Gauge -->
    <div class="surface-card p-4 flex flex-col items-center justify-center">
        <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-3 font-sans">Financial Health</p>
        <div class="relative w-24 h-24">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" :r="R" fill="none" class="stroke-border/30" stroke-width="8" />
                <circle cx="60" cy="60" :r="R" fill="none" :stroke="healthColor" stroke-width="8" stroke-linecap="round" :stroke-dasharray="C" :stroke-dashoffset="healthOffset" class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-lg font-bold font-mono tracking-tighter">{{ animatedHS.toFixed(0) }}</span>
            </div>
        </div>
        <p class="mt-2 text-[10px] font-semibold text-center font-sans" :style="{ color: healthColor }">{{ healthLabel }}</p>
    </div>

    <!-- Year Progress -->
    <div class="surface-card p-4 col-span-2 lg:col-span-1">
        <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2 font-sans">Year Progress</p>
        <div class="w-full h-2 rounded-full bg-muted/30 overflow-hidden mb-3">
            <div class="h-full rounded-full bg-primary transition-all duration-700" :style="{ width: yearProgress + '%' }"></div>
        </div>
        <p class="text-[10px] text-muted-foreground font-sans mb-3">{{ yearProgress }}% of {{ new Date(selectedDate).getFullYear() }} complete</p>
        <div class="space-y-1.5">
            <div class="flex justify-between text-[10px] font-sans">
                <span class="text-muted-foreground">YTD Income</span>
                <span class="font-mono font-semibold text-[--fin-gain]">{{ formatCurrency(ytd.income) }}</span>
            </div>
            <div class="flex justify-between text-[10px] font-sans">
                <span class="text-muted-foreground">YTD Expenses</span>
                <span class="font-mono font-semibold text-[--fin-loss]">{{ formatCurrency(ytd.expense) }}</span>
            </div>
            <div class="flex justify-between text-[10px] font-sans border-t border-border/30 pt-1.5">
                <span class="font-semibold text-foreground">YTD Net</span>
                <span :class="['font-mono font-bold', ytd.net >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ formatCurrency(ytd.net) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Sparkline + Top Expense + Streak -->
<div class="grid gap-3 md:grid-cols-3 animate-fade-up stagger-5">
    <!-- Daily Spending Sparkline -->
    <div class="surface-card p-4 md:col-span-2">
        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Daily Spending</h2>
        <p class="text-[10px] text-muted-foreground font-sans mb-3">{{ fmtMonthYear(currentMonth) }}</p>
        <div v-if="dailySpending?.some(d => d.amount > 0)" class="relative" style="height: 140px;">
            <LineChart :key="JSON.stringify(sparklineData)" :data="sparklineData" :options="sparkOpts" style="position: relative; height: 100%;" />
        </div>
        <div v-else class="flex h-32 items-center justify-center">
            <div class="text-center"><TrendingDown class="h-6 w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[10px] text-muted-foreground font-sans">No spending data</p></div>
        </div>
    </div>

    <!-- Top Expense + Streak -->
    <div class="grid grid-cols-2 gap-3 md:block md:space-y-3">
        <!-- Top Expense Spotlight -->
        <div v-if="topExpense" class="surface-card p-4">
            <div class="flex items-center gap-1.5 mb-2">
                <AlertCircle class="h-3.5 w-3.5 text-[--fin-loss]" />
                <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Biggest Expense</p>
            </div>
            <p class="text-lg font-bold font-mono text-[--fin-loss]">{{ formatCurrency(topExpense.amount) }}</p>
            <p class="text-[11px] font-semibold text-foreground mt-1">{{ topExpense.category }}</p>
            <p v-if="topExpense.description" class="text-[10px] text-muted-foreground mt-0.5">{{ topExpense.description }}</p>
        </div>

        <!-- Spending Streak -->
        <div class="surface-card p-4">
            <div class="flex items-center gap-1.5 mb-2">
                <Flame class="h-3.5 w-3.5 text-amber-500" />
                <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Savings Streak</p>
            </div>
            <p class="text-2xl font-bold font-mono text-foreground">{{ savingsStreak }}</p>
            <p class="text-[10px] text-muted-foreground font-sans">consecutive month{{ savingsStreak !== 1 ? 's' : '' }} saving</p>
        </div>
    </div>
</div>

<!-- Budget Tracker -->
<div v-if="budget" class="surface-card premium-hover p-4 animate-fade-up stagger-5">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-1.5">
            <Target class="h-3.5 w-3.5 text-primary" />
            <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Monthly Budget</p>
        </div>
        <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full', budgetUsedPct > 100 ? 'bg-[--fin-loss]/12 text-[--fin-loss]' : budgetUsedPct > 80 ? 'bg-amber-500/12 text-amber-600' : 'bg-[--fin-gain]/12 text-[--fin-gain]']">
            {{ budgetUsedPct.toFixed(0) }}% used
        </span>
    </div>
    <div class="w-full h-2.5 rounded-full bg-muted/30 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-700" :class="budgetUsedPct > 100 ? 'bg-[--fin-loss]' : budgetUsedPct > 80 ? 'bg-amber-500' : 'bg-[--fin-gain]'" :style="{ width: Math.min(100, budgetUsedPct) + '%' }"></div>
    </div>
    <div class="flex justify-between mt-1.5 text-[10px] font-mono">
        <span class="text-muted-foreground">{{ formatCurrency(animatedExpense) }} spent</span>
        <span class="text-foreground font-semibold">{{ formatCurrency(budget.monthly_limit) }} limit</span>
    </div>
</div>

<!-- Category Breakdown -->
<div v-if="expensesByCategory?.length" class="surface-card premium-hover p-4 animate-fade-up stagger-5">
    <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expense Breakdown</h2>
    <p class="text-[10px] text-muted-foreground font-sans mb-4">Where your money goes</p>
    <div class="space-y-3">
        <div v-for="(cat, idx) in expensesByCategory" :key="cat.category">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: catColors[idx % catColors.length] }"></span><span class="text-xs font-semibold text-foreground font-sans">{{ cat.category }}</span></div>
                <div class="flex items-center gap-2"><span class="text-[10px] text-muted-foreground">{{ cat.percentage }}%</span><span class="text-xs font-bold font-mono text-foreground">{{ formatCurrency(cat.amount) }}</span></div>
            </div>
            <div class="w-full h-1.5 bg-muted/40 rounded-full overflow-hidden"><div class="h-full rounded-full animate-bar-fill origin-left" :style="{ width: cat.percentage + '%', backgroundColor: catColors[idx % catColors.length], animationDelay: (idx * 100) + 'ms' }"></div></div>
        </div>
    </div>
</div>

<!-- Financial Goals -->
<div v-if="financialGoals?.length" class="surface-card p-4 animate-fade-up stagger-5">
    <h2 class="text-sm font-bold text-foreground font-display tracking-tight mb-4">Financial Goals</h2>
    <div class="grid gap-3 md:grid-cols-2">
        <div v-for="goal in financialGoals" :key="goal.id" class="group p-3 rounded-xl bg-muted/20 border border-border/30 relative">
            <button @click="deleteGoal(goal.id)" class="absolute top-2 right-2 p-1 rounded text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100"><X class="h-3 w-3" /></button>
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 shrink-0">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" class="stroke-border/30" stroke-width="8" />
                        <circle cx="60" cy="60" r="50" fill="none" :stroke="goal.color" stroke-width="8" stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 50"
                            :stroke-dashoffset="2 * Math.PI * 50 - (Math.min(1, goal.current_amount / goal.target_amount) * 2 * Math.PI * 50)" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center"><span class="text-[9px] font-bold font-mono">{{ Math.round((goal.current_amount / goal.target_amount) * 100) }}%</span></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-foreground truncate">{{ goal.name }}</p>
                    <p class="text-[10px] font-mono text-muted-foreground">{{ formatCurrency(goal.current_amount) }} / {{ formatCurrency(goal.target_amount) }}</p>
                    <p v-if="goal.deadline" class="text-[9px] text-muted-foreground mt-0.5">Due {{ goal.deadline }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Bills + Recurring -->
<div class="grid grid-cols-2 gap-3 animate-fade-up stagger-5">
    <!-- Upcoming Bills -->
    <div class="surface-card p-4">
        <div class="flex items-center gap-1.5 mb-3">
            <Clock class="h-3.5 w-3.5 text-amber-500" />
            <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Upcoming Bills</h2>
        </div>
        <div v-if="upcomingBills?.length" class="space-y-2">
            <div v-for="bill in upcomingBills" :key="bill.id" class="flex items-center justify-between p-2 rounded-lg bg-muted/20">
                <div>
                    <p class="text-xs font-semibold text-foreground">{{ bill.name }}</p>
                    <p class="text-[10px] text-muted-foreground">{{ bill.days_until === 0 ? 'Due today' : `In ${bill.days_until} day${bill.days_until !== 1 ? 's' : ''}` }}</p>
                </div>
                <span class="text-xs font-bold font-mono text-[--fin-loss]">{{ formatCurrency(bill.amount) }}</span>
            </div>
        </div>
        <div v-else class="flex flex-col items-center py-6"><Clock class="h-6 w-6 text-muted-foreground/20 mb-1" /><p class="text-[10px] text-muted-foreground">No upcoming bills</p></div>
    </div>

    <!-- Recurring Transactions -->
    <div class="surface-card p-4">
        <h2 class="text-sm font-bold text-foreground font-display tracking-tight mb-3">Recurring</h2>
        <div v-if="recurringTransactions?.length" class="space-y-2">
            <div v-for="rt in recurringTransactions" :key="rt.id" class="group flex items-center justify-between p-2 rounded-lg bg-muted/20">
                <div>
                    <p class="text-xs font-semibold text-foreground">{{ rt.name }}</p>
                    <p class="text-[10px] text-muted-foreground capitalize">{{ rt.frequency }} · {{ rt.next_due_date }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span :class="['text-xs font-bold font-mono', rt.type === 'income' ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ rt.type === 'income' ? '+' : '-' }}{{ formatCurrency(rt.amount) }}</span>
                    <button @click="deleteRecurring(rt.id)" class="p-1 rounded text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100"><Trash2 class="h-3 w-3" /></button>
                </div>
            </div>
        </div>
        <div v-else class="flex flex-col items-center py-6"><CalendarClock class="h-6 w-6 text-muted-foreground/20 mb-1" /><p class="text-[10px] text-muted-foreground">No recurring items</p></div>
    </div>
</div>

<!-- Recent Activity Timeline -->
<div v-if="recentActivity?.length" class="surface-card premium-hover p-4 animate-fade-up stagger-5">
    <div class="flex items-center gap-1.5 mb-3"><Activity class="h-3.5 w-3.5 text-primary" /><h2 class="text-sm font-bold text-foreground font-display tracking-tight">Recent Activity</h2></div>
    <div class="space-y-0 divide-y divide-border/20">
        <div v-for="item in recentActivity" :key="`${item.type}-${item.id}`" class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
            <div class="flex items-center gap-2.5">
                <div :class="['w-1.5 h-1.5 rounded-full shrink-0', item.type === 'income' ? 'bg-[--fin-gain]' : 'bg-[--fin-loss]']"></div>
                <div>
                    <p class="text-xs font-semibold text-foreground">{{ item.category || (item.type === 'income' ? 'Income' : 'Expense') }}</p>
                    <p class="text-[10px] text-muted-foreground">{{ item.date }}</p>
                </div>
            </div>
            <span :class="['text-xs font-bold font-mono', item.type === 'income' ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ item.type === 'income' ? '+' : '-' }}{{ formatCurrency(item.amount) }}</span>
        </div>
    </div>
</div>

<!-- Income & Expense Entry Lists -->
<div class="grid grid-cols-2 gap-3 animate-fade-up stagger-5">
    <div class="surface-card premium-hover p-4">
        <div class="mb-3 flex items-center gap-2"><div class="p-1 rounded bg-[--fin-gain]/10"><Wallet class="h-3.5 w-3.5 text-[--fin-gain]" /></div><h2 class="text-sm font-bold text-foreground">Income</h2><span class="text-[10px] text-muted-foreground ml-auto">{{ fmtYearMonth(currentMonth) }}</span></div>
        <div v-if="incomeEntries.length" class="divide-y divide-border/20">
            <TransitionGroup name="list-slide" appear>
                <div v-for="e in incomeEntries" :key="e.id" class="entry-row group flex items-start justify-between py-2.5 first:pt-0">
                    <div><p class="text-xs font-semibold font-mono text-[--fin-gain]">+{{ formatCurrency(e.amount) }}</p><div class="flex gap-1.5 mt-1"><span class="text-[10px] text-muted-foreground">{{ e.date }}</span><span v-if="e.category" class="text-[9px] font-semibold bg-muted/50 px-1.5 py-0.5 rounded-full text-muted-foreground">{{ e.category }}</span></div></div>
                    <button @click="deleteEntry(e.id, 'income')" class="p-1 text-muted-foreground opacity-0 transition-all hover:text-destructive group-hover:opacity-100"><span class="text-xs">✕</span></button>
                </div>
            </TransitionGroup>
        </div>
        <div v-else class="py-6 text-center"><Wallet class="h-6 w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[10px] text-muted-foreground">No income</p></div>
    </div>
    <div class="surface-card premium-hover p-4">
        <div class="mb-3 flex items-center gap-2"><div class="p-1 rounded bg-[--fin-loss]/10"><TrendingDown class="h-3.5 w-3.5 text-[--fin-loss]" /></div><h2 class="text-sm font-bold text-foreground">Expenses</h2><span class="text-[10px] text-muted-foreground ml-auto">{{ fmtYearMonth(currentMonth) }}</span></div>
        <div v-if="expenseEntries.length" class="divide-y divide-border/20">
            <TransitionGroup name="list-slide" appear>
                <div v-for="e in expenseEntries" :key="e.id" class="entry-row group flex items-start justify-between py-2.5 first:pt-0">
                    <div><p class="text-xs font-semibold font-mono text-[--fin-loss]">-{{ formatCurrency(e.amount) }}</p><div class="flex gap-1.5 mt-1"><span class="text-[10px] text-muted-foreground">{{ e.date }}</span><span v-if="e.category" class="text-[9px] font-semibold bg-muted/50 px-1.5 py-0.5 rounded-full text-muted-foreground">{{ e.category }}</span></div></div>
                    <button @click="deleteEntry(e.id, 'expense')" class="p-1 text-muted-foreground opacity-0 transition-all hover:text-destructive group-hover:opacity-100"><span class="text-xs">✕</span></button>
                </div>
            </TransitionGroup>
        </div>
        <div v-else class="py-6 text-center"><TrendingDown class="h-6 w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[10px] text-muted-foreground">No expenses</p></div>
    </div>
</div>

</div>
</AppLayout>

<!-- Welcome Modal -->
<Dialog v-model:open="showWelcomeModal">
    <DialogContent class="sm:max-w-[440px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl animate-in zoom-in-95 duration-300">
            <!-- Background Glow -->
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/10 rounded-full blur-[80px]"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary/10 rounded-full blur-[80px]"></div>

            <div class="relative p-6 sm:p-8 flex flex-col items-center text-center">
                <!-- Mascot Image -->
                <div class="relative mb-4 sm:mb-6 group">
                    <div class="absolute inset-0 bg-primary/20 rounded-full blur-2xl group-hover:bg-primary/30 transition-all duration-500 scale-110"></div>
                    <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-card shadow-xl overflow-hidden bg-muted/30">
                        <img src="/images/welcome_mascot.png" alt="Mascot" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" />
                    </div>
                    <div class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-primary text-primary-foreground p-1.5 sm:p-2 rounded-2xl shadow-lg animate-bounce">
                        <Zap class="w-3 h-3 sm:w-4 sm:h-4" />
                    </div>
                </div>

                <DialogHeader class="space-y-2 sm:space-y-3">
                    <DialogTitle class="text-2xl sm:text-3xl font-black tracking-tight font-display">
                        Welcome Back, <span class="text-primary">{{ user.name.split(' ')[0] }}</span>!
                    </DialogTitle>
                    <DialogDescription class="text-sm sm:text-base text-muted-foreground font-medium leading-relaxed px-2 sm:px-4">
                        We're excited to have you back! Your personal finance hub is ready. Let's make some smart moves today.
                    </DialogDescription>
                </DialogHeader>

                <div class="mt-6 sm:mt-8 grid grid-cols-2 gap-2 sm:gap-3 w-full">
                    <div class="p-3 sm:p-4 rounded-2xl bg-muted/30 border border-border/40 text-left">
                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Net Worth</p>
                        <p class="text-base sm:text-lg font-bold font-mono text-foreground">{{ formatCurrency(props.dashboard.netWorth) }}</p>
                    </div>
                    <div class="p-3 sm:p-4 rounded-2xl bg-muted/30 border border-border/40 text-left">
                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Health Score</p>
                        <p class="text-base sm:text-lg font-bold font-mono" :style="{ color: healthColor }">{{ healthScore }}</p>
                    </div>
                </div>

                <DialogFooter class="mt-6 sm:mt-8 w-full sm:justify-center">
                    <button @click="showWelcomeModal = false" class="w-full sm:w-auto px-8 sm:px-10 py-3 sm:py-4 rounded-[18px] sm:rounded-[20px] bg-primary text-primary-foreground font-bold text-sm sm:text-base shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Get Started
                    </button>
                </DialogFooter>
            </div>
        </div>
    </DialogContent>
</Dialog>

<!-- Add Income Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showIncomeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showIncomeModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-md mx-4 p-6 rounded-2xl">
        <div class="flex items-center gap-2 mb-5"><div class="p-1.5 rounded-lg bg-[--fin-gain]/10"><Wallet class="h-4 w-4 text-[--fin-gain]" /></div><h3 class="text-lg font-bold">Add Income</h3></div>
        <div class="space-y-4">
            <div><label class="block text-sm font-semibold text-muted-foreground">Amount</label><input v-model="incomeForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Date</label><input v-model="incomeForm.date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Category</label><input v-model="incomeForm.category" type="text" placeholder="e.g., Salary" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Description</label><input v-model="incomeForm.description" type="text" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div v-if="accounts?.length"><label class="block text-sm font-semibold text-muted-foreground">Wallet</label><select v-model="incomeForm.account_id" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10"><option value="">No wallet</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }} ({{ walletLabel(a.type) }})</option></select></div>
        </div>
        <div class="flex gap-2 mt-5"><button @click="showIncomeModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="addIncome" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<!-- Add Expense Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showExpenseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showExpenseModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-md mx-4 p-6 rounded-2xl">
        <div class="flex items-center gap-2 mb-5"><div class="p-1.5 rounded-lg bg-[--fin-loss]/10"><TrendingDown class="h-4 w-4 text-[--fin-loss]" /></div><h3 class="text-lg font-bold">Add Expense</h3></div>
        <div class="space-y-4">
            <div><label class="block text-sm font-semibold text-muted-foreground">Amount</label><input v-model="expenseForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Date</label><input v-model="expenseForm.date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Category</label><input v-model="expenseForm.category" type="text" placeholder="e.g., Food" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Description</label><input v-model="expenseForm.description" type="text" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div v-if="accounts?.length"><label class="block text-sm font-semibold text-muted-foreground">Wallet</label><select v-model="expenseForm.account_id" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10"><option value="">No wallet</option><option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.name }} ({{ walletLabel(a.type) }})</option></select></div>
        </div>
        <div class="flex gap-2 mt-5"><button @click="showExpenseModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="addExpense" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<!-- Budget Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showBudgetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showBudgetModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-sm mx-4 p-6 rounded-2xl">
        <div class="flex items-center gap-2 mb-5"><div class="p-1.5 rounded-lg bg-primary/10"><Target class="h-4 w-4 text-primary" /></div><h3 class="text-lg font-bold">Set Monthly Budget</h3></div>
        <div><label class="block text-sm font-semibold text-muted-foreground">Monthly Spending Limit</label><input v-model="budgetForm.monthly_limit" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
        <div class="flex gap-2 mt-5"><button @click="showBudgetModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="saveBudget" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Save</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<!-- Goal Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showGoalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showGoalModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-md mx-4 p-6 rounded-2xl">
        <div class="flex items-center gap-2 mb-5"><div class="p-1.5 rounded-lg bg-primary/10"><Zap class="h-4 w-4 text-primary" /></div><h3 class="text-lg font-bold">Add Financial Goal</h3></div>
        <div class="space-y-4">
            <div><label class="block text-sm font-semibold text-muted-foreground">Goal Name</label><input v-model="goalForm.name" type="text" placeholder="e.g., Emergency Fund" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Target</label><input v-model="goalForm.target_amount" type="number" placeholder="50000" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Saved So Far</label><input v-model="goalForm.current_amount" type="number" placeholder="0" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            </div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Deadline</label><input v-model="goalForm.deadline" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Color</label><input v-model="goalForm.color" type="color" class="mt-1 w-full h-[38px] rounded-lg border border-border bg-card cursor-pointer" /></div>
            </div>
        </div>
        <div class="flex gap-2 mt-5"><button @click="showGoalModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="addGoal" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<!-- Recurring Transaction Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showRecurringModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showRecurringModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-md mx-4 p-6 rounded-2xl">
        <div class="flex items-center gap-2 mb-5"><div class="p-1.5 rounded-lg bg-primary/10"><CalendarClock class="h-4 w-4 text-primary" /></div><h3 class="text-lg font-bold">Add Recurring Transaction</h3></div>
        <div class="space-y-4">
            <div><label class="block text-sm font-semibold text-muted-foreground">Name</label><input v-model="recurringForm.name" type="text" placeholder="e.g., Rent" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Amount</label><input v-model="recurringForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Type</label><select v-model="recurringForm.type" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10"><option value="expense">Expense</option><option value="income">Income</option></select></div>
            </div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Frequency</label><select v-model="recurringForm.frequency" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10"><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Next Due</label><input v-model="recurringForm.next_due_date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            </div>
            <div><label class="block text-sm font-semibold text-muted-foreground">Category</label><input v-model="recurringForm.category" type="text" placeholder="e.g., Housing" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
        </div>
        <div class="flex gap-2 mt-5"><button @click="showRecurringModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="addRecurring" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add</button></div>
    </div></Transition>
</div></Transition>
</Teleport>
<!-- Quick Add Modal -->
<Dialog v-model:open="showQuickAddModal">
    <DialogContent class="sm:max-w-[400px] max-w-[90vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl animate-in zoom-in-95 duration-200">
            <div class="p-6 sm:p-8">
                <DialogHeader class="mb-6">
                    <DialogTitle class="text-xl font-black tracking-tight font-display">Quick Actions</DialogTitle>
                    <DialogDescription class="text-xs text-muted-foreground font-medium">Choose an action to manage your finances.</DialogDescription>
                </DialogHeader>

                <div class="grid grid-cols-1 gap-2.5">
                    <button @click="showQuickAddModal = false; showIncomeModal = true" class="group flex items-center justify-between p-4 rounded-2xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/50 transition-all active:scale-[0.98]">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-[--fin-gain]/10 text-[--fin-gain]"><Wallet class="h-5 w-5" /></div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-foreground">Add Income</p>
                                <p class="text-[10px] text-muted-foreground">Log your earnings</p>
                            </div>
                        </div>
                        <Plus class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-transform group-hover:rotate-90" />
                    </button>

                    <button @click="showQuickAddModal = false; showExpenseModal = true" class="group flex items-center justify-between p-4 rounded-2xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/50 transition-all active:scale-[0.98]">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-xl bg-[--fin-loss]/10 text-[--fin-loss]"><TrendingDown class="h-5 w-5" /></div>
                            <div class="text-left">
                                <p class="text-sm font-bold text-foreground">Add Expense</p>
                                <p class="text-[10px] text-muted-foreground">Log your spending</p>
                            </div>
                        </div>
                        <Plus class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-transform group-hover:rotate-90" />
                    </button>

                    <div class="grid grid-cols-3 gap-2.5 mt-2">
                        <button @click="showQuickAddModal = false; showBudgetModal = true" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/50 transition-all active:scale-[0.98]">
                            <Target class="h-5 w-5 text-primary mb-2" />
                            <p class="text-[10px] font-bold">Budget</p>
                        </button>
                        <button @click="showQuickAddModal = false; showGoalModal = true" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/50 transition-all active:scale-[0.98]">
                            <Zap class="h-5 w-5 text-amber-500 mb-2" />
                            <p class="text-[10px] font-bold">Goal</p>
                        </button>
                        <button @click="showQuickAddModal = false; showRecurringModal = true" class="flex flex-col items-center justify-center p-3 rounded-2xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/50 transition-all active:scale-[0.98]">
                            <CalendarClock class="h-5 w-5 text-blue-500 mb-2" />
                            <p class="text-[10px] font-bold">Recurring</p>
                        </button>
                    </div>
                </div>

                <div class="mt-6">
                    <button @click="showQuickAddModal = false" class="w-full py-3 rounded-xl bg-muted/50 text-muted-foreground font-bold text-sm hover:bg-muted transition-all active:scale-[0.98]">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>
</template>
