<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { settings } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { LogOut } from 'lucide-vue-next';

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
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight tracking-tight">Settings</h1>
                <p class="mt-2 text-sm text-muted-foreground font-sans">Manage your account and preferences</p>
            </div>

            <!-- Settings Card -->
            <div class="surface-card p-5 lg:p-6 max-w-md">
                <div class="space-y-4">
                    <!-- Logout Button -->
                    <div>
                        <button
                            @click="handleLogout"
                            class="w-full flex items-center justify-center gap-2 rounded-lg border border-destructive/50 bg-destructive/10 px-4 py-3 text-sm font-semibold text-destructive transition-all duration-200 hover:bg-destructive/20 hover:border-destructive"
                        >
                            <LogOut class="h-4 w-4" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
