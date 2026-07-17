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
import { usePage } from '@inertiajs/vue3';
import { useAppearance } from '@/composables/useAppearance';
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { login, register, home } from '@/routes';
import { absoluteUrl } from '@/utils/url';

const { appearance, updateAppearance } = useAppearance();
useSmoothScroll();

// JSON-LD structured data
const appUrl = computed(() => (usePage().props.appUrl as string) || '');
const siteUrl = computed(() => absoluteUrl('/', appUrl.value));
const pricingUrl = computed(() => absoluteUrl('/pricing', appUrl.value));

const ldWebsite = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        name: 'Finance Tracker',
        url: siteUrl.value,
        description: 'Choose the perfect plan for your financial journey. Free, Pro ($2/mo), and Enterprise ($5/mo) tiers with transparent pricing.',
    }),
);

const ldOrganization = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Organization',
        name: 'Finance Tracker',
        url: siteUrl.value,
        logo: absoluteUrl('/apple-touch-icon.png', appUrl.value),
    }),
);

const ldBreadcrumb = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: siteUrl.value },
            { '@type': 'ListItem', position: 2, name: 'Pricing', item: pricingUrl.value },
        ],
    }),
);

const isAuthenticated = ref(false);
const mobileMenuOpen = ref(false);
const isAnnual = ref(false);

const tiers = [
    {
        name: 'Free',
        description: 'Get started with the basics.',
        monthlyPrice: 0,
        annualPrice: 0,
        currency: '$',
        popular: false,
        gradient: 'from-slate-500/10 to-slate-500/5',
        borderColor: 'border-slate-500/10',
        iconColor: 'text-slate-400',
        cta: 'Get Started',
        ctaVariant: 'outline' as const,
        features: [
            { text: 'Up to 50 transactions/mo', included: true },
            { text: 'Basic dashboard', included: true },
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
        description: 'Full control over your finances.',
        monthlyPrice: 2,
        annualPrice: 10,
        currency: '$',
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
            { text: 'Recurring automation', included: true },
            { text: 'Custom categories & envelopes', included: true },
            { text: 'Multi-account sync', included: true },
        ],
    },
    {
        name: 'Enterprise',
        description: 'For teams & businesses.',
        monthlyPrice: 5,
        annualPrice: 20,
        currency: '$',
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
            { text: 'On-premise deployment', included: true },
            { text: 'Custom reporting', included: true },
            { text: 'API access & webhooks', included: true },
        ],
    },
];

const faqs = [
    {
        q: 'Can I switch plans at any time?',
        a: 'Yes! Upgrade or downgrade anytime. Upgrades are prorated, downgrades apply at your next billing cycle.',
    },
    {
        q: 'Is there a free trial for Pro?',
        a: 'Absolutely! 14-day free trial with full access. No credit card required — cancel anytime.',
    },
    {
        q: 'What payment methods do you accept?',
        a: 'All major credit cards (Visa, Mastercard, AmEx), PayPal, and GCash. Enterprise plans also support bank transfers.',
    },
    {
        q: 'Is my financial data secure?',
        a: 'AES-256 encryption at rest, TLS 1.3 in transit. Zero-knowledge architecture means even we cannot see your numbers.',
    },
];

const displayPrice = computed(() => {
    return (m: number, a: number) => (isAnnual.value ? a : m);
});

const priceLabel = computed(() => {
    return isAnnual.value ? '/yr' : '/mo';
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
    { canRegister: true },
);

// Comparison table data
const comparisonCategories = [
    {
        category: 'Transactions',
        rows: [
            { feature: 'Monthly transactions', free: '50/mo', pro: 'Unlimited', enterprise: 'Unlimited' },
            { feature: 'Recurring automation', free: false, pro: true, enterprise: true },
            { feature: 'CSV import', free: true, pro: true, enterprise: true },
            { feature: 'CSV export', free: false, pro: true, enterprise: true },
        ],
    },
    {
        category: 'Dashboard & Analytics',
        rows: [
            { feature: 'Dashboard', free: 'Basic', pro: 'Advanced', enterprise: 'Advanced + Custom' },
            { feature: 'Advanced analytics', free: false, pro: true, enterprise: true },
            { feature: 'Custom reporting', free: false, pro: false, enterprise: true },
            { feature: 'Financial goals', free: '1 goal', pro: 'Unlimited', enterprise: 'Unlimited' },
        ],
    },
    {
        category: 'Management',
        rows: [
            { feature: 'Budgets', free: false, pro: true, enterprise: true },
            { feature: 'Custom categories', free: false, pro: true, enterprise: true },
            { feature: 'Envelopes', free: false, pro: true, enterprise: true },
            { feature: 'Multi-account sync', free: false, pro: true, enterprise: true },
        ],
    },
    {
        category: 'Team & Support',
        rows: [
            { feature: 'Team members', free: '1', pro: 'Up to 5', enterprise: 'Unlimited' },
            { feature: 'Support', free: 'Community', pro: 'Priority Email', enterprise: 'Dedicated Manager' },
            { feature: 'Custom integrations', free: false, pro: false, enterprise: true },
            { feature: 'API access & webhooks', free: false, pro: false, enterprise: true },
        ],
    },
];

const pricingRef = ref<HTMLElement | null>(null);
const comparisonRef = ref<HTMLElement | null>(null);
const faqRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);

onMounted(() => {
    isAuthenticated.value = !!(window as any).__inertia_page_props?.auth?.user;

    gsap.registerPlugin(ScrollTrigger);

    gsap.from('.pricing-header', { y: -20, opacity: 0, duration: 0.6, ease: 'power3.out' });

    const heroTl = gsap.timeline();
    heroTl
        .from('.pricing-badge', { y: 16, opacity: 0, scale: 0.9, duration: 0.5, ease: 'back.out(1.7)' })
        .from('.pricing-title', { y: 20, opacity: 0, duration: 0.5, ease: 'power3.out' }, '-=0.2')
        .from('.pricing-description', { y: 16, opacity: 0, duration: 0.4, ease: 'power3.out' }, '-=0.3')
        .from('.pricing-toggle', { y: 16, opacity: 0, duration: 0.4, ease: 'power3.out' }, '-=0.2');

    gsap.from('.pricing-card', {
        scrollTrigger: { trigger: pricingRef.value, start: 'top 80%', toggleActions: 'play none none reverse' },
        y: 30, opacity: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out',
    });

    gsap.from('.comparison-section', {
        scrollTrigger: { trigger: comparisonRef.value, start: 'top 80%', toggleActions: 'play none none reverse' },
        y: 20, opacity: 0, duration: 0.6, ease: 'power3.out',
    });
    gsap.from('.comparison-row', {
        scrollTrigger: { trigger: comparisonRef.value, start: 'top 75%', toggleActions: 'play none none reverse' },
        y: 12, opacity: 0, duration: 0.3, stagger: 0.03, ease: 'power2.out',
    });

    gsap.from('.faq-item', {
        scrollTrigger: { trigger: faqRef.value, start: 'top 80%', toggleActions: 'play none none reverse' },
        y: 20, opacity: 0, duration: 0.4, stagger: 0.08, ease: 'power3.out',
    });
    gsap.from('.faq-heading', {
        scrollTrigger: { trigger: faqRef.value, start: 'top 85%', toggleActions: 'play none none reverse' },
        y: 16, opacity: 0, duration: 0.4, ease: 'power3.out',
    });

    gsap.from('.cta-content', {
        scrollTrigger: { trigger: ctaRef.value, start: 'top 85%', toggleActions: 'play none none reverse' },
        y: 20, opacity: 0, scale: 0.98, duration: 0.6, ease: 'power3.out',
    });

    gsap.from('.footer-content', {
        scrollTrigger: { trigger: '.footer-section', start: 'top 90%', toggleActions: 'play none none reverse' },
        y: 16, opacity: 0, duration: 0.4, ease: 'power3.out',
    });

    ScrollTrigger.refresh();
});
</script>

<template>
    <Head title="Pricing — Finance Tracker">
        <meta name="description" content="Choose the perfect plan for your financial journey. Free, Pro ($2/mo), and Enterprise ($5/mo) tiers with transparent pricing, no hidden fees, and a 14-day free trial." />
        <meta name="keywords" content="finance tracker pricing, budgeting app plans, personal finance subscription, free finance app, pro finance plan" />
        <link rel="canonical" :href="absoluteUrl('/pricing', $page.props.appUrl)" />

        <!-- Open Graph -->
        <meta property="og:title" content="Pricing — Finance Tracker" />
        <meta property="og:description" content="Choose the perfect plan for your financial journey. Start free with the basics, go Pro for $2/mo, or scale with Enterprise at $5/mo. No hidden fees." />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="absoluteUrl('/pricing', $page.props.appUrl)" />
        <meta property="og:image" :content="absoluteUrl('/apple-touch-icon.png', $page.props.appUrl)" />
        <meta property="og:image:width" content="166" />
        <meta property="og:image:height" content="166" />
        <meta property="og:image:alt" content="Finance Tracker Logo" />
        <meta property="og:site_name" content="Finance Tracker" />
        <meta property="og:locale" content="en_US" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="Pricing — Finance Tracker" />
        <meta name="twitter:description" content="Start free, upgrade when you need more. Pro from $2/mo, Enterprise from $5/mo. Transparent pricing, no surprises." />
        <meta name="twitter:image" :content="absoluteUrl('/apple-touch-icon.png', $page.props.appUrl)" />

        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json" v-html="ldWebsite"></script>
        <script type="application/ld+json" v-html="ldOrganization"></script>
        <script type="application/ld+json" v-html="ldBreadcrumb"></script>
    </Head>

    <div class="min-h-screen flex flex-col bg-background overflow-x-hidden">
        <!-- Background glows -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div class="absolute -top-[15%] -left-[10%] w-[50%] h-[50%] bg-primary/6 rounded-full blur-[150px]"></div>
            <div class="absolute top-[40%] -right-[5%] w-[35%] h-[35%] bg-blue-500/5 rounded-full blur-[130px]"></div>
            <div class="absolute bottom-[10%] left-[15%] w-[25%] h-[25%] bg-amber-500/4 rounded-full blur-[120px]"></div>
        </div>

        <!-- ============ HEADER ============ -->
        <header class="pricing-header fixed top-0 left-0 right-0 z-50 border-b border-border/10 bg-background/70 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60">
            <div class="flex items-center justify-between w-full max-w-7xl mx-auto px-4 sm:px-8 h-14 sm:h-16">
                <Link :href="home()" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-shadow">
                        <ArrowUpRight class="text-primary-foreground size-4" />
                    </div>
                    <span class="text-base font-black font-display tracking-tight text-foreground">Finance Tracker</span>
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-1.5">
                    <Link :href="home()" class="px-3 py-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-lg hover:bg-card/50">Home</Link>
                    <a href="#pricing" class="px-3 py-1.5 text-xs font-semibold text-foreground bg-card/30 rounded-lg">Pricing</a>
                    <a href="#faq" class="px-3 py-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-lg hover:bg-card/50">FAQ</a>
                    <div class="w-px h-5 bg-border/40 mx-1.5"></div>
                    <template v-if="isAuthenticated">
                        <Link :href="home()" class="rounded-full bg-primary px-4 py-1.5 text-xs font-bold text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="px-3 py-1.5 text-xs font-bold text-muted-foreground transition-all hover:text-foreground">Log in</Link>
                        <Link v-if="canRegister" :href="register()" class="rounded-full bg-primary px-4 py-1.5 text-xs font-bold text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">Sign Up Free</Link>
                    </template>
                    <button @click="toggleTheme" class="ml-1 inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`">
                        <Sun v-if="appearance === 'dark'" class="h-3.5 w-3.5" />
                        <Moon v-else class="h-3.5 w-3.5" />
                    </button>
                </nav>

                <!-- Mobile -->
                <div class="flex md:hidden items-center gap-1.5">
                    <button @click="toggleTheme" class="inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`">
                        <Sun v-if="appearance === 'dark'" class="h-3.5 w-3.5" />
                        <Moon v-else class="h-3.5 w-3.5" />
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" aria-label="Toggle menu">
                        <Menu v-if="!mobileMenuOpen" class="h-4 w-4" />
                        <XIcon v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-border/10 bg-background/95 backdrop-blur-xl">
                    <div class="px-4 py-3 space-y-1">
                        <Link :href="home()" @click="mobileMenuOpen = false" class="block px-4 py-2 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors">Home</Link>
                        <a href="#pricing" @click="mobileMenuOpen = false" class="block px-4 py-2 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors">Pricing</a>
                        <a href="#faq" @click="mobileMenuOpen = false" class="block px-4 py-2 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors">FAQ</a>
                        <div class="pt-2 space-y-1.5">
                            <template v-if="!isAuthenticated">
                                <Link :href="login()" @click="mobileMenuOpen = false" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-muted-foreground border border-border/40 rounded-xl hover:bg-card/50 transition-colors">Log in</Link>
                                <Link v-if="canRegister" :href="register()" @click="mobileMenuOpen = false" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all">Sign Up Free</Link>
                            </template>
                            <template v-else>
                                <Link :href="home()" @click="mobileMenuOpen = false" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all">Dashboard</Link>
                            </template>
                        </div>
                    </div>
                </div>
            </Transition>
        </header>

        <!-- Spacer -->
        <div class="h-14 sm:h-16"></div>

        <!-- ============ HERO ============ -->
        <section class="relative pt-10 sm:pt-12 md:pt-14 pb-8 sm:pb-10">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8 text-center">
                <div class="pricing-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[9px] font-bold uppercase tracking-[0.1em] backdrop-blur-md">
                    <Sparkles class="size-2.5 text-primary" />
                    Simple, Transparent Pricing
                </div>

                <h1 class="pricing-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black font-display tracking-tight text-foreground mt-4 sm:mt-5">
                    <span class="block">Choose the plan that</span>
                    <span class="block text-primary italic">fits your life</span>
                </h1>

                <p class="pricing-description text-xs sm:text-sm text-muted-foreground/80 font-medium max-w-lg mx-auto mt-2">
                    No hidden fees. No surprises. Start free, upgrade when you need more.
                </p>

                <!-- Toggle -->
                <div class="pricing-toggle flex items-center justify-center gap-2.5 mt-5 sm:mt-6">
                    <span class="text-xs font-semibold transition-colors" :class="!isAnnual ? 'text-foreground' : 'text-muted-foreground/60'">Monthly</span>
                    <button @click="isAnnual = !isAnnual" class="relative inline-flex h-6 w-10 shrink-0 cursor-pointer items-center rounded-full border-2 border-border/40 transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" :class="isAnnual ? 'bg-primary' : 'bg-card/50'" role="switch" :aria-checked="isAnnual">
                        <span class="pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-md ring-0 transition-transform duration-300" :class="isAnnual ? 'translate-x-[1.15rem]' : 'translate-x-[0.125rem]'"></span>
                    </button>
                    <span class="text-xs font-semibold transition-colors" :class="isAnnual ? 'text-foreground' : 'text-muted-foreground/60'">Annual</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[8px] font-bold uppercase tracking-wider">Save up to 67%</span>
                </div>
            </div>
        </section>

        <!-- ============ PRICING CARDS ============ -->
        <section id="pricing" ref="pricingRef" class="relative pb-10 sm:pb-12 md:pb-14">
            <div class="w-full max-w-6xl mx-auto px-4 sm:px-8">
                <div class="grid md:grid-cols-3 gap-3 sm:gap-4 lg:gap-5 max-w-5xl mx-auto items-start">
                    <div v-for="tier in tiers" :key="tier.name" class="pricing-card group relative">
                        <!-- Popular badge -->
                        <div v-if="tier.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 z-20 inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary text-primary-foreground text-[8px] font-bold uppercase tracking-[0.12em] shadow-lg shadow-primary/25">
                            <Star class="size-2.5 fill-current" />
                            Most Popular
                        </div>

                        <div class="relative rounded-2xl border overflow-hidden transition-all duration-300" :class="[tier.popular ? 'border-primary/30 bg-card/50 shadow-xl shadow-primary/5 scale-[1.02] md:scale-105' : 'border-border/30 bg-card/30 backdrop-blur-sm hover:border-border/60 hover:bg-card/50 hover:shadow-lg']">
                            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br opacity-50 transition-opacity duration-500" :class="tier.gradient"></div>

                            <div class="relative z-10 p-4 sm:p-5 lg:p-6">
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-1.5">
                                    <h3 class="text-sm sm:text-base font-bold font-display text-foreground">{{ tier.name }}</h3>
                                    <component :is="tier.name === 'Free' ? Sparkles : tier.name === 'Pro' ? Zap : Globe" class="size-4 sm:size-4.5" :class="tier.iconColor" />
                                </div>
                                <p class="text-[10px] sm:text-[11px] text-muted-foreground/70 mb-3 sm:mb-4">{{ tier.description }}</p>

                                <!-- Price -->
                                <div class="mb-3 sm:mb-4">
                                    <div class="flex items-end gap-0.5">
                                        <span class="text-xl sm:text-2xl lg:text-3xl font-black font-display text-foreground tracking-tight">
                                            {{ tier.currency }}{{ tier.annualPrice === 0 ? '0' : displayPrice(tier.monthlyPrice, tier.annualPrice) }}
                                        </span>
                                        <span class="text-[10px] sm:text-[11px] text-muted-foreground/60 font-medium mb-0.5">{{ tier.annualPrice === 0 ? '/forever' : priceLabel }}</span>
                                    </div>
                                    <p v-if="isAnnual && tier.annualPrice > 0" class="text-[9px] sm:text-[10px] font-bold text-emerald-500 mt-1">
                                        ${{ tier.annualPrice }}/yr — save ${{ tier.monthlyPrice * 12 - tier.annualPrice }}/yr
                                    </p>
                                </div>

                                <!-- CTA -->
                                <Link v-if="tier.ctaVariant === 'primary'" :href="register()" class="group/btn relative w-full inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-xs font-bold transition-all overflow-hidden" :class="['bg-primary text-primary-foreground hover:shadow-lg hover:shadow-primary/25 active:scale-[0.97]']">
                                    <span class="absolute inset-0 bg-white/10 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></span>
                                    <span class="relative flex items-center justify-center gap-1.5">{{ tier.cta }} <ArrowRight class="size-3.5 group-hover/btn:translate-x-0.5 transition-transform" /></span>
                                </Link>
                                <Link v-else-if="tier.name === 'Free'" :href="register()" class="group/btn relative w-full inline-flex items-center justify-center rounded-xl border border-border/60 bg-card/30 px-4 py-2.5 text-xs font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm">
                                    <span class="relative flex items-center justify-center gap-1.5">{{ tier.cta }} <ArrowRight class="size-3.5 group-hover/btn:translate-x-0.5 transition-transform" /></span>
                                </Link>
                                <button v-else class="group/btn relative w-full inline-flex items-center justify-center rounded-xl border border-border/60 bg-card/30 px-4 py-2.5 text-xs font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm cursor-pointer">
                                    <span class="relative flex items-center justify-center gap-1.5">{{ tier.cta }} <ArrowRight class="size-3.5 group-hover/btn:translate-x-0.5 transition-transform" /></span>
                                </button>

                                <!-- Features -->
                                <div class="mt-3 sm:mt-4 space-y-2 sm:space-y-2.5">
                                    <p class="text-[8px] font-bold uppercase tracking-[0.15em] text-muted-foreground/50">What's included</p>
                                    <ul class="space-y-1.5 sm:space-y-2">
                                        <li v-for="feature in tier.features" :key="feature.text" class="flex items-start gap-2">
                                            <div class="shrink-0 mt-0.5" :class="feature.included ? 'text-emerald-500' : 'text-muted-foreground/30'">
                                                <Check v-if="feature.included" class="size-3" />
                                                <X v-else class="size-3" />
                                            </div>
                                            <span class="text-[10px] sm:text-[11px]" :class="feature.included ? 'text-foreground/80' : 'text-muted-foreground/40 line-through'">{{ feature.text }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FEATURE COMPARISON TABLE ============ -->
        <section id="compare" ref="comparisonRef" class="comparison-section relative py-8 sm:py-10 md:py-12">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"></div>

            <div class="w-full max-w-5xl mx-auto px-4 sm:px-8">
                <div class="text-center mb-8 sm:mb-10">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[9px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-4">
                        <Sparkles class="size-2.5 text-primary" />
                        Compare Plans
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black font-display tracking-tight text-foreground">
                        Everything you get,
                        <span class="text-primary italic">side by side</span>
                    </h2>
                </div>

                <!-- Desktop table -->
                <div class="hidden md:block">
                    <div class="rounded-2xl border border-border/20 bg-card/20 backdrop-blur-sm overflow-hidden">
                        <!-- Column headers -->
                        <div class="grid grid-cols-4 gap-px bg-border/10">
                            <div class="p-3 sm:p-4 bg-card/40">
                                <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-muted-foreground/50">Feature</span>
                            </div>
                            <div class="p-3 sm:p-4 bg-card/40 text-center">
                                <span class="text-xs font-bold text-slate-400">Free</span>
                            </div>
                            <div class="p-3 sm:p-4 bg-primary/5 text-center relative">
                                <span class="text-xs font-bold text-primary">Pro</span>
                                <div class="absolute -top-px left-0 right-0 h-0.5 bg-primary rounded-full"></div>
                            </div>
                            <div class="p-3 sm:p-4 bg-card/40 text-center">
                                <span class="text-xs font-bold text-amber-500">Enterprise</span>
                            </div>
                        </div>

                        <!-- Category rows -->
                        <template v-for="(cat, ci) in comparisonCategories" :key="ci">
                            <!-- Category header -->
                            <div class="grid grid-cols-4 gap-px bg-border/10">
                                <div class="col-span-4 px-3 sm:px-4 py-2 bg-card/50">
                                    <span class="text-[9px] font-bold uppercase tracking-[0.12em] text-muted-foreground/60">{{ cat.category }}</span>
                                </div>
                            </div>
                            <!-- Feature rows -->
                            <div v-for="(row, ri) in cat.rows" :key="ri" class="comparison-row grid grid-cols-4 gap-px bg-border/10">
                                <div class="p-3 sm:p-4 bg-card/30 flex items-center">
                                    <span class="text-[11px] sm:text-xs text-foreground/80 font-medium">{{ row.feature }}</span>
                                </div>
                                <div class="p-3 sm:p-4 bg-card/30 flex items-center justify-center">
                                    <template v-if="row.free === true">
                                        <Check class="size-3.5 sm:size-4 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.free === false">
                                        <X class="size-3.5 sm:size-4 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[10px] sm:text-[11px] text-foreground/70 font-medium">{{ row.free }}</span>
                                    </template>
                                </div>
                                <div class="p-3 sm:p-4 bg-primary/[0.02] flex items-center justify-center">
                                    <template v-if="row.pro === true">
                                        <Check class="size-3.5 sm:size-4 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.pro === false">
                                        <X class="size-3.5 sm:size-4 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[10px] sm:text-[11px] text-foreground/70 font-medium">{{ row.pro }}</span>
                                    </template>
                                </div>
                                <div class="p-3 sm:p-4 bg-card/30 flex items-center justify-center">
                                    <template v-if="row.enterprise === true">
                                        <Check class="size-3.5 sm:size-4 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.enterprise === false">
                                        <X class="size-3.5 sm:size-4 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[10px] sm:text-[11px] text-foreground/70 font-medium">{{ row.enterprise }}</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Mobile comparison (card-style) -->
                <div class="md:hidden space-y-3">
                    <div v-for="(cat, ci) in comparisonCategories" :key="ci" class="comparison-row rounded-xl border border-border/20 bg-card/20 backdrop-blur-sm p-3 sm:p-4">
                        <p class="text-[9px] font-bold uppercase tracking-[0.12em] text-muted-foreground/60 mb-2 pb-2 border-b border-border/10">{{ cat.category }}</p>
                        <div v-for="(row, ri) in cat.rows" :key="ri" class="flex items-center justify-between py-1.5 border-b border-border/5 last:border-none">
                            <span class="text-[11px] text-foreground/80 font-medium flex-1">{{ row.feature }}</span>
                            <div class="flex items-center gap-2.5">
                                <!-- Free -->
                                <div class="flex flex-col items-center gap-0.5 w-10">
                                    <span class="text-[7px] font-bold uppercase text-slate-400/60">Free</span>
                                    <template v-if="row.free === true">
                                        <Check class="size-3 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.free === false">
                                        <X class="size-3 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[8px] text-foreground/60 font-medium truncate max-w-[3rem]">{{ row.free }}</span>
                                    </template>
                                </div>
                                <!-- Pro -->
                                <div class="flex flex-col items-center gap-0.5 w-10">
                                    <span class="text-[7px] font-bold uppercase text-primary/70">Pro</span>
                                    <template v-if="row.pro === true">
                                        <Check class="size-3 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.pro === false">
                                        <X class="size-3 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[8px] text-foreground/60 font-medium truncate max-w-[3rem]">{{ row.pro }}</span>
                                    </template>
                                </div>
                                <!-- Enterprise -->
                                <div class="flex flex-col items-center gap-0.5 w-10">
                                    <span class="text-[7px] font-bold uppercase text-amber-500/70">Ent.</span>
                                    <template v-if="row.enterprise === true">
                                        <Check class="size-3 text-emerald-500" />
                                    </template>
                                    <template v-else-if="row.enterprise === false">
                                        <X class="size-3 text-muted-foreground/30" />
                                    </template>
                                    <template v-else>
                                        <span class="text-[8px] text-foreground/60 font-medium truncate max-w-[3rem]">{{ row.enterprise }}</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ HIGHLIGHTS ============ -->
        <section class="relative py-8 sm:py-10 md:py-12">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-w-4xl mx-auto">
                    <div v-for="h in [
                        { icon: ShieldCheck, title: 'Bank-Grade Security', desc: 'AES-256 encryption, zero-knowledge architecture, SOC 2 compliance.', color: 'text-emerald-500', bg: 'bg-emerald-500/10', border: 'border-emerald-500/10' },
                        { icon: Clock, title: '14-Day Free Trial', desc: 'Try Pro free for 14 days. No credit card required, cancel anytime.', color: 'text-blue-500', bg: 'bg-blue-500/10', border: 'border-blue-500/10' },
                        { icon: Users, title: 'Team Collaboration', desc: 'Shared budgets, goals, and real-time sync across all your devices.', color: 'text-violet-500', bg: 'bg-violet-500/10', border: 'border-violet-500/10' },
                    ]" :key="h.title" class="p-4 sm:p-5 rounded-xl sm:rounded-2xl border border-border/30 bg-card/30 backdrop-blur-sm transition-all duration-300 hover:border-border/60 hover:bg-card/50 hover:shadow-lg hover:-translate-y-0.5">
                        <div class="size-9 sm:size-10 rounded-xl border flex items-center justify-center mb-3" :class="[h.border, h.bg]">
                            <component :is="h.icon" class="size-4 sm:size-4.5" :class="h.color" />
                        </div>
                        <h3 class="text-sm sm:text-base font-bold font-display text-foreground mb-1">{{ h.title }}</h3>
                        <p class="text-[10px] sm:text-[11px] text-muted-foreground/70 leading-relaxed">{{ h.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FAQ ============ -->
        <section id="faq" ref="faqRef" class="relative py-8 sm:py-10 md:py-12">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"></div>
            <div class="w-full max-w-2xl mx-auto px-4 sm:px-8">
                <div class="faq-heading text-center max-w-lg mx-auto mb-8 sm:mb-10">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[9px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-4">
                        <HelpCircle class="size-2.5 text-primary" />
                        Got Questions?
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black font-display tracking-tight text-foreground">Frequently asked <span class="text-primary italic">questions</span></h2>
                </div>

                <div class="space-y-2 sm:space-y-3">
                    <div v-for="(faq, i) in faqs" :key="i" class="faq-item rounded-xl border border-border/20 bg-card/20 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300 hover:border-border/40 hover:bg-card/30">
                        <details class="group/details">
                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                <span class="text-xs sm:text-sm font-bold text-foreground pr-3">{{ faq.q }}</span>
                                <div class="shrink-0 size-5 sm:size-6 rounded-full border border-border/30 flex items-center justify-center transition-transform duration-300 group-open/details:rotate-45">
                                    <ArrowRight class="size-2.5 sm:size-3 text-muted-foreground/60" />
                                </div>
                            </summary>
                            <p class="mt-2 text-[10px] sm:text-[11px] text-muted-foreground/70 leading-relaxed max-w-lg">{{ faq.a }}</p>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ CTA ============ -->
        <section ref="ctaRef" class="relative py-8 sm:py-10 md:py-12">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"></div>
            <div class="cta-content w-full max-w-2xl mx-auto px-4 sm:px-8 text-center">
                <div class="relative rounded-2xl sm:rounded-3xl border border-border/30 bg-gradient-to-b from-card/30 to-transparent p-6 sm:p-8 md:p-10 backdrop-blur-sm overflow-hidden">
                    <div class="absolute -top-[30%] left-1/2 -translate-x-1/2 w-[60%] h-[60%] bg-primary/8 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="relative z-10 space-y-4 sm:space-y-5">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[9px] font-bold uppercase tracking-[0.1em] backdrop-blur-md">
                            <Lock class="size-2.5 text-primary" />
                            No credit card required
                        </div>
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-black font-display tracking-tight text-foreground">Start your <span class="text-primary italic">free</span> journey today</h2>
                        <p class="text-xs sm:text-sm text-muted-foreground/80 font-medium max-w-md mx-auto">Join thousands of users already mastering their finances. No commitment, no risk.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                            <Link v-if="canRegister" :href="register()" class="group relative inline-flex items-center justify-center rounded-xl bg-primary px-6 sm:px-8 py-3 text-xs font-bold text-primary-foreground transition-all hover:shadow-xl hover:shadow-primary/25 active:scale-[0.97] overflow-hidden">
                                <span class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
                                <span class="relative flex items-center">Get Started Free <ArrowRight class="ml-1.5 size-3.5 group-hover:translate-x-0.5 transition-transform" /></span>
                            </Link>
                            <Link :href="login()" class="inline-flex items-center justify-center rounded-xl border border-border/60 bg-card/30 px-6 sm:px-8 py-3 text-xs font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm">Sign In</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FOOTER ============ -->
        <footer class="footer-section relative border-t border-border/20 py-8 sm:py-10">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div class="footer-content grid sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
                    <div class="space-y-2 sm:space-y-3 md:col-span-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-primary flex items-center justify-center"><ArrowUpRight class="text-primary-foreground size-3.5" /></div>
                            <span class="text-sm sm:text-base font-black font-display tracking-tight text-foreground">Finance Tracker</span>
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-muted-foreground/70 max-w-xs leading-relaxed">A minimalist approach to financial management. Track, analyze, and grow your wealth with clarity and confidence.</p>
                    </div>
                    <div class="space-y-2 sm:space-y-3">
                        <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-muted-foreground">Product</p>
                        <ul class="space-y-1.5 sm:space-y-2">
                            <li><Link :href="home()" class="text-[10px] sm:text-[11px] text-muted-foreground/70 hover:text-foreground transition-colors font-medium">Home</Link></li>
                            <li><a href="/pricing" class="text-[10px] sm:text-[11px] text-muted-foreground/70 hover:text-foreground transition-colors font-medium">Pricing</a></li>
                        </ul>
                    </div>
                    <div class="space-y-2 sm:space-y-3">
                        <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-muted-foreground">Legal</p>
                        <ul class="space-y-1.5 sm:space-y-2">
                            <li><a href="/sitemap.xml" target="_blank" class="text-[10px] sm:text-[11px] text-muted-foreground/70 hover:text-foreground transition-colors font-medium">Sitemap</a></li>
                            <li><a href="/robots.txt" target="_blank" class="text-[10px] sm:text-[11px] text-muted-foreground/70 hover:text-foreground transition-colors font-medium">robots.txt</a></li>
                            <li><span class="text-[10px] sm:text-[11px] text-muted-foreground/70 font-medium cursor-not-allowed opacity-50">Privacy Policy</span></li>
                            <li><span class="text-[10px] sm:text-[11px] text-muted-foreground/70 font-medium cursor-not-allowed opacity-50">Terms of Service</span></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-content mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-border/20 flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-3">
                    <p class="text-[9px] sm:text-[10px] text-muted-foreground/50 font-medium">&copy; {{ new Date().getFullYear() }} Finance Tracker. All rights reserved.</p>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1 text-[9px] text-muted-foreground/40"><Smartphone class="size-2.5" /><span>Mobile Ready</span></div>
                        <div class="flex items-center gap-1 text-[9px] text-muted-foreground/40"><ShieldCheck class="size-2.5" /><span>Privacy First</span></div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.pricing-card,
.faq-item {
    transition: transform 0.3s ease-out, box-shadow 0.3s ease-out, border-color 0.3s ease-out, background-color 0.3s ease-out;
}

details > summary {
    list-style: none;
}
details > summary::-webkit-details-marker {
    display: none;
}
details[open] summary + * {
    animation: fadeInUp 0.25s ease-out;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
html {
    scroll-behavior: smooth;
}
</style>
