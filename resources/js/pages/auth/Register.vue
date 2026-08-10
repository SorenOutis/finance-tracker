<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login, privacy, register, terms } from '@/routes';
import { register as googleRegister } from '@/routes/google';

const showPassword = ref(false);
const showConfirmation = ref(false);
</script>

<template>
    <AuthLayout
        title="Create your account"
        description="Start with secure access. You’ll set up your wallet after verifying your email."
    >
        <Head title="Register" />

        <Form
            v-bind="googleRegister.form()"
            v-slot="{ errors, processing }"
            class="mb-6 grid gap-4"
        >
            <label class="flex items-start gap-2 text-sm text-muted-foreground">
                <input
                    name="terms"
                    type="checkbox"
                    value="1"
                    required
                    class="mt-0.5 size-4 rounded border-border"
                />
                <span
                    >I agree to the
                    <TextLink :href="terms()">Terms</TextLink> and
                    <TextLink :href="privacy()">Privacy Policy</TextLink>.</span
                >
            </label>
            <InputError :message="errors.terms || errors.google" />
            <Button
                type="submit"
                variant="outline"
                class="h-11"
                :disabled="processing"
                >Register with Google</Button
            >
        </Form>

        <div class="mb-6 flex items-center gap-3 text-xs text-muted-foreground">
            <span class="h-px flex-1 bg-border" />or register with email<span
                class="h-px flex-1 bg-border"
            />
        </div>

        <Form
            v-bind="register.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="grid gap-4"
        >
            <div class="grid gap-2">
                <Label for="name">Full name</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autocomplete="email"
                />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <div class="relative">
                    <Input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        class="pr-11"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 px-3 text-muted-foreground"
                        @click="showPassword = !showPassword"
                        :aria-label="
                            showPassword ? 'Hide password' : 'Show password'
                        "
                    >
                        <EyeOff v-if="showPassword" class="size-4" /><Eye
                            v-else
                            class="size-4"
                        />
                    </button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Use 12+ characters with upper and lowercase letters, a
                    number, and a symbol.
                </p>
                <InputError :message="errors.password" />
            </div>
            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <div class="relative">
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="showConfirmation ? 'text' : 'password'"
                        required
                        autocomplete="new-password"
                        class="pr-11"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 px-3 text-muted-foreground"
                        @click="showConfirmation = !showConfirmation"
                        :aria-label="
                            showConfirmation ? 'Hide password' : 'Show password'
                        "
                    >
                        <EyeOff v-if="showConfirmation" class="size-4" /><Eye
                            v-else
                            class="size-4"
                        />
                    </button>
                </div>
            </div>
            <label class="flex items-start gap-2 text-sm text-muted-foreground">
                <input
                    name="terms"
                    type="checkbox"
                    value="1"
                    required
                    class="mt-0.5 size-4 rounded border-border"
                />
                <span
                    >I agree to the
                    <TextLink :href="terms()">Terms</TextLink> and
                    <TextLink :href="privacy()">Privacy Policy</TextLink>.</span
                >
            </label>
            <InputError :message="errors.terms" />
            <Button type="submit" class="h-11" :disabled="processing">
                <ShieldCheck class="size-4" />{{
                    processing ? 'Creating account…' : 'Create account'
                }}
            </Button>
        </Form>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Already registered? <TextLink :href="login()">Sign in</TextLink>
        </p>
    </AuthLayout>
</template>
