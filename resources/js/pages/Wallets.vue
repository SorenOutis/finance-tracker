<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import {
    CreditCard, Plus, Trash2, X, Pencil, Wallet, Smartphone, Landmark, Coins, Bitcoin, MoreHorizontal, ArrowDownRight, ArrowUpRight, CalendarClock, ChevronRight
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog';
import TransactionModal from '@/components/TransactionModal.vue';
import WalletEntryModal from '@/components/WalletEntryModal.vue';

interface AccountItem {
    id: number; name: string; type: string; color: string | null;
    initial_balance: number; credit_limit: number | null; currency: string;
    last4: string | null; holder_name: string | null;
    balance: number; month_income: number; month_expense: number;
}

interface Props {
    accounts: AccountItem[];
    totalBalance: number;
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Wallets', href: '/wallets' }];

const showAddModal = useStorage('wallets-add-modal', false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showIncomeModal = ref(false);
const showExpenseModal = ref(false);
const editingAccount = ref<AccountItem | null>(null);
const accountToDelete = ref<AccountItem | null>(null);
const selectedAccountForTransaction = ref<AccountItem | null>(null);

const getYYYYMMDD = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

const incomeForm = useForm({
    amount: '',
    date: getYYYYMMDD(new Date()),
    category: '',
    description: '',
    account_id: '' as string | number,
});

const expenseForm = useForm({
    amount: '',
    date: getYYYYMMDD(new Date()),
    category: '',
    description: '',
    account_id: '' as string | number,
});

const accountForm = useForm({
    name: '',
    type: 'cash',
    color: '',
    initial_balance: '0',
    credit_limit: '',
    currency: 'PHP',
    last4: '',
    holder_name: '',
});

const editForm = useForm({
    name: '',
    type: 'cash',
    color: '',
    initial_balance: '0',
    credit_limit: '',
    currency: 'PHP',
    last4: '',
    holder_name: '',
});

const accountTypes = [
    { value: 'cash', label: 'Cash', icon: Coins },
    { value: 'gcash', label: 'GCash', icon: Smartphone },
    { value: 'maya', label: 'Maya', icon: Smartphone },
    { value: 'paypal', label: 'PayPal', icon: Wallet },
    { value: 'bank', label: 'Bank', icon: Landmark },
    { value: 'credit_card', label: 'Credit Card', icon: CreditCard },
    { value: 'crypto', label: 'Crypto', icon: Bitcoin },
    { value: 'other', label: 'Other', icon: MoreHorizontal },
];

const defaultColors: Record<string, string> = {
    gcash: '#007DFE',
    maya: '#00B274',
    paypal: '#003087',
    bank: '#1E3A5F',
    cash: '#6B7280',
    credit_card: '#18181B',
    crypto: '#F7931A',
    other: '#6366F1',
};

const presetColors = [
    '#6366F1', '#007DFE', '#00B274', '#F59E0B', '#EF4444', '#EC4899', '#8B5CF6', '#18181B', '#6B7280'
];

const getCardGradient = (type: string, color: string | null) => {
    const base = color || defaultColors[type] || '#6366F1';
    return `linear-gradient(135deg, ${base} 0%, ${base}CC 50%, ${base}99 100%)`;
};

const getTypeIcon = (type: string) => {
    return accountTypes.find(t => t.value === type)?.icon || Wallet;
};

const getTypeLabel = (type: string) => {
    return accountTypes.find(t => t.value === type)?.label || type;
};

const addAccount = (data: any) => {
    accountForm.name = data.name;
    accountForm.type = data.type;
    accountForm.color = data.color;
    accountForm.initial_balance = data.initial_balance;
    accountForm.credit_limit = data.credit_limit;
    accountForm.last4 = data.last4;
    accountForm.holder_name = data.holder_name;

    if (!accountForm.name) return;
    accountForm.post('/accounts', {
        onSuccess: () => {
            accountForm.reset();
            showAddModal.value = false;
        }
    });
};

const openEdit = (account: AccountItem) => {
    editingAccount.value = account;
    editForm.name = account.name;
    editForm.type = account.type;
    editForm.color = account.color || defaultColors[account.type] || '#6366F1';
    editForm.initial_balance = account.initial_balance.toString();
    editForm.credit_limit = account.credit_limit?.toString() || '';
    editForm.currency = account.currency;
    editForm.last4 = account.last4 || '';
    editForm.holder_name = account.holder_name || '';
    showEditModal.value = true;
};

const updateAccount = (data: any) => {
    if (!editingAccount.value) return;
    
    editForm.name = data.name;
    editForm.type = data.type;
    editForm.color = data.color;
    editForm.initial_balance = data.initial_balance;
    editForm.credit_limit = data.credit_limit;
    editForm.last4 = data.last4;
    editForm.holder_name = data.holder_name;

    if (!editForm.name) return;
    editForm.patch(`/accounts/${editingAccount.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editingAccount.value = null;
        }
    });
};

const deleteAccount = (account: AccountItem) => {
    accountToDelete.value = account;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!accountToDelete.value) return;
    router.delete(`/accounts/${accountToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            accountToDelete.value = null;
        }
    });
};

const openIncomeModal = (account: AccountItem) => {
    selectedAccountForTransaction.value = account;
    incomeForm.account_id = account.id;
    showIncomeModal.value = true;
};

const openExpenseModal = (account: AccountItem) => {
    selectedAccountForTransaction.value = account;
    expenseForm.account_id = account.id;
    showExpenseModal.value = true;
};

const addIncome = (data: any) => {
    incomeForm.amount = data.amount;
    incomeForm.date = data.date;
    incomeForm.category = data.category;
    incomeForm.description = data.description;
    incomeForm.account_id = data.account_id;
    incomeForm.post('/incomes', {
        onSuccess: () => {
            incomeForm.reset();
            incomeForm.date = getYYYYMMDD(new Date());
            showIncomeModal.value = false;
        }
    });
};

const addExpense = (data: any) => {
    expenseForm.amount = data.amount;
    expenseForm.date = data.date;
    expenseForm.category = data.category;
    expenseForm.description = data.description;
    expenseForm.account_id = data.account_id;
    expenseForm.post('/expenses', {
        onSuccess: () => {
            expenseForm.reset();
            expenseForm.date = getYYYYMMDD(new Date());
            showExpenseModal.value = false;
        }
    });
};
</script>

<template>
<Head title="Wallets" />
<AppLayout :breadcrumbs="breadcrumbs">
<div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">

<!-- Header Section (Compact Hero) -->
<div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 mb-1.5 sm:mb-2 animate-fade-up stagger-1">
    <!-- Background abstract shapes -->
    <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
    <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <div class="p-1 rounded-lg bg-primary/10 text-primary">
                    <Wallet class="w-3.5 h-3.5" />
                </div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Finances</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                My <span class="text-primary">Wallets</span>
            </h1>
            <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                Manage your payment accounts and keep track of your <span class="text-foreground font-bold">{{ formatCurrency(totalBalance) }}</span> total balance.
            </p>
        </div>

        <div class="flex-shrink-0 flex items-center gap-2">
            <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                <Plus class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" /> Add Wallet
            </button>
        </div>
    </div>

    <!-- Quick links: Bills & Income -->
    <div class="grid grid-cols-2 gap-2.5 sm:gap-3 mt-4 sm:mt-5">
        <Link href="/bills" class="group relative flex items-center gap-3 rounded-2xl border border-border/40 bg-card p-3 sm:p-4 hover:border-rose-500/40 hover:bg-rose-500/5 transition-all">
            <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 group-hover:bg-rose-500/15 transition-colors">
                <CalendarClock class="h-4 w-4 sm:h-5 sm:w-5" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Recurring</p>
                <p class="text-sm sm:text-base font-black tracking-tight">My Bills</p>
            </div>
            <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all" />
        </Link>
        <Link href="/incomes" class="group relative flex items-center gap-3 rounded-2xl border border-border/40 bg-card p-3 sm:p-4 hover:border-emerald-500/40 hover:bg-emerald-500/5 transition-all">
            <div class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500/15 transition-colors">
                <ArrowDownRight class="h-4 w-4 sm:h-5 sm:w-5" />
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Recurring</p>
                <p class="text-sm sm:text-base font-black tracking-tight">My Income</p>
            </div>
            <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all" />
        </Link>
    </div>
</div>

<!-- Wallet Cards Grid -->
<div v-if="accounts.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 animate-fade-up stagger-3">
    <div v-for="account in accounts" :key="account.id"
        class="group relative overflow-hidden rounded-[20px] p-5 sm:p-6 text-white shadow-xl transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] cursor-default"
        :style="{ background: getCardGradient(account.type, account.color) }">

        <!-- Card pattern overlay -->
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
            <div class="absolute top-4 right-4 w-32 h-32 border-2 border-white rounded-full"></div>
            <div class="absolute top-8 right-8 w-24 h-24 border-2 border-white rounded-full"></div>
            <div class="absolute -bottom-8 -left-8 w-40 h-40 border-2 border-white rounded-full"></div>
        </div>

        <!-- Card actions -->
        <div class="absolute top-4 right-4 flex items-center gap-1.5 z-20 opacity-0 group-hover:opacity-100 transition-all">
            <button @click.stop="openEdit(account)" class="p-2 rounded-xl bg-white/20 backdrop-blur-md hover:bg-white/30 text-white transition-all shadow-lg active:scale-95" title="Edit Wallet">
                <Pencil class="h-4 w-4" />
            </button>
            <button @click.stop="deleteAccount(account)" class="p-2 rounded-xl bg-white/20 backdrop-blur-md hover:bg-red-500/60 text-white transition-all shadow-lg active:scale-95" title="Delete Wallet">
                <Trash2 class="h-4 w-4" />
            </button>
        </div>

        <!-- Card content -->
        <div class="relative z-10">
            <!-- Name Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider opacity-60 mb-0.5">
                        {{ account.holder_name || 'Account Holder' }}
                    </p>
                    <h3 class="text-lg sm:text-xl font-black tracking-tight leading-none">{{ account.name }}</h3>
                </div>
            </div>

            <!-- Balance -->
            <p class="text-2xl sm:text-3xl font-black font-mono tracking-tight leading-none">
                {{ formatCurrency(account.balance) }}
            </p>

            <!-- Type badge (below balance) -->
            <div class="flex items-center gap-1.5 mt-4 mb-1 opacity-90">
                <div class="p-1 rounded-md bg-white/20">
                    <component :is="getTypeIcon(account.type)" class="h-3 w-3" />
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider">{{ getTypeLabel(account.type) }}</span>
                <span v-if="account.last4" class="text-[10px] font-mono opacity-70 ml-2">•••• {{ account.last4 }}</span>
            </div>

            <!-- Credit limit indicator -->
            <div v-if="account.credit_limit" class="mt-4">
                <div class="flex justify-between text-[10px] font-semibold opacity-80 mb-1.5">
                    <span>Credit Usage</span>
                    <span>{{ formatCurrency(account.credit_limit) }} limit</span>
                </div>
                <div class="h-1.5 rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full rounded-full bg-white/60 transition-all"
                        :style="{ width: Math.min(100, Math.abs(account.balance) / account.credit_limit * 100) + '%' }"></div>
                </div>
            </div>

            <!-- Monthly in/out chips -->
            <div class="flex items-center justify-between mt-5 pt-4 border-t border-white/15">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold">
                        <ArrowDownRight class="h-2.5 w-2.5" /> {{ formatCurrency(account.month_income) }}
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-bold">
                        <ArrowUpRight class="h-2.5 w-2.5" /> {{ formatCurrency(account.month_expense) }}
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="openIncomeModal(account)" class="p-1.5 rounded-lg bg-white/20 hover:bg-[--fin-gain] transition-colors" title="Add Income">
                        <ArrowDownRight class="h-3 w-3" />
                    </button>
                    <button @click="openExpenseModal(account)" class="p-1.5 rounded-lg bg-white/20 hover:bg-[--fin-loss] transition-colors" title="Add Expense">
                        <ArrowUpRight class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Empty state -->
<div v-else class="flex flex-col items-center justify-center py-20 animate-fade-up stagger-3">
    <div class="p-4 rounded-full bg-muted/30 mb-4">
        <CreditCard class="h-10 w-10 text-muted-foreground/30" />
    </div>
    <h3 class="text-lg font-bold text-foreground mb-1">No wallets yet</h3>
    <p class="text-sm text-muted-foreground mb-4">Add your first wallet to start tracking where your money lives.</p>
    <button @click="showAddModal = true" class="flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98] transition-all">
        <Plus class="h-4 w-4" /> Add Wallet
    </button>
</div>

</div>
</AppLayout>

<WalletEntryModal 
    v-model:open="showAddModal"
    title="Create Wallet"
    @submit="addAccount"
/>

<WalletEntryModal 
    v-model:open="showEditModal"
    title="Save Changes"
    :initialData="editingAccount || {}"
    @submit="updateAccount"
/>

<!-- Delete Wallet Confirmation Modal -->
<Dialog v-model:open="showDeleteModal">
    <DialogContent class="sm:max-w-[400px] max-w-[90vw] p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-5">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 rounded-xl bg-destructive/10 text-destructive">
                    <Trash2 class="h-5 w-5" />
                </div>
                <DialogTitle class="text-xl font-black tracking-tight font-display text-foreground">Delete Wallet?</DialogTitle>
            </div>
            <DialogDescription class="text-sm text-muted-foreground leading-relaxed">
                Are you sure you want to delete <span class="font-bold text-foreground">{{ accountToDelete?.name }}</span>? 
                This will unlink all transactions associated with this wallet. This action cannot be undone.
            </DialogDescription>
        </DialogHeader>

        <div class="flex gap-3 mt-6">
            <button @click="showDeleteModal = false" class="flex-1 rounded-2xl border border-border px-4 py-3 text-sm font-bold text-muted-foreground hover:bg-accent/50 transition-all active:scale-[0.98]">
                Cancel
            </button>
            <button @click="confirmDelete" class="flex-1 rounded-2xl bg-destructive px-4 py-3 text-sm font-bold text-destructive-foreground hover:bg-destructive/90 shadow-lg shadow-destructive/20 transition-all active:scale-[0.98]">
                Delete Wallet
            </button>
        </div>
    </DialogContent>
</Dialog>

<TransactionModal 
    v-model:open="showIncomeModal"
    type="income"
    title="Add Income"
    :initialAccountId="selectedAccountForTransaction?.id"
    @submit="addIncome"
/>

<TransactionModal 
    v-model:open="showExpenseModal"
    type="expense"
    title="Add Expense"
    :initialAccountId="selectedAccountForTransaction?.id"
    @submit="addExpense"
/>
</template>
