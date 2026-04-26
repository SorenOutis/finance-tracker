<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    X, ChevronRight, Wallet, Smartphone, Landmark, 
    Coins, Bitcoin, MoreHorizontal, Delete, Palette,
    User, Hash, CreditCard, Check, Sparkles, DollarSign
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';
import { formatCurrency } from '@/utils/currency';

interface WalletData {
    name: string;
    type: string;
    color: string;
    initial_balance: string;
    credit_limit: string;
    currency: string;
    last4: string;
    holder_name: string;
}

const props = defineProps<{
    open: boolean;
    title: string;
    initialData?: Partial<WalletData>;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'submit', data: any): void;
}>();

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

const presetColors = [
    '#6366F1', '#007DFE', '#00B274', '#F59E0B', '#EF4444', '#EC4899', '#8B5CF6', '#18181B', '#6B7280'
];

const defaultColors: Record<string, string> = {
    cash: '#6B7280',
    gcash: '#007DFE',
    maya: '#00B274',
    paypal: '#003087',
    bank: '#1E3A5F',
    credit_card: '#18181B',
    crypto: '#F7931A',
    other: '#6366F1',
};

const currencies = [
    { value: 'PHP', symbol: '₱' },
    { value: 'USD', symbol: '$' },
    { value: 'EUR', symbol: '€' },
    { value: 'JPY', symbol: '¥' },
];

const quickAmounts = [100, 500, 1000, 5000, 10000];

const colorManuallySet = ref(false);

const form = ref<WalletData>({
    name: '',
    type: 'cash',
    color: '#6B7280',
    initial_balance: '0',
    credit_limit: '',
    currency: 'PHP',
    last4: '',
    holder_name: '',
});

const pickColor = (color: string) => {
    form.value.color = color;
    colorManuallySet.value = true;
};

const pickType = (type: string) => {
    form.value.type = type;
    if (!colorManuallySet.value) {
        form.value.color = defaultColors[type] || '#6366F1';
    }
};

const applyQuickAmount = (n: number) => {
    form.value.initial_balance = n.toString();
};

const currencySymbol = computed(() => currencies.find(c => c.value === form.value.currency)?.symbol || '₱');

const handleKeypress = (key: string) => {
    if (key === 'backspace') {
        if (form.value.initial_balance.length <= 1) {
            form.value.initial_balance = '0';
        } else {
            form.value.initial_balance = form.value.initial_balance.slice(0, -1);
        }
        return;
    }

    if (key === '00') {
        if (form.value.initial_balance === '0') return;
        if (form.value.initial_balance.includes('.')) {
            const parts = form.value.initial_balance.split('.');
            if (parts[1].length >= 1) return;
        }
        form.value.initial_balance += '00';
        return;
    }

    if (key === '.') {
        if (!form.value.initial_balance.includes('.')) {
            form.value.initial_balance += '.';
        }
        return;
    }

    if (form.value.initial_balance === '0') {
        form.value.initial_balance = key;
    } else {
        if (form.value.initial_balance.includes('.')) {
            const parts = form.value.initial_balance.split('.');
            if (parts[1].length >= 2) return;
        }
        form.value.initial_balance += key;
    }
};

const displayAmount = computed(() => {
    const num = parseFloat(form.value.initial_balance) || 0;
    return num.toLocaleString('en-US', {
        minimumFractionDigits: form.value.initial_balance.includes('.') ? form.value.initial_balance.split('.')[1].length : 0,
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

const close = () => {
    emit('update:open', false);
};

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.initialData && Object.keys(props.initialData).length > 0) {
            form.value = {
                name: props.initialData.name || '',
                type: props.initialData.type || 'cash',
                color: props.initialData.color || defaultColors[props.initialData.type || 'cash'] || '#6366F1',
                initial_balance: props.initialData.initial_balance?.toString() || '0',
                credit_limit: props.initialData.credit_limit?.toString() || '',
                currency: props.initialData.currency || 'PHP',
                last4: props.initialData.last4 || '',
                holder_name: props.initialData.holder_name || '',
            };
            colorManuallySet.value = true;
        } else {
            form.value = {
                name: '',
                type: 'cash',
                color: defaultColors.cash,
                initial_balance: '0',
                credit_limit: '',
                currency: 'PHP',
                last4: '',
                holder_name: '',
            };
            colorManuallySet.value = false;
        }
    }
});

const cardGradient = computed(() => `linear-gradient(135deg, ${form.value.color} 0%, ${form.value.color}CC 50%, ${form.value.color}99 100%)`);

const typeIcon = computed(() => accountTypes.find(t => t.value === form.value.type)?.icon || Wallet);
const typeLabel = computed(() => accountTypes.find(t => t.value === form.value.type)?.label || 'Wallet');
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogContent class="block p-0 overflow-hidden border-none bg-background w-[calc(100vw-2rem)] sm:w-full sm:max-w-[420px] rounded-[32px] shadow-2xl duration-300 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex flex-col h-auto max-h-[95vh] sm:max-h-[85vh]">
                <!-- Header with Live Card Preview -->
                <div class="px-4 pt-4 pb-3 sm:px-6 sm:pt-6 sm:pb-4 relative flex-shrink-0">
                    <button @click="close" class="absolute right-3 top-3 sm:right-4 sm:top-4 p-1.5 rounded-full hover:bg-muted/50 transition-colors z-20">
                        <X class="w-4 h-4 text-muted-foreground" />
                    </button>

                    <div class="relative rounded-2xl p-4 sm:p-5 overflow-hidden shadow-xl transition-all duration-500" :style="{ background: cardGradient }">
                        <div class="absolute -top-12 -right-12 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
                        <div class="absolute -bottom-10 -left-10 w-28 h-28 rounded-full bg-black/10 blur-2xl"></div>

                        <div class="relative flex items-start justify-between mb-4 sm:mb-6">
                            <div>
                                <p class="text-[8px] sm:text-[9px] font-bold uppercase tracking-[0.2em] text-white/70">Initial Balance</p>
                                <p class="text-[11px] sm:text-xs font-bold text-white/90 mt-0.5 truncate max-w-[160px]">{{ form.name || 'New Wallet' }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white/15 backdrop-blur-sm">
                                <component :is="typeIcon" class="w-3 h-3 text-white" />
                                <span class="text-[9px] font-bold text-white">{{ typeLabel }}</span>
                            </div>
                        </div>

                        <div class="relative flex items-baseline gap-1 text-white">
                            <span class="text-base sm:text-2xl font-light opacity-70">{{ currencySymbol }}</span>
                            <span class="text-2xl sm:text-4xl font-black tracking-tighter tabular-nums">{{ displayAmount }}</span>
                        </div>

                        <div class="relative flex items-end justify-between mt-3 sm:mt-4">
                            <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-white/70">{{ form.holder_name || 'Card Holder' }}</p>
                            <p class="text-[10px] sm:text-xs font-mono font-bold text-white/90 tracking-widest">{{ form.last4 ? '•••• ' + form.last4 : '•••• ••••' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 sm:py-5 scrollbar-hide">
                    <!-- Type Chips -->
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2 ml-1">
                            <Sparkles class="w-3 h-3 text-muted-foreground" />
                            <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Account Type</p>
                        </div>
                        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide -mx-1 px-1">
                            <button v-for="t in accountTypes" :key="t.value"
                                @click="pickType(t.value)"
                                :class="[
                                    'flex-shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold transition-all duration-200 active:scale-95 border',
                                    form.type === t.value
                                        ? 'bg-primary text-primary-foreground border-primary shadow-md shadow-primary/20'
                                        : 'bg-muted/30 text-muted-foreground border-transparent hover:border-primary/20'
                                ]"
                            >
                                <component :is="t.icon" class="w-3.5 h-3.5" />
                                {{ t.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Name + Currency -->
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-4">
                        <div class="relative group col-span-2">
                            <label class="absolute left-2.5 top-1.5 text-[7px] sm:text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Name</label>
                            <div class="relative">
                                <Wallet class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <input type="text" v-model="form.name" @focus="scrollIntoView" placeholder="e.g., GCash" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all" />
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] sm:text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Currency</label>
                            <div class="relative">
                                <DollarSign class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <select v-model="form.currency" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-2 py-2.5 pt-4 text-[12px] sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none cursor-pointer">
                                    <option v-for="c in currencies" :key="c.value" :value="c.value">{{ c.value }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Color Theme -->
                    <div class="mb-4 sm:mb-6">
                        <div class="flex items-center gap-2 mb-2 ml-1">
                            <Palette class="w-3 h-3 text-muted-foreground" />
                            <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Color Theme</p>
                        </div>
                        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide items-center">
                            <button v-for="color in presetColors" :key="color"
                                @click="pickColor(color)"
                                :class="[
                                    'w-7 h-7 rounded-full flex-shrink-0 transition-all duration-300 border-2 flex items-center justify-center',
                                    form.color === color ? 'border-foreground scale-110 shadow-lg' : 'border-transparent hover:scale-105'
                                ]"
                                :style="{ backgroundColor: color }"
                            >
                                <Check v-if="form.color === color" class="w-3.5 h-3.5 text-white drop-shadow" />
                            </button>
                            <label class="relative w-7 h-7 rounded-full border-2 border-dashed border-border flex items-center justify-center hover:border-primary transition-colors cursor-pointer flex-shrink-0">
                                <input :value="form.color" @input="pickColor(($event.target as HTMLInputElement).value)" type="color" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                                <Palette class="w-3 h-3 text-muted-foreground" />
                            </label>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Holder</label>
                            <div class="relative">
                                <User class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <input type="text" v-model="form.holder_name" @focus="scrollIntoView" placeholder="Name" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all" />
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Last 4</label>
                            <div class="relative">
                                <Hash class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <input type="text" v-model="form.last4" maxlength="4" placeholder="••••" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all font-mono" />
                            </div>
                        </div>
                    </div>

                        <div v-if="form.type === 'credit_card'" class="animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="relative group">
                                <label class="absolute left-3 top-2.5 text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Credit Limit</label>
                                <div class="relative">
                                    <CreditCard class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary mt-1" />
                                    <input type="number" v-model="form.credit_limit" placeholder="50000" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-2xl pl-10 pr-4 py-4 pt-6 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all placeholder:text-muted-foreground/40" />
                                </div>
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
                            {{ currencySymbol }}{{ n >= 1000 ? (n/1000) + 'K' : n }}
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
                            <template v-else>{{ key }}</template>
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <button @click="handleSubmit" :disabled="!form.name"
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
