<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { Landmark, CreditCard, Scale, Plus, Check, Trash2, LayoutDashboard } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { useOnboarding } from '@/composables/useOnboarding';
import OnboardingTour from '@/components/OnboardingTour.vue';
import { usePageLoading } from '@/composables/usePageLoading';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { assets as assetsRoute } from '@/routes';import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';
import { useToast } from '@/composables/useToast';

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

const { isLoading } = usePageLoading(500);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: assetsRoute(),
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

// Onboarding
const page = usePage();
const seenTour = useStorage('seen-tour-assets', false, sessionStorage);
const tour = useOnboarding([
    { title: 'Assets & Liabilities', description: 'Track your net worth by managing what you own (assets) and what you owe (liabilities).', targetSelector: '.tour-assets-hero' },
    { title: 'Assets Tab', description: 'Add and manage your assets like savings, investments, and property. Check items off as you track them.', targetSelector: '.tour-assets-tab' },
    { title: 'Liabilities Tab', description: 'Track your debts like loans, mortgages, and credit card balances to understand your full financial picture.' },
]);

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

    // Start onboarding after animations
    if (!seenTour.value && !(page.props.auth?.user as any)?.is_onboarded) {
        setTimeout(() => {
            tour.showOnboarding.value = true;
            setTimeout(tour.updateTooltipPosition, 300);
        }, 800);
    }
});
</script>

<template>

    <Head title="Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template v-if="isLoading">
            <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 p-4 sm:p-8 pb-24 md:pb-8 animate-pulse">
                <div class="rounded-[24px] bg-card border border-border/50 p-4 sm:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <Skeleton class="h-6 w-6 rounded-lg" />
                                <Skeleton class="h-3 w-20" />
                            </div>
                            <Skeleton class="h-7 w-48 mb-2" />
                            <Skeleton class="h-4 w-64" />
                        </div>
                        <div class="flex items-center gap-6 lg:pl-6 lg:border-l border-border/50">
                            <Skeleton class="h-14 w-14 sm:h-16 sm:w-16 rounded-full" />
                            <div>
                                <Skeleton class="h-3 w-24 mb-1" />
                                <Skeleton class="h-7 w-32" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-[20px] border border-border/40 bg-card">
                    <div class="flex border-b border-border">
                        <Skeleton class="h-12 flex-1 m-2 rounded-lg" />
                        <Skeleton class="h-12 flex-1 m-2 rounded-lg" />
                    </div>
                    <div class="p-5 lg:p-6 space-y-4">
                        <div v-for="i in 4" :key="i" class="flex items-start justify-between py-4 border-b border-border/20">
                            <div class="flex items-start gap-3 flex-1">
                                <Skeleton class="h-5 w-5 rounded-md mt-0.5" />
                                <div class="flex-1">
                                    <Skeleton class="h-4 w-28 mb-1" />
                                    <Skeleton class="h-4 w-20" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-8 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header Section (Compact Hero) -->
            <div :class="['relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-6 mb-1.5 sm:mb-2 animate-fade-up stagger-1 tour-assets-hero', tour.highlightClass(1)]">
                <!-- Background abstract shapes -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl"></div>

                <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary">
                                <Scale class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Net Worth</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                            Holdings & <span class="text-primary">Liabilities</span>
                        </h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                            Managing <span class="text-foreground font-bold">{{ formatCurrency(props.totalAssets) }}</span> in assets and <span class="text-foreground font-bold">{{ formatCurrency(props.totalLiabilities) }}</span> in liabilities across your portfolio.
                        </p>
                    </div>

                    <div class="flex flex-row items-center gap-6 lg:pl-6 lg:border-l border-border/50">
                        <!-- Net Worth Ring -->
                        <div class="relative w-14 h-14 sm:w-16 sm:h-16 shrink-0">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                    class="stroke-border/30" stroke-width="12" />
                                <circle cx="60" cy="60" :r="ringRadius" fill="none"
                                    :stroke="props.netWorth >= 0 ? 'oklch(0.76 0.16 162)' : 'oklch(0.62 0.22 18)'"
                                    stroke-width="12"
                                    stroke-linecap="round"
                                    :stroke-dasharray="ringCircumference"
                                    :stroke-dashoffset="ringDashOffset"
                                    class="transition-all duration-1000 ease-out" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <Scale class="h-4 w-4 sm:h-5 sm:w-5 text-muted-foreground/50" />
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70">Current Net Worth</span>
                            <span :class="['text-xl sm:text-2xl font-black font-mono tracking-tighter', props.netWorth >= 0 ? 'text-[--fin-gain]' : 'text-[--fin-loss]']">
                                {{ formatCurrency(props.netWorth) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div :class="['surface-card overflow-hidden p-0 animate-fade-up stagger-4 tour-assets-tab', tour.highlightClass(2)]">
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
        </template>
    </AppLayout>

    <OnboardingTour
        :show="tour.showOnboarding.value"
        :step="tour.onboardingStep.value"
        :total="tour.totalSteps"
        :current-step="tour.currentStep.value"
        :tooltip-style="tour.tooltipStyle.value"
        :tooltip-side="tour.tooltipSide.value"
        @next="tour.nextStep()"
        @prev="tour.prevStep()"
        @close="tour.completeOnboarding()"
    />

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
