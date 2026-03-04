<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { ref, computed, onMounted, watch } from 'vue';
import { useLocalStorage } from '@/plugins/localStorage';
import { formatCurrency } from '@/utils/currency';

interface Stat {
    label: string;
    value: number;
}

interface Entry {
    id: number;
    amount: number;
    date: string;
    category?: string;
    description?: string;
}

interface Props {
    stats: Stat[];
    incomeEntries: Entry[];
    expenseEntries: Entry[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const getYYYYMMDDFromDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const getInitialDate = (): string => {
    const params = new URLSearchParams(window.location.search);
    const dateParam = params.get('date');
    return dateParam || getYYYYMMDDFromDate(new Date());
};

const selectedDate = ref(getInitialDate());
const currentMonth = computed(() => {
    const [year, month] = selectedDate.value.split('-');
    return new Date(parseInt(year), parseInt(month) - 1, 1);
});

const formatDate = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
};

const formatMonthYear = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const formatYearMonth = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${year}-${month}`;
};

const displayStats = computed(() => 
    props.stats.map(stat => ({
        label: stat.label,
        value: formatCurrency(stat.value)
    }))
);

const showIncomeModal = ref(false);
const showExpenseModal = ref(false);

// Load modal states from local storage on mount
onMounted(() => {
    if (useLocalStorage.getModalState('dashboard-income-modal')) {
        showIncomeModal.value = true;
    }
    if (useLocalStorage.getModalState('dashboard-expense-modal')) {
        showExpenseModal.value = true;
    }
});

// Save modal states to local storage whenever they change
watch(showIncomeModal, (value) => {
    useLocalStorage.saveModalState('dashboard-income-modal', value);
});

watch(showExpenseModal, (value) => {
    useLocalStorage.saveModalState('dashboard-expense-modal', value);
});

// Reload page when date changes
watch(selectedDate, () => {
    window.location.href = `?date=${selectedDate.value}`;
});

const incomeForm = useForm({
    amount: '',
    date: getYYYYMMDDFormat(new Date()),
    category: '',
    description: '',
});

const expenseForm = useForm({
    amount: '',
    date: getYYYYMMDDFormat(new Date()),
    category: '',
    description: '',
});

function getYYYYMMDDFormat(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

const addIncome = () => {
    if (!incomeForm.amount) return;

    incomeForm.post('/incomes', {
        onSuccess: () => {
            incomeForm.reset();
            incomeForm.date = getYYYYMMDDFormat(new Date());
            showIncomeModal.value = false;
        },
    });
};

const addExpense = () => {
    if (!expenseForm.amount) return;

    expenseForm.post('/expenses', {
        onSuccess: () => {
            expenseForm.reset();
            expenseForm.date = getYYYYMMDDFormat(new Date());
            showExpenseModal.value = false;
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
         <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
             <!-- Page Title -->
             <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Finance Tracker</h1>

             <!-- Header with Month and Buttons -->
              <div class="flex items-center justify-between gap-4">
                  <div class="flex items-center gap-4">
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Date:</span>
                      <input
                          v-model="selectedDate"
                          type="date"
                         class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 dark:border-slate-600 dark:bg-slate-700 dark:text-gray-200"
                     />
                 </div>

                <div class="flex gap-3">
                     <button
                         @click="showIncomeModal = true"
                         class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                     >
                         Add Income
                     </button>
                     <button
                         @click="showExpenseModal = true"
                         class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                     >
                         Add Expenses
                     </button>
                 </div>
            </div>

            <!-- Stats Cards -->
             <div class="grid gap-4 md:grid-cols-4">
                 <div
                     v-for="stat in displayStats"
                     :key="stat.label"
                     class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white"
                 >
                     <p class="text-sm text-gray-600 dark:text-slate-400">{{ stat.label }}</p>
                     <p class="text-4xl font-bold">
                         {{ stat.value }}
                     </p>
                 </div>
             </div>

            <!-- Income and Expenses Entries Section -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Income Section -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                    <h2 class="mb-2 text-lg font-bold">Income</h2>
                    <p class="mb-6 text-sm text-gray-700 dark:text-gray-300">
                        Add or view income entries for
                        {{ formatYearMonth(currentMonth) }}
                    </p>

                    <!-- Entries List -->
                     <div v-if="props.incomeEntries.length > 0" class="space-y-3 mb-6">
                         <div
                             v-for="entry in props.incomeEntries"
                             :key="entry.id"
                             class="rounded-lg border border-gray-300 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                         >
                             <div class="flex items-center justify-between">
                                 <div>
                                     <p class="text-sm font-medium">{{ formatCurrency(entry.amount) }}</p>
                                     <p class="text-xs text-gray-600 dark:text-gray-400">{{ entry.date }}</p>
                                     <p v-if="entry.category" class="text-xs text-gray-600 dark:text-gray-400">{{ entry.category }}</p>
                                     <p v-if="entry.description" class="text-xs text-gray-600 dark:text-gray-400">{{ entry.description }}</p>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Empty State -->
                     <p v-else class="text-center text-gray-500 dark:text-gray-400">
                         No income entries for this month
                     </p>
                </div>

                <!-- Expenses Section -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                    <h2 class="mb-2 text-lg font-bold">Expenses</h2>
                    <p class="mb-6 text-sm text-gray-700 dark:text-gray-300">
                        Add or view expense entries for
                        {{ formatYearMonth(currentMonth) }}
                    </p>

                    <!-- Entries List -->
                     <div v-if="props.expenseEntries.length > 0" class="space-y-3 mb-6">
                         <div
                             v-for="entry in props.expenseEntries"
                             :key="entry.id"
                             class="rounded-lg border border-gray-300 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                         >
                             <div class="flex items-center justify-between">
                                 <div>
                                     <p class="text-sm font-medium">{{ formatCurrency(entry.amount) }}</p>
                                     <p class="text-xs text-gray-600 dark:text-gray-400">{{ entry.date }}</p>
                                     <p v-if="entry.category" class="text-xs text-gray-600 dark:text-gray-400">{{ entry.category }}</p>
                                     <p v-if="entry.description" class="text-xs text-gray-600 dark:text-gray-400">{{ entry.description }}</p>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Empty State -->
                     <p v-else class="text-center text-gray-500 dark:text-gray-400">
                         No expense entries for this month
                     </p>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Income Modal -->
     <div
         v-if="showIncomeModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showIncomeModal = false"
     >
         <div class="w-full max-w-md rounded-lg border border-gray-300 bg-white p-6 text-black dark:border-gray-600 dark:bg-black dark:text-white">
             <h2 class="mb-4 text-lg font-bold">Add Income</h2>

             <div class="space-y-4">
                 <div>
                     <label class="block text-sm font-medium">Amount</label>
                     <input
                         v-model="incomeForm.amount"
                         type="number"
                         placeholder="0.00"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">Date</label>
                     <input
                         v-model="incomeForm.date"
                         type="date"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">
                         Category <span class="text-gray-600 dark:text-gray-400">(Optional)</span>
                     </label>
                     <input
                         v-model="incomeForm.category"
                         type="text"
                         placeholder="Salary, Bonus, etc."
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">Description</label>
                     <input
                         v-model="incomeForm.description"
                         type="text"
                         placeholder="Optional notes"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>
             </div>

             <div class="mt-6 flex gap-3">
                 <button
                     @click="showIncomeModal = false"
                     class="flex-1 rounded-lg border border-gray-300 bg-white py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                 >
                     Cancel
                 </button>
                 <button
                     @click="addIncome"
                     class="flex-1 rounded-lg border border-gray-300 bg-white py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                 >
                     Add Income
                 </button>
             </div>
         </div>
     </div>

    <!-- Expense Modal -->
     <div
         v-if="showExpenseModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showExpenseModal = false"
     >
         <div class="w-full max-w-md rounded-lg border border-gray-300 bg-white p-6 text-black dark:border-gray-600 dark:bg-black dark:text-white">
             <h2 class="mb-4 text-lg font-bold">Add Expense</h2>

             <div class="space-y-4">
                 <div>
                     <label class="block text-sm font-medium">Amount</label>
                     <input
                         v-model="expenseForm.amount"
                         type="number"
                         placeholder="0.00"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">Date</label>
                     <input
                         v-model="expenseForm.date"
                         type="date"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">
                         Category <span class="text-gray-600 dark:text-gray-400">(Optional)</span>
                     </label>
                     <input
                         v-model="expenseForm.category"
                         type="text"
                         placeholder="Food, Transport, Utilities, etc."
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>

                 <div>
                     <label class="block text-sm font-medium">Description</label>
                     <input
                         v-model="expenseForm.description"
                         type="text"
                         placeholder="Optional notes"
                         class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-black placeholder-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                     />
                 </div>
             </div>

             <div class="mt-6 flex gap-3">
                 <button
                     @click="showExpenseModal = false"
                     class="flex-1 rounded-lg border border-gray-300 bg-white py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                 >
                     Cancel
                 </button>
                 <button
                     @click="addExpense"
                     class="flex-1 rounded-lg border border-gray-300 bg-white py-2 font-semibold text-black hover:bg-gray-100 dark:border-gray-600 dark:bg-black dark:text-white dark:hover:bg-gray-800"
                 >
                     Add Expense
                 </button>
             </div>
         </div>
     </div>
</template>
