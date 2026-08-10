<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Password', href: edit() }];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />
        <SettingsLayout>
            <div class="surface-card mx-auto max-w-xl p-6">
                <h2 class="text-xl font-semibold">Change password</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Use at least 12 characters with mixed case, a number, and a
                    symbol.
                </p>
                <Form
                    v-bind="update.form()"
                    reset-on-success
                    v-slot="{ errors, processing, recentlySuccessful }"
                    class="mt-6 grid gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError :message="errors.current_password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError :message="errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm new password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <Button type="submit" :disabled="processing">{{
                        processing ? 'Updating…' : 'Update password'
                    }}</Button>
                    <p v-if="recentlySuccessful" class="text-sm text-green-600">
                        Password updated.
                    </p>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
