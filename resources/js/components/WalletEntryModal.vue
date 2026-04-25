<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    X, ChevronRight, Wallet, Smartphone, Landmark, 
    Coins, Bitcoin, MoreHorizontal, Delete, Palette,
    User, Hash, CreditCard
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

const form = ref<WalletData>({
    name: '',
    type: 'cash',
    color: '#6366F1',
    initial_balance: '0',
    credit_limit: '',
    currency: 'PHP',
    last4: '',
    holder_name: '',
});

const handleKeypress = (key: string) => {
    if (key === 'backspace') {
        if (form.value.initial_balance.length <= 1) {
            form.value.initial_balance = '0';
        } else {
            form.value.initial_balance = form.value.initial_balance.slice(0, -1);
        }
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

const close = () => {
    emit('update:open', false);
};

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        if (props.initialData) {
            form.value = {
                name: props.initialData.name || '',
                type: props.initialData.type || 'cash',
                color: props.initialData.color || '#6366F1',
                initial_balance: props.initialData.initial_balance?.toString() || '0',
                credit_limit: props.initialData.credit_limit?.toString() || '',
                currency: props.initialData.currency || 'PHP',
                last4: props.initialData.last4 || '',
                holder_name: props.initialData.holder_name || '',
            };
        } else {
            form.value = {
                name: '',
                type: 'cash',
                color: '#6366F1',
                initial_balance: '0',
                credit_limit: '',
                currency: 'PHP',
                last4: '',
                holder_name: '',
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
                <div class="py-3 px-6 sm:p-6 text-center relative border-b border-border/40 flex-shrink-0">
                    <button @click="close" class="absolute right-4 top-3 sm:top-4 p-1.5 rounded-full hover:bg-muted/50 transition-colors z-20">
                        <X class="w-4 h-4 text-muted-foreground" />
                    </button>
                    
                    <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground mb-0.5 sm:mb-2">Initial Balance</p>
                    <div class="flex items-center justify-center gap-0.5">
                        <span class="text-lg sm:text-4xl font-light text-muted-foreground/50">₱</span>
                        <span class="text-2xl sm:text-6xl font-black tracking-tighter tabular-nums">{{ displayAmount }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-6 scrollbar-hide">
                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-2 sm:gap-3 mb-4 sm:mb-6">
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] sm:text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Name</label>
                            <div class="relative">
                                <Wallet class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <input type="text" v-model="form.name" placeholder="e.g., GCash" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all" />
                            </div>
                        </div>
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] sm:text-[8px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Type</label>
                            <div class="relative">
                                <div class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1">
                                    <component :is="accountTypes.find(t => t.value === form.type)?.icon || Wallet" class="w-3.5 h-3.5" />
                                </div>
                                <select v-model="form.type" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] sm:text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all appearance-none cursor-pointer">
                                    <option v-for="t in accountTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
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
                        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide">
                            <button v-for="color in presetColors" :key="color"
                                @click="form.color = color"
                                :class="[
                                    'w-7 h-7 rounded-full flex-shrink-0 transition-all duration-300 border-2',
                                    form.color === color ? 'border-primary scale-110' : 'border-transparent'
                                ]"
                                :style="{ backgroundColor: color }"
                            ></button>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <div class="relative group">
                            <label class="absolute left-2.5 top-1.5 text-[7px] font-bold uppercase tracking-wider text-muted-foreground/60 z-10">Holder</label>
                            <div class="relative">
                                <User class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-primary mt-1" />
                                <input type="text" v-model="form.holder_name" placeholder="Name" class="w-full bg-muted/30 border border-transparent hover:border-primary/20 rounded-xl pl-8 pr-3 py-2.5 pt-4 text-[12px] font-bold focus:outline-none focus:ring-2 focus:ring-primary/10 transition-all" />
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
