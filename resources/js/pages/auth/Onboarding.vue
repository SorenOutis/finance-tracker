<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/onboarding';

defineProps<{ currencies: Array<{ value: string; label: string }> }>();
</script>

<template>
    <AuthLayout
        title="Set up your first wallet"
        description="Your account is secure. Add the wallet you want to start tracking."
    >
        <Head title="Set up your workspace" />
        <Form
            v-bind="store.form()"
            v-slot="{ errors, processing }"
            class="grid gap-5"
        >
            <div class="grid gap-2">
                <Label for="account_name">Wallet name</Label>
                <Input
                    id="account_name"
                    name="account_name"
                    value="Main Wallet"
                    required
                    autofocus
                />
                <InputError :message="errors.account_name" />
            </div>
            <div class="grid gap-2">
                <Label for="currency">Currency</Label>
                <select
                    id="currency"
                    name="currency"
                    class="h-10 rounded-md border bg-background px-3 text-sm"
                    required
                >
                    <option
                        v-for="currency in currencies"
                        :key="currency.value"
                        :value="currency.value"
                    >
                        {{ currency.label }}
                    </option>
                </select>
                <InputError :message="errors.currency" />
            </div>
            <div class="grid gap-2">
                <Label for="initial_balance">Opening balance</Label>
                <Input
                    id="initial_balance"
                    name="initial_balance"
                    type="number"
                    value="0"
                    min="0"
                    step="0.01"
                    required
                />
                <InputError :message="errors.initial_balance" />
            </div>
            <Button type="submit" class="h-11" :disabled="processing">{{
                processing ? 'Creating wallet…' : 'Finish setup'
            }}</Button>
        </Form>
    </AuthLayout>
</template>
