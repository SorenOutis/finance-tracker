<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Mail, Sparkles, Smartphone } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm({
    email: '',
    password: '', // This will store the 6-digit PIN
    remember: true,
});

const page = usePage();
const rememberedEmail = ref(localStorage.getItem('remembered-email') || (page.props.defaultEmail as string) || '');
const isEmailHidden = ref(!!rememberedEmail.value);
const pinDigits = ref(['', '', '', '', '', '']);

onMounted(() => {
    if (rememberedEmail.value) {
        form.email = rememberedEmail.value;
        // Focus first PIN input
        setTimeout(() => {
            document.getElementById('pin-0')?.focus();
        }, 100);
    }
});

const switchAccount = () => {
    isEmailHidden.value = false;
    rememberedEmail.value = '';
    form.email = '';
    localStorage.removeItem('remembered-email');
};

const updatePin = (index: number, val: string) => {
    if (val.length > 1) val = val[0];
    if (!/^\d*$/.test(val)) return;
    
    pinDigits.value[index] = val;
    
    if (val && index < 5) {
        const nextInput = document.getElementById(`pin-${index + 1}`);
        nextInput?.focus();
    }

    if (pinDigits.value.every(d => d !== '')) {
        form.password = pinDigits.value.join('');
        submit();
    }
};

const handleBackspace = (index: number) => {
    if (!pinDigits.value[index] && index > 0) {
        const prevInput = document.getElementById(`pin-${index - 1}`);
        prevInput?.focus();
    }
};

const submit = () => {
    form.post('/login', {
        onSuccess: () => {
            localStorage.setItem('remembered-email', form.email);
        },
        onStart: () => {
            sessionStorage.setItem('has-seen-loading', 'false');
        },
        onError: () => {
            sessionStorage.setItem('has-seen-loading', 'true');
            pinDigits.value = ['', '', '', '', '', ''];
            document.getElementById('pin-0')?.focus();
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
            <div :class="isEmailHidden ? 'mb-4 w-16 h-16 rounded-[20px]' : 'mb-8 w-20 h-20 rounded-[24px]'" class="bg-primary/10 flex items-center justify-center mx-auto transition-all duration-500">
                <Sparkles :class="isEmailHidden ? 'w-8 h-8' : 'w-10 h-10'" class="text-primary transition-all duration-500" />
            </div>
            
            <h1 :class="isEmailHidden ? 'text-3xl' : 'text-4xl'" class="font-black tracking-tight mb-2 transition-all duration-500">Welcome Back</h1>
            <p class="text-muted-foreground transition-all duration-500 mb-8 text-sm px-4">
                {{ isEmailHidden ? 'Enter your 6-digit security PIN to continue.' : 'Enter your email and 6-digit PIN to access your account.' }}
            </p>

            <div v-if="status" class="mb-8 p-4 rounded-xl bg-green-500/10 text-green-600 text-sm font-bold text-left">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="grid gap-8 text-left">
                <!-- Email Section -->
                <div v-if="!isEmailHidden" class="grid gap-2 animate-fade-up">
                    <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Email Address</Label>
                    <div class="relative">
                        <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="form.email" type="email" required autofocus
                            class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all" 
                            placeholder="email@example.com" />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div v-else class="flex flex-col items-center gap-2 py-2 animate-fade-up">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20">
                        <Mail class="w-3 h-3 text-primary" />
                        <span class="text-sm font-bold text-foreground">{{ rememberedEmail }}</span>
                    </div>
                    <button @click="switchAccount" type="button" class="text-[10px] font-bold text-muted-foreground hover:text-primary transition-colors uppercase tracking-widest">
                        Switch Account
                    </button>
                </div>

                <!-- PIN Section -->
                <div class="animate-fade-up">
                    <div class="flex items-center justify-between mb-3">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Security PIN</Label>
                        <TextLink v-if="canResetPassword" href="/forgot-password" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-wider">
                            Forgot PIN?
                        </TextLink>
                    </div>
                    <div class="flex justify-between gap-2">
                        <input v-for="(v, i) in pinDigits" :key="i" :id="`pin-${i}`" 
                            v-model="pinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                            @input="e => updatePin(i, (e.target as HTMLInputElement).value)"
                            @keydown.backspace="handleBackspace(i)"
                            class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                    </div>
                    <InputError :message="form.errors.password" class="mt-4 text-center" />
                    <InputError :message="form.errors.email" class="mt-4 text-center" />
                </div>
            </form>

            <div v-if="form.processing" class="flex flex-col items-center gap-3 mt-8 animate-in fade-in zoom-in duration-500">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full border-4 border-primary/20 border-t-primary animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <Smartphone class="w-4 h-4 text-primary animate-pulse" />
                    </div>
                </div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary animate-pulse">Verifying PIN</p>
            </div>

            <div class="mt-12 text-sm text-muted-foreground transition-all duration-500">
                Don't have an account? 
                <TextLink :href="register()" class="text-primary font-bold hover:underline">Sign up</TextLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-up {
    animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
