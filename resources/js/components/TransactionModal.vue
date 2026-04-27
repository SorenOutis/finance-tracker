<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    X, Calendar, FileText, ChevronRight, 
    Wallet, ShoppingCart, Car, Home, 
    Utensils, Coffee, Zap, Heart,
    Smartphone, Landmark, Coins, Bitcoin,
    MoreHorizontal, Delete
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { formatCurrency } from '@/utils/currency';

interface Category {
    name: string;
    icon: any;
    color: string;
}

interface Account {
    id: number | string;
    name: string;
    type: string;
}

const props = defineProps<{
    open: boolean;
    type: 'income' | 'expense';
    title: string;
    accounts?: Account[];
    initialAmount?: string;
    initialCategory?: string;
    initialDate?: string;
    initialDescription?: string;
    initialAccountId?: string | number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', data: any): void;
}>();

const amount = ref(props.initialAmount || '0');
const date = ref(props.initialDate || new Date().toISOString().split('T')[0]);
const category = ref(props.initialCategory || '');
const description = ref(props.initialDescription || '');
const accountId = ref(props.initialAccountId || '');

const categories = computed(() => {
    if (props.type === 'income') {
        return [
            { name: 'Salary', icon: Wallet, color: 'bg-emerald-500' },
            { name: 'Business', icon: Landmark, color: 'bg-blue-500' },
            { name: 'Investment', icon: Zap, color: 'bg-amber-500' },
            { name: 'Gift', icon: Heart, color: 'bg-pink-500' },
            { name: 'Other', icon: MoreHorizontal, color: 'bg-slate-500' },
        ];
    }
    return [
        { name: 'Dining', icon: Utensils, color: 'bg-orange-500' },
        { name: 'Shopping', icon: ShoppingCart, color: 'bg-blue-500' },
        { name: 'Travel', icon: Car, color: 'bg-indigo-500' },
        { name: 'Housing', icon: Home, color: 'bg-rose-500' },
        { name: 'Grocery', icon: ShoppingCart, color: 'bg-emerald-500' },
        { name: 'Cafe', icon: Coffee, color: 'bg-amber-600' },
        { name: 'Health', icon: Heart, color: 'bg-red-500' },
        { name: 'Other', icon: MoreHorizontal, color: 'bg-slate-500' },
    ];
});

const handleKeypress = (key: string) => {
    if (key === 'backspace') {
        if (amount.value.length <= 1) {
            amount.value = '0';
        } else {
            amount.value = amount.value.slice(0, -1);
        }
        return;
    }

    if (key === '.') {
        if (!amount.value.includes('.')) {
            amount.value += '.';
        }
        return;
    }

    if (amount.value === '0') {
        amount.value = key;
    } else {
        // Limit to 2 decimal places
        if (amount.value.includes('.')) {
            const parts = amount.value.split('.');
            if (parts[1].length >= 2) return;
        }
        amount.value += key;
    }
};

const displayAmount = computed(() => {
    const num = parseFloat(amount.value) || 0;
    return num.toLocaleString('en-US', {
        minimumFractionDigits: amount.value.includes('.') ? amount.value.split('.')[1].length : 0,
        maximumFractionDigits: 2
    });
});

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    const today = new Date();
    if (d.toDateString() === today.toDateString()) return 'Today';
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const handleSubmit = () => {
    emit('submit', {
        amount: amount.value,
        date: date.value,
        category: category.value,
        description: description.value,
        account_id: accountId.value
    });
};

const close = () => {
    emit('update:open', false);
};

// Reset amount when opening
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        amount.value = props.initialAmount || '0';
        category.value = props.initialCategory || '';
        date.value = props.initialDate || new Date().toISOString().split('T')[0];
        description.value = props.initialDescription || '';
        accountId.value = props.initialAccountId || '';
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogContent class="p-0 overflow-hidden border-none bg-background sm:max-w-[420px] max-w-[92vw] w-full rounded-[32px] shadow-2xl duration-300">
            <div class="flex flex-col h-auto max-h-[95vh] sm:max-h-[85vh]">
                <!-- Header -->
                <div class="py-3 px-6 sm:p-6 text-center relative flex-shrink-0">
                    <button @click="close" class="absolute right-4 top-3 sm:top-4 p-1.5 rounded-full hover:bg-muted/50 transition-colors z-20">
                        <X class="w-4 h-4 text-muted-foreground" />
                    </button>
                    
                    <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground mb-0.5 sm:mb-2">{{ type === 'income' ? 'Amount Received' : 'Amount Spent' }}</p>
                    <div class="flex items-center justify-center gap-0.5">
                        <span class="text-lg sm:text-4xl font-light text-muted-foreground/50">₱</span>
                        <span class="text-2xl sm:text-6xl font-black tracking-tighter tabular-nums">{{ displayAmount }}</span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 px-6 mb-6">
                    <div class="relative group">
                        <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Date</label>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                            <input type="date" v-model="date" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary pointer-events-none mt-1">{{ formatDate(date) }}</span>
                        </div>
                    </div>
                    <div class="relative group">
                        <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Note</label>
                        <div class="relative">
                            <FileText class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                            <input type="text" v-model="description" placeholder="Add note..." class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                        </div>
                    </div>
                </div>

                <!-- Wallet Selector (If multiple accounts) -->
                <div v-if="accounts?.length" class="px-6 mb-6">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground mb-3 ml-1">Select Wallet</p>
                    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button v-for="acc in accounts" :key="acc.id"
                            @click="accountId = acc.id"
                            :class="[
                                'flex-shrink-0 flex items-center gap-2 px-4 py-2 rounded-xl border-2 transition-all whitespace-nowrap',
                                accountId === acc.id ? 'border-primary bg-primary/5 text-primary' : 'border-border/40 bg-muted/20 text-muted-foreground hover:border-border'
                            ]"
                        >
                            <span class="text-[10px] font-bold">{{ acc.name }}</span>
                        </button>
                    </div>
                </div>

                <!-- Categories -->
                <div class="px-6 mb-6">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Category</p>
                        <button class="text-[9px] font-bold text-primary hover:underline uppercase tracking-wider">View All</button>
                    </div>
                    <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide snap-x">
                        <button v-for="cat in categories" :key="cat.name"
                            @click="category = cat.name"
                            class="flex flex-col items-center gap-2 flex-shrink-0 snap-start group"
                        >
                            <div :class="[
                                'w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm',
                                category === cat.name ? cat.color + ' text-white scale-110 shadow-lg shadow-' + cat.color.split('-')[1] + '-500/20' : 'bg-muted/40 text-muted-foreground group-hover:bg-muted group-hover:scale-105'
                            ]">
                                <component :is="cat.icon" class="w-6 h-6" />
                            </div>
                            <span :class="['text-[10px] font-bold transition-colors', category === cat.name ? 'text-foreground' : 'text-muted-foreground']">{{ cat.name }}</span>
                        </button>
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
                        {{ type === 'income' ? 'Add Income' : 'Add Expense' }}
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
