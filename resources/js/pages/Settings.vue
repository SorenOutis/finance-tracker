<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { settings } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { LogOut, Settings2, Shield, User } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: settings(),
    },
];

const logoutForm = useForm({});

const handleLogout = () => {
    logoutForm.post('/logout');
};
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 overflow-x-auto p-6 sm:p-8 pb-20 md:pb-8">
            <!-- Header -->
            <div class="animate-fade-up stagger-1">
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
    </AppLayout>
</template>
