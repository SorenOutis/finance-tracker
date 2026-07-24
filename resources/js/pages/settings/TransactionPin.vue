<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { destroy, edit, update } from '@/routes/transaction-pin';
import type { BreadcrumbItem } from '@/types';

defineProps<{ hasPin: boolean }>();
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Transaction PIN', href: edit() },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Transaction PIN" />
        <SettingsLayout>
            <div class="surface-card mx-auto max-w-xl p-6">
                <h2 class="text-xl font-semibold">
                    {{
                        hasPin
                            ? 'Change transaction PIN'
                            : 'Create transaction PIN'
                    }}
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    This six-digit PIN is separate from your account password
                    and cannot be used to sign in.
                </p>
                <Form
                    v-bind="update.form()"
                    reset-on-success
                    v-slot="{ errors, processing, recentlySuccessful }"
                    class="mt-6 grid gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">Account password</Label>
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
                        <Label for="pin">Six-digit PIN</Label>
                        <Input
                            id="pin"
                            name="pin"
                            type="password"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required
                            autocomplete="off"
                        />
                        <InputError :message="errors.pin" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="pin_confirmation">Confirm PIN</Label>
                        <Input
                            id="pin_confirmation"
                            name="pin_confirmation"
                            type="password"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required
                            autocomplete="off"
                        />
                    </div>
                    <Button type="submit" :disabled="processing">{{
                        processing ? 'Saving…' : 'Save transaction PIN'
                    }}</Button>
                    <p v-if="recentlySuccessful" class="text-sm text-green-600">
                        Transaction PIN saved.
                    </p>
                </Form>

                <Form
                    v-if="hasPin"
                    v-bind="destroy.form()"
                    v-slot="{ errors, processing }"
                    class="mt-8 border-t pt-6"
                >
                    <Label for="remove_current_password"
                        >Confirm password to remove PIN</Label
                    >
                    <Input
                        id="remove_current_password"
                        name="current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="mt-2"
                    />
                    <InputError
                        :message="errors.current_password"
                        class="mt-2"
                    />
                    <Button
                        type="submit"
                        variant="destructive"
                        class="mt-4"
                        :disabled="processing"
                        >Remove transaction PIN</Button
                    >
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
