<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    X, ChevronRight, Wallet, CalendarClock, Clock, 
    TrendingUp, TrendingDown, Delete, Palette,
    Type, Hash, Calendar, FileText
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
    { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' },
    { value: 'yearly', label: 'Yearly' },
];

const presetColors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#18181B', '#6B7280'
];

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
        <DialogContent class="p-0 overflow-hidden border-none bg-background sm:max-w-[420px] max-w-[92vw] w-full fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 rounded-[32px] shadow-2xl duration-300">
            <div class="flex flex-col h-auto max-h-[95vh] sm:max-h-[85vh]">
                <!-- Header -->
                <div class="py-3 px-6 sm:p-6 text-center relative border-b border-border/40 transition-colors duration-500 flex-shrink-0" :style="{ borderBottomColor: form.color + '20' }">
                    <button @click="close" class="absolute right-4 top-3 sm:top-4 p-1.5 rounded-full hover:bg-muted/50 transition-colors z-20">
                        <X class="w-4 h-4 text-muted-foreground" />
                    </button>
                    
                    <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground mb-0.5 sm:mb-2">{{ form.type === 'income' ? 'Recurring Income' : 'Recurring Bill' }}</p>
                    <div class="flex items-center justify-center gap-0.5" :style="{ color: form.color }">
                        <span class="text-lg sm:text-4xl font-light opacity-50">₱</span>
                        <span class="text-2xl sm:text-6xl font-black tracking-tighter tabular-nums">{{ displayAmount }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-6 scrollbar-hide">
                    <!-- Info Grid -->
                    <div class="grid grid-cols-1 gap-3 mb-4">
                        <div class="relative group">
                            <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Bill Name</label>
                            <div class="relative">
                                <FileText class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                                <input type="text" v-model="form.name" placeholder="e.g., Netflix Subscription" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="relative group">
                            <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Frequency</label>
                            <div class="relative">
                                <Clock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                                <select v-model="form.frequency" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none cursor-pointer">
                                    <option v-for="f in frequencies" :key="f.value" :value="f.value">{{ f.label }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Next Due Date</label>
                            <div class="relative">
                                <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                                <input type="date" v-model="form.next_due_date" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none" />
                            </div>
                        </div>
                    </div>

                    <!-- Category & Color -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="relative group">
                            <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Category</label>
                            <div class="relative">
                                <Type class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                                <input type="text" v-model="form.category" placeholder="e.g., Entertainment" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Color Theme</label>
                            <div class="flex items-center gap-1.5 h-full pt-4 ml-1">
                                <button v-for="color in presetColors.slice(0, 5)" :key="color"
                                    @click="form.color = color"
                                    :class="[
                                        'w-5 h-5 rounded-full flex-shrink-0 transition-all duration-300 border-2',
                                        form.color === color ? 'border-primary scale-110' : 'border-transparent'
                                    ]"
                                    :style="{ backgroundColor: color }"
                                ></button>
                                <div class="relative w-5 h-5 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer group flex-shrink-0">
                                    <input v-model="form.color" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                    <Palette class="w-3 h-3 text-muted-foreground group-hover:text-primary" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between px-2 mb-2">
                        <div class="flex items-center gap-3">
                            <input v-model="form.is_active" type="checkbox" id="is_active_recurring" class="w-4 h-4 rounded border-border bg-card text-primary focus:ring-primary/20 transition-all cursor-pointer" />
                            <label for="is_active_recurring" class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground cursor-pointer">Active Subscription</label>
                        </div>
                        <div v-if="!fixedType" class="flex items-center gap-2">
                             <button @click="form.type = 'expense'" :class="['px-3 py-1 rounded-lg text-[9px] font-bold transition-all', form.type === 'expense' ? 'bg-destructive/10 text-destructive' : 'text-muted-foreground']">Expense</button>
                             <button @click="form.type = 'income'" :class="['px-3 py-1 rounded-lg text-[9px] font-bold transition-all', form.type === 'income' ? 'bg-emerald-500/10 text-emerald-600' : 'text-muted-foreground']">Income</button>
                        </div>
                    </div>
                </div>

                <!-- Keypad -->
                <div class="bg-muted/20 p-3 sm:p-6 pt-4 sm:pt-8 rounded-t-[24px] sm:rounded-t-[40px] flex-shrink-0">
                    <div class="grid grid-cols-3 gap-y-1 sm:gap-y-4 gap-x-4 sm:gap-x-8 max-w-[220px] sm:max-w-xs mx-auto mb-2 sm:mb-8">
                        <button v-for="key in ['1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '0', 'backspace']" :key="key"
                            @click="handleKeypress(key)"
                            class="h-8 sm:h-12 flex items-center justify-center text-base sm:text-2xl font-bold hover:bg-muted/50 rounded-full transition-all active:scale-90"
                        >
                            <template v-if="key === 'backspace'">
                                <Delete class="w-6 h-6 text-muted-foreground" />
                            </template>
                            <template v-else>
                                {{ key }}
                            </template>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button @click="handleSubmit" 
                        class="w-full bg-black text-white dark:bg-white dark:text-black rounded-[16px] py-3 sm:py-5 text-[10px] sm:text-sm font-black tracking-tight hover:opacity-90 active:scale-[0.98] transition-all shadow-xl shadow-black/10 flex items-center justify-center gap-1.5"
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
