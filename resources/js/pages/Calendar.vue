<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays, ChevronRight, CalendarClock, CreditCard
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Calendar', href: '/calendar' }];

// Calendar state
const today = new Date();
const viewDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));
const selectedDay = ref<number | null>(null);

const currentMonth = computed(() => viewDate.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }));
const firstDay = computed(() => new Date(viewDate.value.getFullYear(), viewDate.value.getMonth(), 1).getDay());
const daysInMonth = computed(() => new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + 1, 0).getDate());
const calendarDays = computed(() => Array.from({ length: daysInMonth.value }, (_, i) => i + 1));

const prevMonth = () => viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() - 1, 1);
const nextMonth = () => viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + 1, 1);

// Mock events
const transactions: Record<string, { type: string; amount: number; label: string; category: string }[]> = {
    '2026-04-02': [{ type: 'income', amount: 45000, label: 'Salary', category: 'Salary' }],
    '2026-04-05': [{ type: 'expense', amount: 1699, label: 'Internet', category: 'Utilities' }],
    '2026-04-08': [{ type: 'expense', amount: 549, label: 'Netflix', category: 'Entertainment' }],
    '2026-04-10': [{ type: 'expense', amount: 149, label: 'Spotify', category: 'Entertainment' }],
    '2026-04-12': [{ type: 'expense', amount: 2500, label: 'Groceries', category: 'Groceries' }],
    '2026-04-15': [{ type: 'expense', amount: 8500, label: 'Rent', category: 'Rent' }],
    '2026-04-18': [{ type: 'income', amount: 5000, label: 'Freelance', category: 'Freelance' }],
    '2026-04-20': [{ type: 'expense', amount: 1200, label: 'Dining Out', category: 'Dining Out' }],
    '2026-04-22': [{ type: 'expense', amount: 3500, label: 'Shopping', category: 'Shopping' }],
    '2026-04-25': [{ type: 'expense', amount: 800, label: 'Transport', category: 'Transport' }],
    '2026-04-28': [{ type: 'income', amount: 45000, label: 'Salary', category: 'Salary' }],
};

const getDateStr = (day: number) => {
    const d = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth(), day);
    return d.toISOString().split('T')[0];
};

const getEventsForDay = (day: number) => {
    return transactions[getDateStr(day)] || [];
};

const selectedEvents = computed(() => {
    if (!selectedDay.value) return [];
    return getEventsForDay(selectedDay.value);
});

const selectDay = (day: number) => {
    selectedDay.value = selectedDay.value === day ? null : day;
};

const isToday = (day: number) => {
    return today.getFullYear() === viewDate.value.getFullYear() &&
           today.getMonth() === viewDate.value.getMonth() &&
           today.getDate() === day;
};

const monthIncome = computed(() => {
    let total = 0;
    calendarDays.value.forEach(day => {
        getEventsForDay(day).filter(e => e.type === 'income').forEach(e => total += e.amount);
    });
    return total;
});

const monthExpense = computed(() => {
    let total = 0;
    calendarDays.value.forEach(day => {
        getEventsForDay(day).filter(e => e.type === 'expense').forEach(e => total += e.amount);
    });
    return total;
});
</script>

<template>
    <Head title="Calendar" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 animate-fade-up stagger-1">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary"><CalendarDays class="w-3.5 h-3.5" /></div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Planning</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight font-display">Financial <span class="text-primary">Calendar</span></h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium max-w-xl leading-relaxed">Visual overview of your transactions, bills, and recurring events</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> ₱{{ monthIncome.toLocaleString() }}</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> ₱{{ monthExpense.toLocaleString() }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <Link href="/wallets" class="flex items-center gap-1 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-2.5 py-1 text-[10px] font-bold transition-all"><CreditCard class="w-3 h-3" /> Wallets <ChevronRight class="w-2.5 h-2.5" /></Link>
                    <Link href="/bills" class="flex items-center gap-1 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-2.5 py-1 text-[10px] font-bold transition-all"><CalendarClock class="w-3 h-3" /> Bills <ChevronRight class="w-2.5 h-2.5" /></Link>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-5 animate-fade-up stagger-2">
                <!-- Calendar Grid -->
                <div class="surface-card p-4 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <button @click="prevMonth" class="p-1.5 rounded-lg hover:bg-muted/40 transition-colors"><ChevronRight class="w-4 h-4 rotate-180 text-muted-foreground" /></button>
                        <h3 class="text-sm font-bold">{{ currentMonth }}</h3>
                        <button @click="nextMonth" class="p-1.5 rounded-lg hover:bg-muted/40 transition-colors"><ChevronRight class="w-4 h-4 text-muted-foreground" /></button>
                    </div>
                    <div class="grid grid-cols-7 gap-0.5 mb-1">
                        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center text-[8px] font-bold uppercase text-muted-foreground/60 py-1">{{ day }}</div>
                    </div>
                    <div class="grid grid-cols-7 gap-0.5">
                        <div v-for="i in firstDay" :key="'e'+i" class="aspect-square"></div>
                        <div v-for="day in calendarDays" :key="day" @click="selectDay(day)" class="aspect-square rounded-lg p-0.5 text-[9px] transition-all cursor-pointer relative" :class="[isToday(day) ? 'ring-2 ring-primary/40 bg-primary/5' : 'hover:bg-muted/30', selectedDay === day ? 'bg-primary/10 ring-1 ring-primary/30' : '']">
                            <span class="absolute top-1 left-1.5 font-semibold" :class="isToday(day) ? 'text-primary' : 'text-foreground'">{{ day }}</span>
                            <div class="absolute bottom-1 left-1 right-1 space-y-0.5">
                                <div v-for="event in getEventsForDay(day).slice(0, 2)" :key="event.label" class="text-[6px] px-1 py-0.3 rounded font-bold truncate leading-tight" :class="event.type === 'income' ? 'bg-emerald-500/20 text-emerald-600' : 'bg-rose-500/20 text-rose-500'">{{ event.type === 'income' ? '+' : '-' }}₱{{ event.amount.toLocaleString() }}</div>
                                <div v-if="getEventsForDay(day).length > 2" class="text-[6px] text-muted-foreground text-center">+{{ getEventsForDay(day).length - 2 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-border/20 text-[9px] text-muted-foreground">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Income</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Expense</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Bill Due</span>
                    </div>
                </div>

                <!-- Day Detail Sidebar -->
                <div class="surface-card p-4">
                    <div v-if="selectedDay">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold">{{ new Date(viewDate.getFullYear(), viewDate.getMonth(), selectedDay).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }) }}</h3>
                            <button class="text-[9px] font-semibold text-primary hover:underline">+ Add</button>
                        </div>
                        <div v-if="selectedEvents.length" class="space-y-1.5">
                            <div v-for="event in selectedEvents" :key="event.label + event.amount" class="flex items-center justify-between py-1.5 px-2 rounded-lg" :class="event.type === 'income' ? 'bg-emerald-500/5' : 'bg-rose-500/5'">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="event.type === 'income' ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                                    <div>
                                        <p class="text-[10px] font-semibold">{{ event.label }}</p>
                                        <p class="text-[8px] text-muted-foreground">{{ event.category }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold font-mono" :class="event.type === 'income' ? 'text-emerald-500' : 'text-rose-500'">{{ event.type === 'income' ? '+' : '-' }}₱{{ event.amount.toLocaleString() }}</span>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                            <CalendarDays class="w-8 h-8 text-muted-foreground/20 mb-2" />
                            <p class="text-[10px] text-muted-foreground">No transactions on this day</p>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 text-center h-full">
                        <CalendarDays class="w-10 h-10 text-muted-foreground/20 mb-2" />
                        <p class="text-[10px] text-muted-foreground">Select a day to view details</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
