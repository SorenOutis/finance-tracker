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
    CreditCard, Smartphone, Landmark, Coins, Bitcoin, MoreHorizontal, Sun, SunDim, Moon
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import TransactionModal from '@/components/TransactionModal.vue';
import RecurringEntryModal from '@/components/RecurringEntryModal.vue';
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
interface User { id: number; name: string; email: string; is_onboarded: boolean; }
interface DashboardData {
    totalIncome: number; totalExpense: number; netIncome: number; netWorth: number;
    totalBalance: number; prevMonthIncome: number; prevMonthExpense: number;
}
interface DailySpendingItem { day: number; amount: number; }
interface CategoryBreakdownItem { category: string; amount: number; percentage: number; }
interface TransactionStats { total: number; incomeCount: number; expenseCount: number; avgExpense: number; daysNoSpending: number; }
interface ActivityItem { id: number; type: string; amount: number; category?: string; description?: string; date: string; timestamp: number; }
interface YtdData { income: number; expense: number; net: number; dayOfYear: number; daysInYear: number; }
interface GoalItem { id: number; name: string; target_amount: number; current_amount: number; deadline?: string; color: string; }
interface RecurringItem { id: number; name: string; amount: number; type: string; category?: string; frequency: string; next_due_date: string; color: string | null; }
interface BillItem { id: number; name: string; amount: number; next_due_date: string; days_until: number; frequency: string; color: string | null; }
interface RecurringIncome { id: number; name: string; amount: number; frequency: string; next_due_date: string; color: string | null; }
interface AccountItem { id: number; name: string; type: string; color: string | null; balance: number; last4: string | null; holder_name: string | null; month_income: number; month_expense: number; }

interface Props {
    user: User; dashboard: DashboardData; incomeEntries: Entry[]; expenseEntries: Entry[];
    dailySpending: DailySpendingItem[]; expensesByCategory: CategoryBreakdownItem[];
    topExpense: { amount: number; category: string; description?: string; date: string } | null;
    transactionStats: TransactionStats; recentActivity: ActivityItem[]; savingsStreak: number;
    ytd: YtdData; budget: { monthly_limit: number } | null; financialGoals: GoalItem[];
    recurringIncomes: RecurringIncome[]; upcomingBills: BillItem[];
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
const healthSummary = computed(() => {
    if (healthScore.value >= 80) return "You're doing amazing! Your financial habits are top-notch.";
    if (healthScore.value >= 60) return "Good job! You're on the right track with your finances.";
    if (healthScore.value >= 40) return "Not bad, but there's room for improvement in your spending.";
    return "Time to review your budget. Small changes can make a big difference.";
});

const savingsRate = computed(() => props.dashboard.totalIncome === 0 ? 0 : ((props.dashboard.totalIncome - props.dashboard.totalExpense) / props.dashboard.totalIncome) * 100);

const animatedIncome = useNumberAnimation(() => props.dashboard.totalIncome);
const animatedExpense = useNumberAnimation(() => props.dashboard.totalExpense);
const animatedNetIncome = useNumberAnimation(() => props.dashboard.netIncome);
const animatedTotalBalance = useNumberAnimation(() => props.dashboard.totalBalance);

const displayStats = computed(() => [
    { label: 'Monthly Income', value: formatCurrency(animatedIncome.value), icon: Wallet, tint: 'stat-tint-income', change: incomeChange.value, changeInverted: false },
    { label: 'Monthly Expenses', value: formatCurrency(animatedExpense.value), icon: TrendingDown, tint: 'stat-tint-expense', change: expenseChange.value, changeInverted: true },
    { label: 'Monthly Net', value: formatCurrency(animatedNetIncome.value), icon: DollarSign, tint: props.dashboard.netIncome >= 0 ? 'stat-tint-positive' : 'stat-tint-negative', change: null, changeInverted: false, colorClass: props.dashboard.netIncome >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]' },
    { label: 'Total Balance', value: formatCurrency(animatedTotalBalance.value), icon: PieChart, tint: 'stat-tint-accent', change: null, changeInverted: false },
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
const showOnboarding = ref(false);
const onboardingStep = ref(1);
const totalOnboardingSteps = 5;
const tooltipStyle = ref({ top: '50%', left: '50%', transform: 'translate(-50%, -50%)' });
const tooltipSide = ref<'top' | 'bottom'>('bottom');

const updateTooltipPosition = () => {
    if (!showOnboarding.value) return;

    if (onboardingStep.value === 1 || onboardingStep.value === 5) {
        tooltipStyle.value = { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' };
        tooltipSide.value = 'bottom';
        return;
    }

    const target = document.querySelector(`.onboarding-target-${onboardingStep.value}`);
    if (target) {
        const rect = target.getBoundingClientRect();
        const isSmallScreen = window.innerWidth < 640;
        
        // Position tooltip below the element if there's space, otherwise above
        const spaceBelow = window.innerHeight - rect.bottom;
        const positionAbove = spaceBelow < 250;
        tooltipSide.value = positionAbove ? 'bottom' : 'top'; // Tooltip is on top of element -> arrow is at bottom (so tooltip is 'top')
        
        tooltipStyle.value = {
            top: positionAbove ? `${rect.top - 20}px` : `${rect.bottom + 20}px`,
            left: isSmallScreen ? '50%' : `${rect.left + rect.width / 2}px`,
            transform: isSmallScreen 
                ? `translate(-50%, ${positionAbove ? '-100%' : '0'})` 
                : `translate(-50%, ${positionAbove ? '-100%' : '0'})`,
            maxWidth: isSmallScreen ? 'calc(100vw - 48px)' : '320px',
            position: 'fixed'
        };
    }
};

const completeOnboarding = () => {
    const routeFunc = (window as any).route;
    const url = routeFunc ? routeFunc('onboarding.complete') : '/onboarding/complete';
    router.post(url, {}, {
        onSuccess: () => {
            showOnboarding.value = false;
        }
    });
};

const nextOnboardingStep = () => {
    if (onboardingStep.value < totalOnboardingSteps) {
        onboardingStep.value++;
    } else {
        completeOnboarding();
    }
};

const prevOnboardingStep = () => {
    if (onboardingStep.value > 1) {
        onboardingStep.value--;
    }
};

watch(onboardingStep, (newStep) => {
    setTimeout(() => {
        const target = document.querySelector(`.onboarding-target-${newStep}`);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(updateTooltipPosition, 400); // Wait for scroll to finish
        } else {
            updateTooltipPosition();
        }
    }, 100);
});

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

    window.addEventListener('resize', updateTooltipPosition);
    
    // Sequence: Show onboarding only if welcome modal is closed and user is not onboarded
    if (!showWelcomeModal.value && !props.user.is_onboarded) {
        showOnboarding.value = true;
        updateTooltipPosition();
    }
});

const startOnboardingFromWelcome = () => {
    showWelcomeModal.value = false;
    if (!props.user.is_onboarded) {
        showOnboarding.value = true;
        setTimeout(updateTooltipPosition, 300);
    }
};

const highlightClass = (targetStep: number) => {
    if (!showOnboarding.value || onboardingStep.value !== targetStep) return '';
    return `onboarding-target-${targetStep} relative z-[100] ring-4 ring-primary ring-offset-4 ring-offset-background rounded-[20px] transition-all duration-500 shadow-[0_0_50px_rgba(var(--primary),0.3)] bg-background scale-[1.02] pointer-events-auto`;
};

// Remove getTooltipPosition computed as we now use tooltipStyle
const showWelcomeModal = useStorage('show-welcome-modal-v1', true, sessionStorage);
const showRecurringModal = ref(false);

// Forms
const incomeForm = useForm({ amount: '', date: getYYYYMMDD(new Date()), category: '', description: '', account_id: '' as string | number });
const expenseForm = useForm({ amount: '', date: getYYYYMMDD(new Date()), category: '', description: '', account_id: '' as string | number });

// Wallet card helpers
const walletDefColors: Record<string, string> = { gcash: '#007DFE', maya: '#00B274', paypal: '#003087', bank: '#1E3A5F', cash: '#6B7280', credit_card: '#18181B', crypto: '#F7931A', other: '#6366F1' };
const walletGradient = (type: string, color: string | null) => { const b = color || walletDefColors[type] || '#6366F1'; return `linear-gradient(135deg, ${b} 0%, ${b}CC 50%, ${b}99 100%)`; };
const simpleGradient = (color: string | null, def: string) => { const b = color || def; return `linear-gradient(135deg, ${b} 0%, ${b}CC 50%, ${b}99 100%)`; };
const walletIcon = (type: string) => ({ gcash: Smartphone, maya: Smartphone, paypal: Wallet, bank: Landmark, cash: Coins, credit_card: CreditCard, crypto: Bitcoin }[type] || MoreHorizontal);
const walletLabel = (type: string) => ({ gcash: 'GCash', maya: 'Maya', paypal: 'PayPal', bank: 'Bank', cash: 'Cash', credit_card: 'Credit Card', crypto: 'Crypto' }[type] || type);
const budgetForm = useForm({ monthly_limit: props.budget?.monthly_limit?.toString() || '' });
const goalForm = useForm({ name: '', target_amount: '', current_amount: '', deadline: '', color: '#14b8a6' });
const recurringForm = useForm({ name: '', amount: '', type: 'expense' as string, category: '', description: '', frequency: 'monthly' as string, next_due_date: getYYYYMMDD(new Date()), color: '' });

const addIncome = (data: any) => { 
    incomeForm.amount = data.amount;
    incomeForm.date = data.date;
    incomeForm.category = data.category;
    incomeForm.description = data.description;
    incomeForm.account_id = data.account_id;
    incomeForm.post('/incomes', { onSuccess: () => { incomeForm.reset(); incomeForm.date = getYYYYMMDD(new Date()); showIncomeModal.value = false; } }); 
};
const addExpense = (data: any) => { 
    expenseForm.amount = data.amount;
    expenseForm.date = data.date;
    expenseForm.category = data.category;
    expenseForm.description = data.description;
    expenseForm.account_id = data.account_id;
    expenseForm.post('/expenses', { onSuccess: () => { expenseForm.reset(); expenseForm.date = getYYYYMMDD(new Date()); showExpenseModal.value = false; } }); 
};
const saveBudget = () => { if (!budgetForm.monthly_limit) return; budgetForm.post('/budgets', { onSuccess: () => { showBudgetModal.value = false; } }); };
const addGoal = () => { if (!goalForm.name || !goalForm.target_amount) return; goalForm.post('/financial-goals', { onSuccess: () => { goalForm.reset(); goalForm.color = '#14b8a6'; showGoalModal.value = false; } }); };
const deleteGoal = (id: number) => { router.delete(`/financial-goals/${id}`, { preserveScroll: true }); };
const addRecurring = (data: any) => {
    recurringForm.name = data.name;
    recurringForm.amount = data.amount;
    recurringForm.type = data.type;
    recurringForm.category = data.category;
    recurringForm.frequency = data.frequency;
    recurringForm.next_due_date = data.next_due_date;
    recurringForm.color = data.color;

    if (!recurringForm.name || !recurringForm.amount) return;
    recurringForm.post('/recurring-transactions', {
        onSuccess: () => {
            recurringForm.reset();
            showRecurringModal.value = false;
        }
    });
};
const deleteRecurring = (id: number) => { router.delete(`/recurring-transactions/${id}`, { preserveScroll: true }); };
const deleteEntry = (id: number, type: 'income' | 'expense') => { router.delete(`/${type}s/${id}`, { preserveScroll: true }); };

const exportCsv = () => { window.location.href = `/export/csv?month=${fmtYearMonth(currentMonth.value)}`; };

const greeting = computed(() => { const h = new Date().getHours(); return h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening'; });
const greetingIcon = computed(() => { const h = new Date().getHours(); return h < 12 ? Sun : h < 17 ? SunDim : Moon; });

const financialTips = [
    "Save 20% of your income for long-term goals.",
    "Track every expense to understand your spending habits.",
    "Build an emergency fund covering 3-6 months of expenses.",
    "Pay off high-interest debt as quickly as possible.",
    "Invest early and often to benefit from compound interest.",
    "Review your budget monthly to stay on track.",
    "Avoid impulse purchases by waiting 24 hours before buying.",
];
const randomTip = computed(() => financialTips[Math.floor(Math.random() * financialTips.length)]);

const yearProgress = computed(() => Math.round((props.ytd.dayOfYear / props.ytd.daysInYear) * 100));
const budgetUsedPct = computed(() => props.budget ? Math.min(100, (props.dashboard.totalExpense / props.budget.monthly_limit) * 100) : 0);

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
};
</script>

<template>
<Head title="Dashboard" />
<AppLayout :breadcrumbs="breadcrumbs">
<div class="flex h-full flex-1 flex-col gap-4 md:gap-6 overflow-x-auto p-3 sm:p-8 pb-24 md:pb-8 relative">

<!-- Onboarding Backdrop -->
<Transition
    enter-active-class="transition-opacity duration-500"
    enter-from-class="opacity-0"
    leave-active-class="transition-opacity duration-500"
    leave-to-class="opacity-0"
>
    <div v-if="showOnboarding && onboardingStep > 0" class="fixed inset-0 bg-black/80 backdrop-blur-[2px] z-[50] pointer-events-auto" @click="completeOnboarding"></div>
</Transition>

<!-- Onboarding Overlay (Non-modal) -->
<Teleport to="body">
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="showOnboarding" 
            :style="tooltipStyle"
            class="fixed z-[200] w-full transition-all duration-500 ease-in-out px-6 sm:px-0"
        >
            <div class="bg-white text-black shadow-[0_20px_50px_rgba(0,0,0,0.3)] rounded-xl overflow-visible mx-auto relative p-6 max-w-full">
                <!-- Arrow/Beak -->
                <div v-if="onboardingStep !== 1 && onboardingStep !== 5"
                    :class="[
                        'absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-white rotate-45 transition-all duration-500',
                        tooltipSide === 'top' ? '-top-2' : '-bottom-2'
                    ]"
                ></div>

                <!-- Close button -->
                <button @click="completeOnboarding" class="absolute top-4 right-4 text-black/40 hover:text-black transition-colors">
                    <X class="w-4 h-4" />
                </button>

                <div class="text-left">
                    <!-- Step 1: Welcome -->
                    <div v-if="onboardingStep === 1" class="animate-fade-up">
                        <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">Welcome to Dashboard</h2>
                        <p class="text-sm text-black/70 leading-relaxed">
                            Let's take a quick tour of your new financial hub.
                        </p>
                    </div>

                    <!-- Step 2: Monthly Performance -->
                    <div v-if="onboardingStep === 2" class="animate-fade-up">
                        <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">Live Stats</h2>
                        <p class="text-sm text-black/70 leading-relaxed">
                            Track your real-time performance and financial metrics.
                        </p>
                    </div>

                    <!-- Step 3: Manage Wallets -->
                    <div v-if="onboardingStep === 3" class="animate-fade-up">
                        <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">Account Wallets</h2>
                        <p class="text-sm text-black/70 leading-relaxed">
                            Connect and manage your GCash, Maya, and Bank accounts.
                        </p>
                    </div>

                    <!-- Step 4: Financial Health -->
                    <div v-if="onboardingStep === 4" class="animate-fade-up">
                        <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">Health Score</h2>
                        <p class="text-sm text-black/70 leading-relaxed">
                            See your financial wellbeing based on your spending habits.
                        </p>
                    </div>

                    <!-- Step 5: Ready to Go -->
                    <div v-if="onboardingStep === 5" class="animate-fade-up">
                        <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">You're All Set!</h2>
                        <p class="text-sm text-black/70 leading-relaxed">
                            Ready to start tracking? Click Get Started to begin.
                        </p>
                    </div>

                    <div class="mt-8 flex items-center justify-between">
                        <span class="text-xs font-bold text-black/40">{{ onboardingStep }} of {{ totalOnboardingSteps }}</span>
                        
                        <div class="flex gap-2">
                            <button v-if="onboardingStep > 1" @click="prevOnboardingStep" class="px-4 py-2 rounded-lg border border-black/10 text-xs font-bold hover:bg-black/5 transition-all">
                                ← Previous
                            </button>
                            <Button @click="nextOnboardingStep" size="sm" class="px-6 py-2 rounded-lg bg-black text-white text-xs font-bold hover:bg-black/90 transition-all border-none">
                                {{ onboardingStep === totalOnboardingSteps ? 'Get Started' : 'Next →' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</Teleport>

<!-- Welcome Hero Section -->
<div :class="['relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 mb-1.5 sm:mb-2 animate-fade-up stagger-1', highlightClass(1)]">
    <!-- Background abstract shapes -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
    <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <div class="p-1 rounded-lg bg-amber-500/10 text-amber-500">
                    <component :is="greetingIcon" class="w-3.5 h-3.5" />
                </div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">{{ greeting }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                Hi, <span class="text-primary">{{ user.name.split(' ')[0] }}</span>!
            </h1>
            <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                Your <span class="text-foreground font-bold">{{ fmtMonthYear(currentMonth) }}</span> overview: 
                you've saved <span class="text-[--fin-gain] font-bold">{{ animatedSR.toFixed(0) }}%</span> of your income.
            </p>
        </div>

        <!-- Integrated Tip/Insight - Smaller & Horizontal -->
        <div class="flex-shrink-0 w-full sm:w-auto sm:max-w-[240px]">
            <div class="group relative p-2.5 rounded-xl bg-muted/30 border border-border/40 hover:bg-card hover:border-primary/30 transition-all cursor-default overflow-hidden">
                <div class="relative z-10 flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-primary/10 text-primary shrink-0">
                        <Zap class="w-3 h-3" />
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-primary">Tip</p>
                        <p class="text-[10px] font-semibold text-foreground leading-tight line-clamp-2">
                            {{ randomTip }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div :class="['grid grid-cols-2 gap-2.5 md:grid-cols-4 lg:gap-4', highlightClass(2), showOnboarding && onboardingStep === 2 ? 'relative z-[100]' : '']">
    <div v-for="(stat, idx) in displayStats" :key="stat.label"
        :class="['group p-3.5 rounded-[18px] sm:p-4 sm:rounded-[20px] overflow-hidden relative border border-border/40 bg-card/20 premium-hover cursor-default animate-fade-up', `stagger-${idx+1}`, stat.tint]">
        <div class="absolute top-0 right-0 w-16 h-16 sm:w-20 sm:h-20 opacity-[0.03] pointer-events-none group-hover:scale-110 transition-transform duration-500">
            <component :is="stat.icon" class="size-16 sm:size-20 -translate-y-4 translate-x-4" />
        </div>
        <div class="flex items-center justify-between mb-1">
            <p class="text-[8px] sm:text-[9px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">{{ stat.label }}</p>
            <span v-if="stat.change != null" :class="['inline-flex items-center gap-0.5 rounded-full px-1.2 py-0.3 text-[7px] sm:text-[8px] font-bold',
                stat.changeInverted ? (stat.change <= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]') : (stat.change >= 0 ? 'bg-[--fin-gain]/12 text-[--fin-gain]' : 'bg-[--fin-loss]/12 text-[--fin-loss]')]">
                <component :is="stat.changeInverted ? (stat.change <= 0 ? ArrowDownRight : ArrowUpRight) : (stat.change >= 0 ? ArrowUpRight : ArrowDownRight)" class="h-2 w-2" />
                {{ Math.abs(stat.change).toFixed(1) }}%
            </span>
        </div>
        <div class="pt-0.5">
            <p :class="['text-lg sm:text-2xl font-black font-display tracking-tight leading-none', stat.colorClass || 'text-foreground']">{{ stat.value }}</p>
        </div>
    </div>
</div>

<!-- Wallet Cards Strip -->
<div v-if="accounts?.length" :class="['animate-fade-up stagger-3 mt-1 p-1', highlightClass(3), showOnboarding && onboardingStep === 3 ? 'relative z-[100]' : '']">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">My Wallets</p>
        <a href="/wallets" class="text-[9px] sm:text-[10px] font-semibold text-primary hover:underline">View all</a>
    </div>
    <div class="flex gap-2.5 overflow-x-auto snap-x snap-mandatory pb-2 scrollbar-hide">
        <div v-for="account in accounts" :key="account.id"
            class="group relative flex-shrink-0 w-[190px] sm:w-[260px] snap-start overflow-hidden rounded-[16px] sm:rounded-[18px] p-3.5 sm:p-5 text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5 cursor-default"
            :style="{ background: walletGradient(account.type, account.color) }">
            <!-- Card pattern -->
            <div class="absolute inset-0 opacity-[0.06] pointer-events-none">
                <div class="absolute top-2 right-2 w-16 h-16 border-2 border-white rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-24 h-24 border-2 border-white rounded-full"></div>
            </div>
            <div class="relative z-10">
                <div class="mb-3.5">
                    <p class="text-[7px] sm:text-[8px] font-bold uppercase tracking-wider opacity-60 mb-0.5">{{ account.holder_name || 'Account' }}</p>
                    <h3 class="text-xs sm:text-sm font-black tracking-tight leading-none">{{ account.name }}</h3>
                </div>
                
                <p class="text-base sm:text-xl font-black font-mono tracking-tight leading-none mb-2.5">{{ formatCurrency(account.balance) }}</p>
                
                <div class="flex items-center gap-1.5 opacity-90 mb-3">
                    <div class="p-1 rounded-md bg-white/20"><component :is="walletIcon(account.type)" class="h-2.5 w-2.5" /></div>
                    <span class="text-[7px] sm:text-[8px] font-bold uppercase tracking-wider">{{ walletLabel(account.type) }}</span>
                    <span v-if="account.last4" class="text-[7px] sm:text-[8px] font-mono opacity-70 ml-1.5">•••• {{ account.last4 }}</span>
                </div>

                <div class="flex items-center gap-1.5 mt-2 pt-2 border-t border-white/15">
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.2 py-0.3 text-[8px] sm:text-[9px] font-bold">
                        <ArrowDownRight class="h-2 w-2" /> {{ formatCurrency(account.month_income) }}
                    </span>
                    <span class="inline-flex items-center gap-0.5 rounded-full bg-white/15 px-1.2 py-0.3 text-[8px] sm:text-[9px] font-bold">
                        <ArrowUpRight class="h-2 w-2" /> {{ formatCurrency(account.month_expense) }}
                    </span>
                </div>
            </div>
        </div>
        <!-- Add wallet card -->
        <a href="/wallets" class="flex-shrink-0 w-[100px] sm:w-[120px] snap-start flex flex-col items-center justify-center rounded-[16px] sm:rounded-[18px] border-2 border-dashed border-border/40 bg-card/10 text-muted-foreground hover:border-primary/50 hover:text-primary transition-all cursor-pointer">
            <Plus class="h-4 w-4 sm:h-5 sm:w-5 mb-1" />
            <span class="text-[9px] sm:text-[10px] font-bold">Add Wallet</span>
        </a>
    </div>
</div>

<!-- Incomes Cards Strip -->
<div v-if="recurringIncomes?.length" class="animate-fade-up stagger-3 mt-2 px-1">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">Monthly Incomes</p>
        <a href="/incomes" class="text-[9px] sm:text-[10px] font-semibold text-primary hover:underline">View all</a>
    </div>
    <div class="flex gap-2.5 overflow-x-auto snap-x snap-mandatory pb-2 scrollbar-hide">
        <div v-for="income in recurringIncomes" :key="income.id"
            class="group relative flex-shrink-0 w-[170px] sm:w-[240px] snap-start overflow-hidden rounded-[16px] sm:rounded-[18px] p-3.5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default text-white shadow-md"
            :style="{ background: simpleGradient(income.color, '#10b981') }">
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-1.5">
                        <div class="p-1.5 rounded-lg bg-white/20"><Wallet class="h-3 w-3" /></div>
                        <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider opacity-90 truncate max-w-[70px] sm:max-w-[80px]">{{ income.name }}</span>
                    </div>
                    <span class="text-[7px] sm:text-[8px] font-black px-1.2 py-0.3 rounded-full bg-white/20 uppercase">Active</span>
                </div>
                
                <p class="text-base sm:text-lg font-black font-mono tracking-tight leading-none">+{{ formatCurrency(income.amount) }}</p>
                
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-white/10">
                    <p class="text-[8px] sm:text-[9px] font-bold opacity-80 uppercase">{{ income.frequency }}</p>
                    <p class="text-[8px] sm:text-[9px] font-mono font-bold opacity-70">{{ formatDate(income.next_due_date) }}</p>
                </div>
            </div>
        </div>
        <!-- Add income card -->
        <a href="/incomes" class="flex-shrink-0 w-[100px] sm:w-[120px] snap-start flex flex-col items-center justify-center rounded-[16px] sm:rounded-[18px] border-2 border-dashed border-border/60 bg-card text-muted-foreground hover:border-primary/50 hover:text-primary transition-all cursor-pointer shadow-sm">
            <Plus class="h-4 w-4 sm:h-5 sm:w-5 mb-1" />
            <span class="text-[9px] sm:text-[10px] font-bold">Add Income</span>
        </a>
    </div>
</div>

<!-- Bills Cards Strip -->
<div v-if="upcomingBills?.length" class="animate-fade-up stagger-4 mt-2 px-1">
    <div class="flex items-center justify-between mb-2 px-1">
        <p class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">My Bills</p>
        <a href="/bills" class="text-[9px] sm:text-[10px] font-semibold text-primary hover:underline">View all</a>
    </div>
    <div class="flex gap-2.5 overflow-x-auto snap-x snap-mandatory pb-2 scrollbar-hide">
        <div v-for="bill in upcomingBills" :key="bill.id"
            class="group relative flex-shrink-0 w-[170px] sm:w-[240px] snap-start overflow-hidden rounded-[16px] sm:rounded-[18px] p-3.5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 cursor-default text-white shadow-md"
            :style="{ background: simpleGradient(bill.color, '#ef4444') }">
            
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-1.5">
                        <div class="p-1.5 rounded-lg bg-white/20"><CalendarClock class="h-3 w-3" /></div>
                        <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider opacity-90 truncate max-w-[70px] sm:max-w-[80px]">{{ bill.name }}</span>
                    </div>
                    <span class="text-[7px] sm:text-[8px] font-black px-1.2 py-0.3 rounded-full bg-white/20 uppercase">{{ bill.days_until === 0 ? 'Today' : `In ${bill.days_until}d` }}</span>
                </div>
                
                <p class="text-base sm:text-lg font-black font-mono tracking-tight leading-none">{{ formatCurrency(bill.amount) }}</p>
                
                <div class="flex items-center justify-between mt-3 pt-2 border-t border-white/10">
                    <p class="text-[8px] sm:text-[9px] font-bold opacity-80 uppercase">{{ bill.frequency }}</p>
                    <p class="text-[8px] sm:text-[9px] font-mono font-bold opacity-70">{{ formatDate(bill.next_due_date) }}</p>
                </div>
            </div>
        </div>
        <!-- Add bill card -->
        <a href="/bills" class="flex-shrink-0 w-[100px] sm:w-[120px] snap-start flex flex-col items-center justify-center rounded-[16px] sm:rounded-[18px] border-2 border-dashed border-border/60 bg-card text-muted-foreground hover:border-primary/50 hover:text-primary transition-all cursor-pointer shadow-sm">
            <Plus class="h-4 w-4 sm:h-5 sm:w-5 mb-1" />
            <span class="text-[9px] sm:text-[10px] font-bold">Add Bill</span>
        </a>
    </div>
</div>

<!-- Transaction Chips + Income vs Expense Bar -->
<div class="grid gap-2.5 md:grid-cols-2 animate-fade-up stagger-5 mt-1 px-1">
    <!-- Transaction Stats Chips (Pills layout) -->
    <div class="p-3.5 rounded-[18px] sm:p-4 sm:rounded-[20px] border border-border/40 bg-card/20 flex flex-wrap items-center justify-center gap-1.5">
        <span class="inline-flex items-center gap-1 rounded-full bg-card/40 border border-border/30 px-2.5 py-0.8 text-[10px] sm:text-[11px] font-bold text-muted-foreground transition-all">
            <Activity class="h-2.5 w-2.5 sm:h-3 sm:w-3" /> {{ transactionStats.total }} transactions
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-card/40 border border-border/30 px-2.5 py-0.8 text-[10px] sm:text-[11px] font-bold text-foreground">
            {{ transactionStats.incomeCount }} income
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-card/40 border border-border/30 px-2.5 py-0.8 text-[10px] sm:text-[11px] font-bold text-foreground">
            {{ transactionStats.expenseCount }} expenses
        </span>
        <span v-if="transactionStats.avgExpense > 0" class="inline-flex items-center gap-1 rounded-full bg-card/40 border border-border/30 px-2.5 py-0.8 text-[10px] sm:text-[11px] font-bold text-muted-foreground/80">
            Avg {{ formatCurrency(transactionStats.avgExpense) }}
        </span>
    </div>

    <!-- Income vs Expense Comparison Bar -->
    <div class="surface-card p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px]">
        <p class="text-[8px] sm:text-[9px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-1.5 font-sans">Income vs Expenses</p>
        <div class="flex h-1.5 sm:h-2 rounded-full overflow-hidden bg-muted/30">
            <div class="bg-[--fin-gain] transition-all duration-700 rounded-l-full"
                :style="{ width: (props.dashboard.totalIncome + props.dashboard.totalExpense > 0 ? (props.dashboard.totalIncome / (props.dashboard.totalIncome + props.dashboard.totalExpense)) * 100 : 50) + '%' }"></div>
            <div class="bg-[--fin-loss] transition-all duration-700 rounded-r-full flex-1"></div>
        </div>
        <div class="flex justify-between mt-1 text-[8px] sm:text-[9px] font-mono font-semibold">
            <span class="text-[--fin-gain]">{{ formatCurrency(animatedIncome) }}</span>
            <span class="text-[--fin-loss]">{{ formatCurrency(animatedExpense) }}</span>
        </div>
    </div>
</div>

<!-- Rings Row: Savings + Health + Year Progress -->
<div :class="['grid grid-cols-2 lg:grid-cols-3 gap-2.5 animate-fade-up stagger-5 px-1', showOnboarding && onboardingStep === 4 ? 'relative z-[100]' : '']">
    <!-- Savings Rate Ring -->
    <div class="surface-card p-3.5 sm:p-4 flex flex-col items-center justify-center rounded-[18px] sm:rounded-[20px]">
        <p class="text-[9px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2 sm:mb-3 font-sans">Savings Rate</p>
        <div class="relative w-20 h-20 sm:w-24 sm:h-24">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" :r="R" fill="none" class="stroke-border/30" stroke-width="8" />
                <circle cx="60" cy="60" :r="R" fill="none" :stroke="savingsRate >= 0 ? 'oklch(0.76 0.16 162)' : 'oklch(0.62 0.22 18)'" stroke-width="8" stroke-linecap="round" :stroke-dasharray="C" :stroke-dashoffset="ringOffset" class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-base sm:text-lg font-bold font-mono tracking-tighter" :class="savingsRate >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">{{ animatedSR.toFixed(0) }}%</span>
            </div>
        </div>
        <p class="mt-1.5 text-[8px] sm:text-[10px] text-muted-foreground text-center font-sans">{{ savingsRate >= 20 ? 'Great job!' : savingsRate >= 0 ? 'Room to improve' : 'Over budget' }}</p>
    </div>

    <!-- Health Score Gauge -->
    <div :class="['surface-card p-3.5 sm:p-4 flex flex-col items-center justify-center rounded-[18px] sm:rounded-[20px]', highlightClass(4)]">
        <p class="text-[9px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2 sm:mb-3 font-sans">Financial Health</p>
        <div class="relative w-20 h-20 sm:w-24 sm:h-24">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" :r="R" fill="none" class="stroke-border/30" stroke-width="8" />
                <circle cx="60" cy="60" :r="R" fill="none" :stroke="healthColor" stroke-width="8" stroke-linecap="round" :stroke-dasharray="C" :stroke-dashoffset="healthOffset" class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-base sm:text-lg font-bold font-mono tracking-tighter">{{ animatedHS.toFixed(0) }}</span>
            </div>
        </div>
        <p class="mt-1.5 text-[8px] sm:text-[10px] font-semibold text-center font-sans" :style="{ color: healthColor }">{{ healthLabel }}</p>
        <p class="mt-1 text-[7px] sm:text-[9px] text-muted-foreground text-center font-medium leading-tight px-2">{{ healthSummary }}</p>
    </div>

    <!-- Year Progress -->
    <div class="surface-card p-3.5 sm:p-4 col-span-2 lg:col-span-1 rounded-[18px] sm:rounded-[20px]">
        <p class="text-[9px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2 font-sans">Year Progress</p>
        <div class="w-full h-1.5 sm:h-2 rounded-full bg-muted/30 overflow-hidden mb-2 sm:mb-3">
            <div class="h-full rounded-full bg-primary transition-all duration-700" :style="{ width: yearProgress + '%' }"></div>
        </div>
        <p class="text-[8px] sm:text-[10px] text-muted-foreground font-sans mb-2 sm:mb-3">{{ yearProgress }}% of {{ new Date(selectedDate).getFullYear() }} complete</p>
        <div class="space-y-1">
            <div class="flex justify-between text-[8px] sm:text-[10px] font-sans">
                <span class="text-muted-foreground">YTD Income</span>
                <span class="font-mono font-semibold text-[--fin-gain]">{{ formatCurrency(ytd.income) }}</span>
            </div>
            <div class="flex justify-between text-[8px] sm:text-[10px] font-sans">
                <span class="text-muted-foreground">YTD Expenses</span>
                <span class="font-mono font-semibold text-[--fin-loss]">{{ formatCurrency(ytd.expense) }}</span>
            </div>
            <div class="flex justify-between text-[8px] sm:text-[10px] font-sans border-t border-border/30 pt-1">
                <span class="font-semibold text-foreground">YTD Net</span>
                <span :class="['font-mono font-bold', ytd.net >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ formatCurrency(ytd.net) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Sparkline + Top Expense + Streak -->
<div class="grid gap-2.5 md:grid-cols-3 animate-fade-up stagger-5 px-1">
    <!-- Daily Spending Sparkline -->
    <div class="surface-card p-3.5 sm:p-4 md:col-span-2 rounded-[18px] sm:rounded-[20px]">
        <h2 class="text-xs sm:text-sm font-bold text-foreground font-display tracking-tight">Daily Spending</h2>
        <p class="text-[8px] sm:text-[10px] text-muted-foreground font-sans mb-2 sm:mb-3">{{ fmtMonthYear(currentMonth) }}</p>
        <div v-if="dailySpending?.some(d => d.amount > 0)" class="relative" style="height: 120px; sm:height: 140px;">
            <LineChart :key="JSON.stringify(sparklineData)" :data="sparklineData" :options="sparkOpts" style="position: relative; height: 100%;" />
        </div>
        <div v-else class="flex h-24 sm:h-32 items-center justify-center">
            <div class="text-center"><TrendingDown class="h-5 w-5 sm:h-6 sm:w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[8px] sm:text-[10px] text-muted-foreground font-sans">No spending data</p></div>
        </div>
    </div>

    <!-- Top Expense + Streak -->
    <div class="grid grid-cols-2 gap-2.5 md:block md:space-y-3">
        <!-- Top Expense Spotlight -->
        <div v-if="topExpense" class="surface-card p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px]">
            <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                <AlertCircle class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-[--fin-loss]" />
                <p class="text-[8px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Biggest Expense</p>
            </div>
            <p class="text-base sm:text-lg font-bold font-mono text-[--fin-loss]">{{ formatCurrency(topExpense.amount) }}</p>
            <p class="text-[10px] sm:text-[11px] font-semibold text-foreground mt-0.5 sm:mt-1">{{ topExpense.category }}</p>
            <p v-if="topExpense.description" class="text-[8px] sm:text-[10px] text-muted-foreground mt-0.5 truncate">{{ topExpense.description }}</p>
        </div>

        <!-- Spending Streak -->
        <div class="surface-card p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px]">
            <div class="flex items-center gap-1.5 mb-1.5 sm:mb-2">
                <Flame class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-amber-500" />
                <p class="text-[8px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Savings Streak</p>
            </div>
            <p class="text-xl sm:text-2xl font-bold font-mono text-foreground">{{ savingsStreak }}</p>
            <p class="text-[8px] sm:text-[10px] text-muted-foreground font-sans">consecutive month{{ savingsStreak !== 1 ? 's' : '' }} saving</p>
        </div>
    </div>
</div>

<!-- Budget Tracker -->
<div v-if="budget" class="surface-card premium-hover p-3.5 sm:p-4 animate-fade-up stagger-5 rounded-[18px] sm:rounded-[20px] mx-1">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-1.5">
            <Target class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-primary" />
            <p class="text-[9px] sm:text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground font-sans">Monthly Budget</p>
        </div>
        <span :class="['text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full', budgetUsedPct > 100 ? 'bg-[--fin-loss]/12 text-[--fin-loss]' : budgetUsedPct > 80 ? 'bg-amber-500/12 text-amber-600' : 'bg-[--fin-gain]/12 text-[--fin-gain]']">
            {{ budgetUsedPct.toFixed(0) }}% used
        </span>
    </div>
    <div class="w-full h-2 sm:h-2.5 rounded-full bg-muted/30 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-700" :class="budgetUsedPct > 100 ? 'bg-[--fin-loss]' : budgetUsedPct > 80 ? 'bg-amber-500' : 'bg-[--fin-gain]'" :style="{ width: Math.min(100, budgetUsedPct) + '%' }"></div>
    </div>
    <div class="flex justify-between mt-1.5 text-[9px] sm:text-[10px] font-mono">
        <span class="text-muted-foreground">{{ formatCurrency(animatedExpense) }} spent</span>
        <span class="text-foreground font-semibold">{{ formatCurrency(budget.monthly_limit) }} limit</span>
    </div>
</div>

<!-- Category Breakdown -->
<div v-if="expensesByCategory?.length" class="surface-card premium-hover p-3.5 sm:p-4 animate-fade-up stagger-5 rounded-[18px] sm:rounded-[20px] mx-1">
    <h2 class="text-xs sm:text-sm font-bold text-foreground font-display tracking-tight">Expense Breakdown</h2>
    <p class="text-[9px] sm:text-[10px] text-muted-foreground font-sans mb-3 sm:mb-4">Where your money goes</p>
    <div class="space-y-2.5 sm:space-y-3">
        <div v-for="(cat, idx) in expensesByCategory" :key="cat.category">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full shrink-0" :style="{ backgroundColor: catColors[idx % catColors.length] }"></span><span class="text-[11px] sm:text-xs font-semibold text-foreground font-sans">{{ cat.category }}</span></div>
                <div class="flex items-center gap-2"><span class="text-[9px] sm:text-[10px] text-muted-foreground">{{ cat.percentage }}%</span><span class="text-[11px] sm:text-xs font-bold font-mono text-foreground">{{ formatCurrency(cat.amount) }}</span></div>
            </div>
            <div class="w-full h-1 sm:h-1.5 bg-muted/40 rounded-full overflow-hidden"><div class="h-full rounded-full animate-bar-fill origin-left" :style="{ width: cat.percentage + '%', backgroundColor: catColors[idx % catColors.length], animationDelay: (idx * 100) + 'ms' }"></div></div>
        </div>
    </div>
</div>

<!-- Financial Goals -->
<div v-if="financialGoals?.length" class="surface-card p-3.5 sm:p-4 animate-fade-up stagger-5 rounded-[18px] sm:rounded-[20px] mx-1">
    <h2 class="text-xs sm:text-sm font-bold text-foreground font-display tracking-tight mb-3 sm:mb-4">Financial Goals</h2>
    <div class="grid gap-2.5 sm:gap-3 md:grid-cols-2">
        <div v-for="goal in financialGoals" :key="goal.id" class="group p-2.5 sm:p-3 rounded-xl bg-muted/20 border border-border/30 relative">
            <button @click="deleteGoal(goal.id)" class="absolute top-2 right-2 p-1 rounded text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100"><X class="h-3 w-3" /></button>
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="relative w-10 h-10 sm:w-12 sm:h-12 shrink-0">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" class="stroke-border/30" stroke-width="8" />
                        <circle cx="60" cy="60" r="50" fill="none" :stroke="goal.color" stroke-width="8" stroke-linecap="round"
                            :stroke-dasharray="2 * Math.PI * 50"
                            :stroke-dashoffset="2 * Math.PI * 50 - (Math.min(1, goal.current_amount / goal.target_amount) * 2 * Math.PI * 50)" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center"><span class="text-[8px] sm:text-[9px] font-bold font-mono">{{ Math.round((goal.current_amount / goal.target_amount) * 100) }}%</span></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] sm:text-xs font-bold text-foreground truncate">{{ goal.name }}</p>
                    <p class="text-[9px] sm:text-[10px] font-mono text-muted-foreground">{{ formatCurrency(goal.current_amount) }} / {{ formatCurrency(goal.target_amount) }}</p>
                    <p v-if="goal.deadline" class="text-[8px] sm:text-[9px] text-muted-foreground mt-0.5">Due {{ goal.deadline }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Timeline -->
<div v-if="recentActivity?.length" class="surface-card premium-hover p-3.5 sm:p-4 animate-fade-up stagger-5 mt-1 rounded-[18px] sm:rounded-[20px] mx-1">
    <div class="flex items-center gap-1.5 mb-2.5 sm:mb-3"><Activity class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-primary" /><h2 class="text-xs sm:text-sm font-bold text-foreground font-display tracking-tight">Recent Activity</h2></div>
    <div class="space-y-0 divide-y divide-border/20">
        <div v-for="item in recentActivity" :key="`${item.type}-${item.id}`" class="flex items-center justify-between py-2 sm:py-2.5 first:pt-0 last:pb-0">
            <div class="flex items-center gap-2 sm:gap-2.5">
                <div :class="['w-1.5 h-1.5 rounded-full shrink-0', item.type === 'income' ? 'bg-[--fin-gain]' : 'bg-[--fin-loss]']"></div>
                <div>
                    <p class="text-[11px] sm:text-xs font-semibold text-foreground">{{ item.category || (item.type === 'income' ? 'Income' : 'Expense') }}</p>
                    <p class="text-[9px] sm:text-[10px] text-muted-foreground">{{ item.date }}</p>
                </div>
            </div>
            <span :class="['text-[11px] sm:text-xs font-bold font-mono', item.type === 'income' ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">{{ item.type === 'income' ? '+' : '-' }}{{ formatCurrency(item.amount) }}</span>
        </div>
    </div>
</div>

<!-- Income & Expense Entry Lists -->
<div class="grid grid-cols-2 gap-2.5 sm:gap-3 animate-fade-up stagger-5 px-1">
    <div class="surface-card premium-hover p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px]">
        <div class="mb-2 sm:mb-3 flex items-center gap-1.5 sm:gap-2"><div class="p-1 rounded bg-[--fin-gain]/10"><Wallet class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-[--fin-gain]" /></div><h2 class="text-[11px] sm:text-sm font-bold text-foreground">Income</h2><span class="text-[8px] sm:text-[10px] text-muted-foreground ml-auto">{{ fmtYearMonth(currentMonth) }}</span></div>
        <div v-if="incomeEntries.length" class="divide-y divide-border/20">
            <TransitionGroup name="list-slide" appear>
                <div v-for="e in incomeEntries" :key="e.id" class="entry-row group flex items-start justify-between py-2 sm:py-2.5 first:pt-0">
                    <div><p class="text-[11px] sm:text-xs font-semibold font-mono text-[--fin-gain]">+{{ formatCurrency(e.amount) }}</p><div class="flex gap-1 sm:gap-1.5 mt-0.5 sm:mt-1"><span class="text-[9px] sm:text-[10px] text-muted-foreground">{{ e.date }}</span><span v-if="e.category" class="text-[8px] sm:text-[9px] font-semibold bg-muted/50 px-1 sm:px-1.5 py-0.5 rounded-full text-muted-foreground">{{ e.category }}</span></div></div>
                    <button @click="deleteEntry(e.id, 'income')" class="p-1 text-muted-foreground opacity-0 transition-all hover:text-destructive group-hover:opacity-100"><span class="text-[10px] sm:text-xs">✕</span></button>
                </div>
            </TransitionGroup>
        </div>
        <div v-else class="py-4 sm:py-6 text-center"><Wallet class="h-5 w-5 sm:h-6 sm:w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[9px] sm:text-[10px] text-muted-foreground">No income</p></div>
    </div>
    <div class="surface-card premium-hover p-3.5 sm:p-4 rounded-[18px] sm:rounded-[20px]">
        <div class="mb-2 sm:mb-3 flex items-center gap-1.5 sm:gap-2"><div class="p-1 rounded bg-[--fin-loss]/10"><TrendingDown class="h-3 w-3 sm:h-3.5 sm:w-3.5 text-[--fin-loss]" /></div><h2 class="text-[11px] sm:text-sm font-bold text-foreground">Expenses</h2><span class="text-[8px] sm:text-[10px] text-muted-foreground ml-auto">{{ fmtYearMonth(currentMonth) }}</span></div>
        <div v-if="expenseEntries.length" class="divide-y divide-border/20">
            <TransitionGroup name="list-slide" appear>
                <div v-for="e in expenseEntries" :key="e.id" class="entry-row group flex items-start justify-between py-2 sm:py-2.5 first:pt-0">
                    <div><p class="text-[11px] sm:text-xs font-semibold font-mono text-[--fin-loss]">-{{ formatCurrency(e.amount) }}</p><div class="flex gap-1 sm:gap-1.5 mt-0.5 sm:mt-1"><span class="text-[9px] sm:text-[10px] text-muted-foreground">{{ e.date }}</span><span v-if="e.category" class="text-[8px] sm:text-[9px] font-semibold bg-muted/50 px-1 sm:px-1.5 py-0.5 rounded-full text-muted-foreground">{{ e.category }}</span></div></div>
                    <button @click="deleteEntry(e.id, 'expense')" class="p-1 text-muted-foreground opacity-0 transition-all hover:text-destructive group-hover:opacity-100"><span class="text-[10px] sm:text-xs">✕</span></button>
                </div>
            </TransitionGroup>
        </div>
        <div v-else class="py-4 sm:py-6 text-center"><TrendingDown class="h-5 w-5 sm:h-6 sm:w-6 text-muted-foreground/20 mx-auto mb-1" /><p class="text-[9px] sm:text-[10px] text-muted-foreground">No expenses</p></div>
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
                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Total Balance</p>
                        <p class="text-base sm:text-lg font-bold font-mono text-foreground">{{ formatCurrency(props.dashboard.totalBalance) }}</p>
                    </div>
                    <div class="p-3 sm:p-4 rounded-2xl bg-muted/30 border border-border/40 text-left">
                        <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Health Score</p>
                        <p class="text-base sm:text-lg font-bold font-mono" :style="{ color: healthColor }">{{ healthScore }}</p>
                    </div>
                </div>

                <DialogFooter class="mt-6 sm:mt-8 w-full sm:justify-center">
                    <button @click="startOnboardingFromWelcome" class="w-full sm:w-auto px-8 sm:px-10 py-3 sm:py-4 rounded-[18px] sm:rounded-[20px] bg-primary text-primary-foreground font-bold text-sm sm:text-base shadow-lg shadow-primary/20 hover:shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Get Started
                    </button>
                </DialogFooter>
            </div>
        </div>
    </DialogContent>
</Dialog>

<TransactionModal 
    v-model:open="showIncomeModal"
    type="income"
    title="Add Income"
    :accounts="accounts"
    @submit="addIncome"
/>

<TransactionModal 
    v-model:open="showExpenseModal"
    type="expense"
    title="Add Expense"
    :accounts="accounts"
    @submit="addExpense"
/>

<!-- Budget Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showBudgetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showBudgetModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-sm mx-4 p-4 rounded-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center gap-2 mb-3"><div class="p-1.5 rounded-lg bg-primary/10"><Target class="h-4 w-4 text-primary" /></div><h3 class="text-lg font-bold">Set Monthly Budget</h3></div>
        <div><label class="block text-sm font-semibold text-muted-foreground">Monthly Spending Limit</label><input v-model="budgetForm.monthly_limit" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-1.5 sm:py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
        <div class="flex gap-2 mt-3 sm:mt-5"><button @click="showBudgetModal = false" class="flex-1 rounded-lg border border-border px-4 py-2 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="saveBudget" class="flex-1 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Save</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<!-- Goal Modal -->
<Teleport to="body">
<Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
<div v-if="showGoalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" @click.self="showGoalModal = false">
    <Transition appear enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95 translate-y-2" enter-to-class="opacity-100 scale-100 translate-y-0">
    <div class="surface-elevated w-full max-w-md mx-4 p-4 rounded-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center gap-2 mb-3"><div class="p-1.5 rounded-lg bg-primary/10"><Zap class="h-4 w-4 text-primary" /></div><h3 class="text-lg font-bold">Add Financial Goal</h3></div>
        <div class="space-y-2 sm:space-y-4">
            <div><label class="block text-sm font-semibold text-muted-foreground">Goal Name</label><input v-model="goalForm.name" type="text" placeholder="e.g., Emergency Fund" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-1.5 sm:py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Target</label><input v-model="goalForm.target_amount" type="number" placeholder="50000" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-1.5 sm:py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Saved So Far</label><input v-model="goalForm.current_amount" type="number" placeholder="0" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-1.5 sm:py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
            </div>
            <div class="grid gap-4 grid-cols-2">
                <div><label class="block text-sm font-semibold text-muted-foreground">Deadline</label><input v-model="goalForm.deadline" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-1.5 sm:py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" /></div>
                <div><label class="block text-sm font-semibold text-muted-foreground">Color</label><input v-model="goalForm.color" type="color" class="mt-1 w-full h-[34px] sm:h-[38px] rounded-lg border border-border bg-card cursor-pointer" /></div>
            </div>
        </div>
        <div class="flex gap-2 mt-3 sm:mt-5"><button @click="showGoalModal = false" class="flex-1 rounded-lg border border-border px-4 py-2 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button><button @click="addGoal" class="flex-1 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add</button></div>
    </div></Transition>
</div></Transition>
</Teleport>

<RecurringEntryModal 
    v-model:open="showRecurringModal"
    title="Add Recurring"
    @submit="addRecurring"
/>
</template>
