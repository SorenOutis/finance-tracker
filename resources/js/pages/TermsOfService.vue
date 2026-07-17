<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ArrowUpRight, Sun, Moon, FileText, ArrowLeft } from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAppearance } from '@/composables/useAppearance';
import { useSmoothScroll } from '@/composables/useSmoothScroll';
import { home } from '@/routes';
import { absoluteUrl } from '@/utils/url';

const { appearance, updateAppearance } = useAppearance();
useSmoothScroll();

const appUrl = computed(() => (usePage().props.appUrl as string) || '');
const siteUrl = computed(() => absoluteUrl('/', appUrl.value));

const ldWebsite = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'WebSite',
        name: 'Finance Tracker',
        url: siteUrl.value,
    }),
);

const isAuthenticated = ref(false);
const mobileMenuOpen = ref(false);

const toggleTheme = (event: MouseEvent) => {
    const newTheme = appearance.value === 'dark' ? 'light' : 'dark';
    if (!document.startViewTransition) {
        updateAppearance(newTheme);
        return;
    }
    const x = event.clientX;
    const y = event.clientY;
    const endRadius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    const transition = document.startViewTransition(() => updateAppearance(newTheme));
    transition.ready.then(() => {
        const clipPath = [
            `circle(0px at ${x}px ${y}px)`,
            `circle(${endRadius}px at ${x}px ${y}px)`,
        ];
        document.documentElement.animate(
            { clipPath: newTheme === 'dark' ? [...clipPath].reverse() : clipPath },
            { duration: 500, easing: 'ease-in-out', pseudoElement: newTheme === 'dark' ? '::view-transition-old(root)' : '::view-transition-new(root)' },
        );
    });
};

const lastUpdated = 'April 15, 2026';

onMounted(() => {
    isAuthenticated.value = !!(window as any).__inertia_page_props?.auth?.user;

    gsap.registerPlugin(ScrollTrigger);

    gsap.from('.legal-header', { y: -20, opacity: 0, duration: 0.6, ease: 'power3.out' });

    const heroTl = gsap.timeline();
    heroTl
        .from('.legal-badge', { y: 16, opacity: 0, scale: 0.9, duration: 0.5, ease: 'back.out(1.7)' })
        .from('.legal-title', { y: 20, opacity: 0, duration: 0.5, ease: 'power3.out' }, '-=0.2')
        .from('.legal-meta', { y: 12, opacity: 0, duration: 0.4, ease: 'power3.out' }, '-=0.3');

    gsap.from('.legal-section', {
        y: 20, opacity: 0, duration: 0.5, stagger: 0.06, ease: 'power3.out',
        scrollTrigger: { trigger: '.legal-content', start: 'top 85%', toggleActions: 'play none none reverse' },
    });
});
</script>

<template>
    <Head title="Terms of Service — Finance Tracker">
        <meta name="description" content="Finance Tracker Terms of Service. Understand the terms governing your use of our financial tracking platform." />
        <link rel="canonical" :href="absoluteUrl('/terms', $page.props.appUrl)" />

        <!-- Open Graph -->
        <meta property="og:title" content="Terms of Service — Finance Tracker" />
        <meta property="og:description" content="The terms governing your use of Finance Tracker's financial management platform." />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="absoluteUrl('/terms', $page.props.appUrl)" />
        <meta property="og:image" :content="absoluteUrl('/apple-touch-icon.png', $page.props.appUrl)" />
        <meta property="og:site_name" content="Finance Tracker" />
        <meta property="og:locale" content="en_US" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="Terms of Service — Finance Tracker" />
        <meta name="twitter:description" content="Read the terms governing your use of Finance Tracker." />
        <meta name="twitter:image" :content="absoluteUrl('/apple-touch-icon.png', $page.props.appUrl)" />

        <!-- JSON-LD -->
        <script type="application/ld+json" v-html="ldWebsite"></script>
    </Head>

    <div class="min-h-screen flex flex-col bg-background overflow-x-hidden">
        <!-- Background glows -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div class="absolute -top-[15%] -left-[10%] w-[50%] h-[50%] bg-primary/6 rounded-full blur-[150px]"></div>
            <div class="absolute top-[40%] -right-[5%] w-[35%] h-[35%] bg-blue-500/5 rounded-full blur-[130px]"></div>
        </div>

        <!-- ============ HEADER ============ -->
        <header class="legal-header fixed top-0 left-0 right-0 z-50 border-b border-border/10 bg-background/70 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60">
            <div class="flex items-center justify-between w-full max-w-7xl mx-auto px-4 sm:px-8 h-14 sm:h-16">
                <Link :href="home()" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-shadow">
                        <ArrowUpRight class="text-primary-foreground size-4" />
                    </div>
                    <span class="text-base font-black font-display tracking-tight text-foreground">Finance Tracker</span>
                </Link>
                <nav class="hidden md:flex items-center gap-1.5">
                    <Link :href="home()" class="px-3 py-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors rounded-lg hover:bg-card/50">Home</Link>
                    <div class="w-px h-5 bg-border/40 mx-1.5"></div>
                    <template v-if="isAuthenticated">
                        <Link :href="home()" class="rounded-full bg-primary px-4 py-1.5 text-xs font-bold text-primary-foreground transition-all hover:shadow-lg hover:shadow-primary/20 active:scale-[0.98]">Dashboard</Link>
                    </template>
                    <template v-else>
                        <Link :href="home()" class="px-3 py-1.5 text-xs font-bold text-muted-foreground transition-all hover:text-foreground">Get Started</Link>
                    </template>
                    <button @click="toggleTheme" class="ml-1 inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`">
                        <Sun v-if="appearance === 'dark'" class="h-3.5 w-3.5" />
                        <Moon v-else class="h-3.5 w-3.5" />
                    </button>
                </nav>
                <div class="flex md:hidden items-center gap-1.5">
                    <button @click="toggleTheme" class="inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" :aria-label="`Switch to ${appearance === 'dark' ? 'light' : 'dark'} mode`">
                        <Sun v-if="appearance === 'dark'" class="h-3.5 w-3.5" />
                        <Moon v-else class="h-3.5 w-3.5" />
                    </button>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center rounded-full p-1.5 text-xs transition-colors hover:bg-card/50 text-foreground border border-border/30 bg-card/20" aria-label="Toggle menu">
                        <svg v-if="!mobileMenuOpen" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg v-else class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-4">
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-border/10 bg-background/95 backdrop-blur-xl">
                    <div class="px-4 py-3 space-y-1">
                        <Link :href="home()" @click="mobileMenuOpen = false" class="block px-4 py-2 text-sm font-semibold text-foreground hover:bg-card/50 rounded-xl transition-colors">Home</Link>
                        <Link :href="home()" @click="mobileMenuOpen = false" class="block w-full text-center px-5 py-2.5 text-sm font-bold text-primary-foreground bg-primary rounded-xl hover:shadow-lg hover:shadow-primary/20 transition-all">Get Started</Link>
                    </div>
                </div>
            </Transition>
        </header>

        <div class="h-14 sm:h-16"></div>

        <!-- ============ HERO ============ -->
        <section class="relative pt-10 sm:pt-12 md:pt-14 pb-6 sm:pb-8">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-8 text-center">
                <div class="legal-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-border/30 bg-card/40 text-muted-foreground text-[9px] font-bold uppercase tracking-[0.1em] backdrop-blur-md">
                    <FileText class="size-2.5 text-primary" />
                    Terms of Service
                </div>
                <h1 class="legal-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black font-display tracking-tight text-foreground mt-4 sm:mt-5">
                    Terms of <span class="text-primary italic">Service</span>
                </h1>
                <p class="legal-meta text-[10px] sm:text-[11px] text-muted-foreground/60 font-medium mt-2">Last updated: {{ lastUpdated }}</p>
            </div>
        </section>

        <!-- ============ CONTENT ============ -->
        <section class="legal-content relative pb-14 sm:pb-16 md:pb-20">
            <div class="w-full max-w-3xl mx-auto px-4 sm:px-8">
                <div class="legal-section space-y-6 sm:space-y-8 text-[11px] sm:text-sm text-muted-foreground/80 leading-relaxed">
                    <div class="rounded-2xl border border-border/20 bg-card/20 backdrop-blur-sm p-4 sm:p-6">
                        <p class="font-medium text-foreground/80">By using Finance Tracker, you agree to these terms. Please read them carefully. If you do not agree, please do not use the service.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">1. Acceptance of Terms</h2>
                        <p>By accessing or using Finance Tracker ("the Service"), you agree to be bound by these Terms of Service. If you are using the Service on behalf of an organization, you represent that you have the authority to bind that organization.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">2. Description of Service</h2>
                        <p>Finance Tracker provides a personal financial management platform that allows users to track income, expenses, budgets, financial goals, and generate analytics and reports. The Service is available in three tiers: Free, Pro, and Enterprise.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">3. User Accounts</h2>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>You must create an account to use the Service.</li>
                            <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
                            <li>You must provide accurate and complete registration information.</li>
                            <li>You may not share your account with others or use another person's account.</li>
                            <li>You must be at least 13 years of age to use the Service.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">4. Subscriptions &amp; Billing</h2>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>Free Tier: Available at no cost with limited features.</li>
                            <li>Pro Tier: $2/month or $10/year. Includes a 14-day free trial.</li>
                            <li>Enterprise Tier: $5/month or $20/year. Custom pricing available for teams.</li>
                            <li>All payments are non-refundable except as required by applicable law.</li>
                            <li>We may change pricing with 30 days notice. Continued use after changes constitutes acceptance.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">5. Acceptable Use</h2>
                        <p>You agree not to:</p>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>Use the Service for any unlawful purpose or in violation of any applicable laws.</li>
                            <li>Attempt to gain unauthorized access to the Service or its systems.</li>
                            <li>Interfere with or disrupt the integrity or performance of the Service.</li>
                            <li>Upload or transmit viruses, malware, or any malicious code.</li>
                            <li>Use the Service to store or transmit infringing, obscene, or otherwise unlawful material.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">6. Data Ownership</h2>
                        <p>You retain full ownership of all financial data you enter into the Service. We claim no intellectual property rights over your data. We will not use your data for any purpose other than providing the Service to you, except as outlined in our Privacy Policy.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">7. Service Availability</h2>
                        <p>We strive for 99.9% uptime but do not guarantee uninterrupted access. The Service may be temporarily unavailable for maintenance, updates, or due to circumstances beyond our control. We will make reasonable efforts to notify you of scheduled downtime.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">8. Limitation of Liability</h2>
                        <p>Finance Tracker is provided "as is" without warranties of any kind. We shall not be liable for any indirect, incidental, special, consequential, or punitive damages arising from your use of the Service. Our total liability is limited to the amount you have paid us in the 12 months preceding the claim.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">9. Termination</h2>
                        <p>You may terminate your account at any time from your account settings. We may suspend or terminate your account if you violate these terms. Upon termination, your data will be permanently deleted within 30 days. Pro-rated refunds are not provided for voluntary termination.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">10. Changes to Terms</h2>
                        <p>We may modify these terms at any time. We will notify you of material changes via email or in-app notification. Continued use of the Service after changes take effect constitutes your acceptance of the new terms.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">11. Governing Law</h2>
                        <p>These terms are governed by the laws of the Republic of the Philippines. Any disputes shall be resolved in the courts of Manila, Philippines.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">12. Contact</h2>
                        <p>For questions about these terms, please contact us at <a href="mailto:legal@financetracker.app" class="text-primary hover:text-primary/80 transition-colors font-medium">legal@financetracker.app</a>.</p>
                    </div>

                    <!-- Back link -->
                    <div class="legal-section pt-4">
                        <Link :href="home()" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-primary hover:text-primary/80 transition-colors">
                            <ArrowLeft class="size-3.5" />
                            Back to Home
                        </Link>
                    </div>
                </div>

                <!-- Footer note -->
                <div class="legal-section mt-8 sm:mt-10 pt-4 sm:pt-6 border-t border-border/20">
                    <p class="text-[9px] sm:text-[10px] text-muted-foreground/50 text-center">
                        &copy; {{ new Date().getFullYear() }} Finance Tracker. All rights reserved.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}

.legal-section {
    transition: opacity 0.3s ease-out, transform 0.3s ease-out;
}

details > summary {
    list-style: none;
}
details > summary::-webkit-details-marker {
    display: none;
}
</style>
