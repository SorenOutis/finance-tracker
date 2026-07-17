<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ArrowUpRight, Sun, Moon, Shield, FileText, ArrowLeft } from 'lucide-vue-next';
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
    <Head title="Privacy Policy — Finance Tracker">
        <meta name="description" content="Finance Tracker Privacy Policy. Learn how we collect, use, and protect your personal financial data with our zero-knowledge architecture." />
        <link rel="canonical" :href="absoluteUrl('/privacy', $page.props.appUrl)" />

        <!-- Open Graph -->
        <meta property="og:title" content="Privacy Policy — Finance Tracker" />
        <meta property="og:description" content="Our commitment to your privacy. Zero-knowledge architecture ensures even we cannot see your financial data." />
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="absoluteUrl('/privacy', $page.props.appUrl)" />
        <meta property="og:image" :content="absoluteUrl('/apple-touch-icon.png', $page.props.appUrl)" />
        <meta property="og:site_name" content="Finance Tracker" />
        <meta property="og:locale" content="en_US" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" content="Privacy Policy — Finance Tracker" />
        <meta name="twitter:description" content="Your financial data stays yours. No tracking, no data mining." />
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
                    <Shield class="size-2.5 text-primary" />
                    Privacy First
                </div>
                <h1 class="legal-title text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black font-display tracking-tight text-foreground mt-4 sm:mt-5">
                    Privacy <span class="text-primary italic">Policy</span>
                </h1>
                <p class="legal-meta text-[10px] sm:text-[11px] text-muted-foreground/60 font-medium mt-2">Last updated: {{ lastUpdated }}</p>
            </div>
        </section>

        <!-- ============ CONTENT ============ -->
        <section class="legal-content relative pb-14 sm:pb-16 md:pb-20">
            <div class="w-full max-w-3xl mx-auto px-4 sm:px-8">
                <div class="legal-section space-y-6 sm:space-y-8 text-[11px] sm:text-sm text-muted-foreground/80 leading-relaxed">
                    <div class="rounded-2xl border border-border/20 bg-card/20 backdrop-blur-sm p-4 sm:p-6">
                        <p class="font-medium text-foreground/80">Your privacy matters to us. This policy explains how Finance Tracker ("we," "our," or "us") collects, uses, and protects your information when you use our service.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">1. Information We Collect</h2>
                        <p>We collect only the information necessary to provide you with our financial tracking service:</p>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li><strong class="text-foreground/80">Account Information:</strong> Your email address and encrypted password when you register.</li>
                            <li><strong class="text-foreground/80">Financial Data:</strong> Transaction records, account balances, budgets, and financial goals you enter into the system.</li>
                            <li><strong class="text-foreground/80">Usage Data:</strong> Anonymous analytics about how you interact with the app (page views, feature usage) to help us improve.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">2. How We Use Your Information</h2>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>To provide, maintain, and improve our financial tracking service.</li>
                            <li>To generate reports, charts, and insights based on your financial data.</li>
                            <li>To communicate with you about service updates, security alerts, and support requests.</li>
                            <li>To comply with legal obligations and enforce our Terms of Service.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">3. Zero-Knowledge Architecture</h2>
                        <p>Finance Tracker employs a zero-knowledge architecture. Your financial data is encrypted in transit (TLS 1.3) and at rest (AES-256). In plain terms: <strong class="text-foreground/80">even we cannot see your actual financial figures.</strong> All sensitive computations happen on your device. Our servers store encrypted data that only you can decrypt with your credentials.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">4. Data Sharing &amp; Third Parties</h2>
                        <p>We do not sell, trade, or share your personal financial data with third parties. We may share anonymized, aggregated data that cannot identify you for analytical purposes. We may disclose information if required by law or to protect our rights.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">5. Data Retention</h2>
                        <p>We retain your data for as long as your account is active. If you delete your account, your data is permanently erased from our servers within 30 days. Backup copies may persist temporarily but are securely purged.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">6. Your Rights</h2>
                        <p>You have the right to:</p>
                        <ul class="list-disc pl-5 space-y-1.5">
                            <li>Access, update, or delete your personal data at any time via your account settings.</li>
                            <li>Export your data in CSV format.</li>
                            <li>Withdraw consent for data processing (where applicable).</li>
                            <li>Request a copy of the data we hold about you.</li>
                        </ul>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">7. Security</h2>
                        <p>We implement industry-standard security measures including encryption, regular security audits, and secure data centers. However, no method of electronic storage is 100% secure. We strive to protect your data while you also play a role — use a strong, unique password and enable two-factor authentication.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">8. Cookies</h2>
                        <p>We use essential cookies for authentication and session management. We do not use tracking cookies or third-party marketing cookies. You can control cookie settings through your browser preferences.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">9. Changes to This Policy</h2>
                        <p>We may update this policy from time to time. We will notify you of material changes via email or through the app. Continued use of the service after changes constitutes acceptance of the updated policy.</p>
                    </div>

                    <div class="legal-section space-y-4">
                        <h2 class="text-sm sm:text-base font-bold font-display text-foreground">10. Contact</h2>
                        <p>If you have questions about this Privacy Policy, please contact us at <a href="mailto:privacy@financetracker.app" class="text-primary hover:text-primary/80 transition-colors font-medium">privacy@financetracker.app</a>.</p>
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
