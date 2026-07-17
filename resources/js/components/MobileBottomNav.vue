<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    LayoutGrid, History, CreditCard, User, Plus, Wallet, CalendarClock,
    ArrowDownRight, ArrowUpRight, X, LineChart, Tags, CalendarDays,
    PiggyBank, Download, Sparkles, ReceiptText, Grid3x3,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, analytics } from '@/routes';

const { isCurrentUrl } = useCurrentUrl();

const navItems = [
    { title: 'Dashboard', href: dashboard().url, icon: LayoutGrid },
    { title: 'History', href: '/history', icon: History },
    { title: 'Wallets', href: '/wallets', icon: CreditCard },
    { title: 'More', href: '', icon: Grid3x3 },
];

const moreItems = [
    { title: 'Bills', href: '/bills', icon: CalendarClock },
    { title: 'Expenses', href: '/expenses', icon: ReceiptText },
    { title: 'Income', href: '/incomes', icon: Wallet },
    { title: 'Analytics', href: analytics().url, icon: LineChart },
    { title: 'Categories', href: '/categories', icon: Tags },
    { title: 'Calendar', href: '/calendar', icon: CalendarDays },
    { title: 'Envelopes', href: '/envelopes', icon: PiggyBank },
    { title: 'Import', href: '/import', icon: Download },
    { title: 'Pricing', href: '/pricing', icon: Sparkles },
    { title: 'Profile', href: '/settings/profile', icon: User },
];

function isMoreActive() {
    return moreItems.some(item => item.href && isCurrentUrl(item.href));
}

const showAddSheet = ref(false);
const showMoreSheet = ref(false);

const quickAddOptions = [
    { key: 'wallets-add-modal', href: '/wallets', label: 'Wallet', sub: 'Account or card', icon: Wallet, accent: 'indigo' },
    { key: 'expenses-add-modal', href: '/expenses', label: 'Expense', sub: 'One-time spend', icon: ArrowUpRight, accent: 'amber' },
    { key: 'bills-add-modal', href: '/bills', label: 'Bill', sub: 'Recurring expense', icon: CalendarClock, accent: 'rose' },
    { key: 'incomes-add-modal', href: '/incomes', label: 'Income', sub: 'Recurring earning', icon: ArrowDownRight, accent: 'emerald' },
];

const accentClasses: Record<string, { bg: string; bgHover: string; iconBg: string; iconText: string; border: string }> = {
    indigo:  { bg: 'bg-indigo-500/10',  bgHover: 'hover:bg-indigo-500/15',  iconBg: 'bg-indigo-500/20',  iconText: 'text-indigo-500',  border: 'hover:border-indigo-500/40' },
    rose:    { bg: 'bg-rose-500/10',    bgHover: 'hover:bg-rose-500/15',    iconBg: 'bg-rose-500/20',    iconText: 'text-rose-500',    border: 'hover:border-rose-500/40' },
    emerald: { bg: 'bg-emerald-500/10', bgHover: 'hover:bg-emerald-500/15', iconBg: 'bg-emerald-500/20', iconText: 'text-emerald-500', border: 'hover:border-emerald-500/40' },
    amber:   { bg: 'bg-amber-500/10',   bgHover: 'hover:bg-amber-500/15',   iconBg: 'bg-amber-500/20',   iconText: 'text-amber-500',   border: 'hover:border-amber-500/40' },
};

const triggerAdd = (option: typeof quickAddOptions[number]) => {
    showAddSheet.value = false;
    try { localStorage.setItem(option.key, 'true'); } catch {}
    router.visit(option.href);
};

const navigateTo = (href: string) => {
    showMoreSheet.value = false;
    router.visit(href);
};
</script>

<template>
    <nav
        class="fixed left-4 right-4 sm:left-6 sm:right-6 z-50 rounded-[2.5rem] border border-white/20 dark:border-white/10 bg-background/70 shadow-[0_20px_50px_rgba(0,0,0,0.3)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.6)] focus:outline-none backdrop-blur-2xl supports-[backdrop-filter]:bg-background/40 md:hidden"
        style="bottom: calc(1.5rem + env(safe-area-inset-bottom));"
    >
        <div class="flex items-center justify-around px-2 pt-2 pb-2">
            <Link
                v-for="item in navItems.slice(0, 2)"
                :key="item.title"
                :href="item.href"
                class="relative flex flex-1 flex-col items-center justify-center gap-1 py-1.5 px-1 text-[10px] font-medium transition-all"
                :class="isCurrentUrl(item.href) ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
            >
                <div v-show="isCurrentUrl(item.href)" class="absolute -top-2 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary" />
                <component :is="item.icon" class="h-6 w-6 stroke-[1.5]" />
                <span class="truncate">{{ item.title }}</span>
            </Link>

            <button
                @click="showAddSheet = true"
                class="relative flex flex-col items-center justify-center mx-1 -mt-6"
                aria-label="Quick add"
            >
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-xl shadow-primary/30 ring-4 ring-background transition-transform active:scale-90">
                    <Plus class="h-6 w-6" />
                </div>
                <span class="text-[10px] font-bold mt-1 text-foreground">Add</span>
            </button>

            <!-- Wallet + More -->
            <Link
                :href="navItems[2].href"
                class="relative flex flex-1 flex-col items-center justify-center gap-1 py-1.5 px-1 text-[10px] font-medium transition-all"
                :class="isCurrentUrl(navItems[2].href) ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
            >
                <div v-show="isCurrentUrl(navItems[2].href)" class="absolute -top-2 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary" />
                <component :is="navItems[2].icon" class="h-6 w-6 stroke-[1.5]" />
                <span class="truncate">{{ navItems[2].title }}</span>
            </Link>

            <button
                @click="showMoreSheet = true"
                class="relative flex flex-1 flex-col items-center justify-center gap-1 py-1.5 px-1 text-[10px] font-medium transition-all"
                :class="isMoreActive() ? 'text-primary' : 'text-muted-foreground hover:text-foreground'"
            >
                <div v-show="isMoreActive()" class="absolute -top-2 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full bg-primary" />
                <Grid3x3 class="h-6 w-6 stroke-[1.5]" />
                <span class="truncate">More</span>
            </button>
        </div>
    </nav>

    <!-- ===== Quick Add Sheet ===== -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200" leave-active-class="transition duration-150"
            enter-from-class="opacity-0" leave-to-class="opacity-0"
        >
            <div v-if="showAddSheet" @click="showAddSheet = false" class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm md:hidden" />
        </Transition>
        <Transition
            enter-active-class="transition duration-300 ease-out" leave-active-class="transition duration-200 ease-in"
            enter-from-class="translate-y-full opacity-0" leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="showAddSheet"
                class="fixed left-4 right-4 z-[61] rounded-[28px] border border-border/40 bg-background p-5 shadow-2xl md:hidden"
                style="bottom: calc(7rem + env(safe-area-inset-bottom));"
            >
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black tracking-tight">Quick Add</h3>
                    <button @click="showAddSheet = false" class="p-1.5 rounded-full hover:bg-muted/50">
                        <X class="h-4 w-4 text-muted-foreground" />
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button v-for="opt in quickAddOptions" :key="opt.key"
                        @click="triggerAdd(opt)"
                        :class="['flex flex-col items-center justify-center gap-2 rounded-2xl border border-border/40 p-3 transition-all active:scale-95', accentClasses[opt.accent].bg, accentClasses[opt.accent].bgHover, accentClasses[opt.accent].border]"
                    >
                        <div :class="['p-2 rounded-xl', accentClasses[opt.accent].iconBg, accentClasses[opt.accent].iconText]">
                            <component :is="opt.icon" class="h-5 w-5" />
                        </div>
                        <span class="text-xs font-black">{{ opt.label }}</span>
                        <span class="text-[9px] text-muted-foreground text-center leading-tight">{{ opt.sub }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- ===== More Navigation Sheet ===== -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200" leave-active-class="transition duration-150"
            enter-from-class="opacity-0" leave-to-class="opacity-0"
        >
            <div v-if="showMoreSheet" @click="showMoreSheet = false" class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm md:hidden" />
        </Transition>
        <Transition
            enter-active-class="transition duration-300 ease-out" leave-active-class="transition duration-200 ease-in"
            enter-from-class="translate-y-full opacity-0" leave-to-class="translate-y-full opacity-0"
        >
            <div v-if="showMoreSheet"
                class="fixed left-4 right-4 z-[61] rounded-[28px] border border-border/40 bg-background p-5 shadow-2xl md:hidden"
                style="bottom: calc(7rem + env(safe-area-inset-bottom));"
            >
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-black tracking-tight">All Pages</h3>
                    <button @click="showMoreSheet = false" class="p-1.5 rounded-full hover:bg-muted/50">
                        <X class="h-4 w-4 text-muted-foreground" />
                    </button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-[340px] overflow-y-auto">
                    <button
                        v-for="item in moreItems"
                        :key="item.title"
                        @click="navigateTo(item.href)"
                        class="flex flex-col items-center justify-center gap-1.5 rounded-2xl border border-border/30 bg-card/30 p-3 transition-all active:scale-95 hover:bg-card/60 hover:border-border/60"
                        :class="isCurrentUrl(item.href) ? 'border-primary/40 bg-primary/5 ring-1 ring-primary/20' : ''"
                    >
                        <div class="p-1.5 rounded-xl" :class="isCurrentUrl(item.href) ? 'bg-primary/10 text-primary' : 'bg-muted/30 text-muted-foreground'">
                            <component :is="item.icon" class="h-5 w-5" />
                        </div>
                        <span class="text-[9px] font-bold text-center leading-tight" :class="isCurrentUrl(item.href) ? 'text-primary' : 'text-foreground'">{{ item.title }}</span>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
