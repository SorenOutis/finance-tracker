<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import { formatCurrency } from '@/utils/currency';
import {
    Plus, Trash2, X, Pencil, CalendarClock, Clock, Wallet, TrendingUp, ArrowRight, CheckCircle2, AlertCircle
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
} from '@/components/ui/dialog';
import RecurringEntryModal from '@/components/RecurringEntryModal.vue';

interface IncomeItem {
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
    incomes: IncomeItem[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Income', href: '/incomes' }];

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingIncome = ref<IncomeItem | null>(null);

const getYYYYMMDD = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

const incomeForm = useForm({
    name: '',
    amount: '',
    type: 'income',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: getYYYYMMDD(new Date()),
    color: '#10b981',
});

const editForm = useForm({
    name: '',
    amount: '',
    type: 'income',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: '',
    is_active: true,
    color: '#10b981',
});

const frequencies = [
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
];

const presetColors = [
    '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#EF4444', '#F59E0B', '#18181B', '#6B7280'
];

const addIncome = (data: any) => {
    incomeForm.name = data.name;
    incomeForm.amount = data.amount;
    incomeForm.type = data.type;
    incomeForm.category = data.category;
    incomeForm.description = data.description;
    incomeForm.frequency = data.frequency;
    incomeForm.next_due_date = data.next_due_date;
    incomeForm.color = data.color;

    if (!incomeForm.name || !incomeForm.amount) return;
    incomeForm.post('/recurring-transactions', {
        onSuccess: () => {
            incomeForm.reset();
            showAddModal.value = false;
        }
    });
};

const openEdit = (income: IncomeItem) => {
    editingIncome.value = income;
    editForm.name = income.name;
    editForm.amount = income.amount.toString();
    editForm.type = income.type;
    editForm.category = income.category || '';
    editForm.description = income.description || '';
    editForm.frequency = income.frequency;
    editForm.next_due_date = income.next_due_date;
    editForm.is_active = income.is_active;
    editForm.color = income.color || '#10b981';
    showEditModal.value = true;
};

const updateIncome = (data: any) => {
    if (!editingIncome.value) return;

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
    editForm.patch(`/recurring-transactions/${editingIncome.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editingIncome.value = null;
        }
    });
};

const deleteIncome = (id: number) => {
    if (confirm('Delete this recurring income?')) {
        router.delete(`/recurring-transactions/${id}`, { preserveScroll: true });
    }
};

const getStatusColor = (income: IncomeItem) => {
    if (!income.is_active) return 'bg-muted text-muted-foreground';
    return 'bg-primary/10 text-primary';
};

const getStatusLabel = (income: IncomeItem) => {
    if (!income.is_active) return 'Inactive';
    return 'Active';
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
<Head title="Incomes" />
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
                    <TrendingUp class="w-3.5 h-3.5" />
                </div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Earnings</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                My <span class="text-primary">Incomes</span>
            </h1>
            <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                You have <span class="text-foreground font-bold">{{ incomes.filter(i => i.is_active).length }}</span> active income sources totaling <span class="text-foreground font-bold">{{ formatCurrency(incomes.filter(i => i.is_active && i.frequency === 'monthly').reduce((acc, i) => acc + i.amount, 0)) }}</span> per month.
            </p>
        </div>

        <div class="flex-shrink-0 flex items-center gap-3">
            <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                <Plus class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" /> Add Income
            </button>
        </div>
    </div>
</div>

<!-- Incomes Grid -->
<div v-if="incomes.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 animate-fade-up stagger-3">
    <div v-for="income in incomes" :key="income.id"
        class="group relative overflow-hidden rounded-[24px] p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 text-white shadow-lg"
        :style="{ background: simpleGradient(income.color, '#10b981') }">
        
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-white/20">
                    <Wallet class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="font-bold">{{ income.name }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ income.category || 'No Category' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openEdit(income)" class="p-1.5 rounded-lg hover:bg-white/20 transition-colors">
                    <Pencil class="h-3.5 w-3.5" />
                </button>
                <button @click="deleteIncome(income.id)" class="p-1.5 rounded-lg hover:bg-red-500/40 transition-colors">
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black font-mono">
                    +{{ formatCurrency(income.amount) }}
                </span>
                <span class="text-[10px] font-bold opacity-70 uppercase tracking-widest">/ {{ income.frequency }}</span>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-white/10">
                <div class="flex flex-col">
                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-70 mb-0.5">Next Payout</span>
                    <span class="text-xs font-bold flex items-center gap-1.5">
                        <Clock class="h-3 w-3" /> {{ formatDate(income.next_due_date) }}
                    </span>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20">
                    {{ getStatusLabel(income) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Empty state -->
<div v-else class="flex flex-col items-center justify-center py-20 animate-fade-up stagger-3">
    <div class="p-4 rounded-full bg-muted/30 mb-4">
        <Wallet class="h-10 w-10 text-muted-foreground/30" />
    </div>
    <h3 class="text-lg font-bold text-foreground mb-1">No incomes yet</h3>
    <p class="text-sm text-muted-foreground mb-4">Add your first recurring income source.</p>
    <button @click="showAddModal = true" class="flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98] transition-all">
        <Plus class="h-4 w-4" /> Add Income
    </button>
</div>

</div>
</AppLayout>

<RecurringEntryModal 
    v-model:open="showAddModal"
    title="Create Income"
    fixedType="income"
    @submit="addIncome"
/>

<RecurringEntryModal 
    v-model:open="showEditModal"
    title="Save Changes"
    fixedType="income"
    :initialData="editingIncome || {}"
    @submit="updateIncome"
/>
</template>
