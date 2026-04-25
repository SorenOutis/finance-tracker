<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { formatCurrency } from '@/utils/currency';
import {
    CreditCard, Plus, Trash2, X, Pencil, Wallet, Smartphone, Landmark, Coins, Bitcoin, MoreHorizontal, ArrowDownRight, ArrowUpRight
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog';

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

const showAddModal = ref(false);
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

const addAccount = () => {
    if (!accountForm.name) return;
    if (!accountForm.color) accountForm.color = defaultColors[accountForm.type] || '#6366F1';
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

const updateAccount = () => {
    if (!editingAccount.value || !editForm.name) return;
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

const addIncome = () => {
    if (!incomeForm.amount) return;
    incomeForm.post('/incomes', {
        onSuccess: () => {
            incomeForm.reset();
            incomeForm.date = getYYYYMMDD(new Date());
            showIncomeModal.value = false;
        }
    });
};

const addExpense = () => {
    if (!expenseForm.amount) return;
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

        <div class="flex-shrink-0 flex items-center gap-3">
            <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                <Plus class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" /> Add Wallet
            </button>
        </div>
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

<!-- Add Wallet Modal -->
<Dialog v-model:open="showAddModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl flex flex-col max-h-[85vh]">
            <!-- Preview card -->
            <div class="p-3 sm:p-5 text-white relative overflow-hidden" :style="{ background: getCardGradient(accountForm.type, accountForm.color || null) }">
                <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                    <div class="absolute top-2 right-2 w-20 h-20 border-2 border-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-1 sm:mb-3">
                        <div class="p-1 rounded-lg bg-white/20"><component :is="getTypeIcon(accountForm.type)" class="h-3 w-3 sm:h-3.5 sm:w-3.5" /></div>
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider opacity-90">{{ getTypeLabel(accountForm.type) }}</span>
                        <p v-if="accountForm.last4" class="ml-auto text-[9px] sm:text-[11px] font-mono font-bold tracking-widest opacity-80 sm:hidden">•••• {{ accountForm.last4 }}</p>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-lg sm:text-xl font-black font-mono">{{ formatCurrency(Number(accountForm.initial_balance) || 0) }}</p>
                        <div class="hidden sm:block text-right">
                            <p class="text-[9px] uppercase tracking-wider opacity-50">{{ accountForm.holder_name || 'Account Holder' }}</p>
                            <p class="text-xs font-bold">{{ accountForm.name || 'Wallet Name' }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-end justify-between mt-3">
                        <p v-if="accountForm.last4" class="text-[11px] font-mono font-bold tracking-widest opacity-80">•••• {{ accountForm.last4 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto">
                <DialogHeader class="mb-2 sm:mb-5">
                    <DialogTitle class="text-lg sm:text-xl font-black tracking-tight font-display">Add Wallet</DialogTitle>
                    <DialogDescription class="hidden sm:block text-xs text-muted-foreground">Create a new payment account to track.</DialogDescription>
                </DialogHeader>

                <div class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Wallet Name</label>
                            <input v-model="accountForm.name" type="text" placeholder="e.g., My GCash" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Account Type</label>
                            <select v-model="accountForm.type" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Initial Balance</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                                <input v-model="accountForm.initial_balance" type="number" placeholder="0.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3 py-2 sm:pr-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                            </div>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Color Theme</label>
                            <div class="flex items-center gap-2 px-1">
                                <div v-for="color in presetColors" :key="color" 
                                    @click="accountForm.color = color"
                                    class="w-6 h-6 rounded-full cursor-pointer transition-all hover:scale-110 active:scale-95 border-2"
                                    :class="accountForm.color === color ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
                                    :style="{ backgroundColor: color }">
                                </div>
                                <div class="relative w-6 h-6 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer group">
                                    <input v-model="accountForm.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                    <Plus class="w-3 h-3 text-muted-foreground group-hover:text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Card Holder</label>
                            <input v-model="accountForm.holder_name" type="text" placeholder="Full Name" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Last 4 Digits</label>
                            <input v-model="accountForm.last4" type="text" maxlength="4" placeholder="••••" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50 font-mono" />
                        </div>
                    </div>

                    <div v-if="accountForm.type === 'credit_card'" class="animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Credit Limit</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                            <input v-model="accountForm.credit_limit" type="number" placeholder="50,000.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3 py-2 sm:pr-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 sm:gap-3 mt-4 sm:mt-8">
                    <button @click="showAddModal = false" class="flex-1 rounded-xl border border-border px-4 py-2 sm:py-2.5 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/50 active:scale-95">Cancel</button>
                    <button @click="addAccount" class="flex-2 rounded-xl bg-primary px-4 sm:px-8 py-2 sm:py-2.5 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95">Create Wallet</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>

<!-- Edit Wallet Modal -->
<Dialog v-model:open="showEditModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl flex flex-col max-h-[85vh]">
            <div class="p-3 sm:p-5 text-white relative overflow-hidden" :style="{ background: getCardGradient(editForm.type, editForm.color || null) }">
                <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                    <div class="absolute top-2 right-2 w-20 h-20 border-2 border-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-1 sm:mb-3">
                        <div class="p-1 rounded-lg bg-white/20"><component :is="getTypeIcon(editForm.type)" class="h-3 w-3 sm:h-3.5 sm:w-3.5" /></div>
                        <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider opacity-90">{{ getTypeLabel(editForm.type) }}</span>
                        <p v-if="editForm.last4" class="ml-auto text-[9px] sm:text-[11px] font-mono font-bold tracking-widest opacity-80 sm:hidden">•••• {{ editForm.last4 }}</p>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-lg sm:text-xl font-black font-mono">{{ formatCurrency(editingAccount?.balance ?? 0) }}</p>
                        <div class="hidden sm:block text-right">
                            <p class="text-[9px] uppercase tracking-wider opacity-50">{{ editForm.holder_name || 'Account Holder' }}</p>
                            <p class="text-xs font-bold">{{ editForm.name || 'Wallet Name' }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-end justify-between mt-3">
                        <p v-if="editForm.last4" class="text-[11px] font-mono font-bold tracking-widest opacity-80">•••• {{ editForm.last4 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto">
                <DialogHeader class="mb-2 sm:mb-5">
                    <DialogTitle class="text-lg sm:text-xl font-black tracking-tight font-display">Edit Wallet</DialogTitle>
                    <DialogDescription class="hidden sm:block text-xs text-muted-foreground">Update your account details.</DialogDescription>
                </DialogHeader>

                <div class="space-y-3 sm:space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Wallet Name</label>
                            <input v-model="editForm.name" type="text" placeholder="e.g., My GCash" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5" />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Account Type</label>
                            <select v-model="editForm.type" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Initial Balance</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                                <input v-model="editForm.initial_balance" type="number" placeholder="0.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3 py-2 sm:pr-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5" />
                            </div>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Color Theme</label>
                            <div class="flex items-center gap-2 px-1">
                                <div v-for="color in presetColors" :key="color" 
                                    @click="editForm.color = color"
                                    class="w-6 h-6 rounded-full cursor-pointer transition-all hover:scale-110 active:scale-95 border-2"
                                    :class="editForm.color === color ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
                                    :style="{ backgroundColor: color }">
                                </div>
                                <div class="relative w-6 h-6 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer group">
                                    <input v-model="editForm.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                    <Plus class="w-3 h-3 text-muted-foreground group-hover:text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Card Holder</label>
                            <input v-model="editForm.holder_name" type="text" placeholder="Full Name" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5" />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Last 4 Digits</label>
                            <input v-model="editForm.last4" type="text" maxlength="4" placeholder="••••" class="w-full rounded-xl border border-border bg-card px-3 py-2 sm:px-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 font-mono" />
                        </div>
                    </div>

                    <div v-if="editForm.type === 'credit_card'" class="animate-in fade-in slide-in-from-top-2 duration-300">
                        <label class="block text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1 ml-1">Credit Limit</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                            <input v-model="editForm.credit_limit" type="number" placeholder="50,000.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3 py-2 sm:pr-3.5 sm:py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 sm:gap-3 mt-4 sm:mt-8">
                    <button @click="showEditModal = false" class="flex-1 rounded-xl border border-border px-4 py-2 sm:py-2.5 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/50 active:scale-95">Cancel</button>
                    <button @click="updateAccount" class="flex-2 rounded-xl bg-primary px-4 sm:px-8 py-2 sm:py-2.5 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95">Save Changes</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>

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

<!-- Add Income Modal -->
<Dialog v-model:open="showIncomeModal">
    <DialogContent class="sm:max-w-[440px] max-w-[95vw] p-4 sm:p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-3 sm:mb-5">
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-[--fin-gain]/10">
                    <Wallet class="h-4 w-4 text-[--fin-gain]" />
                </div>
                <DialogTitle class="text-xl font-black tracking-tight font-display">Add Income</DialogTitle>
            </div>
            <DialogDescription class="text-xs text-muted-foreground">Log income to {{ selectedAccountForTransaction?.name }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-3 sm:space-y-4">
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Amount</label>
                <input v-model="incomeForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Date</label>
                <input v-model="incomeForm.date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Category</label>
                <input v-model="incomeForm.category" type="text" placeholder="e.g., Salary" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Description</label>
                <input v-model="incomeForm.description" type="text" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
        </div>
        <div class="flex gap-2 mt-5 sm:mt-6">
            <button @click="showIncomeModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="addIncome" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Income</button>
        </div>
    </DialogContent>
</Dialog>

<!-- Add Expense Modal -->
<Dialog v-model:open="showExpenseModal">
    <DialogContent class="sm:max-w-[440px] max-w-[95vw] p-4 sm:p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-3 sm:mb-5">
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-[--fin-loss]/10">
                    <ArrowUpRight class="h-4 w-4 text-[--fin-loss]" />
                </div>
                <DialogTitle class="text-xl font-black tracking-tight font-display">Add Expense</DialogTitle>
            </div>
            <DialogDescription class="text-xs text-muted-foreground">Log expense from {{ selectedAccountForTransaction?.name }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-3 sm:space-y-4">
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Amount</label>
                <input v-model="expenseForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Date</label>
                <input v-model="expenseForm.date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Category</label>
                <input v-model="expenseForm.category" type="text" placeholder="e.g., Food" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Description</label>
                <input v-model="expenseForm.description" type="text" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
        </div>
        <div class="flex gap-2 mt-5 sm:mt-6">
            <button @click="showExpenseModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="addExpense" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Expense</button>
        </div>
    </DialogContent>
</Dialog>
</template>
