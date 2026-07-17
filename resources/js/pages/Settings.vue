<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import { LogOut, Settings2, User } from 'lucide-vue-next';
import { onMounted } from 'vue';
import OnboardingTour from '@/components/OnboardingTour.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { useOnboarding } from '@/composables/useOnboarding';
import { usePageLoading } from '@/composables/usePageLoading';
import AppLayout from '@/layouts/AppLayout.vue';
import { settings } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: settings(),
    },
];

const { isLoading } = usePageLoading(400);

const logoutForm = useForm({});

// Onboarding
const page = usePage();
const seenTour = useStorage('seen-tour-settings', false, sessionStorage);
const tour = useOnboarding([
    { title: 'Account Settings', description: 'Manage your account preferences, security, and profile settings in one place.', targetSelector: '.tour-settings-hero' },
    { title: 'Account Section', description: 'Access your profile, security settings like two-factor authentication, and logout when needed.' },
]);

onMounted(() => {
    if (!seenTour.value && !(page.props.auth?.user as any)?.is_onboarded) {
        setTimeout(() => {
            tour.showOnboarding.value = true;
            setTimeout(tour.updateTooltipPosition, 300);
        }, 400);
    }
});

const handleLogout = () => {
    logoutForm.post('/logout');
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template v-if="isLoading">
            <div class="flex h-full flex-1 flex-col gap-8 p-6 sm:p-8 pb-20 md:pb-8 animate-pulse">
                <div class="flex items-center gap-3">
                    <Skeleton class="h-9 w-9 rounded-xl" />
                    <div>
                        <Skeleton class="h-8 w-40 mb-1" />
                        <Skeleton class="h-4 w-56" />
                    </div>
                </div>
                <div class="max-w-lg space-y-4">
                    <div class="p-5 lg:p-6 rounded-[20px] border border-border/40 bg-card">
                        <div class="flex items-center gap-2 mb-4">
                            <Skeleton class="h-4 w-4 rounded" />
                            <Skeleton class="h-4 w-20" />
                        </div>
                        <Skeleton class="h-12 w-full rounded-lg" />
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div :class="['animate-fade-up stagger-1 tour-settings-hero', tour.highlightClass(1)]">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-primary/8">
                        <Settings2 class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Settings</h1>
                        <p class="mt-1 text-sm text-muted-foreground font-sans">Manage your account and preferences</p>
                    </div>
                </div>
            </div>

            <!-- Settings Cards -->
            <div class="max-w-lg space-y-4 animate-fade-up stagger-2">
                <!-- Account Section -->
                <div class="surface-card p-5 lg:p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <h2 class="text-sm font-bold text-foreground font-display tracking-tight">Account</h2>
                    </div>
                    <div class="space-y-4">
                        <!-- Logout Button -->
                        <button
                            @click="handleLogout"
                            class="group w-full flex items-center justify-center gap-2 rounded-lg border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm font-semibold text-destructive transition-all duration-200 hover:bg-destructive/15 hover:border-destructive/60 hover:shadow-md"
                        >
                            <LogOut class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-0.5" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </AppLayout>

    <OnboardingTour
        :show="tour.showOnboarding.value"
        :step="tour.onboardingStep.value"
        :total="tour.totalSteps"
        :current-step="tour.currentStep.value"
        :tooltip-style="tour.tooltipStyle.value"
        :tooltip-side="tour.tooltipSide.value"
        @next="tour.nextStep()"
        @prev="tour.prevStep()"
        @close="tour.completeOnboarding()"
    />
</template>
