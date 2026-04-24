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
const showIncomeModal = ref(false);
const showExpenseModal = ref(false);
const editingAccount = ref<AccountItem | null>(null);
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

const deleteAccount = (id: number) => {
    if (confirm('Delete this wallet? Transactions linked to it will be unlinked.')) {
        router.delete(`/accounts/${id}`, { preserveScroll: true });
    }
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

<!-- Header -->
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between animate-fade-up stagger-1">
    <div>
        <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-foreground">Wallets</h1>
        <p class="mt-2 text-sm text-muted-foreground/80 font-medium font-sans">Manage your payment accounts & wallets</p>
    </div>
    <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-2xl bg-primary px-4 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
        <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" /> Add Wallet
    </button>
</div>

<!-- Total Balance Card -->
<div class="surface-card p-5 rounded-[24px] animate-fade-up stagger-2">
    <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">Total Balance</p>
    <p class="text-3xl sm:text-4xl font-black font-display tracking-tight mt-1 text-foreground">{{ formatCurrency(totalBalance) }}</p>
    <p class="text-xs text-muted-foreground mt-1">Across {{ accounts.length }} wallet{{ accounts.length !== 1 ? 's' : '' }}</p>
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
        <div class="absolute top-3 right-3 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="openEdit(account)" class="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-colors">
                <Pencil class="h-3 w-3" />
            </button>
            <button @click="deleteAccount(account.id)" class="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-red-500/60 transition-colors">
                <Trash2 class="h-3 w-3" />
            </button>
        </div>

        <!-- Card content -->
        <div class="relative z-10">
            <!-- Type badge -->
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
                    <component :is="getTypeIcon(account.type)" class="h-4 w-4" />
                </div>
                <span class="text-xs font-bold uppercase tracking-wider opacity-90">{{ getTypeLabel(account.type) }}</span>
            </div>

            <!-- Balance -->
            <p class="text-2xl sm:text-3xl font-black font-mono tracking-tight leading-none">
                {{ formatCurrency(account.balance) }}
            </p>

            <!-- Credit limit indicator -->
            <div v-if="account.credit_limit" class="mt-2">
                <div class="flex justify-between text-[10px] font-semibold opacity-80 mb-1">
                    <span>Used</span>
                    <span>{{ formatCurrency(account.credit_limit) }} limit</span>
                </div>
                <div class="h-1.5 rounded-full bg-white/20 overflow-hidden">
                    <div class="h-full rounded-full bg-white/60 transition-all"
                        :style="{ width: Math.min(100, Math.abs(account.balance) / account.credit_limit * 100) + '%' }"></div>
                </div>
            </div>

            <!-- Bottom row: name + last4 -->
            <div class="flex items-end justify-between mt-5">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider opacity-60 mb-0.5">
                        {{ account.holder_name || 'Account Holder' }}
                    </p>
                    <p class="text-sm font-bold">{{ account.name }}</p>
                </div>
                <div v-if="account.last4" class="text-right">
                    <p class="text-xs font-mono font-bold tracking-widest opacity-80">
                        •••• {{ account.last4 }}
                    </p>
                </div>
            </div>

            <!-- Monthly in/out chips -->
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-white/15">
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
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl">
            <!-- Preview card -->
            <div class="p-5 text-white rounded-t-[32px] relative overflow-hidden" :style="{ background: getCardGradient(accountForm.type, accountForm.color || null) }">
                <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                    <div class="absolute top-2 right-2 w-20 h-20 border-2 border-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="p-1 rounded-lg bg-white/20"><component :is="getTypeIcon(accountForm.type)" class="h-3.5 w-3.5" /></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider opacity-90">{{ getTypeLabel(accountForm.type) }}</span>
                    </div>
                    <p class="text-xl font-black font-mono">{{ formatCurrency(Number(accountForm.initial_balance) || 0) }}</p>
                    <div class="flex items-end justify-between mt-3">
                        <div>
                            <p class="text-[9px] uppercase tracking-wider opacity-50">{{ accountForm.holder_name || 'Account Holder' }}</p>
                            <p class="text-xs font-bold">{{ accountForm.name || 'Wallet Name' }}</p>
                        </div>
                        <p v-if="accountForm.last4" class="text-[11px] font-mono font-bold tracking-widest opacity-80">•••• {{ accountForm.last4 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <DialogHeader class="mb-5">
                    <DialogTitle class="text-xl font-black tracking-tight font-display">Add Wallet</DialogTitle>
                    <DialogDescription class="text-xs text-muted-foreground">Create a new payment account to track.</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Wallet Name</label>
                            <input v-model="accountForm.name" type="text" placeholder="e.g., My GCash" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Type</label>
                            <select v-model="accountForm.type" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                                <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Initial Balance</label>
                            <input v-model="accountForm.initial_balance" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Color</label>
                            <input v-model="accountForm.color" type="color" :value="accountForm.color || defaultColors[accountForm.type]" class="mt-1 w-full h-[38px] rounded-lg border border-border bg-card cursor-pointer" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Last 4 digits</label>
                            <input v-model="accountForm.last4" type="text" maxlength="4" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Holder Name</label>
                            <input v-model="accountForm.holder_name" type="text" placeholder="Optional" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                    </div>
                    <div v-if="accountForm.type === 'credit_card'" >
                        <label class="block text-sm font-semibold text-muted-foreground">Credit Limit</label>
                        <input v-model="accountForm.credit_limit" type="number" placeholder="50000" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button @click="showAddModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
                    <button @click="addAccount" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Wallet</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>

<!-- Edit Wallet Modal -->
<Dialog v-model:open="showEditModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl">
            <div class="p-5 text-white rounded-t-[32px] relative overflow-hidden" :style="{ background: getCardGradient(editForm.type, editForm.color || null) }">
                <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                    <div class="absolute top-2 right-2 w-20 h-20 border-2 border-white rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-28 h-28 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="p-1 rounded-lg bg-white/20"><component :is="getTypeIcon(editForm.type)" class="h-3.5 w-3.5" /></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider opacity-90">{{ getTypeLabel(editForm.type) }}</span>
                    </div>
                    <p class="text-xl font-black font-mono">{{ formatCurrency(editingAccount?.balance ?? 0) }}</p>
                    <div class="flex items-end justify-between mt-3">
                        <div>
                            <p class="text-[9px] uppercase tracking-wider opacity-50">{{ editForm.holder_name || 'Account Holder' }}</p>
                            <p class="text-xs font-bold">{{ editForm.name || 'Wallet Name' }}</p>
                        </div>
                        <p v-if="editForm.last4" class="text-[11px] font-mono font-bold tracking-widest opacity-80">•••• {{ editForm.last4 }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <DialogHeader class="mb-5">
                    <DialogTitle class="text-xl font-black tracking-tight font-display">Edit Wallet</DialogTitle>
                    <DialogDescription class="text-xs text-muted-foreground">Update your wallet details.</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Wallet Name</label>
                            <input v-model="editForm.name" type="text" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Type</label>
                            <select v-model="editForm.type" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                                <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Initial Balance</label>
                            <input v-model="editForm.initial_balance" type="number" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Color</label>
                            <input v-model="editForm.color" type="color" class="mt-1 w-full h-[38px] rounded-lg border border-border bg-card cursor-pointer" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Last 4 digits</label>
                            <input v-model="editForm.last4" type="text" maxlength="4" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground">Holder Name</label>
                            <input v-model="editForm.holder_name" type="text" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>
                    </div>
                    <div v-if="editForm.type === 'credit_card'">
                        <label class="block text-sm font-semibold text-muted-foreground">Credit Limit</label>
                        <input v-model="editForm.credit_limit" type="number" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                    </div>
                </div>

                <div class="flex gap-2 mt-6">
                    <button @click="showEditModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
                    <button @click="updateAccount" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Save</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>

<!-- Add Income Modal -->
<Dialog v-model:open="showIncomeModal">
    <DialogContent class="sm:max-w-[440px] max-w-[95vw] p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-5">
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-[--fin-gain]/10">
                    <Wallet class="h-4 w-4 text-[--fin-gain]" />
                </div>
                <DialogTitle class="text-xl font-black tracking-tight font-display">Add Income</DialogTitle>
            </div>
            <DialogDescription class="text-xs text-muted-foreground">Log income to {{ selectedAccountForTransaction?.name }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
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
        <div class="flex gap-2 mt-6">
            <button @click="showIncomeModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="addIncome" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Income</button>
        </div>
    </DialogContent>
</Dialog>

<!-- Add Expense Modal -->
<Dialog v-model:open="showExpenseModal">
    <DialogContent class="sm:max-w-[440px] max-w-[95vw] p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-5">
            <div class="flex items-center gap-2">
                <div class="p-1.5 rounded-lg bg-[--fin-loss]/10">
                    <ArrowUpRight class="h-4 w-4 text-[--fin-loss]" />
                </div>
                <DialogTitle class="text-xl font-black tracking-tight font-display">Add Expense</DialogTitle>
            </div>
            <DialogDescription class="text-xs text-muted-foreground">Log expense from {{ selectedAccountForTransaction?.name }}</DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
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
        <div class="flex gap-2 mt-6">
            <button @click="showExpenseModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="addExpense" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Expense</button>
        </div>
    </DialogContent>
</Dialog>
</template>
