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

const presetColors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#18181B', '#6B7280'
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
</div>

<!-- Bills Grid -->
<div v-if="bills.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 animate-fade-up stagger-3">
    <div v-for="bill in bills" :key="bill.id"
        class="group relative overflow-hidden rounded-[24px] p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 text-white shadow-lg"
        :style="{ background: simpleGradient(bill.color, '#ef4444') }">
        
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-white/20">
                    <component :is="bill.type === 'income' ? Wallet : CalendarClock" class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="font-bold">{{ bill.name }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-70">{{ bill.category || 'No Category' }}</p>
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

        <div class="space-y-4">
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black font-mono">
                    {{ bill.type === 'income' ? '+' : '-' }}{{ formatCurrency(bill.amount) }}
                </span>
                <span class="text-[10px] font-bold opacity-70 uppercase tracking-widest">/ {{ bill.frequency }}</span>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-white/10">
                <div class="flex flex-col">
                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-70 mb-0.5">Next Due</span>
                    <span class="text-xs font-bold flex items-center gap-1.5">
                        <Clock class="h-3 w-3" /> {{ formatDate(bill.next_due_date) }}
                    </span>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20">
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
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl">
            <!-- Preview card -->
            <div class="p-5 text-white rounded-t-[32px] relative overflow-hidden transition-all duration-500" :style="{ background: simpleGradient(billForm.color, '#ef4444') }">
                <div class="absolute inset-0 opacity-[0.1] pointer-events-none">
                    <div class="absolute top-2 right-2 w-24 h-24 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-white/20">
                                <component :is="billForm.type === 'income' ? Wallet : CalendarClock" class="h-4 w-4" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">{{ billForm.type }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 uppercase tracking-wider">{{ billForm.frequency }}</span>
                    </div>
                    <h3 class="text-lg font-black tracking-tight leading-none mb-1">{{ billForm.name || 'Bill Name' }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-60 mb-4">{{ billForm.category || 'Category' }}</p>
                    
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black font-mono">
                            {{ billForm.type === 'income' ? '+' : '-' }}{{ formatCurrency(Number(billForm.amount) || 0) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <DialogHeader class="mb-5">
                    <DialogTitle class="text-xl font-black tracking-tight font-display">Add Bill</DialogTitle>
                    <DialogDescription class="text-xs text-muted-foreground">Create a new recurring payment or income.</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Bill Name</label>
                            <input v-model="billForm.name" type="text" placeholder="e.g., Netflix Subscription" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Amount</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                                <input v-model="billForm.amount" type="number" placeholder="0.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Type</label>
                            <select v-model="billForm.type" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Frequency</label>
                            <select v-model="billForm.frequency" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Next Due Date</label>
                            <input v-model="billForm.next_due_date" type="date" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 cursor-pointer" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Category</label>
                            <input v-model="billForm.category" type="text" placeholder="e.g., Entertainment" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Color Theme</label>
                            <div class="flex items-center gap-2 px-1 py-2">
                                <div v-for="color in presetColors.slice(0, 5)" :key="color" 
                                    @click="billForm.color = color"
                                    class="w-5 h-5 rounded-full cursor-pointer transition-all hover:scale-110 active:scale-95 border-2"
                                    :class="billForm.color === color ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
                                    :style="{ backgroundColor: color }">
                                </div>
                                <div class="relative w-5 h-5 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer group">
                                    <input v-model="billForm.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                    <Plus class="w-3 h-3 text-muted-foreground group-hover:text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button @click="showAddModal = false" class="flex-1 rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/50 active:scale-95">Cancel</button>
                    <button @click="addBill" class="flex-2 rounded-xl bg-primary px-8 py-2.5 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95">Add Bill</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>

<!-- Edit Bill Modal -->
<Dialog v-model:open="showEditModal">
    <DialogContent class="sm:max-w-[480px] max-w-[95vw] p-0 overflow-hidden border-none bg-transparent shadow-none">
        <div class="relative bg-card rounded-[32px] overflow-hidden border border-border/50 shadow-2xl">
            <!-- Preview card -->
            <div class="p-5 text-white rounded-t-[32px] relative overflow-hidden transition-all duration-500" :style="{ background: simpleGradient(editForm.color, '#ef4444') }">
                <div class="absolute inset-0 opacity-[0.1] pointer-events-none">
                    <div class="absolute top-2 right-2 w-24 h-24 border-2 border-white rounded-full"></div>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-white/20">
                                <component :is="editForm.type === 'income' ? Wallet : CalendarClock" class="h-4 w-4" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">{{ editForm.type }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/20 uppercase tracking-wider">{{ editForm.frequency }}</span>
                    </div>
                    <h3 class="text-lg font-black tracking-tight leading-none mb-1">{{ editForm.name || 'Bill Name' }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-60 mb-4">{{ editForm.category || 'Category' }}</p>
                    
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black font-mono">
                            {{ editForm.type === 'income' ? '+' : '-' }}{{ formatCurrency(Number(editForm.amount) || 0) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <DialogHeader class="mb-5">
                    <DialogTitle class="text-xl font-black tracking-tight font-display">Edit Bill</DialogTitle>
                    <DialogDescription class="text-xs text-muted-foreground">Update your bill details.</DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Bill Name</label>
                            <input v-model="editForm.name" type="text" placeholder="e.g., Netflix Subscription" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Amount</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-xs">₱</span>
                                <input v-model="editForm.amount" type="number" placeholder="0.00" class="w-full rounded-xl border border-border bg-card pl-7 pr-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Type</label>
                            <select v-model="editForm.type" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Frequency</label>
                            <select v-model="editForm.frequency" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 appearance-none cursor-pointer">
                                <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Next Due Date</label>
                            <input v-model="editForm.next_due_date" type="date" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 cursor-pointer" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Category</label>
                            <input v-model="editForm.category" type="text" placeholder="e.g., Entertainment" class="w-full rounded-xl border border-border bg-card px-3.5 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/5 placeholder:text-muted-foreground/50" />
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-muted-foreground mb-1.5 ml-1">Color Theme</label>
                            <div class="flex items-center gap-2 px-1 py-2">
                                <div v-for="color in presetColors.slice(0, 5)" :key="color" 
                                    @click="editForm.color = color"
                                    class="w-5 h-5 rounded-full cursor-pointer transition-all hover:scale-110 active:scale-95 border-2"
                                    :class="editForm.color === color ? 'border-primary ring-2 ring-primary/20' : 'border-transparent'"
                                    :style="{ backgroundColor: color }">
                                </div>
                                <div class="relative w-5 h-5 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer group">
                                    <input v-model="editForm.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                    <Plus class="w-3 h-3 text-muted-foreground group-hover:text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 pt-2 px-1">
                        <input v-model="editForm.is_active" type="checkbox" id="is_active" class="w-4 h-4 rounded border-border bg-card text-primary focus:ring-primary/20 transition-all cursor-pointer" />
                        <label for="is_active" class="text-xs font-bold uppercase tracking-wider text-muted-foreground cursor-pointer">Active Bill</label>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button @click="showEditModal = false" class="flex-1 rounded-xl border border-border px-4 py-2.5 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/50 active:scale-95">Cancel</button>
                    <button @click="updateBill" class="flex-2 rounded-xl bg-primary px-8 py-2.5 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-95">Save Changes</button>
                </div>
            </div>
        </div>
    </DialogContent>
</Dialog>
</template>
