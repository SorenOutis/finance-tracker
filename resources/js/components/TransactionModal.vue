<script setup lang="ts">
import {
    X, Calendar, FileText, ChevronRight,
    Wallet, ShoppingCart, Car, Home,
    Utensils, Coffee, Zap, Heart,
    Landmark, MoreHorizontal,
    ArrowDownRight, ArrowUpRight,
} from 'lucide-vue-next';
import { ref, computed, watch, nextTick } from 'vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';

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

const amount = ref('');
const date = ref(new Date().toISOString().split('T')[0]);
const category = ref('');
const description = ref('');
const accountId = ref<string | number>('');
const amountInput = ref<HTMLInputElement | null>(null);

const categories = computed(() => {
    if (props.type === 'income') {
        return [
            { name: 'Salary',     icon: Wallet,         color: 'bg-emerald-500' },
            { name: 'Business',   icon: Landmark,       color: 'bg-blue-500'    },
            { name: 'Investment', icon: Zap,            color: 'bg-amber-500'   },
            { name: 'Gift',       icon: Heart,          color: 'bg-pink-500'    },
            { name: 'Other',      icon: MoreHorizontal, color: 'bg-slate-500'   },
        ];
    }
    return [
        { name: 'Dining',   icon: Utensils,       color: 'bg-orange-500'  },
        { name: 'Shopping', icon: ShoppingCart,   color: 'bg-blue-500'    },
        { name: 'Travel',   icon: Car,            color: 'bg-indigo-500'  },
        { name: 'Housing',  icon: Home,           color: 'bg-rose-500'    },
        { name: 'Grocery',  icon: ShoppingCart,   color: 'bg-emerald-500' },
        { name: 'Cafe',     icon: Coffee,         color: 'bg-amber-600'   },
        { name: 'Health',   icon: Heart,          color: 'bg-red-500'     },
        { name: 'Other',    icon: MoreHorizontal, color: 'bg-slate-500'   },
    ];
});

const quickAmounts = [50, 100, 500, 1000];

const sanitizeAmount = (raw: string) => {
    let v = raw.replace(/[^0-9.]/g, '');
    const firstDot = v.indexOf('.');
    if (firstDot !== -1) {
        v = v.slice(0, firstDot + 1) + v.slice(firstDot + 1).replace(/\./g, '');
        const [intPart, decPart] = v.split('.');
        v = intPart + '.' + (decPart ?? '').slice(0, 2);
    }
    return v;
};

const onAmountInput = (e: Event) => {
    amount.value = sanitizeAmount((e.target as HTMLInputElement).value);
};

const applyQuickAmount = (n: number) => {
    amount.value = String(n);
    amountInput.value?.focus();
};

const formatDateLabel = (dateStr: string) => {
    const d = new Date(dateStr);
    const today = new Date();
    if (d.toDateString() === today.toDateString()) return 'Today';
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const close = () => emit('update:open', false);

const handleSubmit = () => {
    if (!amount.value || parseFloat(amount.value) <= 0) return;
    emit('submit', {
        amount: amount.value,
        date: date.value,
        category: category.value,
        description: description.value,
        account_id: accountId.value,
    });
};

const submitDisabled = computed(() => !amount.value || parseFloat(amount.value) <= 0);

const accent = computed(() =>
    props.type === 'income'
        ? { fg: 'text-emerald-500', soft: 'bg-emerald-500/10', icon: ArrowDownRight, label: 'Amount Received', verb: 'Income' }
        : { fg: 'text-rose-500',    soft: 'bg-rose-500/10',    icon: ArrowUpRight,   label: 'Amount Spent',    verb: 'Expense' },
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            amount.value      = props.initialAmount      ?? '';
            category.value    = props.initialCategory    ?? '';
            date.value        = props.initialDate        ?? new Date().toISOString().split('T')[0];
            description.value = props.initialDescription ?? '';
            accountId.value   = props.initialAccountId   ?? '';
            nextTick(() => amountInput.value?.focus());
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="close">
        <DialogContent
            :showCloseButton="false"
            class="
                p-0 gap-0 border-0 bg-background shadow-2xl overflow-hidden
                duration-300
                data-[state=open]:zoom-in-100 data-[state=closed]:zoom-out-100
                data-[state=open]:slide-in-from-bottom data-[state=closed]:slide-out-to-bottom
                top-auto bottom-0 left-0 right-0 translate-x-0 translate-y-0
                w-full max-w-none rounded-b-none rounded-t-[24px]
                md:top-auto md:bottom-6 md:left-[50%] md:right-auto
                md:translate-x-[-50%] md:translate-y-0
                md:max-w-[520px] md:rounded-[24px]
            "
        >
            <!-- ============================================================
                 MOBILE LAYOUT (default, hidden on md+)
                 - Bottom sheet
                 - Hero amount, full-width inputs, finger-friendly tiles
                 ============================================================ -->
            <div class="flex max-h-[92vh] flex-col md:hidden">
                <!-- Drag handle -->
                <div class="flex justify-center pt-2">
                    <span class="h-1.5 w-10 rounded-full bg-muted" aria-hidden="true" />
                </div>

                <!-- Header / Amount -->
                <div class="flex-shrink-0 px-5 pt-3 pb-5">
                    <div class="flex items-center justify-between">
                        <div :class="['flex items-center gap-2 rounded-full px-2.5 py-1', accent.soft]">
                            <component :is="accent.icon" :class="['h-3.5 w-3.5', accent.fg]" />
                            <span :class="['text-[10px] font-semibold uppercase tracking-wider', accent.fg]">
                                {{ accent.label }}
                            </span>
                        </div>
                        <button
                            @click="close"
                            class="rounded-full p-1.5 text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                            aria-label="Close"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="mt-3 flex items-baseline justify-center gap-1">
                        <span class="text-3xl font-light text-muted-foreground/60">₱</span>
                        <input
                            ref="amountInput"
                            :value="amount"
                            @input="onAmountInput"
                            @keydown.enter.prevent="handleSubmit"
                            inputmode="decimal"
                            pattern="[0-9]*\.?[0-9]*"
                            placeholder="0"
                            autocomplete="off"
                            class="w-full max-w-[260px] bg-transparent text-center text-[44px] font-black leading-none tracking-tight tabular-nums text-foreground placeholder:text-muted-foreground/40 outline-none"
                        />
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                        <button
                            v-for="n in quickAmounts"
                            :key="n"
                            type="button"
                            @click="applyQuickAmount(n)"
                            class="rounded-full border border-border/60 bg-muted/30 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground transition-all hover:border-border hover:bg-muted/60 hover:text-foreground active:scale-95"
                        >
                            ₱{{ n >= 1000 ? n / 1000 + 'k' : n }}
                        </button>
                    </div>
                </div>

                <!-- Scroll body -->
                <div class="min-h-0 flex-1 overflow-y-auto px-5 pb-3 scrollbar-hide">
                    <p class="mb-2 px-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Category</p>
                    <div class="mb-4 grid grid-cols-4 gap-2">
                        <button
                            v-for="cat in categories"
                            :key="cat.name"
                            type="button"
                            @click="category = cat.name"
                            :class="[
                                'flex flex-col items-center gap-1.5 rounded-2xl border p-2 transition-all active:scale-95',
                                category === cat.name
                                    ? 'border-primary/40 bg-primary/5'
                                    : 'border-border/40 bg-muted/20 hover:border-border hover:bg-muted/40',
                            ]"
                        >
                            <span
                                :class="[
                                    'flex h-9 w-9 items-center justify-center rounded-xl transition-all',
                                    category === cat.name ? cat.color + ' text-white shadow-sm' : 'bg-muted/60 text-muted-foreground',
                                ]"
                            >
                                <component :is="cat.icon" class="h-4 w-4" />
                            </span>
                            <span
                                :class="[
                                    'w-full truncate text-center text-[10px] font-semibold transition-colors',
                                    category === cat.name ? 'text-foreground' : 'text-muted-foreground',
                                ]"
                            >
                                {{ cat.name }}
                            </span>
                        </button>
                    </div>

                    <div v-if="accounts?.length" class="mb-4">
                        <p class="mb-2 px-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Wallet</p>
                        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 scrollbar-hide">
                            <button
                                v-for="acc in accounts"
                                :key="acc.id"
                                type="button"
                                @click="accountId = acc.id"
                                :class="[
                                    'flex-shrink-0 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-[11px] font-semibold transition-all active:scale-95',
                                    accountId === acc.id
                                        ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                        : 'border-border/60 bg-muted/30 text-foreground hover:border-border hover:bg-muted/50',
                                ]"
                            >
                                {{ acc.name }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <label class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                            <Calendar class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Date</span>
                                <input
                                    v-model="date"
                                    type="date"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground outline-none [color-scheme:light] dark:[color-scheme:dark]"
                                />
                            </div>
                            <span class="ml-1 shrink-0 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">
                                {{ formatDateLabel(date) }}
                            </span>
                        </label>

                        <label class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                            <FileText class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Note</span>
                                <input
                                    v-model="description"
                                    type="text"
                                    placeholder="Optional…"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                />
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Sticky submit -->
                <div class="flex-shrink-0 border-t border-border/40 bg-background px-5 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <button
                        type="button"
                        @click="handleSubmit"
                        :disabled="submitDisabled"
                        class="flex w-full items-center justify-center gap-1.5 rounded-2xl bg-primary py-3.5 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50"
                    >
                        Add {{ accent.verb }}
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- ============================================================
                 DESKTOP LAYOUT (md+)
                 - Centered card with hero amount banner + form rows
                 - Generous whitespace, header bar, footer with cancel/save
                 ============================================================ -->
            <div class="hidden max-h-[88vh] flex-col md:flex">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-border/40 px-7 py-4">
                    <div class="flex items-center gap-3">
                        <div :class="['flex h-9 w-9 items-center justify-center rounded-xl', accent.soft]">
                            <component :is="accent.icon" :class="['h-4.5 w-4.5', accent.fg]" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight text-foreground">
                                Add {{ accent.verb }}
                            </h2>
                            <p class="text-[11px] font-medium text-muted-foreground">
                                {{ type === 'income' ? 'Log money received into one of your wallets' : 'Log a one-time expense' }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="close"
                        class="rounded-full p-2 text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                        aria-label="Close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Hero amount banner -->
                <div class="border-b border-border/40 bg-muted/15 px-7 py-6">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        {{ accent.label }}
                    </p>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl font-light text-muted-foreground/60">₱</span>
                        <input
                            ref="amountInput"
                            :value="amount"
                            @input="onAmountInput"
                            @keydown.enter.prevent="handleSubmit"
                            inputmode="decimal"
                            pattern="[0-9]*\.?[0-9]*"
                            placeholder="0"
                            autocomplete="off"
                            class="w-full bg-transparent text-[48px] font-black leading-none tracking-tight tabular-nums text-foreground placeholder:text-muted-foreground/30 outline-none"
                        />
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <button
                            v-for="n in quickAmounts"
                            :key="n"
                            type="button"
                            @click="applyQuickAmount(n)"
                            class="rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground transition-all hover:border-border hover:bg-background hover:text-foreground"
                        >
                            ₱{{ n >= 1000 ? n / 1000 + 'k' : n }}
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="min-h-0 flex-1 overflow-y-auto px-7 py-5 scrollbar-hide">
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Category</p>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="cat in categories"
                            :key="cat.name"
                            type="button"
                            @click="category = cat.name"
                            :class="[
                                'flex flex-col items-center gap-1.5 rounded-xl border p-2.5 transition-all',
                                category === cat.name
                                    ? 'border-primary/40 bg-primary/5'
                                    : 'border-border/40 bg-background hover:border-border hover:bg-muted/40',
                            ]"
                        >
                            <span
                                :class="[
                                    'flex h-9 w-9 items-center justify-center rounded-xl transition-all',
                                    category === cat.name ? cat.color + ' text-white shadow-sm' : 'bg-muted/60 text-muted-foreground',
                                ]"
                            >
                                <component :is="cat.icon" class="h-4 w-4" />
                            </span>
                            <span
                                :class="[
                                    'w-full truncate text-center text-[10px] font-semibold transition-colors',
                                    category === cat.name ? 'text-foreground' : 'text-muted-foreground',
                                ]"
                            >
                                {{ cat.name }}
                            </span>
                        </button>
                    </div>

                    <div v-if="accounts?.length" class="mt-4">
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Wallet</p>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="acc in accounts"
                                :key="acc.id"
                                type="button"
                                @click="accountId = acc.id"
                                :class="[
                                    'whitespace-nowrap rounded-full border px-3.5 py-1.5 text-[11px] font-semibold transition-all',
                                    accountId === acc.id
                                        ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                        : 'border-border/60 bg-background text-foreground hover:border-border hover:bg-muted/50',
                                ]"
                            >
                                {{ acc.name }}
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 transition-colors focus-within:border-primary/60">
                            <Calendar class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Date</span>
                                <input
                                    v-model="date"
                                    type="date"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground outline-none [color-scheme:light] dark:[color-scheme:dark]"
                                />
                            </div>
                            <span class="ml-1 shrink-0 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">
                                {{ formatDateLabel(date) }}
                            </span>
                        </label>

                        <label class="flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 transition-colors focus-within:border-primary/60">
                            <FileText class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Note</span>
                                <input
                                    v-model="description"
                                    type="text"
                                    placeholder="Optional…"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                />
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 border-t border-border/40 bg-background px-7 py-4">
                    <p class="text-[11px] text-muted-foreground">
                        <kbd class="mx-1 rounded border border-border bg-muted px-1.5 py-0.5 text-[10px] font-medium">Enter</kbd>
                        to save
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="close"
                            class="rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted/50"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            @click="handleSubmit"
                            :disabled="submitDisabled"
                            class="flex items-center gap-1.5 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50"
                        >
                            Add {{ accent.verb }}
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
