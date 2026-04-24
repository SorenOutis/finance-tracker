<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { formatCurrency } from '@/utils/currency';
import {
    Plus, Trash2, X, Pencil, CalendarClock, Clock, Wallet, TrendingDown, ArrowRight, CheckCircle2, AlertCircle
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog';

interface BillItem {
    id: number;
    name: string;
    amount: number;
    type: 'income' | 'expense';
    category: string | null;
    description: string | null;
    frequency: 'weekly' | 'monthly' | 'yearly';
    next_due_date: string;
    is_active: boolean;
    color: string | null;
}

interface Props {
    bills: BillItem[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Bills', href: '/bills' }];

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingBill = ref<BillItem | null>(null);

const getYYYYMMDD = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

const billForm = useForm({
    name: '',
    amount: '',
    type: 'expense',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: getYYYYMMDD(new Date()),
    color: '#3b82f6',
});

const editForm = useForm({
    name: '',
    amount: '',
    type: 'expense',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: '',
    is_active: true,
    color: '#3b82f6',
});

const frequencies = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
];

const addBill = () => {
    if (!billForm.name || !billForm.amount) return;
    billForm.post('/recurring-transactions', {
        onSuccess: () => {
            billForm.reset();
            showAddModal.value = false;
        }
    });
};

const openEdit = (bill: BillItem) => {
    editingBill.value = bill;
    editForm.name = bill.name;
    editForm.amount = bill.amount.toString();
    editForm.type = bill.type;
    editForm.category = bill.category || '';
    editForm.description = bill.description || '';
    editForm.frequency = bill.frequency;
    editForm.next_due_date = bill.next_due_date;
    editForm.is_active = bill.is_active;
    editForm.color = bill.color || '#3b82f6';
    showEditModal.value = true;
};

const updateBill = () => {
    if (!editingBill.value || !editForm.name) return;
    editForm.patch(`/recurring-transactions/${editingBill.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editingBill.value = null;
        }
    });
};

const deleteBill = (id: number) => {
    if (confirm('Delete this recurring bill?')) {
        router.delete(`/recurring-transactions/${id}`, { preserveScroll: true });
    }
};

const getStatusColor = (bill: BillItem) => {
    if (!bill.is_active) return 'bg-muted text-muted-foreground';
    const due = new Date(bill.next_due_date);
    const today = new Date();
    today.setHours(0,0,0,0);
    
    const diff = Math.ceil((due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    
    if (diff < 0) return 'bg-destructive/10 text-destructive';
    if (diff <= 3) return 'bg-amber-500/10 text-amber-600';
    return 'bg-primary/10 text-primary';
};

const getStatusLabel = (bill: BillItem) => {
    if (!bill.is_active) return 'Inactive';
    const due = new Date(bill.next_due_date);
    const today = new Date();
    today.setHours(0,0,0,0);
    
    const diff = Math.ceil((due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    
    if (diff < 0) return 'Overdue';
    if (diff === 0) return 'Due today';
    if (diff === 1) return 'Due tomorrow';
    return `In ${diff} days`;
};

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
<Head title="Bills" />
<AppLayout :breadcrumbs="breadcrumbs">
<div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">

<!-- Header -->
<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between animate-fade-up stagger-1">
    <div>
        <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-foreground">Bills</h1>
        <p class="mt-2 text-sm text-muted-foreground/80 font-medium font-sans">Manage your recurring payments and bills</p>
    </div>
    <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-2xl bg-primary px-4 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
        <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" /> Add Bill
    </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 gap-4 animate-fade-up stagger-2">
    <div class="surface-card p-5 rounded-[24px]">
        <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">Upcoming This Month</p>
        <p class="text-2xl sm:text-3xl font-black font-display tracking-tight mt-1 text-foreground">
            {{ bills.filter(b => b.type === 'expense' && b.is_active).length }}
        </p>
    </div>
    <div class="surface-card p-5 rounded-[24px]">
        <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80 font-sans">Total Monthly</p>
        <p class="text-2xl sm:text-3xl font-black font-display tracking-tight mt-1 text-foreground">
            {{ formatCurrency(bills.filter(b => b.type === 'expense' && b.is_active && b.frequency === 'monthly').reduce((acc, b) => acc + b.amount, 0)) }}
        </p>
    </div>
</div>

<!-- Bills Grid -->
<div v-if="bills.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 animate-fade-up stagger-3">
    <div v-for="bill in bills" :key="bill.id"
        class="group relative overflow-hidden rounded-[24px] border border-border/60 bg-card p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 premium-hover shadow-sm"
        :style="bill.color ? { borderLeft: `4px solid ${bill.color}` } : {}">
        
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl" :style="bill.color ? { backgroundColor: `${bill.color}15`, color: bill.color } : {}" :class="!bill.color ? (bill.type === 'income' ? 'bg-[--fin-gain]/10 text-[--fin-gain]' : 'bg-[--fin-loss]/10 text-[--fin-loss]') : ''">
                    <component :is="bill.type === 'income' ? Wallet : CalendarClock" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="font-bold text-foreground">{{ bill.name }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60">{{ bill.category || 'No Category' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openEdit(bill)" class="p-1.5 rounded-lg hover:bg-muted transition-colors">
                    <Pencil class="h-3.5 w-3.5 text-muted-foreground" />
                </button>
                <button @click="deleteBill(bill.id)" class="p-1.5 rounded-lg hover:bg-destructive/10 transition-colors">
                    <Trash2 class="h-3.5 w-3.5 text-destructive" />
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black font-mono" :class="bill.type === 'income' ? 'text-[--fin-gain]' : 'text-[--fin-loss]'">
                    {{ bill.type === 'income' ? '+' : '-' }}{{ formatCurrency(bill.amount) }}
                </span>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">/ {{ bill.frequency }}</span>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-border/10">
                <div class="flex flex-col">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground mb-0.5">Next Due</span>
                    <span class="text-xs font-bold text-foreground flex items-center gap-1.5">
                        <Clock class="h-3 w-3" /> {{ formatDate(bill.next_due_date) }}
                    </span>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold" :class="getStatusColor(bill)">
                    {{ getStatusLabel(bill) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Empty state -->
<div v-else class="flex flex-col items-center justify-center py-20 animate-fade-up stagger-3">
    <div class="p-4 rounded-full bg-muted/30 mb-4">
        <CalendarClock class="h-10 w-10 text-muted-foreground/30" />
    </div>
    <h3 class="text-lg font-bold text-foreground mb-1">No bills yet</h3>
    <p class="text-sm text-muted-foreground mb-4">Add your first recurring payment to stay on top of your finances.</p>
    <button @click="showAddModal = true" class="flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98] transition-all">
        <Plus class="h-4 w-4" /> Add Bill
    </button>
</div>

</div>
</AppLayout>

<!-- Add Bill Modal -->
<Dialog v-model:open="showAddModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-5">
            <DialogTitle class="text-xl font-black tracking-tight font-display">Add Bill</DialogTitle>
            <DialogDescription class="text-xs text-muted-foreground">Create a new recurring payment or income.</DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-muted-foreground">Bill Name</label>
                    <input v-model="billForm.name" type="text" placeholder="e.g., Netflix Subscription" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Amount</label>
                    <input v-model="billForm.amount" type="number" placeholder="0.00" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Type</label>
                    <select v-model="billForm.type" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Frequency</label>
                    <select v-model="billForm.frequency" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                        <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Next Due Date</label>
                    <input v-model="billForm.next_due_date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Category</label>
                <input v-model="billForm.category" type="text" placeholder="e.g., Entertainment" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Card Color</label>
                <div class="flex items-center gap-3 mt-1">
                    <input v-model="billForm.color" type="color" class="h-9 w-16 rounded border border-border bg-card p-1 cursor-pointer" />
                    <span class="text-xs font-mono text-muted-foreground">{{ billForm.color }}</span>
                </div>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <button @click="showAddModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="addBill" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Add Bill</button>
        </div>
    </DialogContent>
</Dialog>

<!-- Edit Bill Modal -->
<Dialog v-model:open="showEditModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-6 rounded-[32px] border border-border/50 bg-card shadow-2xl">
        <DialogHeader class="mb-5">
            <DialogTitle class="text-xl font-black tracking-tight font-display">Edit Bill</DialogTitle>
            <DialogDescription class="text-xs text-muted-foreground">Update your bill details.</DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-muted-foreground">Bill Name</label>
                    <input v-model="editForm.name" type="text" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Amount</label>
                    <input v-model="editForm.amount" type="number" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Type</label>
                    <select v-model="editForm.type" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Frequency</label>
                    <select v-model="editForm.frequency" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10">
                        <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-muted-foreground">Next Due Date</label>
                    <input v-model="editForm.next_due_date" type="date" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Category</label>
                <input v-model="editForm.category" type="text" class="mt-1 w-full rounded-lg border border-border bg-card px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-muted-foreground">Card Color</label>
                <div class="flex items-center gap-3 mt-1">
                    <input v-model="editForm.color" type="color" class="h-9 w-16 rounded border border-border bg-card p-1 cursor-pointer" />
                    <span class="text-xs font-mono text-muted-foreground">{{ editForm.color }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input v-model="editForm.is_active" type="checkbox" id="is_active" class="rounded border-border bg-card text-primary focus:ring-primary/10" />
                <label for="is_active" class="text-sm font-semibold text-muted-foreground">Active</label>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <button @click="showEditModal = false" class="flex-1 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-accent/50">Cancel</button>
            <button @click="updateBill" class="flex-1 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Save Changes</button>
        </div>
    </DialogContent>
</Dialog>
</template>
