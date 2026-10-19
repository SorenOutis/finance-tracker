<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import {
    ArrowUpRight,
    Sun,
    Moon,
    ArrowRight,
    LayoutDashboard,
    TrendingUp,
    PiggyBank,
    Target,
    BarChart3,
    ShieldCheck,
    Smartphone,
    RefreshCw,
    CheckCheck,
    Star,
    Quote,
    Menu,
    X,
} from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { dashboard, login, register, home, pricing } from '@/routes';
import { absoluteUrl } from '@/utils/url';

const { appearance, updateAppearance } = useAppearance();
useSmoothScroll();

// JSON-LD structured data
const appUrl = computed(() => (usePage().props.appUrl as string) || '');
const siteUrl = computed(() => absoluteUrl('/', appUrl.value));

const ldWebsite = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        name: 'Finance Tracker',
        url: siteUrl.value,
        description: 'Master your wealth journey with a minimalist finance dashboard. Track assets, analyze spending, set intelligent budgets, and achieve your financial goals.',
        potentialAction: {
            '@type': 'SearchAction',
            target: { '@type': 'EntryPoint', urlTemplate: siteUrl.value + '?q={search_term_string}' },
            'query-input': 'required name=search_term_string',
        },
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
        ],
    }),
);

// Check if user is authenticated
const isAuthenticated = ref(false);

// Mobile menu
const mobileMenuOpen = ref(false);

// Feature data
const features = [
    {
        icon: LayoutDashboard,
        title: 'Smart Dashboard',
        description:
            'See your entire financial universe at a glance. Real-time net worth, spending summaries, and key metrics all in one place.',
        gradient: 'from-emerald-500/20 to-emerald-500/5',
        borderColor: 'border-emerald-500/10',
        iconColor: 'text-emerald-500',
    },
    {
        icon: TrendingUp,
        title: 'Expense Tracking',
        description:
            'Log every transaction with ease. Auto-categorize spending, attach receipts, and never lose track of where your money goes.',
        gradient: 'from-blue-500/20 to-blue-500/5',
        borderColor: 'border-blue-500/10',
        iconColor: 'text-blue-500',
    },
    {
        icon: PiggyBank,
        title: 'Budget Control',
        description:
            'Set intelligent budgets that adapt to your lifestyle. Get real-time alerts and stay in control of every peso.',
        gradient: 'from-violet-500/20 to-violet-500/5',
        borderColor: 'border-violet-500/10',
        iconColor: 'text-violet-500',
    },
    {
        icon: Target,
        title: 'Financial Goals',
        description:
            'Save for what matters most. Track progress towards your dreams with visual milestones and smart recommendations.',
        gradient: 'from-amber-500/20 to-amber-500/5',
        borderColor: 'border-amber-500/10',
        iconColor: 'text-amber-500',
    },
    {
        icon: BarChart3,
        title: 'Powerful Analytics',
        description:
            'Beautiful charts and actionable insights. Understand spending patterns, identify savings opportunities, and plan ahead.',
        gradient: 'from-rose-500/20 to-rose-500/5',
        borderColor: 'border-rose-500/10',
        iconColor: 'text-rose-500',
    },
    {
        icon: ShieldCheck,
        title: 'Privacy First',
        description:
            'Your financial data stays yours. No tracking, no data mining. Zero-knowledge architecture means even we cannot see your numbers.',
        gradient: 'from-cyan-500/20 to-cyan-500/5',
        borderColor: 'border-cyan-500/10',
        iconColor: 'text-cyan-500',
    },
];

// Stats data
const stats = [
    { value: 100, suffix: '%', label: 'Privacy Focused', prefix: '' },
    { value: 500000, suffix: '+', label: 'Tracked Transactions', prefix: '₱' },
    { value: 15, suffix: 'K+', label: 'Active Users', prefix: '' },
    { value: 99.9, suffix: '%', label: 'Uptime', prefix: '' },
];

// How it works steps
const steps = [
    {
        number: '01',
        title: 'Connect Your Accounts',
        description:
            'Link your accounts or start fresh. Import existing data via CSV or manually add your first transactions.',
        icon: RefreshCw,
    },
    {
        number: '02',
        title: 'Track Automatically',
        description:
            'Set up recurring transactions, categorize spending, and let the system do the heavy lifting.',
        icon: LayoutDashboard,
    },
    {
        number: '03',
        title: 'Gain Actionable Insights',
        description:
            'Watch your wealth grow with real-time analytics, smart budgets, and goal tracking that keeps you motivated.',
        icon: TrendingUp,
    },
];

// Testimonials
const testimonials = [
    {
        quote: 'This completely changed how I manage my finances. The dashboard is incredibly intuitive, and the analytics help me make smarter decisions every day.',
        name: 'Alexis Rivera',
        role: 'Freelance Designer',
        rating: 5,
    },
    {
        quote: 'I\'ve tried dozens of finance apps, but this one stands out. The budget tracking and goal setting features are exactly what I needed to get my savings on track.',
        name: 'Marcus Tan',
        role: 'Software Engineer',
        rating: 5,
    },
    {
        quote: 'Finally, a finance app that respects my privacy while delivering professional-grade tools. The CSV import saved me hours of manual entry.',
        name: 'Sofia Chen',
        role: 'Small Business Owner',
        rating: 5,
    },
];

// Initialize scene refs
const heroRef = ref<HTMLElement | null>(null);
const featuresRef = ref<HTMLElement | null>(null);
const statsRef = ref<HTMLElement | null>(null);
const howItWorksRef = ref<HTMLElement | null>(null);
const testimonialsRef = ref<HTMLElement | null>(null);
const ctaRef = ref<HTMLElement | null>(null);

// Counter element refs
const statValueRefs = ref<(HTMLElement | null)[]>([]);

const setStatValueRef = (el: HTMLElement | null, index: number) => {
    statValueRefs.value[index] = el;
};

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
        Math.max(y, innerHeight - y),
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
                clipPath:
                    newTheme === 'dark'
                        ? [...clipPath].reverse()
                        : clipPath,
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

onMounted(() => {
    isAuthenticated.value = !!(
        window as any
    ).__inertia_page_props?.auth?.user;

    // ============================
    // Hero Entrance Animations
    // ============================
    const heroTl = gsap.timeline();

    heroTl
        .from('.hero-header', {
            y: -20,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out',
        })
        .from(
            '.hero-badge',
            {
                y: 20,
                opacity: 0,
                scale: 0.9,
                duration: 0.6,
                ease: 'back.out(1.7)',
            },
            '-=0.4',
        )
        .from(
            '.hero-title > *',
            {
                y: 40,
                opacity: 0,
                duration: 0.8,
                stagger: 0.15,
                ease: 'power3.out',
            },
            '-=0.3',
        )
        .from(
            '.hero-description',
            {
                y: 30,
                opacity: 0,
                duration: 0.7,
                ease: 'power3.out',
            },
            '-=0.5',
        )
        .from(
            '.hero-actions > *',
            {
                y: 30,
                opacity: 0,
                duration: 0.6,
                stagger: 0.15,
                ease: 'power3.out',
            },
            '-=0.3',
        )
        .from(
            '.hero-stats > *',
            {
                y: 20,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1,
                ease: 'power3.out',
            },
            '-=0.2',
        )
        .from(
            '.mockup-card',
            {
                x: 60,
                opacity: 0,
                duration: 1,
                stagger: 0.15,
                ease: 'expo.out',
            },
            '-=0.8',
        );

    // Floating animation for mockup cards
    gsap.to('.mockup-card', {
        y: 'random(-12, 12)',
        rotation: 'random(-2, 2)',
        duration: 'random(3, 5)',
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut',
        stagger: {
            amount: 1.5,
            from: 'random',
        },
    });

    // Parallax background effect for hero
    window.addEventListener('mousemove', (e) => {
        const { clientX, clientY } = e;
        const xPos = (clientX / window.innerWidth - 0.5) * 30;
        const yPos = (clientY / window.innerHeight - 0.5) * 30;

        gsap.to('.hero-glow-1', {
            x: xPos * 1.2,
            y: yPos * 1.2,
            duration: 2,
            ease: 'power2.out',
        });
        gsap.to('.hero-glow-2', {
            x: xPos * -0.8,
            y: yPos * -0.8,
            duration: 2.5,
            ease: 'power2.out',
        });
    });

    // ============================
    // Features Section Animations
    // ============================
    gsap.from('.feature-card', {
        scrollTrigger: {
            trigger: featuresRef.value,
            start: 'top 75%',
            toggleActions: 'play none none reverse',
        },
        y: 60,
        opacity: 0,
        duration: 0.8,
        stagger: 0.1,
        ease: 'power3.out',
    });

    gsap.from('.features-heading', {
        scrollTrigger: {
            trigger: featuresRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 30,
        opacity: 0,
        duration: 0.7,
        ease: 'power3.out',
    });

    // ============================
    // Stats Section - Animated Counters
    // ============================
    stats.forEach((stat, index) => {
        const obj = { val: 0 };
        const el = statValueRefs.value[index];
        if (!el) return;

        gsap.to(obj, {
            val: stat.value,
            duration: 2.5,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: statsRef.value,
                start: 'top 75%',
                toggleActions: 'play none none reverse',
                onEnter: () => {
                    // When entering viewport, animate
                },
            },
            onUpdate: () => {
                const displayVal =
                    stat.value % 1 === 0
                        ? Math.round(obj.val)
                        : obj.val.toFixed(1);
                el.textContent = stat.prefix + displayVal;
            },
        });
    });

    gsap.from('.stat-item', {
        scrollTrigger: {
            trigger: statsRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 40,
        opacity: 0,
        duration: 0.7,
        stagger: 0.15,
        ease: 'power3.out',
    });

    // ============================
    // How It Works Section
    // ============================
    gsap.from('.step-card', {
        scrollTrigger: {
            trigger: howItWorksRef.value,
            start: 'top 75%',
            toggleActions: 'play none none reverse',
        },
        y: 50,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
    });

    gsap.from('.steps-heading', {
        scrollTrigger: {
            trigger: howItWorksRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 30,
        opacity: 0,
        duration: 0.6,
        ease: 'power3.out',
    });

    // ============================
    // Testimonials Section
    // ============================
    gsap.from('.testimonial-card', {
        scrollTrigger: {
            trigger: testimonialsRef.value,
            start: 'top 75%',
            toggleActions: 'play none none reverse',
        },
        y: 50,
        opacity: 0,
        duration: 0.8,
        stagger: 0.15,
        ease: 'power3.out',
    });

    gsap.from('.testimonials-heading', {
        scrollTrigger: {
            trigger: testimonialsRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 30,
        opacity: 0,
        duration: 0.6,
        ease: 'power3.out',
    });

    // ============================
    // CTA Section
    // ============================
    gsap.from('.cta-content', {
        scrollTrigger: {
            trigger: ctaRef.value,
            start: 'top 80%',
            toggleActions: 'play none none reverse',
        },
        y: 40,
        opacity: 0,
        scale: 0.98,
        duration: 1,
        ease: 'power3.out',
    });

    // ============================
    // Footer Section
    // ============================
    gsap.from('.footer-content', {
        scrollTrigger: {
            trigger: '.footer-section',
            start: 'top 85%',
            toggleActions: 'play none none reverse',
        },
        y: 20,
        opacity: 0,
        duration: 0.6,
        ease: 'power3.out',
    });

    // Refresh ScrollTrigger to recalculate positions
    ScrollTrigger.refresh();
});
</script>

<template>
    <Head title="Finance Tracker — Master Your Wealth">
        <meta name="description" content="A minimalist finance tracker dashboard. Track your assets, analyze spending, set budgets, and secure your financial future with pure clarity. Start free." />
        <meta name="keywords" content="finance tracker, personal finance, budgeting app, expense tracker, wealth management, financial dashboard" />
        <link rel="canonical" :href="absoluteUrl('/', appUrl)" />

        <!-- Open Graph -->
        <meta property="og:title" content="Finance Tracker — Master Your Wealth" />
        <meta property="og:description" content="Master your wealth journey with a minimalist finance dashboard. Track assets, analyze spending, set intelligent budgets, and achieve your financial goals." />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="absoluteUrl('/', appUrl)" />
        <meta property="og:image" :content="absoluteUrl('/apple-touch-icon.png', appUrl)" />
        <meta property="og:image:width" content="166" />
        <meta property="og:image:height" content="166" />
        <meta property="og:image:alt" content="Finance Tracker Logo" />
        <meta property="og:site_name" content="Finance Tracker" />
        <meta property="og:locale" content="en_US" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="Finance Tracker — Master Your Wealth" />
        <meta name="twitter:description" content="Track assets, analyze spending, and secure your financial future with minimalist clarity. Start your journey free." />
        <meta name="twitter:image" :content="absoluteUrl('/apple-touch-icon.png', appUrl)" />

        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json" v-html="ldWebsite"></script>
        <script type="application/ld+json" v-html="ldOrganization"></script>
        <script type="application/ld+json" v-html="ldBreadcrumb"></script>
    </Head>

    <div class="min-h-screen flex flex-col bg-background overflow-x-hidden">
        <!-- ============ DYNAMIC BACKGROUND ============ -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div
                class="hero-glow-1 absolute -top-[15%] -left-[10%] w-[50%] h-[50%] bg-primary/8 rounded-full blur-[150px]"
            ></div>
            <div
                class="hero-glow-2 absolute top-[30%] -right-[5%] w-[40%] h-[40%] bg-blue-500/6 rounded-full blur-[130px]"
            ></div>
            <div
                class="absolute bottom-[20%] left-[10%] w-[30%] h-[30%] bg-purple-500/5 rounded-full blur-[120px]"
            ></div>
            <div
                class="absolute top-[60%] right-[20%] w-[20%] h-[20%] bg-emerald-500/4 rounded-full blur-[100px]"
            ></div>
        </div>

        <!-- ============ HEADER ============ -->
        <header
            class="hero-header fixed top-0 left-0 right-0 z-50 border-b border-border/10 bg-background/70 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60"
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
                        <ArrowUpRight
                            class="text-primary-foreground size-4.5"
                        />
                    </div>
                    <span
                        class="text-lg font-black font-display tracking-tight text-foreground"
                    >
                        Finance Tracker
                    </span>
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden md:flex items-center gap-2">
                    <a
                        href="#features"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        Features
                    </a>
                    <a
                        href="#how-it-works"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        How It Works
                    </a>
                    <a
                        href="#testimonials"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        Testimonials
                    </a>
                    <Link
                        :href="pricing()"
                        class="px-4 py-2 text-sm font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-xl hover:bg-card/50"
                    >
                        Pricing
                    </Link>
                    <div class="w-px h-6 bg-border/40 mx-2"></div>
                    <template v-if="isAuthenticated">
                        <Link
                            :href="dashboard()"
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
                        <X v-else class="h-5 w-5" />
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
                        <a
                            href="#features"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            Features
                        </a>
                        <a
                            href="#how-it-works"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            How It Works
                        </a>
                        <a
                            href="#testimonials"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            Testimonials
                        </a>
                        <Link
                            :href="pricing()"
                            @click="mobileMenuOpen = false"
                            class="block px-4 py-3 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors"
                        >
                            Pricing
                        </Link>
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
                                    :href="dashboard()"
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

        <!-- ============ HERO SECTION ============ -->
        <section
            ref="heroRef"
            class="relative min-h-[calc(100vh-5rem)] flex items-center py-16 sm:py-20 md:py-24"
        >
            <div
                class="w-full max-w-7xl mx-auto px-4 sm:px-8 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center"
            >
                <!-- Left: Hero text -->
                <div class="space-y-8 max-w-xl">
                    <!-- Badge -->
                    <div
                        class="hero-badge inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] font-sans backdrop-blur-md"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"
                        ></span>
                        Personal Finance Intelligence
                    </div>

                    <!-- Title -->
                    <div class="hero-title space-y-2">
                        <h1
                            class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black font-display tracking-tight leading-[0.92] text-foreground"
                        >
                            <span class="block">Master your</span>
                            <span class="block text-primary italic"
                                >wealth</span
                            >
                            <span class="block">journey.</span>
                        </h1>
                    </div>

                    <!-- Description -->
                    <p
                        class="hero-description text-base sm:text-lg md:text-xl text-muted-foreground/80 font-medium leading-relaxed max-w-md font-sans"
                    >
                        A minimalist dashboard for the modern era. Track
                        assets, analyze spending, and secure your financial
                        future with pure clarity.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="hero-actions flex flex-wrap items-center gap-4">
                        <template v-if="!isAuthenticated">
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="group relative inline-flex items-center justify-center rounded-2xl bg-primary px-7 sm:px-9 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-2xl hover:shadow-primary/25 active:scale-[0.97] overflow-hidden"
                            >
                                <span
                                    class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"
                                ></span>
                                <span class="relative flex items-center">
                                    Get Started Free
                                    <ArrowRight
                                        class="ml-2 size-4 group-hover:translate-x-1 transition-transform"
                                    />
                                </span>
                            </Link>
                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center rounded-2xl border border-border/60 bg-card/30 px-7 sm:px-9 py-4 text-sm font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm"
                            >
                                Sign In
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="dashboard()"
                                class="group relative inline-flex items-center justify-center rounded-2xl bg-primary px-7 sm:px-9 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-2xl hover:shadow-primary/25 active:scale-[0.97] overflow-hidden"
                            >
                                <span
                                    class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"
                                ></span>
                                <span class="relative flex items-center">
                                    Open Dashboard
                                    <ArrowRight
                                        class="ml-2 size-4 group-hover:translate-x-1 transition-transform"
                                    />
                                </span>
                            </Link>
                        </template>
                    </div>

                    <!-- Trust indicators -->
                    <div
                        class="hero-stats flex items-center gap-6 sm:gap-8 pt-4 border-t border-border/20"
                    >
                        <div
                            v-for="stat in [
                                { value: '100%', label: 'Privacy Focused' },
                                { value: '0.02s', label: 'Instant Analysis' },
                                { value: '24/7', label: 'Available' },
                            ]"
                            :key="stat.label"
                            class="space-y-1"
                        >
                            <p
                                class="text-xl sm:text-2xl font-black font-display text-foreground"
                            >
                                {{ stat.value }}
                            </p>
                            <p
                                class="text-[9px] sm:text-[10px] uppercase font-bold text-muted-foreground tracking-wider"
                            >
                                {{ stat.label }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Dashboard mockup cards -->
                <div
                    class="relative w-full flex flex-col gap-5 sm:gap-6 lg:pl-8 select-none"
                >
                    <!-- Card 1: Net Worth -->
                    <div
                        class="mockup-card self-end lg:mr-12 w-full max-w-[280px] sm:max-w-[300px] p-5 sm:p-6 rounded-[28px] sm:rounded-[32px] border border-border/40 bg-card/40 backdrop-blur-md shadow-2xl"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <p
                                class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground/80"
                            >
                                Net Worth
                            </p>
                            <div
                                class="size-7 sm:size-8 rounded-full bg-[--fin-gain]/10 flex items-center justify-center"
                            >
                                <ArrowUpRight
                                    class="size-3.5 sm:size-4 text-[--fin-gain]"
                                />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <p
                                class="text-3xl sm:text-4xl font-black font-display tracking-tight text-foreground"
                            >
                                ₱245,600
                            </p>
                            <p
                                class="text-[10px] sm:text-[11px] font-bold text-[--fin-gain]"
                            >
                                +₱12,400 this month
                            </p>
                        </div>
                        <div class="mt-5 sm:mt-6 flex gap-1">
                            <div
                                v-for="i in 5"
                                :key="i"
                                class="flex-1 h-2.5 sm:h-3 rounded-full bg-muted/20 overflow-hidden"
                            >
                                <div
                                    class="h-full bg-primary/40 rounded-full"
                                    :style="{
                                        width:
                                            [40, 70, 50, 90, 60][i - 1] + '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Budget -->
                    <div
                        class="mockup-card self-start lg:ml-6 w-full max-w-[240px] sm:max-w-[260px] p-4 sm:p-5 rounded-[28px] sm:rounded-[32px] border border-border/40 bg-card/40 backdrop-blur-md shadow-2xl"
                    >
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="flex items-center gap-1.5">
                                <Target class="h-3.5 sm:h-4 w-3.5 sm:w-4 text-primary" />
                                <p
                                    class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground"
                                >
                                    Budget
                                </p>
                            </div>
                            <span
                                class="text-[9px] sm:text-[10px] font-black px-2 py-0.5 rounded-full bg-[--fin-gain]/12 text-[--fin-gain]"
                                >65%</span
                            >
                        </div>
                        <div
                            class="w-full h-2.5 sm:h-3 rounded-full bg-muted/30 overflow-hidden my-3 sm:my-4"
                        >
                            <div
                                class="h-full rounded-full bg-primary"
                                style="width: 65%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-[10px] sm:text-[11px] font-mono">
                            <span class="text-muted-foreground font-bold"
                                >₱13,000</span
                            >
                            <span class="text-foreground font-black"
                                >₱20,000</span
                            >
                        </div>
                    </div>

                    <!-- Card 3: Recent Expense -->
                    <div
                        class="mockup-card self-end lg:mr-6 w-full max-w-[240px] sm:max-w-[260px] p-4 sm:p-5 rounded-[28px] sm:rounded-[32px] border border-border/40 bg-card/40 backdrop-blur-md shadow-2xl"
                    >
                        <div class="flex items-center gap-2.5 sm:gap-3 mb-3 sm:mb-4">
                            <div
                                class="size-8 sm:size-10 rounded-[12px] sm:rounded-[14px] bg-[--fin-loss]/10 flex items-center justify-center"
                            >
                                <svg
                                    class="h-4 sm:h-5 w-4 sm:w-5 text-[--fin-loss]"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="m7 17 10-10"
                                    />
                                    <path d="M17 17V7H7" />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-[9px] sm:text-[10px] font-bold tracking-widest uppercase text-muted-foreground"
                                >
                                    Latest Spend
                                </p>
                                <p
                                    class="text-xs sm:text-sm font-bold text-foreground"
                                >
                                    Coffee & Dining
                                </p>
                            </div>
                        </div>
                        <div class="flex items-end justify-between">
                            <p
                                class="text-xl sm:text-2xl font-black font-display text-[--fin-loss]"
                            >
                                -₱850.00
                            </p>
                            <span
                                class="text-[9px] sm:text-[10px] font-bold text-muted-foreground mb-0.5 sm:mb-1"
                                >Today, 2:45 PM</span
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FEATURES SECTION ============ -->
        <section
            id="features"
            ref="featuresRef"
            class="relative py-20 sm:py-28 md:py-36"
        >
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <!-- Section heading -->
                <div class="features-heading text-center max-w-2xl mx-auto mb-14 sm:mb-20">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-6"
                    >
                        Everything You Need
                    </div>
                    <h2
                        class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground mb-4"
                    >
                        Powerful features for
                        <span class="text-primary italic">financial clarity</span>
                    </h2>
                    <p
                        class="text-sm sm:text-base text-muted-foreground/80 font-medium max-w-lg mx-auto"
                    >
                        From tracking every peso to visualizing your financial
                        future — we've got you covered.
                    </p>
                </div>

                <!-- Feature cards grid -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="feature-card group relative p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-border/30 bg-card/30 backdrop-blur-sm transition-all duration-300 hover:border-border/60 hover:bg-card/50 hover:shadow-xl hover:-translate-y-1"
                    >
                        <!-- Gradient background -->
                        <div
                            class="absolute inset-0 rounded-2xl sm:rounded-3xl bg-gradient-to-br opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                            :class="feature.gradient"
                        ></div>

                        <div class="relative z-10 space-y-4 sm:space-y-5">
                            <!-- Icon -->
                            <div
                                class="size-11 sm:size-12 rounded-2xl border flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
                                :class="[
                                    feature.borderColor,
                                    feature.iconColor,
                                    `bg-${feature.iconColor.replace('text-', '')}/5`,
                                ]"
                            >
                                <component
                                    :is="feature.icon"
                                    class="size-5 sm:size-5.5"
                                />
                            </div>

                            <!-- Content -->
                            <div class="space-y-2 sm:space-y-3">
                                <h3
                                    class="text-base sm:text-lg font-bold font-display text-foreground"
                                >
                                    {{ feature.title }}
                                </h3>
                                <p
                                    class="text-xs sm:text-sm text-muted-foreground/80 leading-relaxed"
                                >
                                    {{ feature.description }}
                                </p>
                            </div>

                            <!-- Hover indicator -->
                            <div
                                class="flex items-center gap-1.5 text-[11px] font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                :class="feature.iconColor"
                            >
                                <span>Learn more</span>
                                <ArrowRight class="size-3.5" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ STATS SECTION ============ -->
        <section
            ref="statsRef"
            class="relative py-20 sm:py-28 md:py-36"
        >
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>
            <div
                class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>

            <div
                class="w-full max-w-7xl mx-auto px-4 sm:px-8 text-center"
            >
                <!-- Label -->
                <div
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-8"
                >
                    By the Numbers
                </div>

                <!-- Stats grid -->
                <div
                    class="grid grid-cols-2 lg:grid-cols-4 gap-8 sm:gap-12 lg:gap-16 max-w-4xl mx-auto"
                >
                    <div
                        v-for="(stat, index) in stats"
                        :key="stat.label"
                        class="stat-item space-y-2 sm:space-y-3"
                    >
                        <p
                            :ref="(el: any) => setStatValueRef(el, index)"
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black font-display tracking-tight text-foreground tabular-nums"
                        >
                            0{{ stat.suffix }}
                        </p>
                        <p
                            class="text-[10px] sm:text-[11px] uppercase font-bold text-muted-foreground tracking-wider"
                        >
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ HOW IT WORKS SECTION ============ -->
        <section
            id="how-it-works"
            ref="howItWorksRef"
            class="relative py-20 sm:py-28 md:py-36"
        >
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <!-- Section heading -->
                <div class="steps-heading text-center max-w-2xl mx-auto mb-14 sm:mb-20">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-6"
                    >
                        Simple Process
                    </div>
                    <h2
                        class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground mb-4"
                    >
                        Start in
                        <span class="text-primary italic">three</span>
                        simple steps
                    </h2>
                    <p
                        class="text-sm sm:text-base text-muted-foreground/80 font-medium max-w-lg mx-auto"
                    >
                        No complicated setup. No credit card required. Just
                        pure financial clarity.
                    </p>
                </div>

                <!-- Steps -->
                <div
                    class="grid md:grid-cols-3 gap-8 sm:gap-10 max-w-5xl mx-auto relative"
                >
                    <!-- Connecting line (desktop) -->
                    <div
                        class="hidden md:block absolute top-20 left-[16.66%] right-[16.66%] h-px bg-gradient-to-r from-primary/40 via-border/40 to-primary/40"
                    ></div>

                    <div
                        v-for="(step, index) in steps"
                        :key="step.number"
                        class="step-card relative flex flex-col items-center text-center"
                    >
                        <!-- Step number circle -->
                        <div
                            class="relative z-10 size-14 sm:size-16 rounded-full border-2 border-primary/20 bg-card/40 backdrop-blur-sm flex items-center justify-center mb-5 sm:mb-6 shadow-lg shadow-primary/5"
                        >
                            <component
                                :is="step.icon"
                                class="size-5 sm:size-6 text-primary"
                            />
                        </div>

                        <!-- Content -->
                        <div class="space-y-2 sm:space-y-3 max-w-xs">
                            <p
                                class="text-[11px] font-bold text-primary/60 tracking-[0.15em] uppercase"
                            >
                                Step {{ step.number }}
                            </p>
                            <h3
                                class="text-lg sm:text-xl font-bold font-display text-foreground"
                            >
                                {{ step.title }}
                            </h3>
                            <p
                                class="text-xs sm:text-sm text-muted-foreground/70 leading-relaxed"
                            >
                                {{ step.description }}
                            </p>
                        </div>

                        <!-- Arrow connector (mobile) -->
                        <div
                            v-if="index < steps.length - 1"
                            class="md:hidden flex items-center justify-center mt-6 sm:mt-8"
                        >
                            <svg
                                class="size-5 text-muted-foreground/30"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M12 5v14M19 12l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TESTIMONIALS SECTION ============ -->
        <section
            id="testimonials"
            ref="testimonialsRef"
            class="relative py-20 sm:py-28 md:py-36"
        >
            <div
                class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-border/40 to-transparent"
            ></div>

            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <!-- Section heading -->
                <div
                    class="testimonials-heading text-center max-w-2xl mx-auto mb-14 sm:mb-20"
                >
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[10px] font-bold uppercase tracking-[0.1em] backdrop-blur-md mb-6"
                    >
                        Testimonials
                    </div>
                    <h2
                        class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground mb-4"
                    >
                        Loved by
                        <span class="text-primary italic">users</span>
                        like you
                    </h2>
                    <p
                        class="text-sm sm:text-base text-muted-foreground/80 font-medium max-w-lg mx-auto"
                    >
                        Hear from people who've transformed their financial
                        habits.
                    </p>
                </div>

                <!-- Testimonials grid -->
                <div
                    class="grid md:grid-cols-3 gap-5 sm:gap-6 max-w-5xl mx-auto"
                >
                    <div
                        v-for="(testimonial, index) in testimonials"
                        :key="index"
                        class="testimonial-card group relative p-6 sm:p-8 rounded-2xl sm:rounded-3xl border border-border/30 bg-card/30 backdrop-blur-sm transition-all duration-300 hover:border-border/60 hover:bg-card/50 hover:shadow-xl hover:-translate-y-1"
                    >
                        <!-- Quote icon -->
                        <Quote
                            class="size-6 sm:size-8 text-primary/15 mb-3 sm:mb-4"
                        />

                        <!-- Stars -->
                        <div class="flex gap-1 mb-3 sm:mb-4">
                            <Star
                                v-for="i in testimonial.rating"
                                :key="i"
                                class="size-3.5 sm:size-4 text-amber-500 fill-amber-500"
                            />
                        </div>

                        <!-- Quote text -->
                        <p
                            class="text-xs sm:text-sm text-muted-foreground/80 leading-relaxed mb-5 sm:mb-6"
                        >
                            "{{ testimonial.quote }}"
                        </p>

                        <!-- Author -->
                        <div class="flex items-center gap-3">
                            <div
                                class="size-8 sm:size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs sm:text-sm"
                            >
                                {{
                                    testimonial.name
                                        .split(' ')
                                        .map((n) => n[0])
                                        .join('')
                                }}
                            </div>
                            <div>
                                <p
                                    class="text-xs sm:text-sm font-bold text-foreground"
                                >
                                    {{ testimonial.name }}
                                </p>
                                <p
                                    class="text-[10px] sm:text-[11px] text-muted-foreground/60"
                                >
                                    {{ testimonial.role }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ CTA SECTION ============ -->
        <section
            ref="ctaRef"
            class="relative py-20 sm:py-28 md:py-36"
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
                            <CheckCheck class="size-3 text-primary" />
                            No credit card required
                        </div>

                        <h2
                            class="text-3xl sm:text-4xl md:text-5xl font-black font-display tracking-tight text-foreground"
                        >
                            Ready to master your
                            <span class="text-primary italic">wealth</span>?
                        </h2>

                        <p
                            class="text-sm sm:text-base text-muted-foreground/80 font-medium max-w-md mx-auto"
                        >
                            Join thousands of users who've taken control of
                            their financial future. Start your journey today.
                        </p>

                        <div
                            class="flex flex-wrap items-center justify-center gap-3 sm:gap-4"
                        >
                            <template v-if="!isAuthenticated">
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
                                        <ArrowRight
                                            class="ml-2 size-4 group-hover:translate-x-1 transition-transform"
                                        />
                                    </span>
                                </Link>
                                <Link
                                    :href="login()"
                                    class="inline-flex items-center justify-center rounded-2xl border border-border/60 bg-card/30 px-8 sm:px-10 py-4 text-sm font-bold transition-all hover:bg-card/50 active:scale-[0.97] backdrop-blur-sm"
                                >
                                    Sign In
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    :href="dashboard()"
                                    class="group relative inline-flex items-center justify-center rounded-2xl bg-primary px-8 sm:px-10 py-4 text-sm font-bold text-primary-foreground transition-all hover:shadow-2xl hover:shadow-primary/25 active:scale-[0.97] overflow-hidden"
                                >
                                    <span
                                        class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"
                                    ></span>
                                    <span class="relative flex items-center">
                                        Open Dashboard
                                        <ArrowRight
                                            class="ml-2 size-4 group-hover:translate-x-1 transition-transform"
                                        />
                                    </span>
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FOOTER SECTION ============ -->
        <footer class="footer-section relative border-t border-border/20 py-12 sm:py-16">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8">
                <div
                    class="footer-content grid sm:grid-cols-2 md:grid-cols-4 gap-8 sm:gap-10"
                >
                    <!-- Brand -->
                    <div class="space-y-3 sm:space-y-4 md:col-span-2">
                        <div
                            class="flex items-center gap-2.5"
                        >
                            <div
                                class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-primary flex items-center justify-center"
                            >
                                <ArrowUpRight
                                    class="text-primary-foreground size-4"
                                />
                            </div>
                            <span
                                class="text-base sm:text-lg font-black font-display tracking-tight text-foreground"
                            >
                                Finance Tracker
                            </span>
                        </div>
                        <p
                            class="text-xs sm:text-sm text-muted-foreground/70 max-w-sm leading-relaxed"
                        >
                            A minimalist approach to financial management.
                            Track, analyze, and grow your wealth with
                            clarity and confidence.
                        </p>
                    </div>

                    <!-- Links -->
                    <div class="space-y-3 sm:space-y-4">
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground"
                        >
                            Product
                        </p>
                        <ul class="space-y-2 sm:space-y-3">
                            <li>
                                <a
                                    href="#features"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Features
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#how-it-works"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    How It Works
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#testimonials"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Testimonials
                                </a>
                            </li>
                            <li>
                                <Link
                                    :href="pricing()"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Pricing
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div class="space-y-3 sm:space-y-4">
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.15em] text-muted-foreground"
                        >
                            Legal
                        </p>
                        <ul class="space-y-2 sm:space-y-3">
                            <li>
                                <Link
                                    :href="'/privacy'"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Privacy Policy
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="'/terms'"
                                    class="text-xs sm:text-sm text-muted-foreground/70 hover:text-foreground transition-colors font-medium"
                                >
                                    Terms of Service
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div
                    class="footer-content mt-10 sm:mt-14 pt-6 sm:pt-8 border-t border-border/20 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4"
                >
                    <p
                        class="text-[10px] sm:text-[11px] text-muted-foreground/50 font-medium"
                    >
                        &copy; {{ new Date().getFullYear() }} Finance
                        Tracker. All rights reserved.
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center gap-1.5 text-[10px] text-muted-foreground/40"
                        >
                            <Smartphone class="size-3" />
                            <span>Mobile Ready</span>
                        </div>
                        <div
                            class="flex items-center gap-1.5 text-[10px] text-muted-foreground/40"
                        >
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
/* Ensure smooth transitions for hover effects */
.feature-card,
.testimonial-card {
    transition:
        transform 0.3s ease-out,
        box-shadow 0.3s ease-out,
        border-color 0.3s ease-out,
        background-color 0.3s ease-out;
}

/* Tabular numbers for stat counters */
.tabular-nums {
    font-variant-numeric: tabular-nums;
}

/* Smooth scroll behavior for anchor links (fallback for browsers without JS) */
html {
    scroll-behavior: smooth;
}
</style>
