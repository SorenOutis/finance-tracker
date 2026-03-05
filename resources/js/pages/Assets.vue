<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { assets } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, onMounted } from 'vue';

import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';
import { Landmark, CreditCard, Scale, Plus, Check, Trash2 } from 'lucide-vue-next';

interface AssetItem {
    id: number;
    name: string;
    amount: number;
    type?: string;
    checked: boolean;
    created_at: string;
}

interface LiabilityItem {
    id: number;
    name: string;
    amount: number;
    type?: string;
    checked: boolean;
    created_at: string;
}

interface Props {
    assets: AssetItem[];
    liabilities: LiabilityItem[];
    totalAssets: number;
    totalLiabilities: number;
    netWorth: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: assets(),
    },
];

const activeTab = useStorage<'assets' | 'liabilities'>('assets-tab', 'assets');

const showAssetNotification = ref(false);
const showLiabilityNotification = ref(false);
const assetNotificationMessage = ref('');
const liabilityNotificationMessage = ref('');
const showAddForm = ref(false);

const assetForm = useForm({
    name: '',
    amount: '',
    type: '',
});

const liabilityForm = useForm({
    name: '',
    amount: '',
    type: '',
});

const showNotification = (message: string, type: 'asset' | 'liability') => {
    if (type === 'asset') {
        assetNotificationMessage.value = message;
        showAssetNotification.value = true;
        setTimeout(() => {
            showAssetNotification.value = false;
        }, 2500);
    } else {
        liabilityNotificationMessage.value = message;
        showLiabilityNotification.value = true;
        setTimeout(() => {
            showLiabilityNotification.value = false;
        }, 2500);
    }
};

const addAsset = () => {
    if (!assetForm.name || !assetForm.amount) return;

    const assetName = assetForm.name;

    assetForm.post('/assets', {
        onSuccess: () => {
            assetForm.reset();
            showAddForm.value = false;
            showNotification(`Asset "${assetName}" added successfully!`, 'asset');
        },
    });
};

const addLiability = () => {
    if (!liabilityForm.name || !liabilityForm.amount) return;

    const liabilityName = liabilityForm.name;

    liabilityForm.post('/liabilities', {
        onSuccess: () => {
            liabilityForm.reset();
            showAddForm.value = false;
            showNotification(`Liability "${liabilityName}" added successfully!`, 'liability');
        },
    });
};

const deleteAsset = (id: number) => {
    router.delete(`/assets/${id}`, {
        preserveScroll: true,
    });
};

const deleteLiability = (id: number) => {
    router.delete(`/liabilities/${id}`, {
        preserveScroll: true,
    });
};

const toggleAsset = (id: number) => {
    router.patch(`/assets/${id}`, {}, {
        preserveScroll: true,
    });
};

const toggleLiability = (id: number) => {
    router.patch(`/liabilities/${id}`, {}, {
        preserveScroll: true,
    });
};

// Net worth ring animation
const animatedNetWorthPct = ref(0);
const ringRadius = 54;
const ringCircumference = 2 * Math.PI * ringRadius;

const netWorthPct = computed(() => {
    if (props.totalAssets === 0) return 0;
    return Math.min(100, Math.max(0, (props.netWorth / props.totalAssets) * 100));
});

const ringDashOffset = computed(() => {
    const clamped = Math.max(0, Math.min(100, animatedNetWorthPct.value));
    return ringCircumference - (clamped / 100) * ringCircumference;
});

onMounted(() => {
    const target = netWorthPct.value;
    const duration = 1200;
    const startTime = performance.now();

    const animate = (currentTime: number) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        animatedNetWorthPct.value = eased * target;
        if (progress < 1) requestAnimationFrame(animate);
    };
    requestAnimationFrame(animate);
});

// Stat cards
const statCards = computed(() => [
    {
        label: 'Total Assets',
        value: formatCurrency(props.totalAssets),
        icon: Landmark,
        tint: 'stat-tint-income',
    },
    {
        label: 'Total Liabilities',
        value: formatCurrency(props.totalLiabilities),
        icon: CreditCard,
        tint: 'stat-tint-expense',
    },
]);
</script>

<template>

    <Head title="Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div class="animate-fade-up stagger-1">
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Assets & Liabilities</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Manage your financial holdings and track your net worth</p>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:gap-6">
                <div v-for="(stat, idx) in statCards" :key="stat.label"
                    :class="['group surface-card p-4 lg:p-5 overflow-hidden relative hover:scale-[1.03] transition-all duration-300 hover:shadow-lg cursor-default animate-fade-up', `stagger-${idx + 1}`, stat.tint]">
                    <div class="absolute top-0 right-0 w-20 h-20 opacity-[0.04] pointer-events-none transition-transform duration-500 group-hover:scale-110">
                        <component :is="stat.icon" class="size-20 -translate-y-3 translate-x-3" />
                    </div>
                    <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                        {{ stat.label }}</p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">{{
                        stat.value }}</p>
                </div>

                <!-- Net Worth with Ring -->
                <div class="surface-card p-4 lg:p-5 overflow-hidden relative stat-tint-accent animate-fade-up stagger-3 hover:scale-[1.03] transition-all duration-300 hover:shadow-lg cursor-default group md:col-span-1 col-span-2">
                    <div class="flex items-center gap-4">
                        <div class="relative w-16 h-16 shrink-0">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                    class="stroke-border/30" stroke-width="8" />
                                <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                    :stroke="props.netWorth >= 0 ? 'oklch(0.76 0.16 162)' : 'oklch(0.62 0.22 18)'"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="ringCircumference"
                                    :stroke-dashoffset="ringDashOffset"
                                    class="transition-all duration-1000 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <Scale class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-1.5 font-sans truncate">
                                Net Worth</p>
                            <p :class="['text-2xl lg:text-3xl font-bold font-mono tracking-tighter leading-none truncate', props.netWorth >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">
                                {{ formatCurrency(props.netWorth) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="surface-card overflow-hidden p-0 animate-fade-up stagger-4">
                <div class="flex gap-0 border-b border-border">
                    <button @click="activeTab = 'assets'; showAddForm = false" :class="[
                        'flex-1 px-6 py-4 text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2',
                        activeTab === 'assets'
                            ? 'border-b-2 border-primary text-primary bg-primary/3'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/30'
                    ]">
                        <Landmark class="h-4 w-4" />
                        Assets
                        <span v-if="props.assets.length > 0" class="ml-1 text-[10px] font-bold bg-muted/60 px-2 py-0.5 rounded-full">{{ props.assets.length }}</span>
                    </button>
                    <button @click="activeTab = 'liabilities'; showAddForm = false" :class="[
                        'flex-1 px-6 py-4 text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2',
                        activeTab === 'liabilities'
                            ? 'border-b-2 border-primary text-primary bg-primary/3'
                            : 'text-muted-foreground hover:text-foreground hover:bg-accent/30'
                    ]">
                        <CreditCard class="h-4 w-4" />
                        Liabilities
                        <span v-if="props.liabilities.length > 0" class="ml-1 text-[10px] font-bold bg-muted/60 px-2 py-0.5 rounded-full">{{ props.liabilities.length }}</span>
                    </button>
                </div>

                <!-- Assets Tab Content -->
                <div v-if="activeTab === 'assets'" class="p-5 lg:p-6">
                    <!-- Add button / form toggle -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Your Assets</h2>
                        <button @click="showAddForm = !showAddForm"
                            :class="['group flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200',
                                showAddForm
                                    ? 'bg-muted/50 text-muted-foreground'
                                    : 'bg-primary/8 text-primary hover:bg-primary/12']">
                            <Plus :class="['h-4 w-4 transition-transform duration-200', showAddForm ? 'rotate-45' : 'group-hover:rotate-90']" />
                            {{ showAddForm ? 'Cancel' : 'Add Asset' }}
                        </button>
                    </div>

                    <!-- Collapsible Add Form -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 max-h-0"
                        enter-to-class="opacity-100 translate-y-0 max-h-96"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0 max-h-96"
                        leave-to-class="opacity-0 -translate-y-2 max-h-0">
                        <div v-if="showAddForm" class="mb-6 p-5 rounded-xl bg-muted/20 border border-border/30 overflow-hidden">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-muted-foreground font-sans">Asset Name</label>
                                    <input v-model="assetForm.name" type="text" name="name" placeholder="e.g., Savings Account"
                                        class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                                        <input v-model="assetForm.amount" type="number" name="amount" placeholder="0.00"
                                            class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-muted-foreground font-sans">
                                            Type <span class="font-normal text-muted-foreground/70">(Optional)</span>
                                        </label>
                                        <input v-model="assetForm.type" type="text" name="type"
                                            placeholder="e.g., Savings, Checking"
                                            class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                    </div>
                                </div>
                            </div>

                            <button @click="addAsset"
                                class="mt-5 w-full rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                                Add Asset
                            </button>
                        </div>
                    </Transition>

                    <!-- Assets List -->
                    <div v-if="props.assets.length > 0" class="divide-y divide-border/30 space-y-0">
                        <div v-for="asset in props.assets" :key="asset.id"
                            class="entry-row group flex items-start justify-between py-4 transition-colors first:pt-0">
                            <div class="flex items-start gap-3 flex-1">
                                <button @click="toggleAsset(asset.id)"
                                    :class="['mt-0.5 flex h-5 w-5 items-center justify-center rounded-md border-2 transition-all duration-200 cursor-pointer shrink-0',
                                        asset.checked
                                            ? 'border-primary bg-primary'
                                            : 'border-border hover:border-primary/50']">
                                    <Check v-if="asset.checked" class="h-3 w-3 text-primary-foreground" />
                                </button>
                                <div class="flex-1">
                                    <p :class="['font-semibold text-[13px] font-sans transition-all', asset.checked ? 'line-through text-muted-foreground' : 'text-foreground']">{{
                                        asset.name }}</p>
                                    <p class="mt-1 text-sm font-mono text-[--fin-gain] font-semibold">{{ formatCurrency(asset.amount) }}</p>
                                    <div v-if="asset.type" class="mt-2">
                                        <span class="inline-flex items-center text-[10px] font-semibold text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-full font-sans">{{ asset.type }}</span>
                                    </div>
                                </div>
                            </div>
                            <button @click="deleteAsset(asset.id)"
                                class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12">
                        <Landmark class="h-12 w-12 text-muted-foreground/20 mb-3" />
                        <p class="text-sm text-muted-foreground font-sans">No assets added yet</p>
                        <button v-if="!showAddForm" @click="showAddForm = true" class="mt-3 text-[11px] font-semibold text-primary hover:underline">
                            Add your first asset →
                        </button>
                    </div>
                </div>

                <!-- Liabilities Tab Content -->
                <div v-if="activeTab === 'liabilities'" class="p-5 lg:p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Your Liabilities</h2>
                        <button @click="showAddForm = !showAddForm"
                            :class="['group flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200',
                                showAddForm
                                    ? 'bg-muted/50 text-muted-foreground'
                                    : 'bg-primary/8 text-primary hover:bg-primary/12']">
                            <Plus :class="['h-4 w-4 transition-transform duration-200', showAddForm ? 'rotate-45' : 'group-hover:rotate-90']" />
                            {{ showAddForm ? 'Cancel' : 'Add Liability' }}
                        </button>
                    </div>

                    <!-- Collapsible Add Form -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 max-h-0"
                        enter-to-class="opacity-100 translate-y-0 max-h-96"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0 max-h-96"
                        leave-to-class="opacity-0 -translate-y-2 max-h-0">
                        <div v-if="showAddForm" class="mb-6 p-5 rounded-xl bg-muted/20 border border-border/30 overflow-hidden">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-muted-foreground font-sans">Liability Name</label>
                                    <input v-model="liabilityForm.name" type="text" name="name" placeholder="e.g., Car Loan"
                                        class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                                        <input v-model="liabilityForm.amount" type="number" name="amount" placeholder="0.00"
                                            class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-muted-foreground font-sans">
                                            Type <span class="font-normal text-muted-foreground/70">(Optional)</span>
                                        </label>
                                        <input v-model="liabilityForm.type" type="text" name="type"
                                            placeholder="e.g., Loan, Mortgage"
                                            class="mt-2 w-full rounded-lg border border-border bg-card px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                                    </div>
                                </div>
                            </div>

                            <button @click="addLiability"
                                class="mt-5 w-full rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                                Add Liability
                            </button>
                        </div>
                    </Transition>

                    <!-- Liabilities List -->
                    <div v-if="props.liabilities.length > 0" class="divide-y divide-border/30 space-y-0">
                        <div v-for="liability in props.liabilities" :key="liability.id"
                            class="entry-row group flex items-start justify-between py-4 transition-colors first:pt-0">
                            <div class="flex items-start gap-3 flex-1">
                                <button @click="toggleLiability(liability.id)"
                                    :class="['mt-0.5 flex h-5 w-5 items-center justify-center rounded-md border-2 transition-all duration-200 cursor-pointer shrink-0',
                                        liability.checked
                                            ? 'border-primary bg-primary'
                                            : 'border-border hover:border-primary/50']">
                                    <Check v-if="liability.checked" class="h-3 w-3 text-primary-foreground" />
                                </button>
                                <div class="flex-1">
                                    <p :class="['font-semibold text-[13px] font-sans transition-all', liability.checked ? 'line-through text-muted-foreground' : 'text-foreground']">{{
                                        liability.name }}</p>
                                    <p class="mt-1 text-sm font-mono text-[--fin-loss] font-semibold">{{ formatCurrency(liability.amount) }}</p>
                                    <div v-if="liability.type" class="mt-2">
                                        <span class="inline-flex items-center text-[10px] font-semibold text-muted-foreground bg-muted/50 px-2 py-0.5 rounded-full font-sans">{{ liability.type }}</span>
                                    </div>
                                </div>
                            </div>
                            <button @click="deleteLiability(liability.id)"
                                class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-12">
                        <CreditCard class="h-12 w-12 text-muted-foreground/20 mb-3" />
                        <p class="text-sm text-muted-foreground font-sans">No liabilities added yet</p>
                        <button v-if="!showAddForm" @click="showAddForm = true" class="mt-3 text-[11px] font-semibold text-primary hover:underline">
                            Add your first liability →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Notification Toasts -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-3 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-3 scale-95">
        <div v-if="showAssetNotification"
            class="fixed top-4 right-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border border-[--fin-gain]/30 bg-[--fin-gain]/8 px-4 py-3 text-sm font-medium text-[--fin-gain] shadow-lg backdrop-blur-sm">
                <Check class="h-4 w-4" />
                {{ assetNotificationMessage }}
            </div>
        </div>
    </Transition>

    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-3 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-3 scale-95">
        <div v-if="showLiabilityNotification"
            class="fixed top-4 right-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border border-[--fin-gain]/30 bg-[--fin-gain]/8 px-4 py-3 text-sm font-medium text-[--fin-gain] shadow-lg backdrop-blur-sm">
                <Check class="h-4 w-4" />
                {{ liabilityNotificationMessage }}
            </div>
        </div>
    </Transition>
</template>
