<script setup lang="ts">
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import {
    Plus, Trash2, ArrowUpRight, CalendarClock, CreditCard, ArrowDownRight, ChevronRight,
    ReceiptText, Inbox, Camera, X as XIcon,
} from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { useOnboarding } from '@/composables/useOnboarding';
import OnboardingTour from '@/components/OnboardingTour.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import SwipeToDelete from '@/components/SwipeToDelete.vue';
import TransactionModal from '@/components/TransactionModal.vue';
import { usePageLoading } from '@/composables/usePageLoading';
import HeroHeaderSkeleton from '@/components/Skeletons/HeroHeaderSkeleton.vue';
import CardGridSkeleton from '@/components/Skeletons/CardGridSkeleton.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';
import { useToast } from '@/composables/useToast';

interface AccountRef { id: number; name: string; color?: string | null; type?: string; }
interface ExpenseItem {
    id: number;
    amount: number;
    date: string;
    category: string | null;
    description: string | null;
    receipt_url: string | null;
    account: AccountRef | null;
}

interface Props {
    expenses: ExpenseItem[];
    accounts: { id: number; name: string; type: string }[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Expenses', href: '/expenses' }];

const { isLoading } = usePageLoading(500);

const showAddModal = useStorage('expenses-add-modal', false);
const expandedReceipt = ref<number | null>(null);
const uploadingReceipt = ref<number | null>(null);

const { addToast } = useToast();

const getYYYYMMDD = (d: Date) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

const expenseForm = useForm({
    amount: '',
    date: getYYYYMMDD(new Date()),
    category: '',
    description: '',
    account_id: '' as string | number,
});

const addExpense = (data: any) => {
    expenseForm.amount = data.amount;
    expenseForm.date = data.date;
    expenseForm.category = data.category;
    expenseForm.description = data.description;
    expenseForm.account_id = data.account_id;

    // If we have a receipt photo, use FormData
    if (data.receipt_photo) {
        const formData = new FormData();
        formData.append('amount', data.amount);
        formData.append('date', data.date);
        if (data.category) formData.append('category', data.category);
        if (data.description) formData.append('description', data.description);
        if (data.account_id) formData.append('account_id', String(data.account_id));
        formData.append('receipt_photo', data.receipt_photo);

        router.post('/expenses', formData, {
            onSuccess: () => {
                expenseForm.reset();
                expenseForm.date = getYYYYMMDD(new Date());
                showAddModal.value = false;
                addToast({ type: 'success', title: 'Expense Added', message: `₱${Number(data.amount).toLocaleString()} · ${data.category || 'Uncategorized'}` });
            },
        });
    } else {
        expenseForm.post('/expenses', {
            onSuccess: () => {
                expenseForm.reset();
                expenseForm.date = getYYYYMMDD(new Date());
                showAddModal.value = false;
                addToast({ type: 'success', title: 'Expense Added', message: `₱${Number(data.amount).toLocaleString()} · ${data.category || 'Uncategorized'}` });
            },
        });
    }
};

const deleteExpense = (id: number) => {
    if (!confirm('Delete this expense?')) return;
    router.delete(`/expenses/${id}`, { preserveScroll: true, onSuccess: () => { addToast({ type: 'info', title: 'Expense Removed', message: 'Transaction deleted' }); } });
};

const uploadReceiptForExpense = (expenseId: number, file: File) => {
    uploadingReceipt.value = expenseId;
    const formData = new FormData();
    formData.append('receipt_photo', file);
    formData.append('_method', 'POST');

    router.post(`/expenses/${expenseId}/receipt`, formData, {
        preserveScroll: true,
        onFinish: () => {
            uploadingReceipt.value = null;
        },
    });
};

const deleteReceiptForExpense = (expenseId: number) => {
    if (!confirm('Remove receipt photo?')) return;
    router.delete(`/expenses/${expenseId}/receipt`, { preserveScroll: true, onSuccess: () => { addToast({ type: 'info', title: 'Receipt Removed', message: 'Photo deleted' }); } });
};

const handleReceiptFileInput = (expenseId: number, e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;
    uploadReceiptForExpense(expenseId, input.files[0]);
    input.value = '';
};

const totalThisMonth = computed(() => {
    const now = new Date();
    return props.expenses
        .filter((e) => {
            const d = new Date(e.date);
            return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth();
        })
        .reduce((s, e) => s + e.amount, 0);
});

const formatDate = (s: string) =>
    new Date(s).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const refreshExpenses = () => {
    router.reload({ preserveScroll: true, only: ['expenses', 'accounts'] });
};

// Onboarding
const page = usePage();
const seenTour = useStorage('seen-tour-expenses', false, sessionStorage);
const tour = useOnboarding([
    { title: 'Welcome to Expenses', description: 'This is where all your spending is tracked. Each card shows the amount, category, and wallet used.', targetSelector: '.tour-expenses-hero' },
    { title: 'Expense Cards', description: 'Each expense displays the amount, date, receipt photo, and wallet. Swipe to delete on mobile or use the trash icon on desktop.', targetSelector: '.tour-expenses-grid' },
    { title: 'Add Expenses', description: 'Tap the Add Expense button to log a new expense with amount, category, wallet, and even a receipt photo.' },
]);

onMounted(() => {
    if (!seenTour.value && !(page.props.auth?.user as any)?.is_onboarded) {
        tour.showOnboarding.value = true;
        setTimeout(tour.updateTooltipPosition, 300);
    }
});
</script>

<template>
    <Head title="Expenses" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <template v-if="isLoading">
            <div class="flex h-full flex-1 flex-col gap-5 md:gap-6 p-4 sm:p-8 pb-24 md:pb-8">
                <HeroHeaderSkeleton />
                <CardGridSkeleton :count="6" />
            </div>
        </template>
        <template v-else>
            <PullToRefresh @refresh="refreshExpenses" class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">

            <!-- Header -->
            <div :class="['relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 mb-1.5 sm:mb-2 animate-fade-up stagger-1 tour-expenses-hero', tour.highlightClass(1)]">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-secondary/5 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary">
                                <ReceiptText class="w-3.5 h-3.5" />
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80 font-sans">Spending</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground font-display">
                            My <span class="text-primary">Expenses</span>
                        </h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">
                            <span class="text-foreground font-bold">{{ expenses.length }}</span> entries logged · <span class="text-foreground font-bold">{{ formatCurrency(totalThisMonth) }}</span> spent this month.
                        </p>
                    </div>

                    <div class="flex-shrink-0 flex items-center gap-3">
                        <button @click="showAddModal = true" class="group flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98]">
                            <Plus class="h-3.5 w-3.5 transition-transform group-hover:rotate-90" /> Add Expense
                        </button>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="flex flex-wrap gap-2 mt-3">
                    <Link href="/wallets" class="group flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-3 py-1.5 text-[11px] font-bold text-foreground transition-all">
                        <CreditCard class="h-3.5 w-3.5 text-muted-foreground" />
                        <span>Wallets</span>
                        <ChevronRight class="h-3 w-3 text-muted-foreground group-hover:translate-x-0.5 transition-transform" />
                    </Link>
                    <Link href="/bills" class="group flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-3 py-1.5 text-[11px] font-bold text-foreground transition-all">
                        <CalendarClock class="h-3.5 w-3.5 text-muted-foreground" />
                        <span>Bills</span>
                        <ChevronRight class="h-3 w-3 text-muted-foreground group-hover:translate-x-0.5 transition-transform" />
                    </Link>
                    <Link href="/incomes" class="group flex items-center gap-1.5 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-3 py-1.5 text-[11px] font-bold text-foreground transition-all">
                        <ArrowDownRight class="h-3.5 w-3.5 text-muted-foreground" />
                        <span>Income</span>
                        <ChevronRight class="h-3 w-3 text-muted-foreground group-hover:translate-x-0.5 transition-transform" />
                    </Link>
                </div>
            </div>

            <!-- Expenses Grid -->
            <div v-if="expenses.length" :class="['grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 animate-fade-up stagger-3 tour-expenses-grid', tour.highlightClass(2)]">
                <div v-for="expense in expenses" :key="expense.id" class="flex flex-col">
                    <SwipeToDelete
                        @delete="deleteExpense(expense.id)"
                        confirm-label="Delete?"
                    >
                        <div
                            class="group relative overflow-hidden rounded-[18px] sm:rounded-[24px] p-3 sm:p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 text-white shadow-lg"
                            :style="{ background: `linear-gradient(135deg, ${expense.account?.color || '#ef4444'}, #b91c1c)` }"
                        >
                            <div class="flex items-start justify-between mb-3 sm:mb-4">
                                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                    <div class="p-1.5 sm:p-2.5 rounded-lg sm:rounded-xl bg-white/20 flex-shrink-0">
                                        <ArrowUpRight class="h-3.5 w-3.5 sm:h-5 sm:w-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-xs sm:text-base truncate">{{ expense.category || 'Expense' }}</h3>
                                        <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-wider opacity-70 truncate">
                                            {{ expense.account?.name || 'No Wallet' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all hidden sm:flex">
                                    <button @click="deleteExpense(expense.id)" class="p-1.5 rounded-lg hover:bg-red-500/40 transition-colors">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex items-baseline gap-1 flex-wrap">
                                    <span class="text-base sm:text-2xl font-black font-mono">
                                        -{{ formatCurrency(expense.amount) }}
                                    </span>
                                </div>

                                <!-- Receipt thumbnail -->
                                <div v-if="expense.receipt_url" class="relative">
                                    <button
                                        @click.stop="expandedReceipt = expandedReceipt === expense.id ? null : expense.id"
                                        class="relative w-full rounded-xl overflow-hidden border border-white/10 group/receipt transition-all"
                                    >
                                        <img
                                            :src="expense.receipt_url"
                                            alt="Receipt"
                                            class="w-full h-20 object-cover transition-all group-hover/receipt:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                                        <div class="absolute bottom-1.5 left-2 flex items-center gap-1.5">
                                            <Camera class="w-3 h-3 text-white/80" />
                                            <span class="text-[9px] font-bold text-white/80">Receipt</span>
                                        </div>
                                    </button>

                                    <!-- Expanded receipt preview -->
                                    <div
                                        v-if="expandedReceipt === expense.id"
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
                                        @click="expandedReceipt = null"
                                    >
                                        <div class="relative max-w-2xl w-full" @click.stop>
                                            <button
                                                @click="expandedReceipt = null"
                                                class="absolute -top-3 -right-3 z-10 p-1.5 rounded-full bg-black/60 text-white hover:bg-black/80 transition-colors"
                                            >
                                                <XIcon class="w-4 h-4" />
                                            </button>
                                            <img
                                                :src="expense.receipt_url"
                                                alt="Receipt full view"
                                                class="w-full rounded-2xl shadow-2xl"
                                            />
                                            <div class="mt-2 flex justify-end">
                                                <button
                                                    @click="deleteReceiptForExpense(expense.id)"
                                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 text-[10px] font-bold hover:bg-red-500/30 transition-colors"
                                                >
                                                    Remove Photo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Upload receipt button (when no receipt) -->
                                <div v-else class="pt-1">
                                    <label class="flex items-center gap-1.5 cursor-pointer opacity-0 group-hover:opacity-100 transition-all">
                                        <Camera class="w-3 h-3 text-white/50" />
                                        <span class="text-[9px] font-semibold text-white/50 hover:text-white/80 transition-colors">
                                            {{ uploadingReceipt === expense.id ? 'Uploading...' : 'Attach receipt' }}
                                        </span>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            @change="handleReceiptFileInput(expense.id, $event)"
                                            class="hidden"
                                            :disabled="uploadingReceipt === expense.id"
                                        />
                                    </label>
                                </div>

                                <div class="flex flex-col gap-1 pt-3 sm:pt-4 border-t border-white/10">
                                    <span class="text-[8px] sm:text-[9px] font-bold uppercase tracking-wider opacity-70">Date</span>
                                    <span class="text-[10px] sm:text-xs font-bold">{{ formatDate(expense.date) }}</span>
                                    <p v-if="expense.description" class="text-[10px] sm:text-xs opacity-80 truncate mt-0.5">{{ expense.description }}</p>
                                </div>
                            </div>
                        </div>
                    </SwipeToDelete>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center py-20 animate-fade-up stagger-3">
                <div class="p-4 rounded-full bg-muted/30 mb-4">
                    <Inbox class="h-10 w-10 text-muted-foreground/30" />
                </div>
                <h3 class="text-lg font-bold text-foreground mb-1">No expenses yet</h3>
                <p class="text-sm text-muted-foreground mb-4">Log your first expense to start tracking your spending.</p>
                <button @click="showAddModal = true" class="flex items-center gap-2 rounded-2xl bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground hover:bg-primary/90 shadow-lg shadow-primary/20 active:scale-[0.98] transition-all">
                    <Plus class="h-4 w-4" /> Add Expense
                </button>
            </div>

        </PullToRefresh>
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

    <TransactionModal
        v-model:open="showAddModal"
        type="expense"
        title="Add Expense"
        :accounts="accounts"
        @submit="addExpense"
    />
</template>
