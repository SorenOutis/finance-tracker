<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import {
    ArrowUpRight,
    TrendingDown,
    Target,
    Sun,
    Moon
} from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const toggleTheme = (event: MouseEvent) => {
    const newTheme = appearance.value === 'dark' ? 'light' : 'dark';
    
    if (!document.startViewTransition) {
        updateAppearance(newTheme);
        return;
    }

    const x = event.clientX;
    const y = event.clientY;
    const endRadius = Math.hypot(
        Math.max(x, innerWidth - x),
        Math.max(y, innerHeight - y)
    );

    const transition = document.startViewTransition(() => {
        updateAppearance(newTheme);
    });

    transition.ready.then(() => {
        const clipPath = [
            `circle(0px at ${x}px ${y}px)`,
            `circle(${endRadius}px at ${x}px ${y}px)`,
        ];
        
        document.documentElement.animate(
            {
                clipPath: newTheme === 'dark' ? [...clipPath].reverse() : clipPath,
            },
            {
                duration: 500,
                easing: 'ease-in-out',
                pseudoElement: newTheme === 'dark'
                    ? '::view-transition-old(root)'
                    : '::view-transition-new(root)',
            }
        );
    });
};

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen flex flex-col p-4 sm:p-8">
        
        <!-- Header -->
        <header class="flex items-center justify-between w-full max-w-7xl mx-auto animate-fade-up stagger-1 z-10">
            <div class="flex items-center gap-2.5">
                <span class="text-xl font-extrabold font-display tracking-tight text-foreground">Finance Tracker</span>
            </div>

            <nav class="flex items-center gap-2 sm:gap-3">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="rounded-[20px] border border-border/60 bg-card/30 px-5 py-2.5 text-sm font-bold transition-all hover:bg-card active:scale-[0.98]"
                    >
                        Dashboard
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="px-4 py-2 text-sm font-bold text-muted-foreground transition-all hover:text-foreground hidden sm:block"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-[20px] border border-border/60 bg-card/30 px-5 py-2 text-sm font-bold transition-all hover:bg-card active:scale-[0.98]"
                    >
                        Register
                    </Link>
                </template>
                <button
                    @click="toggleTheme"
                    class="ml-2 inline-flex items-center justify-center rounded-full p-2 text-sm font-medium transition-colors hover:bg-card/50 text-foreground border border-border/40 bg-card/20"
                    :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`"
                >
                    <Sun v-if="appearance === 'dark'" class="h-4 w-4" />
                    <Moon v-else class="h-4 w-4" />
                </button>
            </nav>
        </header>

        <!-- Main section -->
        <main class="flex-1 flex items-center justify-center w-full max-w-7xl mx-auto py-12 md:py-20 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 md:gap-8 items-center w-full">
                
                <!-- Left: Text content -->
                <div class="space-y-6 max-w-lg mb-8 md:mb-0 animate-fade-up stagger-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.08em] font-sans">
                        Personal Finance portal
                    </div>
                    
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black font-display tracking-tight leading-[1.05] text-foreground">
                        Track your wealth,<br />
                        <span class="text-muted-foreground">minimize the noise.</span>
                    </h1>
                    
                    <p class="text-sm md:text-base text-muted-foreground/80 font-medium leading-relaxed max-w-md font-sans">
                        A clean, fast, and local-first dashboard designed for absolute clarity. Stay on top of your budget, track savings streaks, and understand exactly where your money goes.
                    </p>
                    
                    <div class="flex flex-wrap gap-2.5 pt-4">
                        <template v-if="!$page.props.auth.user">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex items-center justify-center rounded-[20px] bg-foreground px-6 py-2.5 text-sm font-bold text-background transition-all hover:scale-[0.98]"
                            >
                                Get Started
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center rounded-[20px] border border-border/60 bg-card/30 px-6 py-2.5 text-sm font-bold transition-all hover:bg-card active:scale-[0.98]"
                            >
                                Sign In
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="dashboard()"
                                class="inline-flex items-center justify-center rounded-[20px] bg-foreground px-6 py-2.5 text-sm font-bold text-background transition-all hover:scale-[0.98]"
                            >
                                Open Dashboard
                            </Link>
                        </template>
                    </div>
                </div>

                <!-- Right: Dashboard mockup -->
                <div class="relative w-full flex flex-col gap-4 pl-0 md:pl-10 animate-fade-up stagger-3 select-none pointer-events-none">
                    
                    <!-- Card 1: Net Worth -->
                    <div class="self-end md:mr-10 w-full max-w-[260px] p-5 rounded-[24px] border border-border/40 bg-card/20 surface-card transform xl:translate-x-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80">Net Worth</p>
                            <span class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[9px] font-bold bg-[--fin-gain]/12 text-[--fin-gain]">
                                <ArrowUpRight class="h-2.5 w-2.5" /> 4.2%
                            </span>
                        </div>
                        <div class="pt-1.5">
                            <p class="text-3xl font-black font-display tracking-tight text-foreground">₱245k</p>
                        </div>
                    </div>

                    <!-- Card 2: Budget -->
                    <div class="self-start md:ml-4 w-full max-w-[240px] p-4 rounded-[24px] border border-border/40 bg-card/20 surface-card">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <Target class="h-3.5 w-3.5 text-primary" />
                                <p class="text-[10px] font-semibold tracking-[0.08em] uppercase text-muted-foreground">Monthly Budget</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[--fin-gain]/12 text-[--fin-gain]">
                                65% used
                            </span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-muted/30 overflow-hidden my-2.5">
                            <div class="h-full rounded-full bg-[--fin-gain]" style="width: 65%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] font-mono">
                            <span class="text-muted-foreground">₱13,000 spent</span>
                            <span class="text-foreground font-semibold">₱20k limit</span>
                        </div>
                    </div>

                    <!-- Card 3: Recent Expense -->
                    <div class="self-end md:mr-2 w-full max-w-[260px] p-4 rounded-[24px] border border-border/40 bg-card/20 surface-card relative">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="p-1 rounded bg-[--fin-loss]/10">
                                <TrendingDown class="h-3.5 w-3.5 text-[--fin-loss]" />
                            </div>
                            <h2 class="text-sm font-bold text-foreground font-display">Recent Expense</h2>
                        </div>
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold font-mono text-[--fin-loss]">-₱850.00</p>
                                <div class="flex gap-1.5 mt-1">
                                    <span class="text-[10px] text-muted-foreground">Today</span>
                                    <span class="text-[9px] font-semibold bg-muted/50 px-1.5 py-0.5 rounded-full text-muted-foreground">Dining</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>
</template>
