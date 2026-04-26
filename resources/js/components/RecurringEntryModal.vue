<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    X, ChevronRight, Wallet, CalendarClock, Clock, 
    TrendingUp, TrendingDown, Delete, Palette,
    Type, Hash, Calendar, FileText, Tag, Sparkles, AlignLeft
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { formatCurrency } from '@/utils/currency';

interface RecurringData {
    name: string;
    amount: string;
    type: 'income' | 'expense';
    category: string;
    description: string;
    frequency: 'weekly' | 'monthly' | 'yearly';
    next_due_date: string;
    is_active: boolean;
    color: string;
}

const props = defineProps<{
    open: boolean;
    title: string;
    initialData?: Partial<RecurringData>;
    fixedType?: 'income' | 'expense';
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', data: any): void;
}>();

const frequencies = [
    { value: 'weekly', label: 'Weekly', sub: 'Every 7 days' },
    { value: 'monthly', label: 'Monthly', sub: 'Every month' },
    { value: 'yearly', label: 'Yearly', sub: 'Once a year' },
];

const presetColors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#18181B', '#6B7280'
];

const expenseCategories = ['Subscription', 'Rent', 'Utilities', 'Internet', 'Insurance', 'Loan', 'Food', 'Transport'];
const incomeCategories = ['Salary', 'Freelance', 'Business', 'Investment', 'Allowance', 'Bonus', 'Rental', 'Other'];

const quickAmounts = [100, 500, 1000, 5000, 10000];

const form = ref<RecurringData>({
    name: '',
    amount: '0',
    type: props.fixedType || 'expense',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: new Date().toISOString().split('T')[0],
    is_active: true,
    color: props.fixedType === 'income' ? '#10B981' : '#EF4444',
});

const handleKeypress = (key: string) => {
    if (key === 'backspace') {
        if (form.value.amount.length <= 1) {
            form.value.amount = '0';
        } else {
            form.value.amount = form.value.amount.slice(0, -1);
        }
        return;
    }

    if (key === '.') {
        if (!form.value.amount.includes('.')) {
            form.value.amount += '.';
        }
        return;
    }

    if (form.value.amount === '0') {
        form.value.amount = key;
    } else {
        if (form.value.amount.includes('.')) {
            const parts = form.value.amount.split('.');
            if (parts[1].length >= 2) return;
        }
        form.value.amount += key;
    }
};

const displayAmount = computed(() => {
    const num = parseFloat(form.value.amount) || 0;
    return num.toLocaleString('en-US', {
        minimumFractionDigits: form.value.amount.includes('.') ? form.value.amount.split('.')[1].length : 0,
        maximumFractionDigits: 2
    });
});

const handleSubmit = () => {
    emit('submit', { ...form.value });
};

const scrollIntoView = (e: FocusEvent) => {
    setTimeout(() => {
        (e.target as HTMLElement)?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }, 300);
};

const applyQuickAmount = (n: number) => {
    form.value.amount = n.toString();
};

const categorySuggestions = computed(() => form.value.type === 'income' ? incomeCategories : expenseCategories);

const daysUntilDue = computed(() => {
    if (!form.value.next_due_date) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(form.value.next_due_date);
    due.setHours(0, 0, 0, 0);
    const diff = Math.round((due.getTime() - today.getTime()) / 86400000);
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    if (diff < 0) return `${Math.abs(diff)} days ago`;
    return `In ${diff} days`;
});

const close = () => {
    emit('update:open', false);
};

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.initialData) {
            form.value = {
                name: props.initialData.name || '',
                amount: props.initialData.amount?.toString() || '0',
                type: props.initialData.type || props.fixedType || 'expense',
                category: props.initialData.category || '',
                description: props.initialData.description || '',
                frequency: props.initialData.frequency || 'monthly',
                next_due_date: props.initialData.next_due_date || new Date().toISOString().split('T')[0],
                is_active: props.initialData.is_active ?? true,
                color: props.initialData.color || (props.fixedType === 'income' ? '#10B981' : '#EF4444'),
            };
        } else {
            form.value = {
                name: '',
                amount: '0',
                type: props.fixedType || 'expense',
                category: '',
                description: '',
                frequency: 'monthly',
                next_due_date: new Date().toISOString().split('T')[0],
                is_active: true,
                color: props.fixedType === 'income' ? '#10B981' : '#EF4444',
            };
        }
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogContent class="block p-0 overflow-hidden border-none bg-background w-[calc(100vw-2rem)] sm:w-full sm:max-w-[420px] rounded-[32px] shadow-2xl duration-300 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex flex-col h-auto max-h-[95vh] sm:max-h-[85vh]">
                <!-- Header -->
                <div class="py-3 px-6 sm:p-6 text-center relative border-b border-border/40 transition-colors duration-500 flex-shrink-0" :style="{ borderBottomColor: form.color + '40' }">
                    <button @click="close" class="absolute right-4 top-3 sm:top-4 p-1.5 rounded-full hover:bg-muted/50 transition-colors z-20">
                        <X class="w-4 h-4 text-muted-foreground" />
                    </button>
                    
                    <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground mb-0.5 sm:mb-2">{{ form.type === 'income' ? 'Recurring Income' : 'Recurring Bill' }}</p>
                    <div class="flex items-center justify-center gap-0.5" :style="{ color: form.color }">
                        <span class="text-lg sm:text-4xl font-light opacity-50">₱</span>
                        <span class="text-2xl sm:text-6xl font-black tracking-tighter tabular-nums">{{ displayAmount }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5 scrollbar-hide">
                    <!-- Name -->
                    <div class="relative group mb-3">
                        <label class="absolute left-3 top-2 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">{{ form.type === 'income' ? 'Income Name' : 'Bill Name' }}</label>
                        <div class="relative">
                            <FileText class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 mt-1" :style="{ color: form.color }" />
                            <input type="text" v-model="form.name" @focus="scrollIntoView" :placeholder="form.type === 'income' ? 'e.g., Monthly Salary' : 'e.g., Netflix Subscription'" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-3 pt-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                        </div>
                    </div>

                    <!-- Frequency Chips -->
                    <div class="mb-3">
                        <div class="flex items-center gap-2 mb-2 ml-1">
                            <Clock class="w-3 h-3 text-muted-foreground" />
                            <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Frequency</p>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button v-for="f in frequencies" :key="f.value"
                                @click="form.frequency = f.value as any"
                                :class="[
                                    'flex flex-col items-center justify-center px-2 py-2 rounded-xl text-[11px] font-bold transition-all duration-200 active:scale-95 border',
                                    form.frequency === f.value
                                        ? 'text-white shadow-md'
                                        : 'bg-muted/30 text-muted-foreground border-transparent hover:border-primary/20'
                                ]"
                                :style="form.frequency === f.value ? { backgroundColor: form.color, borderColor: form.color } : {}"
                            >
                                <span>{{ f.label }}</span>
                                <span class="text-[8px] font-medium opacity-70 mt-0.5">{{ f.sub }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Next Due Date with countdown -->
                    <div class="relative group mb-3">
                        <label class="absolute left-3 top-2 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Next Due Date</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 mt-1" :style="{ color: form.color }" />
                            <input type="date" v-model="form.next_due_date" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-24 py-3 pt-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none" />
                            <span v-if="daysUntilDue" class="absolute right-3 top-1/2 -translate-y-1/2 mt-1 px-2 py-0.5 rounded-md text-[9px] font-bold tracking-wider" :style="{ backgroundColor: form.color + '20', color: form.color }">{{ daysUntilDue }}</span>
                        </div>
                    </div>

                    <!-- Category with quick suggestions -->
                    <div class="mb-3">
                        <div class="flex items-center gap-2 mb-2 ml-1">
                            <Tag class="w-3 h-3 text-muted-foreground" />
                            <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Category</p>
                        </div>
                        <div class="relative group mb-2">
                            <Type class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" :style="{ color: form.color }" />
                            <input type="text" v-model="form.category" @focus="scrollIntoView" :placeholder="form.type === 'income' ? 'e.g., Salary' : 'e.g., Entertainment'" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-3 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                        </div>
                        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide -mx-1 px-1">
                            <button v-for="cat in categorySuggestions" :key="cat"
                                @click="form.category = cat"
                                :class="[
                                    'flex-shrink-0 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all active:scale-95',
                                    form.category === cat ? 'text-white' : 'bg-muted/40 text-muted-foreground hover:bg-muted/60'
                                ]"
                                :style="form.category === cat ? { backgroundColor: form.color } : {}"
                            >{{ cat }}</button>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="relative group mb-3">
                        <label class="absolute left-3 top-2 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Note (optional)</label>
                        <div class="relative">
                            <AlignLeft class="absolute left-3 top-5 w-4 h-4" :style="{ color: form.color }" />
                            <textarea v-model="form.description" @focus="scrollIntoView" rows="2" placeholder="Add a note..." class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-3 pt-5 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40 resize-none" />
                        </div>
                    </div>

                    <!-- Color Theme -->
                    <div class="mb-3">
                        <div class="flex items-center gap-2 mb-2 ml-1">
                            <Palette class="w-3 h-3 text-muted-foreground" />
                            <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Color Theme</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button v-for="color in presetColors" :key="color"
                                @click="form.color = color"
                                :class="[
                                    'w-7 h-7 rounded-full flex-shrink-0 transition-all duration-300 border-2',
                                    form.color === color ? 'border-foreground scale-110 shadow-lg' : 'border-transparent hover:scale-105'
                                ]"
                                :style="{ backgroundColor: color }"
                            ></button>
                            <label class="relative w-7 h-7 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer flex-shrink-0">
                                <input v-model="form.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                <Palette class="w-3 h-3 text-muted-foreground" />
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-1">
                        <label for="is_active_recurring" class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.is_active" type="checkbox" id="is_active_recurring" class="w-4 h-4 rounded border-border bg-card text-primary focus:ring-primary/20 transition-all cursor-pointer" />
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Active</span>
                        </label>
                        <div v-if="!fixedType" class="flex items-center gap-1">
                             <button @click="form.type = 'expense'" :class="['px-3 py-1 rounded-lg text-[9px] font-bold transition-all', form.type === 'expense' ? 'bg-destructive/10 text-destructive' : 'text-muted-foreground']">Expense</button>
                             <button @click="form.type = 'income'" :class="['px-3 py-1 rounded-lg text-[9px] font-bold transition-all', form.type === 'income' ? 'bg-emerald-500/10 text-emerald-600' : 'text-muted-foreground']">Income</button>
                        </div>
                    </div>
                </div>

                <!-- Keypad -->
                <div class="bg-muted/20 px-3 sm:px-6 pt-3 sm:pt-5 pb-3 sm:pb-6 rounded-t-[24px] sm:rounded-t-[40px] flex-shrink-0">
                    <!-- Quick Amount Chips -->
                    <div class="flex gap-1.5 overflow-x-auto pb-2 sm:pb-3 mb-2 sm:mb-3 scrollbar-hide justify-center">
                        <button v-for="n in quickAmounts" :key="n" @click="applyQuickAmount(n)"
                            class="flex-shrink-0 px-2.5 py-1 rounded-lg bg-muted/40 hover:bg-primary/10 hover:text-primary text-[10px] font-bold tracking-wider transition-all active:scale-95"
                        >
                            ₱{{ n >= 1000 ? (n/1000) + 'K' : n }}
                        </button>
                    </div>

                    <div class="grid grid-cols-3 gap-y-0.5 sm:gap-y-2 gap-x-2 sm:gap-x-6 max-w-[260px] sm:max-w-xs mx-auto mb-2 sm:mb-4">
                        <button v-for="key in ['1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '0', 'backspace']" :key="key"
                            @click="handleKeypress(key)"
                            class="h-9 sm:h-11 flex items-center justify-center text-base sm:text-xl font-bold hover:bg-muted/50 rounded-full transition-all active:scale-90"
                        >
                            <template v-if="key === 'backspace'">
                                <Delete class="w-5 h-5 text-muted-foreground" />
                            </template>
                            <template v-else>
                                {{ key }}
                            </template>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button @click="handleSubmit" :disabled="!form.name || form.amount === '0'"
                        class="w-full bg-black text-white dark:bg-white dark:text-black rounded-[16px] py-3 sm:py-5 text-[10px] sm:text-sm font-black tracking-tight hover:opacity-90 active:scale-[0.98] transition-all shadow-xl shadow-black/10 flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100"
                    >
                        {{ title }}
                        <ChevronRight class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
