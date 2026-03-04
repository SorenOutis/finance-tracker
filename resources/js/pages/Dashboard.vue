<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, watch } from 'vue';
import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import { Wallet, TrendingDown, DollarSign, PieChart } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

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
}

interface Props {
    user: User;
    dashboard: DashboardData;
    incomeEntries: Entry[];
    expenseEntries: Entry[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

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

const formatDate = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
};

const formatMonthYear = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const formatYearMonth = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${year}-${month}`;
};

const getStatIcon = (label: string) => {
    switch (label.toLowerCase()) {
        case 'total income': return Wallet;
        case 'total expenses': return TrendingDown;
        case 'net income': return DollarSign;
        case 'net worth': return PieChart;
        default: return DollarSign;
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
        icon: getStatIcon('total income')
    },
    {
        label: 'Total Expenses',
        value: formatCurrency(props.dashboard.totalExpense),
        icon: getStatIcon('total expenses')
    },
    {
        label: 'Net Income',
        value: formatCurrency(props.dashboard.netIncome),
        icon: getStatIcon('net income')
    },
    {
        label: 'Net Worth',
        value: formatCurrency(props.dashboard.netWorth),
        icon: getStatIcon('net worth')
    }
]);

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
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Finance Tracker</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Welcome back, {{
                    props.user.name }}</p>
            </div>

            <!-- Controls -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-semibold text-muted-foreground font-sans">Date</label>
                    <input v-model="selectedDate" type="date"
                        class="rounded-lg border border-border bg-card px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:border-border focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>

                <div class="flex gap-3">
                    <button @click="showIncomeModal = true"
                        class="rounded-lg border border-border bg-card px-5 py-2.5 text-sm font-semibold text-foreground shadow-sm transition-all duration-200 hover:border-border hover:bg-accent/50 hover:shadow-md">
                        Add Income
                    </button>
                    <button @click="showExpenseModal = true"
                        class="rounded-lg border border-primary bg-primary/8 px-5 py-2.5 text-sm font-bold text-primary hover:bg-primary/12 transition-all duration-200 hover:shadow-md">
                        Add Expense
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:gap-6">
                <div v-for="(stat, idx) in displayStats" :key="stat.label"
                    class="group surface-card p-4 lg:p-5 overflow-hidden relative hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-0 right-0 w-24 h-24 opacity-[0.03] pointer-events-none">
                        <component :is="stat.icon" class="size-24 -translate-y-4 translate-x-4" />
                    </div>
                    <p
                        class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                        {{ stat.label }}
                    </p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">
                        {{ stat.value }}
                    </p>
                </div>
            </div>

            <!-- Savings Rate Card -->
            <div class="surface-card p-4 lg:p-6 max-w-sm">
                <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                    Savings Rate
                </p>
                <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">
                    {{ savingsRate.toFixed(0) }}%
                </p>
            </div>

            <!-- Entries Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Income Section -->
                <div class="surface-card p-5 lg:p-6 relative overflow-hidden">
                    <div class="mb-4 flex items-center gap-2">
                        <Wallet class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Income</h2>
                    </div>
                    <p class="text-[11px] text-muted-foreground font-sans">
                        {{ formatYearMonth(currentMonth) }}
                    </p>

                    <div v-if="props.incomeEntries.length > 0" class="mt-6 divide-y divide-border/30 space-y-0">
                        <div v-for="entry in props.incomeEntries" :key="entry.id"
                            class="group flex items-start justify-between py-4 transition-colors first:pt-0 hover:bg-accent/20">
                            <div class="flex-1">
                                <p class="font-semibold text-foreground text-[13px] font-sans">{{
                                    formatCurrency(entry.amount) }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="text-xs font-medium text-muted-foreground font-sans">{{ entry.date
                                    }}</span>
                                    <span v-if="entry.category"
                                        class="text-xs font-medium text-muted-foreground bg-muted/40 px-2.5 py-1 rounded-lg font-sans">{{
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

                    <p v-else class="mt-6 text-center text-[11px] font-medium text-muted-foreground font-sans">No
                        entries
                        for this month</p>
                </div>

                <!-- Expenses Section -->
                <div class="surface-card p-5 lg:p-6 relative overflow-hidden">
                    <div class="mb-4 flex items-center gap-2">
                        <TrendingDown class="h-5 w-5 text-muted-foreground" />
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Expenses</h2>
                    </div>
                    <p class="text-[11px] text-muted-foreground font-sans">
                        {{ formatYearMonth(currentMonth) }}
                    </p>

                    <div v-if="props.expenseEntries.length > 0" class="mt-6 divide-y divide-border/30 space-y-0">
                        <div v-for="entry in props.expenseEntries" :key="entry.id"
                            class="group flex items-start justify-between py-4 transition-colors first:pt-0 hover:bg-accent/20">
                            <div class="flex-1">
                                <p class="font-semibold text-foreground text-[13px] font-sans">{{
                                    formatCurrency(entry.amount) }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="text-xs font-medium text-muted-foreground font-sans">{{ entry.date
                                    }}</span>
                                    <span v-if="entry.category"
                                        class="text-xs font-medium text-muted-foreground bg-muted/40 px-2.5 py-1 rounded-lg font-sans">{{
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

                    <p v-else class="mt-6 text-center text-[11px] font-medium text-muted-foreground font-sans">No
                        entries
                        for this month</p>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Income Modal -->
    <div v-if="showIncomeModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        @click.self="showIncomeModal = false">
        <div class="w-full max-w-md transform rounded-2xl surface-card p-8 transition-all">
            <h2 class="text-lg font-bold leading-tight">Add Income</h2>

            <div class="mt-6 space-y-5">
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
    </div>

    <!-- Expense Modal -->
    <div v-if="showExpenseModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
        @click.self="showExpenseModal = false">
        <div class="w-full max-w-md transform rounded-2xl surface-card p-8 transition-all">
            <h2 class="text-lg font-bold leading-tight">Add Expense</h2>

            <div class="mt-6 space-y-5">
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
    </div>
</template>
