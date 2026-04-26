<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import {
    Plus, Trash2, X, Pencil, CalendarClock, Clock, Wallet, TrendingDown, ArrowRight, CheckCircle2, AlertCircle, CreditCard, ArrowDownRight, ChevronRight
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog';
import RecurringEntryModal from '@/components/RecurringEntryModal.vue';

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

const showAddModal = useStorage('bills-add-modal', false);
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

const presetColors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#18181B', '#6B7280'
];

const addBill = (data: any) => {
    billForm.name = data.name;
    billForm.amount = data.amount;
    billForm.type = data.type;
    billForm.category = data.category;
    billForm.description = data.description;
    billForm.frequency = data.frequency;
    billForm.next_due_date = data.next_due_date;
    billForm.color = data.color;

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

const updateBill = (data: any) => {
    if (!editingBill.value) return;

    editForm.name = data.name;
    editForm.amount = data.amount;
    editForm.type = data.type;
    editForm.category = data.category;
    editForm.description = data.description;
    editForm.frequency = data.frequency;
    editForm.next_due_date = data.next_due_date;
    editForm.is_active = data.is_active;
    editForm.color = data.color;

    if (!editForm.name) return;
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

const simpleGradient = (color: string | null, def: string) => { 
    const b = color || def; 
    return `linear-gradient(135deg, ${b} 0%, ${b}CC 50%, ${b}99 100%)`; 
};
</script>

<template>
<Head title="Bills" />
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
                    <CalendarClock class="w-3.5 h-3.5" />
                </div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Recurring</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                My <span class="text-primary">Bills</span>
            </h1>
            <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                You have <span class="text-foreground font-bold">{{ bills.filter(b => b.type === 'expense' && b.is_active).length }}</span> upcoming bills this month totaling <span class="text-foreground font-bold">{{ formatCurrency(bills.filter(b => b.type === 'expense' && b.is_active && b.frequency === 'monthly').reduce((acc, b) => acc + b.amount, 0)) }}</span>.
            </p>
        </div>

        <div class="flex-shrink-0 flex items-center gap-3">
            <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                <Plus class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" /> Add Bill
            </button>
        </div>
    </div>

    <!-- Quick links: Wallets & Income -->
    <div class="flex gap-2 mt-3">
        <Link href="/wallets" class="group flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-3 py-1.5 text-[11px] font-bold text-foreground transition-all">
            <CreditCard class="h-3.5 w-3.5 text-muted-foreground" />
            <span>Wallets</span>
            <ChevronRight class="h-3 w-3 text-muted-foreground group-hover:translate-x-0.5 transition-transform" />
        </Link>
        <Link href="/incomes" class="group flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-3 py-1.5 text-[11px] font-bold text-foreground transition-all">
            <ArrowDownRight class="h-3.5 w-3.5 text-muted-foreground" />
            <span>Income</span>
            <ChevronRight class="h-3 w-3 text-muted-foreground group-hover:translate-x-0.5 transition-transform" />
        </Link>
    </div>
</div>

<!-- Bills Grid -->
<div v-if="bills.length" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 animate-fade-up stagger-3">
    <div v-for="bill in bills" :key="bill.id"
        class="group relative overflow-hidden rounded-[18px] sm:rounded-[24px] p-3 sm:p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 text-white shadow-lg"
        :style="{ background: simpleGradient(bill.color, '#ef4444') }">
        
        <div class="flex items-start justify-between mb-3 sm:mb-4">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="p-1.5 sm:p-2.5 rounded-lg sm:rounded-xl bg-white/20 flex-shrink-0">
                    <component :is="bill.type === 'income' ? Wallet : CalendarClock" class="h-3.5 w-3.5 sm:h-5 sm:w-5" />
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-xs sm:text-base truncate">{{ bill.name }}</h3>
                    <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider opacity-70 truncate">{{ bill.category || 'No Category' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openEdit(bill)" class="p-1.5 rounded-lg hover:bg-white/20 transition-colors">
                    <Pencil class="h-3.5 w-3.5" />
                </button>
                <button @click="deleteBill(bill.id)" class="p-1.5 rounded-lg hover:bg-red-500/40 transition-colors">
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            <div class="flex items-baseline gap-1 flex-wrap">
                <span class="text-base sm:text-2xl font-black font-mono">
                    {{ bill.type === 'income' ? '+' : '-' }}{{ formatCurrency(bill.amount) }}
                </span>
                <span class="text-[8px] sm:text-[10px] font-bold opacity-70 uppercase tracking-widest">/ {{ bill.frequency }}</span>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pt-3 sm:pt-4 border-t border-white/10">
                <div class="flex flex-col">
                    <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider opacity-70 mb-0.5">Next Due</span>
                    <span class="text-[10px] sm:text-xs font-bold flex items-center gap-1 sm:gap-1.5">
                        <Clock class="h-2.5 w-2.5 sm:h-3 sm:w-3" /> {{ formatDate(bill.next_due_date) }}
                    </span>
                </div>
                <span class="self-start sm:self-auto inline-flex items-center px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[8px] sm:text-[10px] font-bold bg-white/20">
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

<RecurringEntryModal 
    v-model:open="showAddModal"
    title="Create Bill"
    fixedType="expense"
    @submit="addBill"
/>

<RecurringEntryModal 
    v-model:open="showEditModal"
    title="Save Changes"
    fixedType="expense"
    :initialData="editingBill || {}"
    @submit="updateBill"
/>
</template>
