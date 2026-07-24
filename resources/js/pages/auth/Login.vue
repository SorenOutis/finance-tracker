<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login, register } from '@/routes';
import { login as googleLogin } from '@/routes/google';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const showPassword = ref(false);
</script>

<template>
    <AuthLayout
        title="Welcome back"
        description="Sign in securely to continue to your finance workspace."
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-5 rounded-lg bg-primary/10 p-3 text-sm text-primary"
        >
            {{ status }}
        </div>

        <a
            :href="googleLogin().url"
            class="flex h-11 w-full items-center justify-center rounded-lg border bg-background font-medium transition hover:bg-muted"
        >
            Continue with Google
        </a>

        <div class="my-6 flex items-center gap-3 text-xs text-muted-foreground">
            <span class="h-px flex-1 bg-border" />
            or use your password
            <span class="h-px flex-1 bg-border" />
        </div>

        <Form
            v-bind="login.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="grid gap-5"
        >
            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <TextLink v-if="canResetPassword" :href="request()"
                        >Forgot password?</TextLink
                    >
                </div>
                <div class="relative">
                    <Input
                        id="password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        class="pr-11"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 px-3 text-muted-foreground"
                        :aria-label="
                            showPassword ? 'Hide password' : 'Show password'
                        "
                        @click="showPassword = !showPassword"
                    >
                        <EyeOff v-if="showPassword" class="size-4" />
                        <Eye v-else class="size-4" />
                    </button>
                </div>
                <InputError :message="errors.password" />
                <InputError :message="errors.google" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input
                    name="remember"
                    type="checkbox"
                    value="1"
                    class="size-4 rounded border-border"
                />
                Keep me signed in
            </label>

            <Button type="submit" class="h-11" :disabled="processing">
                <LockKeyhole class="size-4" />
                {{ processing ? 'Signing in…' : 'Sign in' }}
            </Button>
        </Form>

        <p
            v-if="canRegister"
            class="mt-6 text-center text-sm text-muted-foreground"
        >
            New here? <TextLink :href="register()">Create an account</TextLink>
        </p>
    </AuthLayout>
</template>
