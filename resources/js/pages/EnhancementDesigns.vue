<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Bell, BellRing, CalendarDays, Download, PiggyBank, Camera, ScanLine,
    Tags, Repeat2, Sparkles, Check, X, ArrowRight, ChevronRight,
    TrendingUp, Plus, ArrowUpRight,
    Target, CalendarClock, Clock, Upload, ShieldCheck, EyeOff,
    MapPin, AlertTriangle, Gift, Heart, Home, ShoppingCart, Utensils,
    Zap, BarChart3, Trash2, Pencil, FileText, Search,
    CheckCheck, Info, AlertCircle, Repeat
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Enhancement Designs', href: '/enhancement-designs' },
];

const sections = [
    { id: 'notifications', title: 'Smart Notifications', icon: BellRing, color: '#6366F1' },
    { id: 'auto-post', title: 'Recurring Auto-Posting', icon: Repeat2, color: '#10B981' },
    { id: 'categories', title: 'Custom Categories', icon: Tags, color: '#F59E0B' },
    { id: 'calendar', title: 'Financial Calendar', icon: CalendarDays, color: '#3B82F6' },
    { id: 'import', title: 'Data Import Wizard', icon: Download, color: '#8B5CF6' },
    { id: 'savings', title: 'Savings Envelopes', icon: PiggyBank, color: '#EC4899' },
    { id: 'receipts', title: 'Receipt Scanning', icon: Camera, color: '#14B8A6' },
    { id: 'auto-categorize', title: 'AI Auto-Categorize', icon: Sparkles, color: '#F97316' },
];

const activeSection = ref('notifications');

const scrollTo = (id: string) => {
    activeSection.value = id;
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const mockBills = [
    { name: 'Netflix', amount: 549, due: '2026-04-15', days: 3, color: '#E50914', freq: 'Monthly' },
    { name: 'Internet', amount: 1699, due: '2026-04-18', days: 6, color: '#3B82F6', freq: 'Monthly' },
    { name: 'Spotify', amount: 149, due: '2026-04-10', days: 0, color: '#1DB954', freq: 'Monthly' },
];

const mockNotifications = [
    { icon: AlertTriangle, text: 'Spotify bill due today', time: '2h ago', color: 'text-amber-500', bg: 'bg-amber-500/10' },
    { icon: BellRing, text: 'Budget alert: 85% of limit used', time: '5h ago', color: 'text-rose-500', bg: 'bg-rose-500/10' },
    { icon: Target, text: 'Emergency Fund goal 75% complete!', time: '1d ago', color: 'text-emerald-500', bg: 'bg-emerald-500/10' },
    { icon: TrendingUp, text: 'Monthly income +8% vs last month', time: '2d ago', color: 'text-blue-500', bg: 'bg-blue-500/10' },
];

const showNotifPanel = ref(false);
const showAddCategory = ref(false);

const presetCategories = [
    { name: 'Groceries', icon: ShoppingCart, color: '#10B981', type: 'expense', count: 23 },
    { name: 'Dining Out', icon: Utensils, color: '#F59E0B', type: 'expense', count: 15 },
    { name: 'Transport', icon: MapPin, color: '#3B82F6', type: 'expense', count: 12 },
    { name: 'Shopping', icon: ShoppingCart, color: '#EC4899', type: 'expense', count: 18 },
    { name: 'Health', icon: Heart, color: '#EF4444', type: 'expense', count: 7 },
    { name: 'Gifts', icon: Gift, color: '#8B5CF6', type: 'expense', count: 4 },
    { name: 'Housing', icon: Home, color: '#6366F1', type: 'expense', count: 6 },
    { name: 'Salary', icon: TrendingUp, color: '#10B981', type: 'income', count: 12 },
];

const colorPalette = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6', '#F97316', '#6B7280'];

const today = new Date();
const currentMonth = today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
const firstDay = new Date(today.getFullYear(), today.getMonth(), 1).getDay();
const daysInMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
const calendarDays = Array.from({ length: daysInMonth }, (_, i) => i + 1);

const calendarEvents: Record<number, { type: string; amount: number; label: string }[]> = {
    2: [{ type: 'income', amount: 45000, label: 'Salary' }],
    5: [{ type: 'expense', amount: 1699, label: 'Internet' }],
    8: [{ type: 'expense', amount: 549, label: 'Netflix' }],
    10: [{ type: 'expense', amount: 149, label: 'Spotify' }],
    12: [{ type: 'expense', amount: 2500, label: 'Groceries' }],
    15: [{ type: 'expense', amount: 8500, label: 'Rent' }],
    18: [{ type: 'income', amount: 5000, label: 'Freelance' }],
    20: [{ type: 'expense', amount: 1200, label: 'Dining' }],
    22: [{ type: 'expense', amount: 3500, label: 'Shopping' }],
    25: [{ type: 'expense', amount: 800, label: 'Transport' }],
    28: [{ type: 'income', amount: 45000, label: 'Salary' }],
};

const envelopes = [
    { name: 'Rent Fund', target: 8500, saved: 8500, color: '#6366F1', icon: Home, due: 'Apr 15' },
    { name: 'Groceries', target: 10000, saved: 6500, color: '#10B981', icon: ShoppingCart, due: 'Apr 30' },
    { name: 'Entertainment', target: 3000, saved: 1200, color: '#F59E0B', icon: Heart, due: 'Apr 30' },
    { name: 'Emergency Fund', target: 50000, saved: 37500, color: '#EF4444', icon: ShieldCheck, due: 'Ongoing' },
    { name: 'Vacation', target: 30000, saved: 15000, color: '#3B82F6', icon: MapPin, due: 'Dec 2026' },
    { name: 'Savings', target: 20000, saved: 8000, color: '#8B5CF6', icon: PiggyBank, due: 'Ongoing' },
];

const importStep = ref(1);
const totalImportSteps = 3;

const receiptAmount = '1,250.00';
const receiptMerchant = 'SM Supermarket';
const receiptDate = 'Apr 8, 2026';
const receiptCategories = ['Groceries', 'Dining', 'Shopping', 'Transport', 'Other'];
const detectedCategory = ref('Groceries');
</script>

<template>
    <Head title="Enhancement Designs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-0 overflow-x-auto p-0 pb-24 md:pb-8">
            
            <!-- Full-width Header Banner -->
            <div class="relative overflow-hidden bg-gradient-to-br from-primary/5 via-primary/[0.02] to-transparent border-b border-border/40 px-4 sm:px-8 py-6 sm:py-10">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary/[0.03] rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary/20 rounded-full blur-3xl"></div>
                <div class="relative max-w-4xl">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                            <Sparkles class="w-4 h-4" />
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Design Preview</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight font-display">
                        Enhancement <span class="text-primary">Designs</span>
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground max-w-2xl leading-relaxed">
                        Interactive mockups of upcoming features. Each section shows the proposed UI concept 
                        using your existing design system. Click around to explore the vision.
                    </p>
                </div>
            </div>

            <div class="flex gap-0 flex-1">
                <!-- Sidebar Navigation -->
                <div class="hidden lg:block w-[220px] shrink-0 sticky top-0 self-start border-r border-border/30 p-4">
                    <p class="text-[9px] font-bold uppercase tracking-widest text-muted-foreground/60 mb-3 px-2">Feature Concepts</p>
                    <nav class="space-y-0.5">
                        <button v-for="s in sections" :key="s.id" @click="scrollTo(s.id)"
                            class="flex items-center gap-2.5 w-full rounded-lg px-3 py-2 text-[11px] font-semibold text-left transition-all"
                            :class="activeSection === s.id ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-accent/30'"
                        >
                            <div class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: s.color }"></div>
                            <span>{{ s.title }}</span>
                        </button>
                    </nav>
                    <div class="mt-6 pt-4 border-t border-border/20 px-2">
                        <p class="text-[9px] text-muted-foreground/60 leading-relaxed">
                            ⚡ These are <strong class="text-foreground">design concepts</strong> showing what each feature would look like. None are implemented yet.
                        </p>
                    </div>
                </div>

                <!-- Design Content -->
                <div class="flex-1 min-w-0 p-4 sm:p-8 space-y-12 pb-32">

                    <!-- SECTION 1: Smart Notifications -->
                    <section id="notifications" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #6366F110; color: #6366F1">
                                <BellRing class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Smart Notifications</h2>
                                <p class="text-[11px] text-muted-foreground">An alert system that proactively notifies you about financial events</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Concept A · Bell in Header</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">New Component</span>
                                </div>
                                <div class="relative bg-muted/30 rounded-xl border border-border/30 p-4">
                                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-border/20">
                                        <div class="h-3 w-20 rounded-full bg-muted/60"></div>
                                        <div class="flex items-center gap-2">
                                            <div class="relative cursor-pointer" @click.stop="showNotifPanel = !showNotifPanel">
                                                <div class="p-1.5 rounded-lg bg-card border border-border/40 hover:bg-accent/30 transition-all">
                                                    <Bell class="w-3.5 h-3.5 text-muted-foreground" />
                                                </div>
                                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-[7px] font-bold text-white flex items-center justify-center ring-2 ring-card">4</span>
                                            </div>
                                            <div class="w-7 h-7 rounded-full bg-muted/60"></div>
                                        </div>
                                    </div>
                                    <div v-if="showNotifPanel" class="absolute top-16 right-4 w-[320px] rounded-2xl border border-border/40 bg-card shadow-2xl z-50 overflow-hidden">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-border/20">
                                            <div class="flex items-center gap-2"><BellRing class="w-4 h-4 text-primary" /><span class="text-sm font-bold">Notifications</span></div>
                                            <button class="text-[10px] font-semibold text-primary hover:underline">Mark all read</button>
                                        </div>
                                        <div class="max-h-[280px] overflow-y-auto">
                                            <div v-for="(n, i) in mockNotifications" :key="i" class="flex items-start gap-3 px-4 py-3 hover:bg-accent/20 transition-colors cursor-pointer border-b border-border/10 last:border-0">
                                                <div :class="['p-1.5 rounded-lg shrink-0', n.bg]"><component :is="n.icon" :class="['w-3.5 h-3.5', n.color]" /></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-foreground">{{ n.text }}</p>
                                                    <p class="text-[10px] text-muted-foreground mt-0.5">{{ n.time }}</p>
                                                </div>
                                                <div class="w-2 h-2 rounded-full bg-primary shrink-0 mt-1.5"></div>
                                            </div>
                                        </div>
                                        <div class="px-4 py-2.5 border-t border-border/20 bg-muted/20">
                                            <button class="w-full text-center text-[10px] font-semibold text-muted-foreground hover:text-foreground transition-colors">View all notifications</button>
                                        </div>
                                    </div>
                                    <div class="space-y-2"><div class="h-3 w-3/4 rounded-full bg-muted/40"></div><div class="h-3 w-1/2 rounded-full bg-muted/30"></div></div>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between">
                                    <p class="text-[10px] text-muted-foreground">Sits in the AppHeader next to the search icon</p>
                                    <span class="text-[9px] font-bold text-muted-foreground/60">~2h build</span>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Concept B · Notification Types</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">UX Pattern</span>
                                </div>
                                <div class="space-y-2.5">
                                    <div v-for="(n, i) in mockNotifications" :key="i" class="flex items-start gap-3 p-2.5 rounded-xl bg-muted/20 border border-border/20 hover:border-border/40 transition-all">
                                        <div :class="['p-1.5 rounded-lg shrink-0', n.bg]"><component :is="n.icon" :class="['w-3.5 h-3.5', n.color]" /></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] font-semibold text-foreground">{{ n.text }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[9px] text-muted-foreground">{{ n.time }}</span>
                                                <span class="text-[9px] px-1.5 py-0.2 rounded-full font-bold" :class="i === 0 ? 'bg-amber-500/10 text-amber-600' : i === 1 ? 'bg-rose-500/10 text-rose-500' : i === 2 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-blue-500/10 text-blue-600'">{{ i === 0 ? 'Due Soon' : i === 1 ? 'Budget Alert' : i === 2 ? 'Milestone' : 'Insight' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="surface-card p-5 md:col-span-2">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Concept C · Toast/Snackbar Alerts</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">Micro Interaction</span>
                                </div>
                                <div class="grid sm:grid-cols-3 gap-3">
                                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 shadow-lg">
                                        <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-500"><CheckCheck class="w-3.5 h-3.5" /></div>
                                        <div><p class="text-[10px] font-bold text-emerald-600">Transaction Added</p><p class="text-[9px] text-emerald-600/70">₱1,200 · Groceries</p></div>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 shadow-lg">
                                        <div class="p-1 rounded-lg bg-amber-500/20 text-amber-500"><AlertTriangle class="w-3.5 h-3.5" /></div>
                                        <div><p class="text-[10px] font-bold text-amber-600">Budget Warning</p><p class="text-[9px] text-amber-600/70">85% of monthly limit used</p></div>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 shadow-lg">
                                        <div class="p-1 rounded-lg bg-blue-500/20 text-blue-500"><Info class="w-3.5 h-3.5" /></div>
                                        <div><p class="text-[10px] font-bold text-blue-600">Bill Due Tomorrow</p><p class="text-[9px] text-blue-600/70">Spotify · ₱149</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 2: Recurring Auto-Posting -->
                    <section id="auto-post" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #10B98110; color: #10B981"><Repeat2 class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Recurring Auto-Posting</h2>
                                <p class="text-[11px] text-muted-foreground">Automatically create transactions when bills come due</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="surface-card p-5 md:col-span-2">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">New Dashboard Section · Scheduled Transactions</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 font-bold">Todo</span>
                                </div>
                                <div class="rounded-xl bg-muted/20 border border-border/30 p-4">
                                    <div class="rounded-xl border border-border/30 bg-card overflow-hidden">
                                        <div class="flex items-center justify-between px-3 py-2.5 border-b border-border/20">
                                            <div class="flex items-center gap-2">
                                                <div class="p-1 rounded-md bg-emerald-500/10 text-emerald-500"><Repeat2 class="w-3 h-3" /></div>
                                                <span class="text-[11px] font-bold">Scheduled Auto-Posts</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] text-muted-foreground">Next run: Today 12:00</span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 text-[9px] font-bold"><Zap class="w-2.5 h-2.5" /> Active</span>
                                            </div>
                                        </div>
                                        <div class="divide-y divide-border/10">
                                            <div v-for="(bill, i) in mockBills" :key="i" class="flex items-center justify-between px-3 py-2.5 hover:bg-accent/20 transition-colors">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[9px] font-bold" :style="{ backgroundColor: bill.color }">{{ bill.name[0] }}</div>
                                                    <div><p class="text-[11px] font-semibold">{{ bill.name }}</p><p class="text-[9px] text-muted-foreground">{{ bill.freq }} · <Clock class="inline w-2.5 h-2.5" /> due {{ bill.due }}</p></div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[11px] font-bold font-mono text-emerald-500">-₱{{ bill.amount.toLocaleString() }}</span>
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[8px] font-bold" :class="bill.days === 0 ? 'bg-rose-500/10 text-rose-500' : 'bg-muted/40 text-muted-foreground'">{{ bill.days === 0 ? 'Today' : `In ${bill.days}d` }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-3 py-2 bg-muted/20 border-t border-border/10">
                                            <p class="text-[9px] text-muted-foreground flex items-center gap-1"><Clock class="w-2.5 h-2.5" /> Artisan command runs daily via scheduler</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Backend · Scheduled Command</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">New File</span>
                                </div>
                                <div class="bg-[#1a1a2e] rounded-xl p-4 font-mono text-[10px] leading-relaxed text-gray-300 overflow-x-auto">
                                <pre class="m-0">
<span class="text-purple-400">php artisan</span> <span class="text-green-400">make:command</span> PostRecurringTransactions

<span class="text-gray-500">// In app/Console/Commands/PostRecurringTransactions.php</span>

<span class="text-yellow-400">public function</span> <span class="text-blue-400">handle</span>() {
    <span class="text-purple-400">RecurringTransaction</span>::<span class="text-blue-400">query</span>()
        -&gt;<span class="text-blue-400">where</span>('next_due_date', '&lt;=', today)
        -&gt;<span class="text-blue-400">where</span>('is_active', <span class="text-purple-400">true</span>)
        -&gt;<span class="text-blue-400">each</span>(fn($tx) =&gt; {
            <span class="text-gray-500">// Create Income/Expense record</span>
            <span class="text-gray-500">// Advance next_due_date</span>
        });
}
                                </pre>
                            </div>
                                <div class="mt-2.5 flex items-center justify-between">
                                    <p class="text-[10px] text-muted-foreground">Creates actual Income/Expense records + advances dates</p>
                                    <span class="text-[9px] font-bold text-muted-foreground/60">~3h build</span>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Auto-Post History</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">New Page</span>
                                </div>
                                <div class="space-y-2">
                                    <div v-for="i in 4" :key="i" class="flex items-center justify-between p-2 rounded-lg bg-muted/20 border border-border/20">
                                        <div class="flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full" :class="i <= 2 ? 'bg-emerald-500' : 'bg-amber-500'"></div>
                                            <div><p class="text-[10px] font-semibold">Netflix · ₱549</p><p class="text-[8px] text-muted-foreground">{{ `Apr ${i * 7}, 2026` }}</p></div>
                                        </div>
                                        <span class="text-[8px] font-bold px-1.5 py-0.5 rounded-full" :class="i <= 2 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'">{{ i <= 2 ? 'Posted' : 'Skipped' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 3: Custom Categories -->
                    <section id="categories" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #F59E0B10; color: #F59E0B"><Tags class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Custom Category Management</h2>
                                <p class="text-[11px] text-muted-foreground">Let users create, edit, and color-code their own transaction categories</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Category Manager UI</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 font-bold">New Page</span>
                                </div>
                                <div class="flex items-center justify-between mb-3">
                                    <div class="relative flex-1 max-w-[200px]">
                                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-muted-foreground" />
                                        <input type="text" placeholder="Search categories..." class="w-full rounded-lg border border-border/40 bg-muted/20 pl-7 pr-2.5 py-1.5 text-[10px] font-medium outline-none focus:border-primary/40" />
                                    </div>
                                    <button class="flex items-center gap-1 rounded-lg bg-primary px-2.5 py-1.5 text-[10px] font-bold text-primary-foreground" @click="showAddCategory = !showAddCategory"><Plus class="w-3 h-3" /> New</button>
                                </div>
                                <div v-if="showAddCategory" class="mb-3 p-3 rounded-xl bg-muted/30 border border-border/30">
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <input type="text" placeholder="Category name" class="col-span-2 rounded-lg border border-border/40 bg-background px-2.5 py-1.5 text-[10px] outline-none focus:border-primary/40" />
                                        <select class="rounded-lg border border-border/40 bg-background px-2.5 py-1.5 text-[10px] outline-none focus:border-primary/40"><option>Income</option><option selected>Expense</option></select>
                                        <div class="flex items-center gap-1">
                                            <div v-for="c in colorPalette.slice(0, 6)" :key="c" class="w-5 h-5 rounded-full border-2 border-transparent hover:scale-110 transition-transform cursor-pointer" :style="{ backgroundColor: c }"></div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-1.5">
                                        <button class="px-2.5 py-1 rounded-lg text-[9px] font-semibold border border-border/40 hover:bg-muted/50" @click="showAddCategory = false">Cancel</button>
                                        <button class="px-2.5 py-1 rounded-lg text-[9px] font-bold bg-primary text-primary-foreground">Save</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 max-h-[280px] overflow-y-auto pr-1">
                                    <div v-for="(cat, i) in presetCategories" :key="i" class="group flex items-center gap-2 p-2.5 rounded-xl border border-border/20 hover:border-border/40 hover:bg-accent/20 transition-all cursor-pointer">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" :style="{ backgroundColor: cat.color }"><component :is="cat.icon" class="w-4 h-4" /></div>
                                        <div class="flex-1 min-w-0"><p class="text-[11px] font-bold truncate">{{ cat.name }}</p><p class="text-[8px] text-muted-foreground">{{ cat.count }} txns · {{ cat.type }}</p></div>
                                        <button class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-muted/40 transition-all"><Pencil class="w-3 h-3 text-muted-foreground" /></button>
                                    </div>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Data Model</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">Migration</span>
                                </div>
                                <div class="bg-[#1a1a2e] rounded-xl p-4 font-mono text-[10px] leading-relaxed text-gray-300 overflow-x-auto">
                                <pre class="m-0">
<span class="text-purple-400">Schema</span>::<span class="text-blue-400">create</span>('categories', fn($table) =&gt; {
    $table-&gt;<span class="text-blue-400">id</span>();
    $table-&gt;<span class="text-blue-400">foreignId</span>('user_id')-&gt;<span class="text-blue-400">constrained</span>()-&gt;<span class="text-blue-400">cascadeOnDelete</span>();
    $table-&gt;<span class="text-blue-400">string</span>('name');
    $table-&gt;<span class="text-blue-400">enum</span>('type', ['income', 'expense']);
    $table-&gt;<span class="text-blue-400">string</span>('color')-&gt;<span class="text-blue-400">nullable</span>();
    $table-&gt;<span class="text-blue-400">string</span>('icon')-&gt;<span class="text-blue-400">nullable</span>();
    $table-&gt;<span class="text-blue-400">integer</span>('sort_order')-&gt;<span class="text-blue-400">default</span>(<span class="text-purple-400">0</span>);
    $table-&gt;<span class="text-blue-400">timestamps</span>();
});
                                </pre>
                            </div>
                                <div class="mt-3 space-y-1.5">
                                    <div class="flex items-center gap-2"><Check class="w-3 h-3 text-emerald-500" /><span class="text-[10px] text-muted-foreground">Pre-seeded defaults</span></div>
                                    <div class="flex items-center gap-2"><Check class="w-3 h-3 text-emerald-500" /><span class="text-[10px] text-muted-foreground">Custom icons from Lucide</span></div>
                                    <div class="flex items-center gap-2"><Check class="w-3 h-3 text-emerald-500" /><span class="text-[10px] text-muted-foreground">Budgets per category</span></div>
                                </div>
                                <div class="mt-3 p-2.5 rounded-lg bg-muted/20 border border-border/20">
                                    <p class="text-[9px] text-muted-foreground">💡 <strong class="text-foreground">Replace hardcoded categories</strong> in TransactionModal.vue &amp; RecurringEntryModal.vue with dynamic data from this table.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 4: Financial Calendar -->
                    <section id="calendar" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #3B82F610; color: #3B82F6"><CalendarDays class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Financial Calendar</h2>
                                <p class="text-[11px] text-muted-foreground">A visual calendar showing transactions, due dates, and recurring events</p>
                            </div>
                        </div>
                        <div class="surface-card p-5 mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Monthly Calendar View</span>
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-600 font-bold">New Page</span>
                            </div>
                            <div class="rounded-xl border border-border/30 bg-muted/10 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <button class="p-1.5 rounded-lg hover:bg-muted/40 transition-colors"><ChevronRight class="w-4 h-4 rotate-180 text-muted-foreground" /></button>
                                    <h3 class="text-sm font-bold">{{ currentMonth }}</h3>
                                    <button class="p-1.5 rounded-lg hover:bg-muted/40 transition-colors"><ChevronRight class="w-4 h-4 text-muted-foreground" /></button>
                                </div>
                                <div class="grid grid-cols-7 gap-0.5 mb-1">
                                    <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center text-[8px] font-bold uppercase text-muted-foreground/60 py-1">{{ day }}</div>
                                </div>
                                <div class="grid grid-cols-7 gap-0.5">
                                    <div v-for="i in firstDay" :key="'e'+i" class="aspect-square"></div>
                                    <div v-for="day in calendarDays" :key="day" class="aspect-square rounded-lg p-0.5 text-[9px] transition-all hover:bg-muted/30 cursor-pointer relative" :class="day === today.getDate() ? 'ring-2 ring-primary/40 bg-primary/5' : ''">
                                        <span class="absolute top-1 left-1.5 font-semibold" :class="day === today.getDate() ? 'text-primary' : 'text-foreground'">{{ day }}</span>
                                        <div class="absolute bottom-1 left-1 right-1 space-y-0.5">
                                            <div v-for="event in calendarEvents[day]" :key="event.label" class="text-[6px] px-1 py-0.3 rounded font-bold truncate leading-tight" :class="event.type === 'income' ? 'bg-emerald-500/20 text-emerald-600' : 'bg-rose-500/20 text-rose-500'">{{ event.type === 'income' ? '+' : '-' }}₱{{ event.amount.toLocaleString() }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 rounded-xl bg-muted/20 border border-border/30">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[10px] font-bold">April 10, 2026 <span class="font-normal text-muted-foreground">· 3 transactions</span></p>
                                    <button class="text-[9px] font-semibold text-primary hover:underline">Add transaction</button>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-lg bg-rose-500/5 border border-rose-500/10"><div class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div><span class="text-[10px] font-semibold">Spotify</span></div><span class="text-[10px] font-bold text-rose-500">-₱149</span></div>
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-lg bg-rose-500/5 border border-rose-500/10"><div class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-rose-500"></div><span class="text-[10px] font-semibold">Groceries</span></div><span class="text-[10px] font-bold text-rose-500">-₱2,500</span></div>
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-lg bg-emerald-500/5 border border-emerald-500/10"><div class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div><span class="text-[10px] font-semibold">Freelance Payment</span></div><span class="text-[10px] font-bold text-emerald-500">+₱5,000</span></div>
                                </div>
                            </div>
                            <div class="mt-2.5 flex items-center gap-3 text-[9px] text-muted-foreground">
                                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500/50"></span> Income</span>
                                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500/50"></span> Expense</span>
                                <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500/50"></span> Bill Due</span>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 5: Data Import Wizard -->
                    <section id="import" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #8B5CF610; color: #8B5CF6"><Download class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Data Import Wizard</h2>
                                <p class="text-[11px] text-muted-foreground">Import transactions from CSV, OFX, or bank/GCash/Maya exports</p>
                            </div>
                        </div>
                        <div class="surface-card p-5 mt-4">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Import Wizard · Step {{ importStep }} of {{ totalImportSteps }}</span>
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-violet-500/10 text-violet-600 font-bold">New Page</span>
                            </div>
                            <div class="flex items-center gap-2 mb-5">
                                <div v-for="s in totalImportSteps" :key="s" class="flex items-center gap-2 flex-1">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[9px] font-bold transition-all" :class="s <= importStep ? 'bg-primary text-primary-foreground' : 'bg-muted/40 text-muted-foreground'">{{ s }}</div>
                                    <div class="flex-1 h-0.5 rounded-full" :class="s < importStep ? 'bg-primary' : 'bg-muted/30'"></div>
                                </div>
                            </div>
                            <div v-if="importStep === 1" class="text-center py-8">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-violet-500/10 border-2 border-dashed border-violet-500/30 flex items-center justify-center"><Upload class="w-6 h-6 text-violet-500" /></div>
                                <h3 class="text-sm font-bold mb-1">Upload your file</h3>
                                <p class="text-[10px] text-muted-foreground mb-4">CSV, OFX, QFX — or export from GCash, Maya, PayPal, or your bank</p>
                                <button class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-[10px] font-bold text-primary-foreground"><Upload class="w-3.5 h-3.5" /> Choose File</button>
                                <div class="mt-3 flex justify-center gap-3">
                                    <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">GCash CSV</span>
                                    <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">Maya CSV</span>
                                    <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">Bank OFX</span>
                                </div>
                            </div>
                            <div v-if="importStep === 2" class="space-y-3">
                                <p class="text-[10px] font-semibold text-muted-foreground">Map your file columns to tracker fields</p>
                                <div v-for="field in ['Date', 'Amount', 'Description', 'Category']" :key="field" class="flex items-center gap-3 p-2.5 rounded-xl bg-muted/20 border border-border/20">
                                    <span class="text-[10px] font-bold w-20 shrink-0">{{ field }}</span>
                                    <select class="flex-1 rounded-lg border border-border/30 bg-background px-2.5 py-1.5 text-[10px] outline-none focus:border-primary/40">
                                        <option>— Skip column —</option>
                                        <option selected>{{ field === 'Date' ? 'Transaction Date' : field === 'Amount' ? 'Amount (₱)' : field === 'Description' ? 'Description' : 'Category' }}</option>
                                    </select>
                                </div>
                            </div>
                            <div v-if="importStep === 3" class="space-y-3">
                                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                    <div class="flex items-center gap-2"><CheckCheck class="w-4 h-4 text-emerald-500" /><span class="text-[11px] font-bold text-emerald-600">24 transactions ready to import</span></div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="text-center p-2 rounded-lg bg-muted/20"><p class="text-sm font-bold text-emerald-500">₱84,500</p><p class="text-[8px] text-muted-foreground">Total Income</p></div>
                                    <div class="text-center p-2 rounded-lg bg-muted/20"><p class="text-sm font-bold text-rose-500">₱12,300</p><p class="text-[8px] text-muted-foreground">Total Expenses</p></div>
                                    <div class="text-center p-2 rounded-lg bg-muted/20"><p class="text-sm font-bold">24</p><p class="text-[8px] text-muted-foreground">Transactions</p></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-5 pt-4 border-t border-border/20">
                                <button v-if="importStep > 1" @click="importStep--" class="px-3 py-1.5 rounded-lg text-[10px] font-semibold border border-border/30 hover:bg-muted/40 transition-all">← Back</button>
                                <div v-else></div>
                                <button @click="importStep < totalImportSteps ? importStep++ : null" class="px-4 py-1.5 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground hover:bg-primary/90 transition-all">{{ importStep < totalImportSteps ? 'Next →' : 'Import ✅' }}</button>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 6: Savings Envelopes -->
                    <section id="savings" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #EC489910; color: #EC4899"><PiggyBank class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Savings Envelopes</h2>
                                <p class="text-[11px] text-muted-foreground">Allocate wallet money toward specific purposes like Rent, Vacation, or Emergency Fund</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Envelope Dashboard</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-pink-500/10 text-pink-600 font-bold">New Section</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div v-for="(env, i) in envelopes" :key="i" class="group relative p-3 rounded-xl border border-border/30 bg-card hover:shadow-md transition-all cursor-pointer">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white" :style="{ backgroundColor: env.color }"><component :is="env.icon" class="w-3.5 h-3.5" /></div>
                                            <span class="text-[8px] font-bold text-muted-foreground/60">{{ env.due }}</span>
                                        </div>
                                        <p class="text-[10px] font-bold truncate">{{ env.name }}</p>
                                        <p class="text-[11px] font-black font-mono mt-1" :style="{ color: env.color }">{{ formatCurrency(env.saved) }}</p>
                                        <div class="mt-1.5 h-1.5 rounded-full bg-muted/30 overflow-hidden"><div class="h-full rounded-full transition-all" :style="{ width: Math.min(100, (env.saved / env.target) * 100) + '%', backgroundColor: env.color }"></div></div>
                                        <div class="flex justify-between mt-1"><span class="text-[8px] text-muted-foreground">{{ Math.round((env.saved / env.target) * 100) }}%</span><span class="text-[8px] text-muted-foreground">{{ formatCurrency(env.target) }}</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Allocate &amp; Manage</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">Modal</span>
                                </div>
                                <div class="p-3 rounded-xl bg-muted/20 border border-border/30">
                                    <p class="text-[10px] font-bold mb-2">Transfer to Envelope</p>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2"><select class="flex-1 rounded-lg border border-border/30 bg-background px-2.5 py-1.5 text-[10px] outline-none"><option>From: GCash</option><option>From: Maya</option><option selected>From: Bank Account</option></select></div>
                                        <div class="flex items-center gap-2"><select class="flex-1 rounded-lg border border-border/30 bg-background px-2.5 py-1.5 text-[10px] outline-none"><option selected>To: Emergency Fund</option><option>To: Rent Fund</option><option>To: Vacation</option></select></div>
                                        <div class="flex items-center gap-2"><input type="text" placeholder="Amount" value="5,000" class="flex-1 rounded-lg border border-border/30 bg-background px-2.5 py-1.5 text-[10px] outline-none" /><button class="px-3 py-1.5 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground">Allocate</button></div>
                                    </div>
                                </div>
                                <div class="mt-3 p-2.5 rounded-lg bg-blue-500/5 border border-blue-500/20">
                                    <p class="text-[9px] text-muted-foreground">💡 Extends the <strong class="text-foreground">Financial Goals</strong> system but tied to specific wallet balances. Works like the "envelope budgeting" method.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 7: Receipt Scanning -->
                    <section id="receipts" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #14B8A610; color: #14B8A6"><Camera class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">Receipt Scanning</h2>
                                <p class="text-[11px] text-muted-foreground">Snap a photo of a receipt and auto-extract the amount, merchant, and category</p>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Receipt Camera UI</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-teal-500/10 text-teal-600 font-bold">New Component</span>
                                </div>
                                <div class="rounded-xl bg-gradient-to-br from-gray-900 to-gray-800 aspect-[4/3] relative overflow-hidden flex items-center justify-center">
                                    <div class="absolute inset-4 border-2 border-dashed border-white/20 rounded-xl"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center"><Camera class="w-10 h-10 text-white/30 mx-auto mb-2" /><p class="text-white/50 text-[10px] font-semibold">Position receipt within frame</p></div>
                                    </div>
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2"><div class="w-14 h-14 rounded-full border-4 border-white/40 flex items-center justify-center"><div class="w-10 h-10 rounded-full bg-white/20"></div></div></div>
                                    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-teal-400/50 to-transparent animate-pulse"></div>
                                </div>
                                <div class="mt-2.5 flex justify-around">
                                    <button class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-muted/30 transition-colors"><ScanLine class="w-4 h-4 text-muted-foreground" /><span class="text-[8px] text-muted-foreground">Scan</span></button>
                                    <button class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-muted/30 transition-colors"><Upload class="w-4 h-4 text-muted-foreground" /><span class="text-[8px] text-muted-foreground">Gallery</span></button>
                                    <button class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-muted/30 transition-colors"><FileText class="w-4 h-4 text-muted-foreground" /><span class="text-[8px] text-muted-foreground">Manual</span></button>
                                </div>
                            </div>
                            <div class="surface-card p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Extracted Data</span>
                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-teal-500/10 text-teal-600 font-bold">Auto-detected</span>
                                </div>
                                <div class="p-4 rounded-xl bg-muted/20 border border-border/30">
                                    <div class="flex items-center justify-between mb-3 pb-3 border-b border-border/20">
                                        <div class="flex items-center gap-2"><div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-500 flex items-center justify-center"><FileText class="w-4 h-4" /></div><div><p class="text-[11px] font-bold text-foreground">Receipt Detected</p><p class="text-[9px] text-muted-foreground">SM Supermarket · {{ receiptDate }}</p></div></div>
                                        <div class="flex items-center gap-1"><button class="p-1 rounded hover:bg-muted/40 transition-colors"><EyeOff class="w-3 h-3 text-muted-foreground" /></button><button class="p-1 rounded hover:bg-muted/40 transition-colors"><Trash2 class="w-3 h-3 text-muted-foreground" /></button></div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between"><span class="text-[9px] text-muted-foreground">Merchant</span><span class="text-[10px] font-semibold">{{ receiptMerchant }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-[9px] text-muted-foreground">Amount</span><span class="text-sm font-black text-foreground font-mono">₱{{ receiptAmount }}</span></div>
                                        <div class="flex items-center justify-between"><span class="text-[9px] text-muted-foreground">Date</span><span class="text-[10px] font-semibold">{{ receiptDate }}</span></div>
                                        <div class="pt-2 border-t border-border/20">
                                            <span class="text-[9px] text-muted-foreground block mb-1.5">Category</span>
                                            <div class="flex flex-wrap gap-1">
                                                <button v-for="cat in receiptCategories" :key="cat" @click="detectedCategory = cat" class="px-2 py-1 rounded-full text-[9px] font-semibold border transition-all" :class="detectedCategory === cat ? 'bg-teal-500/10 border-teal-500/30 text-teal-600' : 'border-border/30 text-muted-foreground hover:border-border/60'">{{ cat }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button class="mt-3 w-full rounded-xl bg-primary py-2.5 text-[11px] font-bold text-primary-foreground hover:bg-primary/90 transition-all">Add to Expenses ✅</button>
                            </div>
                        </div>
                    </section>

                    <!-- SECTION 8: AI Auto-Categorize -->
                    <section id="auto-categorize" class="scroll-mt-16">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-xl" style="background: #F9731610; color: #F97316"><Sparkles class="w-4 h-4" /></div>
                            <div>
                                <h2 class="text-base font-bold tracking-tight">AI Auto-Categorization</h2>
                                <p class="text-[11px] text-muted-foreground">Smart category suggestions based on transaction description &amp; historical patterns</p>
                            </div>
                        </div>
                        <div class="surface-card p-5 mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Smart Suggestions &amp; Insights</span>
                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-orange-500/10 text-orange-600 font-bold">AI Feature</span>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="p-3 rounded-xl bg-muted/20 border border-border/30">
                                    <p class="text-[10px] font-bold mb-2 flex items-center gap-1.5"><Sparkles class="w-3.5 h-3.5 text-orange-500" /> While adding a transaction</p>
                                    <div class="p-2.5 rounded-lg bg-card border border-border/30">
                                        <div class="flex items-center justify-between mb-1"><span class="text-[9px] text-muted-foreground">You typed:</span><span class="text-[10px] font-semibold">"Jollibee Delivery"</span></div>
                                        <div class="flex items-center gap-1.5 mt-1.5 pt-1.5 border-t border-border/20">
                                            <span class="text-[8px] font-bold uppercase tracking-wider text-orange-500">Suggested</span>
                                            <button class="px-2.5 py-1 rounded-full bg-orange-500/10 text-orange-600 text-[9px] font-bold border border-orange-500/20 flex items-center gap-1"><Sparkles class="w-2.5 h-2.5" /> Dining Out</button>
                                            <button class="px-2.5 py-1 rounded-full text-[9px] font-semibold border border-border/30 text-muted-foreground hover:border-border/60">Food</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-muted/20 border border-border/30">
                                    <p class="text-[10px] font-bold mb-2 flex items-center gap-1.5"><BarChart3 class="w-3.5 h-3.5 text-orange-500" /> Spending Insights</p>
                                    <div class="space-y-2">
                                        <div class="p-2.5 rounded-lg bg-card border border-border/20"><div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-amber-500/10"><TrendingUp class="w-3 h-3 text-amber-500" /></div><div><p class="text-[10px] font-semibold">Dining spending up 35%</p><p class="text-[8px] text-muted-foreground">Compared to last month</p></div></div></div>
                                        <div class="p-2.5 rounded-lg bg-card border border-border/20"><div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-emerald-500/10"><Zap class="w-3 h-3 text-emerald-500" /></div><div><p class="text-[10px] font-semibold">Best time to save: Fridays</p><p class="text-[8px] text-muted-foreground">Lowest spending day of the week</p></div></div></div>
                                        <div class="p-2.5 rounded-lg bg-card border border-border/20"><div class="flex items-center gap-2"><div class="p-1.5 rounded-lg bg-rose-500/10"><AlertCircle class="w-3 h-3 text-rose-500" /></div><div><p class="text-[10px] font-semibold">Unusual: ₱8,500 grocery this week</p><p class="text-[8px] text-muted-foreground">2.5x higher than your average</p></div></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 p-3 rounded-xl bg-gradient-to-r from-orange-500/5 to-primary/5 border border-orange-500/10">
                                <p class="text-[9px] text-muted-foreground flex items-center gap-1.5"><Sparkles class="w-3 h-3 text-orange-500" /><strong class="text-foreground">Approach:</strong> Uses description matching + ML to suggest categories. Can be as simple as keyword rules initially, upgradeable to external AI later.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Bottom Summary -->
                    <div class="rounded-2xl border border-border/30 bg-gradient-to-br from-primary/5 to-transparent p-6 text-center">
                        <h3 class="text-sm font-bold mb-2">✨ Ready to choose your first enhancement?</h3>
                        <p class="text-[11px] text-muted-foreground max-w-md mx-auto mb-4">
                            All designs follow your existing <strong class="text-foreground">design system</strong> — OKLCH colors, 
                            Instrument Sans font, surface-card patterns, and premium-hover interactions.
                        </p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <span v-for="s in sections" :key="s.id" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[9px] font-bold border border-border/40 bg-card">
                                <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: s.color }"></span>{{ s.title }}
                            </span>
                        </div>
                        <p class="mt-4 text-[9px] text-muted-foreground">Estimated build times: ⚡ 2-3 hours each · 🚀 Full day for complex features</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
    transition: all 0.2s ease;
}
.v-enter-from,
.v-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
.max-h-\[280px\]::-webkit-scrollbar {
    width: 3px;
}
.max-h-\[280px\]::-webkit-scrollbar-track {
    background: transparent;
}
.max-h-\[280px\]::-webkit-scrollbar-thumb {
    background: oklch(0.708 0 0 / 0.3);
    border-radius: 10px;
}
</style>
