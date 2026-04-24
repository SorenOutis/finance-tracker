<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import LoadingScreen from '@/components/LoadingScreen.vue';
import { ref } from 'vue';
import { Lock, Mail, ArrowRight, Sparkles } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const pin = ref(['', '', '', '', '', '']);

const updatePin = (index: number, val: string) => {
    if (val.length > 1) val = val[0];
    if (!/^\d*$/.test(val)) return;
    
    pin.value[index] = val;
    
    if (val && index < 5) {
        const nextInput = document.getElementById(`pin-${index + 1}`);
        nextInput?.focus();
    }
    
    form.password = pin.value.join('');
};

const handleBackspace = (index: number) => {
    if (!pin.value[index] && index > 0) {
        const prevInput = document.getElementById(`pin-${index - 1}`);
        prevInput?.focus();
    }
};

const submit = () => {
    form.post('/login', {
        onStart: () => {
            sessionStorage.setItem('has-seen-loading', 'false');
        },
        onError: () => {
            sessionStorage.setItem('has-seen-loading', 'true');
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-background flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-hidden text-center">
        <!-- Background Orbs -->
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

        <Head title="Log in" />

        <div class="w-full max-w-[400px] z-10">
            <div class="w-20 h-20 bg-primary/10 rounded-[24px] flex items-center justify-center mb-8 mx-auto animate-bounce-slow">
                <Sparkles class="w-10 h-10 text-primary" />
            </div>
            
            <h1 class="text-4xl font-black tracking-tight mb-2">Welcome Back</h1>
            <p class="text-muted-foreground mb-12">Enter your email and PIN to access your dashboard.</p>

            <div v-if="status" class="mb-8 p-4 rounded-xl bg-green-500/10 text-green-600 text-sm font-bold">
                {{ status }}
            </div>

            <div class="grid gap-8 text-left">
                <div class="grid gap-2">
                    <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Email Address</Label>
                    <div class="relative">
                        <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <input v-model="form.email" type="email" required autofocus
                            class="w-full h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none" 
                            placeholder="email@example.com" />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1 block mb-3">Security PIN</Label>
                    <div class="flex justify-between gap-2">
                        <input v-for="(v, i) in pin" :key="i" :id="`pin-${i}`" 
                            v-model="pin[i]" type="password" inputmode="numeric" maxlength="1"
                            @input="e => updatePin(i, (e.target as HTMLInputElement).value)"
                            @keydown.backspace="handleBackspace(i)"
                            class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                    </div>
                    <InputError :message="form.errors.password" class="mt-2 text-center" />
                </div>

                <div class="flex flex-col gap-4 mt-4">
                    <Button @click="submit" :disabled="form.password.length !== 6 || !form.email || form.processing" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Log In
                        <ArrowRight v-if="!form.processing" class="ml-2 w-5 h-5" />
                    </Button>
                </div>
            </div>

            <div class="mt-12 text-sm text-muted-foreground">
                Don't have an account? 
                <TextLink :href="register()" class="text-primary font-bold hover:underline">Sign up</TextLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-bounce-slow {
    animation: bounceSlow 3s infinite ease-in-out;
}

@keyframes bounceSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
</style>
