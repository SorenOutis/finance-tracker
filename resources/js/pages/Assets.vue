<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { assets } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

import { useStorage } from '@vueuse/core';
import { formatCurrency } from '@/utils/currency';

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
</script>

<template>

    <Head title="Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Assets & Liabilities</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Manage your financial holdings</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:gap-6">
                <div class="surface-card p-4 lg:p-5 overflow-hidden relative">
                    <p
                        class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                        Total Assets</p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">{{
                        formatCurrency(props.totalAssets) }}</p>
                </div>
                <div class="surface-card p-4 lg:p-5 overflow-hidden relative">
                    <p
                        class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                        Total Liabilities</p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">{{
                        formatCurrency(props.totalLiabilities) }}</p>
                </div>
                <div class="surface-card p-4 lg:p-5 overflow-hidden relative">
                    <p
                        class="text-[11px] font-semibold tracking-[0.08em] uppercase text-muted-foreground mb-2.5 font-sans">
                        Net Worth</p>
                    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono tracking-tighter leading-none">{{
                        formatCurrency(props.netWorth) }}</p>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="surface-card overflow-hidden p-0">
                <div class="flex gap-0 border-b border-border">
                    <button @click="activeTab = 'assets'" :class="[
                        'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
                        activeTab === 'assets'
                            ? 'border-b-2 border-primary text-primary'
                            : 'text-muted-foreground hover:text-foreground'
                    ]">
                        Assets
                    </button>
                    <button @click="activeTab = 'liabilities'" :class="[
                        'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
                        activeTab === 'liabilities'
                            ? 'border-b-2 border-primary text-primary'
                            : 'text-muted-foreground hover:text-foreground'
                    ]">
                        Liabilities
                    </button>
                </div>

                <!-- Assets Tab Content -->
                <div v-if="activeTab === 'assets'" class="p-5 lg:p-6">
                    <h2 class="mb-6 text-sm font-bold text-foreground font-display tracking-tight">Add Asset</h2>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Asset
                                Name</label>
                            <input v-model="assetForm.name" type="text" name="name" placeholder="e.g., Savings Account"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                                <input v-model="assetForm.amount" type="number" name="amount" placeholder="0.00"
                                    class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-muted-foreground font-sans">
                                    Type <span class="font-normal text-muted-foreground/70">(Optional)</span>
                                </label>
                                <input v-model="assetForm.type" type="text" name="type"
                                    placeholder="e.g., Savings, Checking, Investments"
                                    class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                            </div>
                        </div>
                    </div>

                    <button @click="addAsset"
                        class="mt-8 w-full rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                        Add Asset
                    </button>

                    <!-- Assets List -->
                    <div v-if="props.assets.length > 0" class="mt-8">
                        <h3 class="mb-4 text-sm font-bold text-foreground font-display tracking-tight">Your Assets</h3>
                        <div class="divide-y divide-border/30 space-y-0">
                            <div v-for="asset in props.assets" :key="asset.id"
                                class="group flex items-start justify-between py-4 transition-colors first:pt-0 hover:bg-accent/20">
                                <div class="flex items-start gap-3 flex-1">
                                    <input type="checkbox" :checked="asset.checked" @change="toggleAsset(asset.id)"
                                        class="mt-1 h-4 w-4 rounded border border-border cursor-pointer" />
                                    <div class="flex-1">
                                        <p :class="['font-semibold text-[13px] font-sans', asset.checked ? 'line-through text-muted-foreground' : 'text-foreground']">{{
                                            asset.name }}</p>
                                        <p class="mt-1 text-sm font-mono text-foreground">{{ formatCurrency(asset.amount)
                                        }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span v-if="asset.type"
                                                class="text-xs font-medium text-muted-foreground bg-muted/40 px-2.5 py-1 rounded-lg font-sans">{{
                                                    asset.type
                                                }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="deleteAsset(asset.id)"
                                    class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                    <span class="text-sm">✕</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liabilities Tab Content -->
                <div v-if="activeTab === 'liabilities'" class="p-5 lg:p-6">
                    <h2 class="mb-6 text-sm font-bold text-foreground font-display tracking-tight">Add Liability</h2>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-muted-foreground font-sans">Liability
                                Name</label>
                            <input v-model="liabilityForm.name" type="text" name="name" placeholder="e.g., Car Loan"
                                class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-muted-foreground font-sans">Amount</label>
                                <input v-model="liabilityForm.amount" type="number" name="amount" placeholder="0.00"
                                    class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-muted-foreground font-sans">
                                    Type <span class="font-normal text-muted-foreground/70">(Optional)</span>
                                </label>
                                <input v-model="liabilityForm.type" type="text" name="type"
                                    placeholder="e.g., Loan, Mortgage, Credit Card"
                                    class="mt-2 w-full rounded-lg border border-border bg-muted/40 px-4 py-2.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/10" />
                            </div>
                        </div>
                    </div>

                    <button @click="addLiability"
                        class="mt-8 w-full rounded-lg border border-primary bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md">
                        Add Liability
                    </button>

                    <!-- Liabilities List -->
                    <div v-if="props.liabilities.length > 0" class="mt-8">
                        <h3 class="mb-4 text-sm font-bold text-foreground font-display tracking-tight">Your Liabilities
                        </h3>
                        <div class="divide-y divide-border/30 space-y-0">
                            <div v-for="liability in props.liabilities" :key="liability.id"
                                class="group flex items-start justify-between py-4 transition-colors first:pt-0 hover:bg-accent/20">
                                <div class="flex items-start gap-3 flex-1">
                                    <input type="checkbox" :checked="liability.checked" @change="toggleLiability(liability.id)"
                                        class="mt-1 h-4 w-4 rounded border border-border cursor-pointer" />
                                    <div class="flex-1">
                                        <p :class="['font-semibold text-[13px] font-sans', liability.checked ? 'line-through text-muted-foreground' : 'text-foreground']">{{
                                            liability.name }}</p>
                                        <p class="mt-1 text-sm font-mono text-foreground">{{ formatCurrency(liability.amount)
                                        }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span v-if="liability.type"
                                                class="text-xs font-medium text-muted-foreground bg-muted/40 px-2.5 py-1 rounded-lg font-sans">{{
                                                    liability.type
                                                }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="deleteLiability(liability.id)"
                                    class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 transition-all hover:bg-destructive/10 hover:text-destructive group-hover:opacity-100">
                                    <span class="text-sm">✕</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Asset Added Notification -->
    <div v-if="showAssetNotification"
        class="fixed top-4 right-4 z-50 animate-in fade-in slide-in-from-top-2 duration-300">
        <div
            class="rounded-lg border border-green-500/50 bg-green-500/10 px-4 py-3 text-sm font-medium text-green-700 shadow-lg">
            ✓ {{ assetNotificationMessage }}
        </div>
    </div>

    <!-- Liability Added Notification -->
    <div v-if="showLiabilityNotification"
        class="fixed top-4 right-4 z-50 animate-in fade-in slide-in-from-top-2 duration-300">
        <div
            class="rounded-lg border border-green-500/50 bg-green-500/10 px-4 py-3 text-sm font-medium text-green-700 shadow-lg">
            ✓ {{ liabilityNotificationMessage }}
        </div>
    </div>
</template>
