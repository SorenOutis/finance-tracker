<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { assets } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, onMounted, watch } from 'vue';
import { useLocalStorage } from '@/plugins/localStorage';
import { formatCurrency } from '@/utils/currency';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assets',
        href: assets(),
    },
];

const activeTab = ref<'assets' | 'liabilities'>('assets');

// Load active tab from local storage on mount
onMounted(() => {
    const savedTab = useLocalStorage.getTab('assets') as 'assets' | 'liabilities' | null;
    if (savedTab) {
        activeTab.value = savedTab;
    }
});

// Save active tab to local storage whenever it changes
watch(activeTab, (newTab) => {
    useLocalStorage.saveTab('assets', newTab);
});

const assetsList = ref<Array<{ name: string; amount: string; type: string }>>([]);
const liabilitiesList = ref<Array<{ name: string; amount: string; type: string }>>([]);

const assetForm = ref({
    name: '',
    amount: '',
    type: '',
});

const liabilityForm = ref({
    name: '',
    amount: '',
    type: '',
});

const assetTypes = ['Savings', 'Checking', 'Investments', 'Real Estate', 'Vehicle', 'Other'];
const liabilityTypes = ['Loan', 'Credit Card', 'Mortgage', 'Student Loan', 'Other'];

const addAsset = async () => {
    if (!assetForm.value.name || !assetForm.value.amount) return;

    try {
        const response = await fetch('/api/assets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify(assetForm.value),
        });

        if (response.ok) {
            const data = await response.json();
            assetsList.value.push(data);
            assetForm.value = {
                name: '',
                amount: '',
                type: '',
            };
        }
    } catch (error) {
        console.error('Error adding asset:', error);
    }
};

const addLiability = async () => {
    if (!liabilityForm.value.name || !liabilityForm.value.amount) return;

    try {
        const response = await fetch('/api/liabilities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify(liabilityForm.value),
        });

        if (response.ok) {
            const data = await response.json();
            liabilitiesList.value.push(data);
            liabilityForm.value = {
                name: '',
                amount: '',
                type: '',
            };
        }
    } catch (error) {
        console.error('Error adding liability:', error);
    }
};
</script>

<template>
    <Head title="Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Stats Cards -->
             <div class="grid gap-4 md:grid-cols-3">
                 <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                     <p class="text-sm text-gray-600 dark:text-gray-400">Total Assets</p>
                     <p class="text-4xl font-bold">{{ formatCurrency(0) }}</p>
                 </div>
                 <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                     <p class="text-sm text-gray-600 dark:text-gray-400">Total Liabilities</p>
                     <p class="text-4xl font-bold">{{ formatCurrency(0) }}</p>
                 </div>
                 <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                     <p class="text-sm text-gray-600 dark:text-gray-400">Net Worth</p>
                     <p class="text-4xl font-bold">{{ formatCurrency(0) }}</p>
                 </div>
             </div>

            <!-- Tab Navigation -->
            <div class="rounded-lg border border-gray-200 bg-white p-0 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                <div class="flex gap-0 border-b border-gray-300 dark:border-gray-700">
                    <button
                        @click="activeTab = 'assets'"
                        :class="[
                            'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
                            activeTab === 'assets'
                                ? 'border-b-2 border-black text-black dark:border-white dark:text-white'
                                : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Assets
                    </button>
                    <button
                        @click="activeTab = 'liabilities'"
                        :class="[
                            'flex-1 px-6 py-4 text-sm font-semibold transition-colors',
                            activeTab === 'liabilities'
                                ? 'border-b-2 border-black text-black dark:border-white dark:text-white'
                                : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        Liabilities
                    </button>
                </div>

                <!-- Assets Tab Content -->
                <div v-if="activeTab === 'assets'" class="p-6">
                    <h2 class="mb-6 text-xl font-bold">Add Asset</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Asset Name</label>
                            <input
                                v-model="assetForm.name"
                                type="text"
                                placeholder="e.g., Savings Account"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input
                                    v-model="assetForm.amount"
                                    type="number"
                                    placeholder="0.00"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium">
                                    Type <span class="text-gray-600 dark:text-gray-400">(Optional)</span>
                                </label>
                                <input
                                    v-model="assetForm.type"
                                    type="text"
                                    placeholder="e.g., Savings, Checking, Investments"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        @click="addAsset"
                        class="mt-6 w-full rounded-lg border border-gray-300 bg-white py-3 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                    >
                        Add Asset
                    </button>
                </div>

                <!-- Liabilities Tab Content -->
                <div v-if="activeTab === 'liabilities'" class="p-6">
                    <h2 class="mb-6 text-xl font-bold">Add Liability</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium">Liability Name</label>
                            <input
                                v-model="liabilityForm.name"
                                type="text"
                                placeholder="e.g., Car Loan"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input
                                    v-model="liabilityForm.amount"
                                    type="number"
                                    placeholder="0.00"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium">
                                    Type <span class="text-gray-600 dark:text-gray-400">(Optional)</span>
                                </label>
                                <input
                                    v-model="liabilityForm.type"
                                    type="text"
                                    placeholder="e.g., Loan, Mortgage, Credit Card"
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        @click="addLiability"
                        class="mt-6 w-full rounded-lg border border-gray-300 bg-white py-3 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                    >
                        Add Liability
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
