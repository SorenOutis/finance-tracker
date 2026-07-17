<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import {
    ArrowUpRight,
    Sun,
    Moon,
    ArrowRight,
    Check,
    X,
    Menu,
    X as XIcon,
    Sparkles,
    ShieldCheck,
    Smartphone,
    Zap,
    Globe,
    Users,
    Lock,
    Clock,
    Star,
    HelpCircle,
} from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { login, register, home } from '@/routes';

const { appearance, updateAppearance } = useAppearance();
useSmoothScroll();

const isAuthenticated = ref(false);
const mobileMenuOpen = ref(false);
const isAnnual = ref(true);

const tiers = [
    {
        name: 'Free',
        description: 'Perfect for getting started with personal finance tracking.',
        monthlyPrice: 0,
        annualPrice: 0,
        currency: '₱',
        popular: false,
        gradient: 'from-slate-500/10 to-slate-500/5',
        borderColor: 'border-slate-500/10',
        iconColor: 'text-slate-400',
        cta: 'Get Started Free',
        ctaVariant: 'outline' as const,
        features: [
            { text: 'Up to 50 transactions/month', included: true },
            { text: 'Basic dashboard & insights', included: true },
            { text: '1 financial goal', included: true },
            { text: 'CSV import', included: true },
            { text: 'Community support', included: true },
            { text: 'Advanced analytics', included: false },
            { text: 'Unlimited budgets', included: false },
            { text: 'Priority support', included: false },
        ],
    },
    {
        name: 'Pro',
        description: 'For serious budgeters who want full control over their finances.',
        monthlyPrice: 299,
        annualPrice: 2499,
        currency: '₱',
        popular: true,
        gradient: 'from-primary/15 to-primary/5',
        borderColor: 'border-primary/20',
        iconColor: 'text-primary',
        cta: 'Start Free Trial',
        ctaVariant: 'primary' as const,
        features: [
            { text: 'Unlimited transactions', included: true },
            { text: 'Advanced dashboard & analytics', included: true },
            { text: 'Unlimited financial goals', included: true },
            { text: 'CSV import & export', included: true },
            { text: 'Priority email support', included: true },
            { text: 'Recurring transaction automation', included: true },
            { text: 'Custom categories & envelopes', included: true },
            { text: 'Multi-account sync', included: true },
        ],
    },
    {
        name: 'Enterprise',
        description: 'For teams and businesses needing advanced financial infrastructure.',
        monthlyPrice: 999,
        annualPrice: 9999,
        currency: '₱',
        popular: false,
        gradient: 'from-amber-500/10 to-amber-500/5',
        borderColor: 'border-amber-500/10',
        iconColor: 'text-amber-500',
        cta: 'Contact Sales',
        ctaVariant: 'outline' as const,
        features: [
            { text: 'Everything in Pro', included: true },
            { text: 'Unlimited team members', included: true },
            { text: 'Custom integrations', included: true },
            { text: 'Dedicated account manager', included: true },
            { text: 'SLA guarantee (99.9% uptime)', included: true },
            { text: 'On-premise deployment option', included: true },
            { text: 'Custom reporting & dashboards', included: true },
            { text: 'API access & webhooks', included: true },
        ],
    },
];

const faqs = [
    {
        q: 'Can I switch plans at any time?',
        a: 'Yes! You can upgrade or downgrade your plan at any time. If you upgrade, you\'ll be charged the prorated difference. If you downgrade, the new rate applies at the start of your next billing cycle.',
    },
    {
        q: 'Is there a free trial for Pro?',
        a: 'Absolutely! You get a 14-day free trial of Pro with full access to all features. No credit card required. Cancel anytime during the trial — no questions asked.',
    },
    {
        q: 'What payment methods do you accept?',
        a: 'We accept all major credit cards (Visa, Mastercard, AmEx), PayPal, and GCash. For Enterprise plans, we also support bank transfers and invoicing.',
    },
    {
        q: 'Is my financial data secure?',
        a: 'Security is our top priority. We use AES-256 encryption at rest and TLS 1.3 in transit. Our zero-knowledge architecture ensures that even we cannot see your actual financial numbers.',
    },
];

const currentPrice = computed(() => {
    return (price: number) => (isAnnual.value ? price : Math.round(price / 12));
});

const priceLabel = computed(() => {
    return isAnnual.value ? '/year' : '/month';
});

const savingsText = computed(() => {
    return (annual: number, monthly: number) => {
        if (annual === 0) return '';
        const saved = monthly * 12 - annual;
        return `Save ₱${saved.toLocaleString()}/year`;
    };
});

const toggleTheme = (event: MouseEvent) => {
    const newTheme = appearance.value === 'dark' ? 'light' : 'dark';

    if (!document.startViewTransition) {
        updateAppearance(newTheme);
        return;
    }

    const x = event.clientX;
    const y = event.clientY;
    const endRadius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

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
                pseudoElement:
                    newTheme === 'dark'
                        ? '::view-transition-old(root)'
                        : '::view-transition-new(root)',
            },
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

// Refs for scroll animations
const pricingRef = ref<HTMLElement | null>(null);
const faqRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);

onMounted(() => {
    isAuthenticated.value = !!(window as any).__inertia_page_props?.auth?.user;

    gsap.registerPlugin(ScrollTrigger);

    // Header entrance
    gsap.from('.pricing-header', {
        y: -20,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out',
    });

    // Hero section entrance
    const heroTl = gsap.timeline();
    heroTl
        .from('.pricing-badge', {
            y: 20,
            opacity: 0,
            scale: 0.9,
            duration: 0.6,
            ease: 'back.out(1.7)',
        })
        .from(
            '.pricing-title',
            {
                y: 30,
                opacity: 0,
                duration: 0.7,
                ease: 'power3.out',
            },
            '-=0.3',
        )
        .from(
            '.pricing-description',
            {
                y: 20,
                opacity: 0,
                duration: 0.6,
                ease: 'power3.out',
            },
            '-=0.4',
        )
        .from(
            '.pricing-toggle',
            {
                y: 20,
                opacity: 0,
                duration: 0.5,
                ease: 'power3.out',
            },
            '-=0.3',
        );

    // Pricing cards entrance
    gsap.from('.pricing-card', {
        scrollTrigger: {
            trigger: pricingRef.value,
            start: 'top 75%',
            toggleActions: 'play none none reverse',
        },
        y: 50,
        opacity: 0,
        duration: 0.8,
        stagger: 0.15,
        ease: 'power3.out',
    });

    // FAQ entrance
    gsap.from('.faq-item', {
        scrollTrigger: {
            trigger: faqRef.value,
            start: 'top 75%',
            toggleActions: 'play none none reverse',
        },
        y: 30,
        opacity: 0,
        duration: 0.6,
        stagger: 0.1,
        ease: 'power3.out',
    });

    gsap.from('.faq-heading', {
        scrollTrigger: {
            trigger: faqRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 20,
        opacity: 0,
        duration: 0.5,
        ease: 'power3.out',
    });

    // CTA section
    gsap.from('.cta-content', {
        scrollTrigger: {
            trigger: ctaRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 30,
        opacity: 0,
        scale: 0.98,
        duration: 0.8,
        ease: 'power3.out',
    });

    // Footer
    gsap.from('.footer-content', {
        scrollTrigger: {
            trigger: '.footer-section',
            start: 'top 85%',
            toggleActions: 'play none none reverse',
        },
        y: 20,
        opacity: 0,
        duration: 0.5,
        ease: 'power3.out',
    });

    ScrollTrigger.refresh();
});
</script>

<template>
    <Head title="Pricing — Finance Tracker" />

    <div class="min-h-screen flex flex-col bg-background overflow-x-hidden">
        <!-- ============ DYNAMIC BACKGROUND ============ -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div
                class="absolute -top-[15%] -left-[10%] w-[50%] h-[50%] bg-primary/6 rounded-full blur-[150px]"
            ></div>
            <div
                class="absolute top-[40%] -right-[5%] w-[35%] h-[35%] bg-blue-500/5 rounded-full blur-[130px]"
            ></div>
            <div
                class="absolute bottom-[10%] left-[15%] w-[25%] h-[25%] bg-amber-500/4 rounded-full blur-[120px]"
            ></div>
        </div>

        <!-- ============ HEADER ============ -->
        <header
            class="pricing-header fixed top-0 left-0 right-0 z-50 border-b border-border/10 bg-background/70 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60"
        >
            <div
                class="flex items-center justify-between w-full max-w-7xl mx-auto px-4 sm:px-8 h-16 sm:h-20"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2.5 group"
                >
                    <div
                        class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-shadow"
                    >
                        <ArrowUpRight class="text-primary-foreground size-4.5" />
                    </div>
                    <span class="text-lg font-black font-display tracking-tight text-foreground">
                        Finance Tracker
                    </span>
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-2">
                    <Link
                        :href="home({ query: { scrollTo: null } })"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        Home
                    </Link>
                    <a
                        href="#pricing"
                        class="px-4 py-2 text-sm font-semibold text-foreground bg-card/30 rounded-xl"
                    >
                        Pricing
                    </a>
                    <a
                        href="#faq"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        FAQ
                    </a>
                    <div class="w-px h-6 bg-border/40 mx-2"></div>
                    <template v-if="isAuthenticated">
                        <Link
                            :href="home({ query: { scrollTo: null } })"
                            class="rounded-[20px] bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98]"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-4 py-2 text-sm font-bold text-muted-foreground transition-all hover:text-foreground"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-[20px] bg-primary px-5 py-2.5 text-sm font-bold text-primary-foreground transition-all hover:shadow-xl hover:shadow-primary/20 active:scale-[0.98]"
                        >
                            Sign Up Free
                        </Link>
                    </template>
                    <button
                        @click="toggleTheme"
                        class="ml-1 inline-flex items-center justify-center rounded-full p-2.5 text-sm font-medium transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20"
                        :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`"
                    >
                        <Sun v-if="appearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>
                </nav>

                <!-- Mobile menu button -->
                <div class="flex md:hidden items-center gap-2">
                    <button
                        @click="toggleTheme"
                        class="inline-flex items-center justify-center rounded-full p-2 text-sm font-medium transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20"
                        :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`"
                    >
                        <Sun v-if="appearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center rounded-full p-2 text-sm font-medium transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20"
                        aria-label="Toggle menu"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <XIcon v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu dropdown -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="md:hidden border-t border-border/10 bg-background/95 backdrop-blur-xl"
                >
                    <div class="px-4 py-4 space-y-2">
                        <Link
                            :href="home()"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            Home
                        </Link>
                        <a
                            href="#pricing"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            Pricing
                        </a>
                        <a
                            href="#faq"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            FAQ
                        </a>
                        <div class="pt-2 space-y-2">
                            <template v-if="!isAuthenticated">
                                <Link
                                    :href="login()"
                                    @click="mobileMenuOpen = false"
                                    class="block w-full text-center px-5 py-3 text-sm font-bold text-muted-foreground border border-border/40 rounded-xl hover:bg-card/50 transition-colors"
                                >
                                    Log in
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="register()"
                                    @click="mobileMenuOpen = false"
                                    class="block w-full text-center px-5 py-3 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all"
                                >
                                    Sign Up Free
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    :href="home({ query: { scrollTo: null } })"
                                    @click="mobileMenuOpen = false"
                                    class="block w-full text-center px-5 py-3 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all"
                                >
                                    Dashboard
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </Transition>
        </header>

        <!-- Spacer for fixed header -->
        <div class="h-16 sm:h-20"></div>

        <!-- ============ HERO / PRICING INTRO ============ -->
        <section class="relative pt-16 sm:pt-20 md:pt-24 pb-12 sm:pb-16">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8 text-center">
                <!-- Badge -->
                <div
                    class="pricing-badge inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md"
                >
                    <Sparkles class="size-3 text-primary" />
                    Simple, Transparent Pricing
                </div>

                <!-- Title -->
                <h1
                    class="pricing-title text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black font-display tracking-tight text-foreground mt-6 sm:mt-8"
                >
                    <span class="block">Choose the plan that</span>
                    <span class="block text-primary italic">fits your life</span>
                </h1>

                <!-- Description -->
                <p
                    class="pricing-description text-base sm:text-lg text-muted-foreground/80 font-medium max-w-xl mx-auto mt-4 sm:mt-6"
                >
                    No hidden fees. No surprises. Start for free and upgrade
                    when you need more power.
                </p>

                <!-- Toggle: Monthly / Annual -->
                <div class="pricing-toggle flex items-center justify-center gap-3 sm:gap-4 mt-8 sm:mt-10">
                    <span
                        class="text-sm font-semibold transition-colors"
                        :class="!isAnnual ? 'text-foreground' : 'text-muted-foreground/60'"
                    >
                        Monthly
                    </span>
                    <button
                        @click="isAnnual = !isAnnual"
                        class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full border-2 border-border/40 transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        :class="isAnnual ? 'bg-primary' : 'bg-card/50'"
                        role="switch"
                        :aria-checked="isAnnual"
                    >
                        <span
                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform duration-300"
                            :class="isAnnual ? 'translate-x-[1.375rem]' : 'translate-x-[0.125rem]'"
                        ></span>
                    </button>
                    <span
                        class="text-sm font-semibold transition-colors"
                        :class="isAnnual ? 'text-foreground' : 'text-muted-foreground/60'"
                    >
                        Annual
                    </span>
                    <span
                        class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider"
                    >
                        Save up to 30%
                    </span>
                </div>
            </div>
        </section>

        <!-- ============ PRICING CARDS ============ -->
        <section
            id="pricing"
            ref="pricingRef"
            class="relative pb-16 sm:pb-20 md:pb-24"
        >
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div
                    class="grid md:grid-cols-3 gap-5 sm:gap-6 lg:gap-8 max-w-6xl mx-auto items-start"
                >
                    <div
                        v-for="(tier, index) in tiers"
                        :key="tier.name"
                        class="pricing-card group relative"
                    >
                        <!-- Popular badge -->
                        <div
                            v-if="tier.popular"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-20 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-primary text-primary-foreground text-[10px] font-bold uppercase tracking-[0.12em] shadow-xl shadow-primary/25"
                        >
                            <Star class="size-3 fill-current" />
                            Most Popular
                        </div>

                        <!-- Card -->
                        <div
                            class="relative rounded-3xl border overflow-hidden transition-all duration-300"
                            :class="[
                                tier.popular
                                    ? 'border-primary/30 bg-card/50 shadow-2xl shadow-primary/5 scale-[1.02] md:scale-105'
                                    : 'border-border/30 bg-card/30 backdrop-blur-sm hover:border-border/60 hover:bg-card/50 hover:shadow-xl',
                            ]"
                        >
                            <!-- Gradient background -->
                            <div
                                class="absolute inset-0 rounded-3xl bg-gradient-to-br opacity-50 transition-opacity duration-500"
                                :class="tier.gradient"
                            ></div>

                            <div class="relative z-10 p-6 sm:p-8 lg:p-10">
                                <!-- Tier header -->
                                <div class="flex items-center justify-between mb-2">
                                    <h3
                                        class="text-lg sm:text-xl font-bold font-display text-foreground"
                                    >
                                        {{ tier.name }}
                                    </h3>
                                    <component
                                        :is="tier.name === 'Free' ? Sparkles : tier.name === 'Pro' ? Zap : Globe"
                                        class="size-5 sm:size-5.5"
                                        :class="tier.iconColor"
                                    />
                                </div>

                                <p class="text-xs sm:text-sm text-muted-foreground/70 mb-5 sm:mb-6">
                                    {{ tier.description }}
                                </p>

                                <!-- Price -->
                                <div class="mb-5 sm:mb-6">
                                    <div class="flex items-end gap-1">
                                        <span
                                            class="text-3xl sm:text-4xl lg:text-5xl font-black font-display text-foreground tracking-tight"
                                        >
                                            {{ tier.currency
                                            }}{{
                                                tier.annualPrice === 0
                                                    ? '0'
                                                    : currentPrice(
                                                          isAnnual
                                                              ? tier.annualPrice
                                                              : tier.monthlyPrice,
                                                      ).toLocaleString()
                                            }}
                                        </span>
                                        <span
                                            class="text-xs sm:text-sm text-muted-foreground/60 font-medium mb-1 sm:mb-1.5"
                                        >
                                            {{ tier.annualPrice === 0 ? '/forever' : priceLabel }}
                                        </span>
                                    </div>

                                    <!-- Savings label -->
                                    <p
                                        v-if="
                                            isAnnual && tier.annualPrice > 0
                                        "
                                        class="text-[10px] sm:text-[11px] font-bold text-emerald-500 mt-1.5"
                                    >
                                        {{ savingsText(tier.annualPrice, tier.monthlyPrice) }}
                                    </p>
                                </div>

                                <!-- CTA Button -->
                                <Link
                                    v-if="tier.ctaVariant === 'primary'"
                                    :href="register()"
                                    class="group/btn relative w-full inline-flex items-center justify-center rounded-2xl px-6 py-3.5 text-sm font-bold transition-all overflow-hidden"
                                    :class="[
                                        tier.popular
                                            ? 'bg-primary text-primary-foreground hover:shadow-2xl hover:shadow-primary/25 active:scale-[0.97]'
                                            : '',
                                    ]"
                                >
                                    <span
                                        class="absolute inset-0 bg-white/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"
                                    ></span>
                                    <span class="relative flex items-center justify-center gap-2">
                                        {{ tier.cta }}
                                        <ArrowRight class="size-4 group-hover/btn:translate-x-1 transition-transform" />
                                    </span>
                                </Link>
                                <Link
                                    v-else-if="tier.name === 'Free'"
                                    :href="register()"
                                    class="group/btn relative w-full inline-flex items-center justify-center rounded-2xl border border-border/60 bg-card/30 px-6 py-3.5 text-sm font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm"
                                >
                                    <span class="relative flex items-center justify-center gap-2">
                                        {{ tier.cta }}
                                        <ArrowRight class="size-4 group-hover/btn:translate-x-1 transition-transform" />
                                    </span>
                                </Link>
                                <button
                                    v-else
                                    class="group/btn relative w-full inline-flex items-center justify-center rounded-2xl border border-border/60 bg-card/30 px-6 py-3.5 text-sm font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm cursor-pointer"
                                >
                                    <span class="relative flex items-center justify-center gap-2">
                                        {{ tier.cta }}
                                        <ArrowRight class="size-4 group-hover/btn:translate-x-1 transition-transform" />
                                    </span>
                                </button>

                                <!-- Features list -->
                                <div class="mt-6 sm:mt-8 space-y-3 sm:space-y-4">
                                    <p
                                        class="text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground/50"
                                    >
                                        What's included
                                    </p>
                                    <ul class="space-y-2.5 sm:space-y-3">
                                        <li
                                            v-for="feature in tier.features"
                                            :key="feature.text"
                                            class="flex items-start gap-2.5"
                                        >
                                            <div
                                                class="shrink-0 mt-0.5"
                                                :class="
                                                    feature.included
                                                        ? 'text-emerald-500'
                                                        : 'text-muted-foreground/30'
                                                "
                                            >
                                                <Check
                                                    v-if="feature.included"
                                                    class="size-3.5 sm:size-4"
                                                />
                                                <X
                                                    v-else
                                                    class="size-3.5 sm:size-4"
                                                />
                                            </div>
                                            <span
                                                class="text-xs sm:text-sm"
                                                :class="
                                                    feature.included
                                                        ? 'text-foreground/80'
                                                        : 'text-muted-foreground/40 line-through'
                                                "
                                            >
                                                {{ feature.text }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FEATURE COMPARISON HIGHLIGHTS ============ -->
        <section class="relative py-16 sm:py-20 md:py-24">
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>

            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div
                    class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 max-w-5xl mx-auto"
                >
                    <div
                        v-for="highlight in [
                            {
                                icon: ShieldCheck,
                                title: 'Bank-Grade Security',
                                description:
                                    'AES-256 encryption, zero-knowledge architecture, and SOC 2 compliance keep your data safe.',
                                color: 'text-emerald-500',
                                bg: 'bg-emerald-500/10',
                                border: 'border-emerald-500/10',
                            },
                            {
                                icon: Clock,
                                title: '14-Day Free Trial',
                                description:
                                    'Try Pro free for 14 days with full access. No credit card required, cancel anytime.',
                                color: 'text-blue-500',
                                bg: 'bg-blue-500/10',
                                border: 'border-blue-500/10',
                            },
                            {
                                icon: Users,
                                title: 'Team Collaboration',
                                description:
                                    'Invite family members or teammates. Shared budgets, goals, and real-time sync across devices.',
                                color: 'text-violet-500',
                                bg: 'bg-violet-500/10',
                                border: 'border-violet-500/10',
                            },
                        ]"
                        :key="highlight.title"
                        class="p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-border/30 bg-card/30 backdrop-blur-sm transition-all duration-300 hover:border-border/60 hover:bg-card/50 hover:shadow-xl hover:-translate-y-0.5"
                    >
                        <div
                            class="size-11 sm:size-12 rounded-2xl border flex items-center justify-center mb-4 sm:mb-5 transition-transform group-hover:scale-110"
                            :class="[highlight.border, highlight.bg]"
                        >
                            <component
                                :is="highlight.icon"
                                class="size-5 sm:size-5.5"
                                :class="highlight.color"
                            />
                        </div>
                        <h3
                            class="text-base sm:text-lg font-bold font-display text-foreground mb-2"
                        >
                            {{ highlight.title }}
                        </h3>
                        <p
                            class="text-xs sm:text-sm text-muted-foreground/70 leading-relaxed"
                        >
                            {{ highlight.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FAQ SECTION ============ -->
        <section
            id="faq"
            ref="faqRef"
            class="relative py-16 sm:py-20 md:py-24"
        >
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>

            <div class="w-full max-w-3xl mx-auto px-4 sm:px-8">
                <!-- FAQ heading -->
                <div class="faq-heading text-center max-w-xl mx-auto mb-12 sm:mb-16">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-5"
                    >
                        <HelpCircle class="size-3 text-primary" />
                        Got Questions?
                    </div>
                    <h2
                        class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground"
                    >
                        Frequently asked
                        <span class="text-primary italic">questions</span>
                    </h2>
                </div>

                <!-- FAQ Items -->
                <div class="space-y-3 sm:space-y-4">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="faq-item group rounded-2xl border border-border/20 bg-card/20 backdrop-blur-sm p-5 sm:p-6 transition-all duration-300 hover:border-border/40 hover:bg-card/30"
                    >
                        <details class="group/details">
                            <summary
                                class="flex items-center justify-between cursor-pointer list-none"
                            >
                                <span
                                    class="text-sm sm:text-base font-bold text-foreground pr-4"
                                >
                                    {{ faq.q }}
                                </span>
                                <div
                                    class="shrink-0 size-6 sm:size-7 rounded-full border border-border/30 flex items-center justify-center transition-transform duration-300 group-open/details:rotate-45"
                                >
                                    <ArrowRight class="size-3 sm:size-3.5 text-muted-foreground/60" />
                                </div>
                            </summary>
                            <p
                                class="mt-3 sm:mt-4 text-xs sm:text-sm text-muted-foreground/70 leading-relaxed max-w-lg"
                            >
                                {{ faq.a }}
                            </p>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ CTA SECTION ============ -->
        <section
            ref="ctaRef"
            class="relative py-16 sm:py-20 md:py-24"
        >
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>

            <div
                class="cta-content w-full max-w-3xl mx-auto px-4 sm:px-8 text-center"
            >
                <div
                    class="relative rounded-3xl sm:rounded-4xl border border-border/30 bg-gradient-to-b from-card/30 to-transparent p-8 sm:p-12 md:p-16 backdrop-blur-sm overflow-hidden"
                >
                    <!-- Background glow -->
                    <div
                        class="absolute -top-[30%] left-1/2 -translate-x-1/2 w-[60%] h-[60%] bg-primary/8 rounded-full blur-[100px] pointer-events-none"
                    ></div>

                    <div class="relative z-10 space-y-6 sm:space-y-8">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md"
                        >
                            <Lock class="size-3 text-primary" />
                            No credit card required
                        </div>

                        <h2
                            class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground"
                        >
                            Start your
                            <span class="text-primary italic">free</span>
                            journey today
                        </h2>

                        <p
                            class="text-sm sm:text-base text-muted-foreground/80 font-medium max-w-md mx-auto"
                        >
                            Join thousands of users already mastering their
                            finances. No commitment, no risk.
                        </p>

                        <div
                            class="flex flex-wrap items-center justify-center gap-3 sm:gap-4"
                        >
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group relative inline-flex items-center justify-center rounded-2xl bg-primary px-8 sm:px-10 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-2xl hover:shadow-primary/25 active:scale-[0.97] overflow-hidden"
                            >
                                <span
                                    class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"
                                ></span>
                                <span class="relative flex items-center">
                                    Get Started Free
                                    <ArrowRight class="ml-2 size-4 group-hover:translate-x-1 transition-transform" />
                                </span>
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center rounded-2xl border border-border/60 bg-card/30 px-8 sm:px-10 py-4 text-sm font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm"
                            >
                                Sign In
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FOOTER SECTION ============ -->
        <footer class="footer-section relative border-t border-border/20 py-12 sm:py-16">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div class="footer-content grid sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10">
                    <!-- Brand -->
                    <div class="space-y-3 sm:space-y-4 md:col-span-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-primary flex items-center justify-center">
                                <ArrowUpRight class="text-primary-foreground size-4" />
                            </div>
                            <span class="text-base sm:text-lg font-black font-display tracking-tight text-foreground">
                                Finance Tracker
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-muted-foreground/70 max-w-sm leading-relaxed">
                            A minimalist approach to financial management.
                            Track, analyze, and grow your wealth with clarity
                            and confidence.
                        </p>
                    </div>

                    <!-- Product Links -->
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground">
                            Product
                        </p>
                        <ul class="space-y-2 sm:space-y-3">
                            <li>
                                <Link
                                    :href="home()"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Home
                                </Link>
                            </li>
                            <li>
                                <a
                                    href="/pricing"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Pricing
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground">
                            Legal
                        </p>
                        <ul class="space-y-2 sm:space-y-3">
                            <li>
                                <span class="text-xs sm:text-sm text-muted-foreground/70 font-medium cursor-not-allowed opacity-50">
                                    Privacy Policy
                                </span>
                            </li>
                            <li>
                                <span class="text-xs sm:text-sm text-muted-foreground/70 font-medium cursor-not-allowed opacity-50">
                                    Terms of Service
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="footer-content mt-10 sm:mt-14 pt-6 sm:pt-8 border-t border-border/20 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground/50 font-medium">
                        &copy; {{ new Date().getFullYear() }} Finance Tracker.
                        All rights reserved.
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground/40">
                            <Smartphone class="size-3" />
                            <span>Mobile Ready</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground/40">
                            <ShieldCheck class="size-3" />
                            <span>Privacy First</span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Smooth transitions for hover effects */
.pricing-card,
.faq-item {
    transition:
        transform 0.3s ease-out,
        box-shadow 0.3s ease-out,
        border-color 0.3s ease-out,
        background-color 0.3s ease-out;
}

/* Details/summary marker reset */
details > summary {
    list-style: none;
}

details > summary::-webkit-details-marker {
    display: none;
}

/* Smooth open/close for details */
details[open] summary + * {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}
</style>
