<script setup lang="ts">
import {
    X, ChevronRight, Calendar, FileText, Palette,
    AlignLeft, ArrowDownRight, ArrowUpRight, Check, Repeat,
} from 'lucide-vue-next';
import { ref, computed, watch, nextTick } from 'vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';

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
    { value: 'weekly',  label: 'Weekly',  per: 'week'  },
    { value: 'monthly', label: 'Monthly', per: 'month' },
    { value: 'yearly',  label: 'Yearly',  per: 'year'  },
];

const presetColors = [
    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1',
    '#8B5CF6', '#EC4899', '#18181B', '#6B7280',
];

const expenseCategories = ['Subscription', 'Rent', 'Utilities', 'Internet', 'Insurance', 'Loan', 'Food', 'Transport'];
const incomeCategories  = ['Salary', 'Freelance', 'Business', 'Investment', 'Allowance', 'Bonus', 'Rental', 'Other'];

const quickAmounts = [100, 500, 1000, 5000];

const nameInput = ref<HTMLInputElement | null>(null);
const showNote = ref(false);

const form = ref<RecurringData>({
    name: '',
    amount: '',
    type: props.fixedType || 'expense',
    category: '',
    description: '',
    frequency: 'monthly',
    next_due_date: new Date().toISOString().split('T')[0],
    is_active: true,
    color: props.fixedType === 'income' ? '#10B981' : '#EF4444',
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

const onAmountInput = (e: Event) => {
    form.value.amount = sanitizeAmount((e.target as HTMLInputElement).value);
};

const applyQuickAmount = (n: number) => {
    form.value.amount = String(n);
};

const categorySuggestions = computed(() =>
    form.value.type === 'income' ? incomeCategories : expenseCategories,
);

const perLabel = computed(
    () => frequencies.find(f => f.value === form.value.frequency)?.per ?? 'month',
);

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
    if (diff < 0) return `${Math.abs(diff)}d ago`;
    return `In ${diff}d`;
});

const accent = computed(() =>
    form.value.type === 'income'
        ? { fg: 'text-emerald-500', soft: 'bg-emerald-500/10', icon: ArrowDownRight, label: 'Recurring Income' }
        : { fg: 'text-rose-500',    soft: 'bg-rose-500/10',    icon: ArrowUpRight,   label: 'Recurring Bill'   },
);

const submitDisabled = computed(
    () => !form.value.name.trim() || !form.value.amount || parseFloat(form.value.amount) <= 0,
);

const close = () => emit('update:open', false);
const handleSubmit = () => {
    if (submitDisabled.value) return;
    emit('submit', { ...form.value });
};

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;
        if (props.initialData && Object.keys(props.initialData).length > 0) {
            form.value = {
                name:           props.initialData.name           ?? '',
                amount:         props.initialData.amount?.toString() ?? '',
                type:           props.initialData.type           ?? props.fixedType ?? 'expense',
                category:       props.initialData.category       ?? '',
                description:    props.initialData.description    ?? '',
                frequency:      props.initialData.frequency      ?? 'monthly',
                next_due_date:  props.initialData.next_due_date  ?? new Date().toISOString().split('T')[0],
                is_active:      props.initialData.is_active      ?? true,
                color:          props.initialData.color          ?? (props.fixedType === 'income' ? '#10B981' : '#EF4444'),
            };
            showNote.value = !!form.value.description;
        } else {
            form.value = {
                name: '',
                amount: '',
                type: props.fixedType || 'expense',
                category: '',
                description: '',
                frequency: 'monthly',
                next_due_date: new Date().toISOString().split('T')[0],
                is_active: true,
                color: props.fixedType === 'income' ? '#10B981' : '#EF4444',
            };
            showNote.value = false;
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
                md:max-w-[560px] md:rounded-[24px]
            "
        >
            <!-- ============================================================
                 MOBILE LAYOUT (default, hidden on md+)
                 - Bottom sheet
                 - Single column, dense, scrollable
                 - Compact spacing, smaller type
                 ============================================================ -->
            <div class="flex max-h-[92vh] flex-col md:hidden">
                <!-- Drag handle -->
                <div class="flex justify-center pt-2">
                    <span class="h-1.5 w-10 rounded-full bg-muted" aria-hidden="true" />
                </div>

                <!-- Header chip -->
                <div class="flex items-center justify-between px-5 pt-3">
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

                <!-- Scroll body -->
                <div class="min-h-0 flex-1 overflow-y-auto px-5 pb-3 pt-3 scrollbar-hide">
                    <!-- Hero amount (the input itself is the display) -->
                    <div class="rounded-3xl border border-border/40 bg-muted/20 px-4 py-5 text-center">
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                            Amount per {{ perLabel }}
                        </p>
                        <div class="flex items-baseline justify-center gap-1" :style="{ color: form.color }">
                            <span class="text-2xl font-light opacity-60">₱</span>
                            <input
                                :value="form.amount"
                                @input="onAmountInput"
                                inputmode="decimal"
                                pattern="[0-9]*\.?[0-9]*"
                                placeholder="0"
                                autocomplete="off"
                                class="w-full max-w-[220px] bg-transparent text-center text-[40px] font-black leading-none tracking-tight tabular-nums outline-none placeholder:opacity-30"
                                :style="{ color: form.color }"
                            />
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                            <button
                                v-for="n in quickAmounts"
                                :key="n"
                                type="button"
                                @click="applyQuickAmount(n)"
                                class="rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-[11px] font-semibold text-muted-foreground transition-all hover:border-border hover:bg-background hover:text-foreground active:scale-95"
                            >
                                ₱{{ n >= 1000 ? n / 1000 + 'k' : n }}
                            </button>
                        </div>
                    </div>

                    <!-- Frequency tiles -->
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <button
                            v-for="f in frequencies"
                            :key="f.value"
                            type="button"
                            @click="form.frequency = f.value as any"
                            :class="[
                                'relative flex flex-col items-center justify-center gap-0.5 rounded-2xl border px-2 py-2.5 transition-all active:scale-95',
                                form.frequency === f.value
                                    ? 'border-primary/40 bg-primary/5'
                                    : 'border-border/40 bg-muted/20 hover:border-border hover:bg-muted/40',
                            ]"
                        >
                            <Repeat
                                :class="[
                                    'h-3.5 w-3.5',
                                    form.frequency === f.value ? 'text-primary' : 'text-muted-foreground',
                                ]"
                            />
                            <span
                                :class="[
                                    'text-[11px] font-bold',
                                    form.frequency === f.value ? 'text-foreground' : 'text-muted-foreground',
                                ]"
                            >
                                {{ f.label }}
                            </span>
                        </button>
                    </div>

                    <!-- Name -->
                    <label class="group relative mt-3 flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                        <FileText class="h-4 w-4 shrink-0 text-primary" />
                        <div class="min-w-0 flex-1">
                            <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">
                                {{ form.type === 'income' ? 'Income Name' : 'Bill Name' }}
                            </span>
                            <input
                                ref="nameInput"
                                v-model="form.name"
                                type="text"
                                :placeholder="form.type === 'income' ? 'e.g. Monthly Salary' : 'e.g. Netflix'"
                                class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                            />
                        </div>
                    </label>

                    <!-- Next due date -->
                    <label class="group relative mt-2 flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 transition-colors focus-within:border-primary/60 focus-within:bg-muted/50">
                        <Calendar class="h-4 w-4 shrink-0 text-primary" />
                        <div class="min-w-0 flex-1">
                            <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Next Due Date</span>
                            <input
                                v-model="form.next_due_date"
                                type="date"
                                class="w-full bg-transparent text-sm font-semibold text-foreground outline-none [color-scheme:light] dark:[color-scheme:dark]"
                            />
                        </div>
                        <span
                            v-if="daysUntilDue"
                            class="ml-1 shrink-0 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary"
                        >
                            {{ daysUntilDue }}
                        </span>
                    </label>

                    <!-- Category -->
                    <p class="mt-4 mb-2 px-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                        Category
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="cat in categorySuggestions"
                            :key="cat"
                            type="button"
                            @click="form.category = cat"
                            :class="[
                                'rounded-full border px-3 py-1 text-[11px] font-semibold transition-all active:scale-95',
                                form.category === cat
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border/60 bg-muted/30 text-muted-foreground hover:border-border hover:bg-muted/50',
                            ]"
                        >
                            {{ cat }}
                        </button>
                        <input
                            v-model="form.category"
                            type="text"
                            placeholder="Custom…"
                            class="min-w-0 flex-1 rounded-full border border-dashed border-border/60 bg-transparent px-3 py-1 text-[11px] font-semibold text-foreground placeholder:text-muted-foreground/60 outline-none focus:border-primary/60"
                        />
                    </div>

                    <!-- Color -->
                    <p class="mt-4 mb-2 px-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                        Color
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            v-for="color in presetColors"
                            :key="color"
                            type="button"
                            @click="form.color = color"
                            :class="[
                                'flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full border-2 transition-all',
                                form.color === color ? 'border-foreground scale-110' : 'border-transparent',
                            ]"
                            :style="{ backgroundColor: color }"
                            :aria-label="color"
                        >
                            <Check v-if="form.color === color" class="h-3 w-3 text-white drop-shadow" />
                        </button>
                        <label class="relative flex h-7 w-7 flex-shrink-0 cursor-pointer items-center justify-center rounded-full border-2 border-dashed border-border/60 hover:border-primary">
                            <input
                                v-model="form.color"
                                type="color"
                                class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            />
                            <Palette class="h-3 w-3 text-muted-foreground" />
                        </label>
                    </div>

                    <!-- Note (collapsible) -->
                    <button
                        type="button"
                        @click="showNote = !showNote"
                        class="mt-4 flex w-full items-center justify-between rounded-2xl border border-border/40 bg-muted/20 px-3.5 py-2.5 text-[11px] font-semibold text-muted-foreground transition-colors hover:bg-muted/40"
                    >
                        <span class="flex items-center gap-2">
                            <AlignLeft class="h-3.5 w-3.5" />
                            Add a note (optional)
                        </span>
                        <ChevronRight :class="['h-4 w-4 transition-transform', showNote ? 'rotate-90' : '']" />
                    </button>
                    <textarea
                        v-if="showNote"
                        v-model="form.description"
                        rows="2"
                        placeholder="Add a note…"
                        class="mt-2 w-full resize-none rounded-2xl border border-border/60 bg-muted/30 px-3.5 py-2.5 text-sm font-medium text-foreground placeholder:text-muted-foreground/50 outline-none focus:border-primary/60"
                    />

                    <!-- Active toggle + type switch -->
                    <div class="mt-4 flex items-center justify-between rounded-2xl border border-border/40 bg-muted/20 px-3.5 py-2.5">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 cursor-pointer rounded border-border bg-card text-primary"
                            />
                            <span class="text-[11px] font-semibold text-foreground">Active</span>
                        </label>
                        <div v-if="!fixedType" class="flex items-center gap-1 rounded-full bg-muted/40 p-0.5">
                            <button
                                type="button"
                                @click="form.type = 'expense'"
                                :class="[
                                    'rounded-full px-3 py-1 text-[10px] font-bold transition-all',
                                    form.type === 'expense' ? 'bg-rose-500/15 text-rose-500' : 'text-muted-foreground',
                                ]"
                            >
                                Expense
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'income'"
                                :class="[
                                    'rounded-full px-3 py-1 text-[10px] font-bold transition-all',
                                    form.type === 'income' ? 'bg-emerald-500/15 text-emerald-500' : 'text-muted-foreground',
                                ]"
                            >
                                Income
                            </button>
                        </div>
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
                 - Centered card, wider (560px)
                 - Hero on left, form fields on right (2-col main)
                 - Generous padding, larger type
                 ============================================================ -->
            <div class="hidden max-h-[88vh] flex-col md:flex">
                <!-- Header bar -->
                <div class="flex items-center justify-between border-b border-border/40 px-7 py-4">
                    <div class="flex items-center gap-3">
                        <div :class="['flex h-9 w-9 items-center justify-center rounded-xl', accent.soft]">
                            <component :is="accent.icon" :class="['h-4.5 w-4.5', accent.fg]" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold tracking-tight text-foreground">{{ title }}</h2>
                            <p class="text-[11px] font-medium text-muted-foreground">
                                {{ form.type === 'income' ? 'Recurring income that lands in your accounts' : 'A bill you pay on a schedule' }}
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

                <!-- Body: two-column -->
                <div class="grid min-h-0 flex-1 grid-cols-5 gap-0">
                    <!-- LEFT: hero amount + frequency -->
                    <div class="col-span-2 flex flex-col gap-4 border-r border-border/40 bg-muted/15 p-6">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                Amount per {{ perLabel }}
                            </p>
                            <div class="mt-2 flex items-baseline gap-1" :style="{ color: form.color }">
                                <span class="text-2xl font-light opacity-60">₱</span>
                                <input
                                    :value="form.amount"
                                    @input="onAmountInput"
                                    inputmode="decimal"
                                    pattern="[0-9]*\.?[0-9]*"
                                    placeholder="0"
                                    autocomplete="off"
                                    class="w-full bg-transparent text-[44px] font-black leading-none tracking-tight tabular-nums outline-none placeholder:opacity-30"
                                    :style="{ color: form.color }"
                                />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
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

                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                Frequency
                            </p>
                            <div class="flex flex-col gap-1.5">
                                <button
                                    v-for="f in frequencies"
                                    :key="f.value"
                                    type="button"
                                    @click="form.frequency = f.value as any"
                                    :class="[
                                        'flex items-center justify-between rounded-xl border px-3 py-2 text-left transition-all',
                                        form.frequency === f.value
                                            ? 'border-primary/40 bg-primary/5'
                                            : 'border-border/40 bg-background/40 hover:border-border hover:bg-background',
                                    ]"
                                >
                                    <span class="flex items-center gap-2">
                                        <Repeat
                                            :class="[
                                                'h-3.5 w-3.5',
                                                form.frequency === f.value ? 'text-primary' : 'text-muted-foreground',
                                            ]"
                                        />
                                        <span
                                            :class="[
                                                'text-[12px] font-bold',
                                                form.frequency === f.value ? 'text-foreground' : 'text-muted-foreground',
                                            ]"
                                        >
                                            {{ f.label }}
                                        </span>
                                    </span>
                                    <span class="text-[10px] text-muted-foreground/80">per {{ f.per }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Color (compact in left rail) -->
                        <div class="mt-auto">
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                                Color
                            </p>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button
                                    v-for="color in presetColors"
                                    :key="color"
                                    type="button"
                                    @click="form.color = color"
                                    :class="[
                                        'flex h-6 w-6 items-center justify-center rounded-full border-2 transition-all',
                                        form.color === color ? 'border-foreground scale-110' : 'border-transparent',
                                    ]"
                                    :style="{ backgroundColor: color }"
                                    :aria-label="color"
                                >
                                    <Check v-if="form.color === color" class="h-2.5 w-2.5 text-white drop-shadow" />
                                </button>
                                <label class="relative flex h-6 w-6 cursor-pointer items-center justify-center rounded-full border-2 border-dashed border-border/60 hover:border-primary">
                                    <input
                                        v-model="form.color"
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
                        <label class="group relative flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 transition-colors focus-within:border-primary/60">
                            <FileText class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">
                                    {{ form.type === 'income' ? 'Income Name' : 'Bill Name' }}
                                </span>
                                <input
                                    ref="nameInput"
                                    v-model="form.name"
                                    type="text"
                                    :placeholder="form.type === 'income' ? 'e.g. Monthly Salary' : 'e.g. Netflix Subscription'"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground placeholder:text-muted-foreground/50 outline-none"
                                />
                            </div>
                        </label>

                        <label class="group relative mt-3 flex items-center gap-3 rounded-xl border border-border/60 bg-background px-3.5 py-2.5 transition-colors focus-within:border-primary/60">
                            <Calendar class="h-4 w-4 shrink-0 text-primary" />
                            <div class="min-w-0 flex-1">
                                <span class="block text-[9px] font-semibold uppercase tracking-wider text-muted-foreground/80">Next Due Date</span>
                                <input
                                    v-model="form.next_due_date"
                                    type="date"
                                    class="w-full bg-transparent text-sm font-semibold text-foreground outline-none [color-scheme:light] dark:[color-scheme:dark]"
                                />
                            </div>
                            <span
                                v-if="daysUntilDue"
                                class="ml-1 shrink-0 rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary"
                            >
                                {{ daysUntilDue }}
                            </span>
                        </label>

                        <p class="mt-4 mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                            Category
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="cat in categorySuggestions"
                                :key="cat"
                                type="button"
                                @click="form.category = cat"
                                :class="[
                                    'rounded-full border px-3 py-1 text-[11px] font-semibold transition-all',
                                    form.category === cat
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border/60 bg-background text-muted-foreground hover:border-border hover:bg-muted/50',
                                ]"
                            >
                                {{ cat }}
                            </button>
                            <input
                                v-model="form.category"
                                type="text"
                                placeholder="Custom…"
                                class="min-w-[100px] flex-1 rounded-full border border-dashed border-border/60 bg-transparent px-3 py-1 text-[11px] font-semibold text-foreground placeholder:text-muted-foreground/60 outline-none focus:border-primary/60"
                            />
                        </div>

                        <!-- Note (always visible on desktop, more spacious) -->
                        <p class="mt-4 mb-2 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                            Note <span class="font-normal normal-case text-muted-foreground/70">(optional)</span>
                        </p>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Add any extra context…"
                            class="w-full resize-none rounded-xl border border-border/60 bg-background px-3.5 py-2.5 text-sm font-medium text-foreground placeholder:text-muted-foreground/50 outline-none focus:border-primary/60"
                        />

                        <!-- Active + Type -->
                        <div class="mt-4 flex items-center justify-between rounded-xl border border-border/40 bg-muted/20 px-3.5 py-2.5">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 cursor-pointer rounded border-border bg-card text-primary"
                                />
                                <span class="text-[11px] font-semibold text-foreground">Active</span>
                                <span class="text-[10px] text-muted-foreground">
                                    Counts toward your dashboard
                                </span>
                            </label>
                            <div v-if="!fixedType" class="flex items-center gap-1 rounded-full bg-muted/40 p-0.5">
                                <button
                                    type="button"
                                    @click="form.type = 'expense'"
                                    :class="[
                                        'rounded-full px-3 py-1 text-[10px] font-bold transition-all',
                                        form.type === 'expense' ? 'bg-rose-500/15 text-rose-500' : 'text-muted-foreground',
                                    ]"
                                >
                                    Expense
                                </button>
                                <button
                                    type="button"
                                    @click="form.type = 'income'"
                                    :class="[
                                        'rounded-full px-3 py-1 text-[10px] font-bold transition-all',
                                        form.type === 'income' ? 'bg-emerald-500/15 text-emerald-500' : 'text-muted-foreground',
                                    ]"
                                >
                                    Income
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer with submit -->
                <div class="flex items-center justify-between gap-3 border-t border-border/40 bg-background px-7 py-4">
                    <p class="text-[11px] text-muted-foreground">
                        <span class="font-semibold text-foreground">Tip:</span>
                        press <kbd class="mx-1 rounded border border-border bg-muted px-1.5 py-0.5 text-[10px] font-medium">Esc</kbd> to close
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
