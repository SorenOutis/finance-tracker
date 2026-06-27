<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import {
    ArrowUpRight,
    TrendingDown,
    Target,
    Sun,
    Moon,
    ArrowRight
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard, login, register } from '@/routes';

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

const container = ref<HTMLElement | null>(null);

onMounted(() => {
    const tl = gsap.timeline();

    // Entrance animation
    tl.from('.welcome-header', {
        y: -20,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    })
    .from('.welcome-content > *', {
        y: 30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out'
    }, '-=0.4')
    .from('.mockup-card', {
        x: 40,
        opacity: 0,
        duration: 1,
        stagger: 0.15,
        ease: 'expo.out'
    }, '-=1');

    // Floating animation for mockup cards
    gsap.to('.mockup-card', {
        y: 'random(-15, 15)',
        x: 'random(-10, 10)',
        rotation: 'random(-2, 2)',
        duration: 'random(3, 5)',
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut',
        stagger: {
            amount: 1,
            from: 'random'
        }
    });

    // Parallax background effect
    window.addEventListener('mousemove', (e) => {
        const { clientX, clientY } = e;
        const xPos = (clientX / window.innerWidth - 0.5) * 40;
        const yPos = (clientY / window.innerHeight - 0.5) * 40;

        gsap.to('.background-glow', {
            x: xPos,
            y: yPos,
            duration: 2,
            ease: 'power2.out'
        });
    });
});
</script>

<template>
    <Head title="Welcome" />

    <div ref="container" class="min-h-screen flex flex-col p-4 sm:p-8 relative overflow-hidden bg-background">
        
        <!-- Dynamic Background Glows -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="background-glow absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-[120px]"></div>
            <div class="background-glow absolute top-[20%] -right-[5%] w-[35%] h-[35%] bg-blue-500/5 rounded-full blur-[100px]"></div>
            <div class="background-glow absolute -bottom-[10%] left-[20%] w-[30%] h-[30%] bg-purple-500/5 rounded-full blur-[110px]"></div>
        </div>

        <!-- Header -->
        <header class="welcome-header flex items-center justify-between w-full max-w-7xl mx-auto z-20">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20">
                    <ArrowUpRight class="text-primary-foreground size-5" />
                </div>
                <span class="text-xl font-black font-display tracking-tight text-foreground">Finance Tracker</span>
            </div>

            <nav class="flex items-center gap-2 sm:gap-3">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="dashboard()"
                        class="rounded-[20px] border border-border/60 bg-card/30 px-5 py-2.5 text-sm font-bold transition-all hover:bg-card active:scale-[0.98] backdrop-blur-sm"
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
                        class="rounded-[20px] border border-border/60 bg-card/30 px-5 py-2 text-sm font-bold transition-all hover:bg-card active:scale-[0.98] backdrop-blur-sm"
                    >
                        Register
                    </Link>
                </template>
                <button
                    @click="toggleTheme"
                    class="ml-2 inline-flex items-center justify-center rounded-full p-2 text-sm font-medium transition-colors hover:bg-card/50 text-foreground border border-border/40 bg-card/20 backdrop-blur-sm"
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
                <div class="welcome-content space-y-8 max-w-lg mb-8 md:mb-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.08em] font-sans backdrop-blur-md">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        Personal Finance Intelligence
                    </div>
                    
                    <div class="space-y-4">
                        <h1 class="text-5xl md:text-6xl lg:text-7xl font-black font-display tracking-tight leading-[0.95] text-foreground">
                            Master your <br />
                            <span class="text-primary italic">wealth</span> journey.
                        </h1>
                        <p class="text-base md:text-lg text-muted-foreground/80 font-medium leading-relaxed max-w-md font-sans">
                            A minimalist dashboard for the modern era. Track assets, analyze spending, and secure your financial future with pure clarity.
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4 pt-4">
                        <template v-if="!$page.props.auth.user">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group inline-flex items-center justify-center rounded-[24px] bg-primary px-8 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98]"
                            >
                                Get Started <ArrowRight class="ml-2 size-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center rounded-[24px] border border-border bg-card/50 px-8 py-4 text-sm font-bold transition-all hover:bg-card active:scale-[0.98] backdrop-blur-sm"
                            >
                                Sign In
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="dashboard()"
                                class="group inline-flex items-center justify-center rounded-[24px] bg-primary px-8 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98]"
                            >
                                Open Dashboard <ArrowRight class="ml-2 size-4 group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </template>
                    </div>

                    <div class="flex items-center gap-6 pt-4 border-t border-border/20">
                        <div class="space-y-1">
                            <p class="text-2xl font-black font-display">100%</p>
                            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">Privacy Focused</p>
                        </div>
                        <div class="w-px h-8 bg-border/20"></div>
                        <div class="space-y-1">
                            <p class="text-2xl font-black font-display">0.02s</p>
                            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">Instant Analysis</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Dashboard mockup cards -->
                <div class="relative w-full flex flex-col gap-6 pl-0 md:pl-10 select-none">
                    
                    <!-- Card 1: Net Worth -->
                    <div class="mockup-card self-end md:mr-10 w-full max-w-[280px] p-6 rounded-[32px] border border-border/40 bg-card/40 surface-card premium-hover transform xl:translate-x-8 backdrop-blur-md">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80">Net Worth</p>
                            <div class="size-8 rounded-full bg-[--fin-gain]/10 flex items-center justify-center">
                                <ArrowUpRight class="size-4 text-[--fin-gain]" />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p class="text-4xl font-black font-display tracking-tight text-foreground">₱245,600</p>
                            <p class="text-[11px] font-bold text-[--fin-gain]">+₱12,400 this month</p>
                        </div>
                        <div class="mt-6 flex gap-1">
                            <div v-for="i in 5" :key="i" class="flex-1 h-3 rounded-full bg-muted/20 overflow-hidden">
                                <div class="h-full bg-primary/40 rounded-full" :style="{ width: [40, 70, 50, 90, 60][i-1] + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Budget -->
                    <div class="mockup-card self-start md:ml-4 w-full max-w-[260px] p-5 rounded-[32px] border border-border/40 bg-card/40 surface-card premium-hover backdrop-blur-md shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-1.5">
                                <Target class="h-4 w-4 text-primary" />
                                <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground">Budget</p>
                            </div>
                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-[--fin-gain]/12 text-[--fin-gain]">65%</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-muted/30 overflow-hidden my-4 relative">
                            <div class="h-full rounded-full bg-primary" style="width: 65%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] font-mono">
                            <span class="text-muted-foreground font-bold">₱13,000</span>
                            <span class="text-foreground font-black">₱20,000</span>
                        </div>
                    </div>

                    <!-- Card 3: Recent Expense -->
                    <div class="mockup-card self-end md:mr-4 w-full max-w-[260px] p-5 rounded-[32px] border border-border/40 bg-card/40 surface-card premium-hover relative transform xl:-translate-x-4 backdrop-blur-md">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="size-10 rounded-[14px] bg-[--fin-loss]/10 flex items-center justify-center">
                                <TrendingDown class="h-5 w-5 text-[--fin-loss]" />
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-widest uppercase text-muted-foreground">Latest Spend</p>
                                <p class="text-sm font-bold text-foreground">Coffee & Dining</p>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <p class="text-2xl font-black font-display text-[--fin-loss]">-₱850.00</p>
                            <span class="text-[10px] font-bold text-muted-foreground mb-1">Today, 2:45 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>
</template>

<style scoped>
.surface-card {
    transition: transform 0.3s ease-out, box-shadow 0.3s ease-out, border-color 0.3s ease-out;
}
</style>
