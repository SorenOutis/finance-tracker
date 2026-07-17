<script setup lang="ts">
import {
    X, ChevronRight, Wallet, Smartphone, Landmark,
    Coins, Bitcoin, MoreHorizontal, Palette,
    User, Hash, CreditCard, Check,
} from 'lucide-vue-next';
import { ref, computed, watch, nextTick } from 'vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { useHaptic } from '@/composables/useHaptic';
import { useSheetDrag } from '@/composables/useSheetDrag';

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
    { value: 'cash',        label: 'Cash',        icon: Coins          },
    { value: 'gcash',       label: 'GCash',       icon: Smartphone     },
    { value: 'maya',        label: 'Maya',        icon: Smartphone     },
    { value: 'paypal',      label: 'PayPal',      icon: Wallet         },
    { value: 'bank',        label: 'Bank',        icon: Landmark       },
    { value: 'credit_card', label: 'Credit Card', icon: CreditCard     },
    { value: 'crypto',      label: 'Crypto',      icon: Bitcoin        },
    { value: 'other',       label: 'Other',       icon: MoreHorizontal },
];

const presetColors = [
    '#6366F1', '#007DFE', '#00B274', '#F59E0B', '#EF4444',
    '#EC4899', '#8B5CF6', '#18181B', '#6B7280',
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

const colorManuallySet = ref(false);
const showAdvanced = ref(false);
const nameInput = ref<HTMLInputElement | null>(null);

const { sheetStyle, onHandleTouchStart, onHandleTouchMove, onHandleTouchEnd, registerDismiss } = useSheetDrag();
registerDismiss(() => emit('update:open', false));

const form = ref<WalletData>({
    name: '',
    type: 'cash',
    color: '#6B7280',
    initial_balance: '',
    credit_limit: '',
    currency: 'PHP',
    last4: '',
    holder_name: '',
});

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

const onBalanceInput = (e: Event) => {
    form.value.initial_balance = sanitizeAmount((e.target as HTMLInputElement).value);
};
const onCreditLimitInput = (e: Event) => {
    form.value.credit_limit = sanitizeAmount((e.target as HTMLInputElement).value);
};

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

const currencySymbol = computed(
    () => currencies.find(c => c.value === form.value.currency)?.symbol || '₱',
);

const displayAmount = computed(() => {
    const num = parseFloat(form.value.initial_balance) || 0;
    return num.toLocaleString('en-US', { maximumFractionDigits: 2 });
});

const typeIcon = computed(
    () => accountTypes.find(t => t.value === form.value.type)?.icon || Wallet,
);
const typeLabel = computed(
    () => accountTypes.find(t => t.value === form.value.type)?.label || 'Wallet',
);

const cardGradient = computed(
    () => `linear-gradient(135deg, ${form.value.color} 0%, ${form.value.color}CC 50%, ${form.value.color}99 100%)`,
);

const haptic = useHaptic();

const submitDisabled = computed(() => !form.value.name.trim());

const close = () => emit('update:open', false);
const handleSubmit = () => {
    if (submitDisabled.value) return;
    haptic.success();
    emit('submit', { ...form.value });
};

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;
        if (props.initialData && Object.keys(props.initialData).length > 0) {
            form.value = {
                name:            props.initialData.name            ?? '',
                type:            props.initialData.type            ?? 'cash',
                color:           props.initialData.color           ?? defaultColors[props.initialData.type ?? 'cash'] ?? '#6366F1',
                initial_balance: props.initialData.initial_balance?.toString() ?? '',
                credit_limit:    props.initialData.credit_limit?.toString()    ?? '',
                currency:        props.initialData.currency        ?? 'PHP',
                last4:           props.initialData.last4           ?? '',
                holder_name:     props.initialData.holder_name     ?? '',
            };
            colorManuallySet.value = true;
            showAdvanced.value =
                !!form.value.holder_name || !!form.value.last4 || !!form.value.credit_limit;
        } else {
            form.value = {
                name: '',
                type: 'cash',
                color: defaultColors.cash,
                initial_balance: '',
                credit_limit: '',
                currency: 'PHP',
                last4: '',
                holder_name: '',
            };
            colorManuallySet.value = false;
            showAdvanced.value = false;
        }
        nextTick(() => nameInput.value?.focus());
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
                md:max-w-[640px] md:rounded-[24px]
            "
        >
            <!-- ============================================================
                 MOBILE LAYOUT (default, hidden on md+)
                 - Bottom sheet
                 - Card preview at top, fields stacked, advanced collapsible
                 ============================================================ -->
            <div class="flex max-h-[92vh] flex-col md:hidden" :style="sheetStyle">
                <!-- Drag handle -->
                <div
                    class="flex justify-center pt-2"
                    @touchstart="onHandleTouchStart"
                    @touchmove="onHandleTouchMove"
                    @touchend="onHandleTouchEnd"
                >
                    <span class="h-1.5 w-10 rounded-full bg-muted" aria-hidden="true" />
                </div>

                <!-- Header chip -->
                <div class="flex items-center justify-between px-5 pt-3">
                    <div class="flex items-center gap-2 rounded-full bg-primary/10 px-2.5 py-1">
                        <Wallet class="h-3.5 w-3.5 text-primary" />
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-primary">{{ title }}</span>
                    </div>
                    <button
                        @click="close"
                        class="rounded-full p-1.5 text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                        aria-label="Close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Scroll body -->
                <div class="min-h-0 flex-1 overflow-y-auto px-5 pb-3 pt-3 scrollbar-hide">
                    <!-- Card preview -->
                    <div
                        class="relative overflow-hidden rounded-2xl p-4 shadow-xl transition-all duration-500"
                        :style="{ background: cardGradient }"
                    >
                        <div class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-white/10 blur-2xl" />
                        <div class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-black/10 blur-2xl" />

                        <div class="relative mb-5 flex items-start justify-between">
                            <div class="min-w-0">
                                <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/70">Initial Balance</p>
                                <p class="mt-0.5 max-w-[180px] truncate text-xs font-bold text-white/90">{{ form.name || 'New Wallet' }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 rounded-lg bg-white/15 px-2 py-1 backdrop-blur-sm">
                                <component :is="typeIcon" class="h-3 w-3 text-white" />
                                <span class="text-[9px] font-bold text-white">{{ typeLabel }}</span>
                            </div>
                        </div>

                        <div class="relative flex items-baseline gap-1 text-white">
                            <span class="text-2xl font-light opacity-70">{{ currencySymbol }}</span>
                            <span class="text-[34px] font-black leading-none tracking-tight tabular-nums">{{ displayAmount }}</span>
                        </div>

                        <div class="relative mt-3 flex items-end justify-between">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-white/70">{{ form.holder_name || 'Card Holder' }}</p>
                            <p class="text-[11px] font-mono font-bold tracking-widest text-white/90">
                                {{ form.last4 ? '•••• ' + form.last4 : '•••• ••••' }}
                            </p>
                        </div>
                    </div>

                    <!-- Account Type -->
                    <p class="mt-4 mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Account Type</p>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            v-for="t in accountTypes"
                            :key="t.value"
                            type="button"
                            @click="pickType(t.value)"
                            :class="[
                                'flex flex-col items-center gap-1.5 rounded-2xl border p-2 transition-all active:scale-95',
                                form.type === t.value
                                    ? 'border-primary/40 bg-primary/5'
                                    : 'border-border/40 bg-muted/20 hover:border-border hover:bg-muted/40',
                            ]"
                        >
                            <span
                                :class="[
                                    'flex h-8 w-8 items-center justify-center rounded-xl transition-all',
                                    form.type === t.value ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-muted/60 text-muted-foreground',
                                ]"
                            >
                                <component :is="t.icon" class="h-4 w-4" />
                            </span>
                            <span
                                :class="[
                                    'w-full truncate text-center text-[10px] font-semibold transition-colors',
                                    form.type === t.value ? 'text-foreground' : 'text-muted-foreground',
                                ]"
                            >
                                {{ t.label }}
                            </span>
                        </button>
                    </div>

                    <!-- Name -->
                    <label class="mt-4 flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                        <Wallet class="h-4 w-4 shrink-0 text-primary" />
                        <div class="min-w-0 flex-1">
                            <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Wallet Name</span>
                            <input
                                ref="nameInput"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g. GCash, Main Bank, Cash"
                                class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                            />
                        </div>
                    </label>

                    <!-- Initial balance + currency -->
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <label class="col-span-2 flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                            <span class="text-base font-semibold text-primary">{{ currencySymbol }}</span>
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Initial Balance</span>
                                <input
                                    :value="form.initial_balance"
                                    @input="onBalanceInput"
                                    inputmode="decimal"
                                    pattern="[0-9]*\.?[0-9]*"
                                    placeholder="0.00"
                                    class="w-full bg-transparent text-sm font-semibold tabular-nums text-foreground placeholder:text-muted-foreground/50 outline-none"
                                />
                            </div>
                        </label>
                        <label class="flex items-center gap-2 rounded-2xl border border-border/60 bg-muted/30 px-3 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Currency</span>
                                <select
                                    v-model="form.currency"
                                    class="w-full cursor-pointer appearance-none bg-transparent text-sm font-semibold text-foreground outline-none"
                                >
                                    <option v-for="c in currencies" :key="c.value" :value="c.value">{{ c.value }}</option>
                                </select>
                            </div>
                        </label>
                    </div>

                    <!-- Color -->
                    <p class="mt-4 mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Color</p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            v-for="color in presetColors"
                            :key="color"
                            type="button"
                            @click="pickColor(color)"
                            :class="[
                                'flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 transition-all',
                                form.color === color ? 'border-foreground scale-110' : 'border-transparent',
                            ]"
                            :style="{ backgroundColor: color }"
                            :aria-label="color"
                        >
                            <Check v-if="form.color === color" class="h-3.5 w-3.5 text-white drop-shadow" />
                        </button>
                        <label class="relative flex h-8 w-8 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-dashed border-border/60 hover:border-primary">
                            <input
                                :value="form.color"
                                @input="pickColor(($event.target as HTMLInputElement).value)"
                                type="color"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />
                            <Palette class="h-3 w-3 text-muted-foreground" />
                        </label>
                    </div>

                    <!-- Advanced -->
                    <button
                        type="button"
                        @click="showAdvanced = !showAdvanced"
                        class="mt-4 flex w-full items-center justify-between rounded-2xl border border-border/40 bg-muted/20 px-3.5 py-2.5 text-[11px] font-semibold text-muted-foreground transition-colors hover:bg-muted/40"
                    >
                        <span>Optional details (holder, last 4{{ form.type === 'credit_card' ? ', credit limit' : '' }})</span>
                        <ChevronRight :class="['h-4 w-4 transition-transform', showAdvanced ? 'rotate-90' : '']" />
                    </button>
                    <div v-if="showAdvanced" class="mt-2 space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 focus-within:border-primary/60 focus-within:bg-muted/50">
                                <User class="h-4 w-4 shrink-0 text-primary" />
                                <div class="min-w-0 flex-1">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Holder</span>
                                    <input
                                        v-model="form.holder_name"
                                        type="text"
                                        placeholder="Card holder"
                                        class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                    />
                                </div>
                            </label>
                            <label class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 focus-within:border-primary/60 focus-within:bg-muted/50">
                                <Hash class="h-4 w-4 shrink-0 text-primary" />
                                <div class="min-w-0 flex-1">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Last 4</span>
                                    <input
                                        v-model="form.last4"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="4"
                                        placeholder="••••"
                                        class="w-full bg-transparent font-mono text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                    />
                                </div>
                            </label>
                        </div>
                        <label
                            v-if="form.type === 'credit_card'"
                            class="flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 focus-within:border-primary/60 focus-within:bg-muted/50"
                        >
                            <CreditCard class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Credit Limit</span>
                                <input
                                    :value="form.credit_limit"
                                    @input="onCreditLimitInput"
                                    inputmode="decimal"
                                    pattern="[0-9]*\.?[0-9]*"
                                    placeholder="50000"
                                    class="w-full bg-transparent text-sm font-semibold tabular-nums text-foreground placeholder:text-muted-foreground/50 outline-none"
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
                        {{ title }}
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- ============================================================
                 DESKTOP LAYOUT (md+)
                 - Two-column: card preview (left), form fields (right)
                 - Header bar + footer with cancel/save
                 ============================================================ -->
            <div class="hidden max-h-[88vh] flex-col md:flex">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-border/40 px-7 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10">
                            <Wallet class="h-4.5 w-4.5 text-primary" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight text-foreground">{{ title }}</h2>
                            <p class="text-[11px] font-medium text-muted-foreground">
                                Set up an account to track money in and out
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

                <!-- Body: 2-column -->
                <div class="grid min-h-0 flex-1 grid-cols-5">
                    <!-- LEFT: card preview + color -->
                    <div class="col-span-2 flex flex-col gap-4 border-r border-border/40 bg-muted/15 p-6">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Live preview</p>
                        <div
                            class="relative overflow-hidden rounded-2xl p-5 shadow-xl transition-all duration-500"
                            :style="{ background: cardGradient }"
                        >
                            <div class="absolute -top-12 -right-12 h-32 w-32 rounded-full bg-white/10 blur-2xl" />
                            <div class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-black/10 blur-2xl" />

                            <div class="relative mb-6 flex items-start justify-between">
                                <div class="min-w-0">
                                    <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-white/70">Initial Balance</p>
                                    <p class="mt-0.5 max-w-[160px] truncate text-xs font-bold text-white/90">{{ form.name || 'New Wallet' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 rounded-lg bg-white/15 px-2 py-1 backdrop-blur-sm">
                                    <component :is="typeIcon" class="h-3 w-3 text-white" />
                                    <span class="text-[9px] font-bold text-white">{{ typeLabel }}</span>
                                </div>
                            </div>

                            <div class="relative flex items-baseline gap-1 text-white">
                                <span class="text-2xl font-light opacity-70">{{ currencySymbol }}</span>
                                <span class="text-[36px] font-black leading-none tracking-tight tabular-nums">{{ displayAmount }}</span>
                            </div>

                            <div class="relative mt-4 flex items-end justify-between">
                                <p class="text-[9px] font-bold uppercase tracking-wider text-white/70">{{ form.holder_name || 'Card Holder' }}</p>
                                <p class="text-[11px] font-mono font-bold tracking-widest text-white/90">
                                    {{ form.last4 ? '•••• ' + form.last4 : '•••• ••••' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Color</p>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button
                                    v-for="color in presetColors"
                                    :key="color"
                                    type="button"
                                    @click="pickColor(color)"
                                    :class="[
                                        'flex h-7 w-7 items-center justify-center rounded-full border-2 transition-all',
                                        form.color === color ? 'border-foreground scale-110' : 'border-transparent hover:scale-105',
                                    ]"
                                    :style="{ backgroundColor: color }"
                                    :aria-label="color"
                                >
                                    <Check v-if="form.color === color" class="h-3 w-3 text-white drop-shadow" />
                                </button>
                                <label class="relative flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border-2 border-dashed border-border/60 hover:border-primary">
                                    <input
                                        :value="form.color"
                                        @input="pickColor(($event.target as HTMLInputElement).value)"
                                        type="color"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                    />
                                    <Palette class="h-3 w-3 text-muted-foreground" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: form fields -->
                    <div class="col-span-3 flex flex-col overflow-y-auto p-6 scrollbar-hide">
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Account Type</p>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="t in accountTypes"
                                :key="t.value"
                                type="button"
                                @click="pickType(t.value)"
                                :class="[
                                    'flex flex-col items-center gap-1.5 rounded-xl border p-2 transition-all',
                                    form.type === t.value
                                        ? 'border-primary/40 bg-primary/5'
                                        : 'border-border/40 bg-background hover:border-border hover:bg-muted/40',
                                ]"
                            >
                                <span
                                    :class="[
                                        'flex h-8 w-8 items-center justify-center rounded-xl transition-all',
                                        form.type === t.value ? 'bg-primary text-primary-foreground shadow-sm' : 'bg-muted/60 text-muted-foreground',
                                    ]"
                                >
                                    <component :is="t.icon" class="h-4 w-4" />
                                </span>
                                <span
                                    :class="[
                                        'w-full truncate text-center text-[10px] font-semibold transition-colors',
                                        form.type === t.value ? 'text-foreground' : 'text-muted-foreground',
                                    ]"
                                >
                                    {{ t.label }}
                                </span>
                            </button>
                        </div>

                        <label class="mt-4 flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 focus-within:border-primary/60">
                            <Wallet class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Wallet Name</span>
                                <input
                                    ref="nameInput"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g. GCash, Main Bank, Cash"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                />
                            </div>
                        </label>

                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <label class="col-span-2 flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 focus-within:border-primary/60">
                                <span class="text-base font-semibold text-primary">{{ currencySymbol }}</span>
                                <div class="min-w-0 flex-1">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Initial Balance</span>
                                    <input
                                        :value="form.initial_balance"
                                        @input="onBalanceInput"
                                        inputmode="decimal"
                                        pattern="[0-9]*\.?[0-9]*"
                                        placeholder="0.00"
                                        class="w-full bg-transparent text-sm font-semibold tabular-nums text-foreground placeholder:text-muted-foreground/50 outline-none"
                                    />
                                </div>
                            </label>
                            <label class="flex items-center gap-2 rounded-xl border border-border/60 bg-background px-3 py-2.5 focus-within:border-primary/60">
                                <div class="min-w-0 flex-1">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Currency</span>
                                    <select
                                        v-model="form.currency"
                                        class="w-full cursor-pointer appearance-none bg-transparent text-sm font-semibold text-foreground outline-none"
                                    >
                                        <option v-for="c in currencies" :key="c.value" :value="c.value">{{ c.value }}</option>
                                    </select>
                                </div>
                            </label>
                        </div>

                        <button
                            type="button"
                            @click="showAdvanced = !showAdvanced"
                            class="mt-4 flex w-full items-center justify-between rounded-xl border border-border/40 bg-muted/20 px-3.5 py-2.5 text-[11px] font-semibold text-muted-foreground transition-colors hover:bg-muted/40"
                        >
                            <span>Optional details (holder, last 4{{ form.type === 'credit_card' ? ', credit limit' : '' }})</span>
                            <ChevronRight :class="['h-4 w-4 transition-transform', showAdvanced ? 'rotate-90' : '']" />
                        </button>
                        <div v-if="showAdvanced" class="mt-2 space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 focus-within:border-primary/60">
                                    <User class="h-4 w-4 shrink-0 text-primary" />
                                    <div class="min-w-0 flex-1">
                                        <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Holder</span>
                                        <input
                                            v-model="form.holder_name"
                                            type="text"
                                            placeholder="Card holder"
                                            class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                        />
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 focus-within:border-primary/60">
                                    <Hash class="h-4 w-4 shrink-0 text-primary" />
                                    <div class="min-w-0 flex-1">
                                        <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Last 4</span>
                                        <input
                                            v-model="form.last4"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="4"
                                            placeholder="••••"
                                            class="w-full bg-transparent font-mono text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                        />
                                    </div>
                                </label>
                            </div>
                            <label
                                v-if="form.type === 'credit_card'"
                                class="flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 focus-within:border-primary/60"
                            >
                                <CreditCard class="h-4 w-4 shrink-0 text-primary" />
                                <div class="min-w-0 flex-1">
                                    <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Credit Limit</span>
                                    <input
                                        :value="form.credit_limit"
                                        @input="onCreditLimitInput"
                                        inputmode="decimal"
                                        pattern="[0-9]*\.?[0-9]*"
                                        placeholder="50000"
                                        class="w-full bg-transparent text-sm font-semibold tabular-nums text-foreground placeholder:text-muted-foreground/50 outline-none"
                                    />
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 border-t border-border/40 bg-background px-7 py-4">
                    <p class="text-[11px] text-muted-foreground">
                        <span class="font-semibold text-foreground">Tip:</span>
                        you can edit this wallet later
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
                            {{ title }}
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
