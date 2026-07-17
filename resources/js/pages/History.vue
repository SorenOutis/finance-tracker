<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import {
    Search, ArrowDownRight, ArrowUpRight, History as HistoryIcon, Trash2, Inbox,
} from 'lucide-vue-next';
import { ref, computed, watch, onMounted } from 'vue';
import OnboardingTour from '@/components/OnboardingTour.vue';
import PullToRefresh from '@/components/PullToRefresh.vue';
import HistorySkeleton from '@/components/Skeletons/HistorySkeleton.vue';
import SwipeToDelete from '@/components/SwipeToDelete.vue';
import { useOnboarding } from '@/composables/useOnboarding';
import { usePageLoading } from '@/composables/usePageLoading';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';

interface AccountRef { id: number; name: string; color: string | null; type: string; }
interface HistoryItem {
    id: string;
    raw_id: number;
    type: 'income' | 'expense';
    amount: number;
    category: string | null;
    description: string | null;
    date: string;
    account: AccountRef | null;
}

const props = defineProps<{ items: HistoryItem[]; query: string }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'History', href: '/history' }];

const { isLoading } = usePageLoading(500);

const { addToast } = useToast();

const search = ref(props.query || '');

let debounce: ReturnType<typeof setTimeout> | null = null;
watch(search, (val) => {
    if (debounce) clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/history', val ? { q: val } : {}, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
});

const startOfDay = (d: Date) => { const x = new Date(d); x.setHours(0, 0, 0, 0); return x; };
const today = startOfDay(new Date());
const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
const startOfWeek = new Date(today); startOfWeek.setDate(today.getDate() - today.getDay());
const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

const groupLabel = (dateStr: string) => {
    const d = startOfDay(new Date(dateStr));
    if (d.getTime() === today.getTime()) return 'Today';
    if (d.getTime() === yesterday.getTime()) return 'Yesterday';
    if (d >= startOfWeek) return 'This Week';
    if (d >= startOfMonth) return 'This Month';
    return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const groupOrder = ['Today', 'Yesterday', 'This Week', 'This Month'];

const grouped = computed(() => {
    const map = new Map<string, HistoryItem[]>();
    for (const item of props.items) {
        const label = groupLabel(item.date);
        if (!map.has(label)) map.set(label, []);
        map.get(label)!.push(item);
    }
    const entries = Array.from(map.entries());
    entries.sort((a, b) => {
        const ai = groupOrder.indexOf(a[0]);
        const bi = groupOrder.indexOf(b[0]);
        if (ai !== -1 && bi !== -1) return ai - bi;
        if (ai !== -1) return -1;
        if (bi !== -1) return 1;
        // both are month-year; compare actual dates of first item
        return new Date(b[1][0].date).getTime() - new Date(a[1][0].date).getTime();
    });
    return entries;
});

const totalIncome = computed(() => props.items.filter(i => i.type === 'income').reduce((s, i) => s + i.amount, 0));
const totalExpense = computed(() => props.items.filter(i => i.type === 'expense').reduce((s, i) => s + i.amount, 0));

const formatDay = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
};

const deleteItem = (item: HistoryItem) => {
    if (!confirm(`Delete this ${item.type}?`)) return;
    const url = item.type === 'income' ? `/incomes/${item.raw_id}` : `/expenses/${item.raw_id}`;
    router.delete(url, { preserveScroll: true, onSuccess: () => { addToast({ type: 'info', title: item.type === 'income' ? 'Income Deleted' : 'Expense Deleted', message: item.category || 'Entry removed' }); } });
};

const refreshHistory = () => {
    router.reload({ only: ['items', 'query'] });
};

// Onboarding
const page = usePage();
const seenTour = useStorage('seen-tour-history', false, sessionStorage);
const tour = useOnboarding([
    { title: 'Transaction History', description: 'View all your income and expenses in one place, grouped by date for easy browsing.', targetSelector: '.tour-history-hero' },
    { title: 'Search & Stats', description: 'Use the search bar to find transactions by category or description. The stats strip shows totals at a glance.', targetSelector: '.tour-history-search' },
    { title: 'Transaction List', description: 'Each entry shows the type, amount, category, and date. Swipe to delete or use the trash icon on desktop.' },
]);

onMounted(() => {
    if (!seenTour.value && !(page.props.auth?.user as any)?.is_onboarded) {
        tour.showOnboarding.value = true;
        setTimeout(tour.updateTooltipPosition, 300);
    }
});
</script>

<template>
    <Head title="History" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div :class="['flex items-center justify-between gap-4 tour-history-hero', tour.highlightClass(1)]">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="p-1.5 rounded-lg bg-primary/10">
                            <HistoryIcon class="h-3.5 w-3.5 text-primary" />
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Transactions</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight font-display">
                        Your <span class="text-primary">History</span>
                    </h1>
                </div>
            </div>

            <!-- Stat strip -->
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                <div class="rounded-2xl border border-border/50 bg-card p-3">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Total In</p>
                    <p class="text-sm sm:text-base font-black text-emerald-500 mt-0.5">+{{ formatCurrency(totalIncome) }}</p>
                </div>
                <div class="rounded-2xl border border-border/50 bg-card p-3">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Total Out</p>
                    <p class="text-sm sm:text-base font-black text-rose-500 mt-0.5">-{{ formatCurrency(totalExpense) }}</p>
                </div>
                <div class="rounded-2xl border border-border/50 bg-card p-3">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Entries</p>
                    <p class="text-sm sm:text-base font-black mt-0.5">{{ items.length }}</p>
                </div>
            </div>

            <!-- Search -->
            <div :class="['relative tour-history-search', tour.highlightClass(2)]">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search by category or description..."
                    class="w-full rounded-2xl bg-card border border-border/50 pl-11 pr-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 transition-all placeholder:text-muted-foreground/50"
                />
            </div>

            <template v-if="isLoading">
                <HistorySkeleton />
            </template>
            <template v-else>
            <PullToRefresh @refresh="refreshHistory">

            <!-- Empty state -->
            <div v-if="!items.length" class="flex flex-col items-center justify-center py-16 text-center">
                <div class="p-4 rounded-2xl bg-muted/30 mb-4">
                    <Inbox class="h-8 w-8 text-muted-foreground" />
                </div>
                <h3 class="text-base font-bold mb-1">No transactions found</h3>
                <p class="text-xs text-muted-foreground max-w-xs">
                    {{ search ? `No results for "${search}". Try a different search.` : 'Start tracking by adding income or expenses from the dashboard.' }}
                </p>
            </div>

            <!-- Grouped list -->
            <div v-else class="flex flex-col gap-5">
                <section v-for="[label, list] in grouped" :key="label">
                    <div class="flex items-baseline justify-between mb-2 px-1">
                        <h2 class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">{{ label }}</h2>
                        <span class="text-[10px] font-medium text-muted-foreground/70">{{ list.length }} {{ list.length === 1 ? 'entry' : 'entries' }}</span>
                    </div>
                    <ul class="flex flex-col gap-1.5">
                        <li v-for="item in list" :key="item.id">
                            <SwipeToDelete
                                :disabled="false"
                                @delete="deleteItem(item)"
                            >
                                <div class="group flex items-center gap-3 rounded-2xl border border-border/40 bg-card p-3 hover:border-primary/30 transition-all">
                                    <div :class="[
                                        'flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl',
                                        item.type === 'income' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500'
                                    ]">
                                        <component :is="item.type === 'income' ? ArrowDownRight : ArrowUpRight" class="h-4 w-4" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold truncate">{{ item.category || (item.type === 'income' ? 'Income' : 'Expense') }}</p>
                                            <span v-if="item.account" class="hidden sm:inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase tracking-wider"
                                                :style="{ backgroundColor: (item.account.color || '#6366F1') + '20', color: item.account.color || '#6366F1' }"
                                            >{{ item.account.name }}</span>
                                        </div>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">
                                            {{ formatDay(item.date) }}<span v-if="item.description"> · {{ item.description }}</span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <p :class="[
                                            'text-sm font-black tabular-nums',
                                            item.type === 'income' ? 'text-emerald-500' : 'text-rose-500'
                                        ]">
                                            {{ item.type === 'income' ? '+' : '-' }}{{ formatCurrency(item.amount) }}
                                        </p>
                                        <!-- Desktop hover delete button -->
                                        <button @click="deleteItem(item)" class="p-1.5 rounded-lg text-muted-foreground hover:text-rose-500 hover:bg-rose-500/10 opacity-0 group-hover:opacity-100 transition-all hidden sm:block">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </SwipeToDelete>
                        </li>
                    </ul>
                </section>
            </div>
            </PullToRefresh>
            </template>
        </div>
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
</template>
