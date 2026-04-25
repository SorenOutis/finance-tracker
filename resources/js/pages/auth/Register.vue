<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { ref, computed, watch } from 'vue';
import { 
    ArrowRight, 
    ArrowLeft, 
    Check, 
    User, 
    Mail, 
    Lock, 
    Wallet, 
    Coins, 
    Globe,
    Smartphone
} from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const step = ref(1);
const totalSteps = 3;
const pinSetupStep = ref<'create' | 'confirm'>('create');
const pinError = ref('');

const form = useForm({
    name: '',
    email: '',
    password: '', // This will store the 6-digit PIN
    password_confirmation: '',
    account_name: 'Main Wallet',
    initial_balance: '0',
    currency: 'PHP',
});

const pinDigits = ref(['', '', '', '', '', '']);
const confirmPinDigits = ref(['', '', '', '', '', '']);

const nextStep = () => {
    if (step.value === 1) {
        if (!form.name || !form.email) return;
        step.value = 2;
    } else if (step.value === 2) {
        if (pinSetupStep.value === 'create') {
            if (pinDigits.value.join('').length === 6) {
                pinSetupStep.value = 'confirm';
                // Focus first digit of confirm pin
                setTimeout(() => document.getElementById('confirm-pin-0')?.focus(), 100);
            }
        } else {
            if (isStep2Valid.value) {
                form.password = pinDigits.value.join('');
                form.password_confirmation = confirmPinDigits.value.join('');
                step.value = 3;
            }
        }
    }
};

const prevStep = () => {
    if (step.value === 2 && pinSetupStep.value === 'confirm') {
        pinSetupStep.value = 'create';
        confirmPinDigits.value = ['', '', '', '', '', ''];
        setTimeout(() => document.getElementById('pin-0')?.focus(), 100);
    } else if (step.value > 1) {
        step.value--;
    }
};

const updatePin = (index: number, val: string, isConfirm: boolean = false) => {
    const target = isConfirm ? confirmPinDigits : pinDigits;
    if (val.length > 1) val = val[0];
    if (!/^\d*$/.test(val)) return;
    
    target.value[index] = val;
    
    if (val && index < 5) {
        const nextInput = document.getElementById(`${isConfirm ? 'confirm-' : ''}pin-${index + 1}`);
        nextInput?.focus();
    }

    // Auto-advance if 6 digits are entered
    if (target.value.every(d => d !== '')) {
        if (!isConfirm) {
            setTimeout(nextStep, 300);
        } else {
            if (pinDigits.value.join('') !== confirmPinDigits.value.join('')) {
                pinError.value = "PINs don't match. Try again.";
                confirmPinDigits.value = ['', '', '', '', '', ''];
                setTimeout(() => document.getElementById('confirm-pin-0')?.focus(), 100);
            } else {
                pinError.value = '';
                setTimeout(nextStep, 300);
            }
        }
    }
};

const handleBackspace = (index: number, isConfirm: boolean = false) => {
    const target = isConfirm ? confirmPinDigits : pinDigits;
    if (!target.value[index] && index > 0) {
        const prevInput = document.getElementById(`${isConfirm ? 'confirm-' : ''}pin-${index - 1}`);
        prevInput?.focus();
    }
};

const submit = () => {
    form.post('/register', {
        onStart: () => {
            sessionStorage.setItem('has-seen-loading', 'false');
        },
        onSuccess: () => {
            localStorage.setItem('remembered-email', form.email);
        },
        onError: () => {
            sessionStorage.setItem('has-seen-loading', 'true');
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const currencies = [
    { label: 'Philippine Peso (₱)', value: 'PHP' },
    { label: 'US Dollar ($)', value: 'USD' },
    { label: 'Euro (€)', value: 'EUR' },
    { label: 'Japanese Yen (¥)', value: 'JPY' },
    { label: 'British Pound (£)', value: 'GBP' },
];

const isStep2Valid = computed(() => {
    const pin = pinDigits.value.join('');
    const confirmPin = confirmPinDigits.value.join('');
    return pin.length === 6 && confirmPin.length === 6 && pin === confirmPin;
});

// Watch for changes in pinDigits to clear errors
watch([pinDigits, confirmPinDigits], () => {
    if (pinError.value) pinError.value = '';
}, { deep: true });
</script>

<template>
    <div class="min-h-screen bg-background flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-hidden">
        <!-- Background Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

        <Head title="Register" />

        <div class="w-full max-w-[400px] z-10">
            <!-- Progress Dots -->
            <div class="flex justify-center gap-2 mb-12">
                <div v-for="i in totalSteps" :key="i" 
                    :class="['h-1.5 rounded-full transition-all duration-500', 
                        step === i ? 'w-8 bg-primary' : 'w-2 bg-muted']">
                </div>
            </div>

            <!-- Step 1: Basic Information -->
            <div v-if="step === 1" class="animate-fade-up">
                <h1 class="text-4xl font-black tracking-tight mb-2">Create Account</h1>
                <p class="text-muted-foreground mb-8">Tell us who you are to get started.</p>
                
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Full Name</Label>
                        <div class="relative">
                            <User class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="form.name" class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all" placeholder="Enter your name" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Email Address</Label>
                        <div class="relative">
                            <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="form.email" type="email" class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all" placeholder="email@example.com" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 mt-12">
                    <Button @click="nextStep" :disabled="!form.name || !form.email" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-primary/20">
                        Continue
                        <ArrowRight class="ml-2 w-5 h-5" />
                    </Button>
                    <p class="text-center text-sm text-muted-foreground">
                        Already have an account? 
                        <TextLink :href="login()" class="text-primary font-bold hover:underline">Log in</TextLink>
                    </p>
                </div>
            </div>

            <!-- Step 2: PIN Setup (Sequential) -->
            <div v-if="step === 2" class="animate-fade-up">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-6 mx-auto">
                    <Smartphone class="w-8 h-8 text-primary" />
                </div>
                
                <div v-if="pinSetupStep === 'create'" class="text-center animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-3xl font-black tracking-tight mb-2">Set Your PIN</h2>
                    <p class="text-muted-foreground mb-8">Create a 6-digit security PIN for your account.</p>
                    
                    <div class="flex justify-between gap-2 mb-4">
                        <input v-for="(v, i) in pinDigits" :key="i" :id="`pin-${i}`" 
                            v-model="pinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                            @input="e => updatePin(i, (e.target as HTMLInputElement).value, false)"
                            @keydown.backspace="handleBackspace(i, false)"
                            class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                    </div>
                </div>

                <div v-else class="text-center animate-in fade-in slide-in-from-right-4 duration-300">
                    <h2 class="text-3xl font-black tracking-tight mb-2">Confirm PIN</h2>
                    <p class="text-muted-foreground mb-8">Please re-enter your PIN to confirm.</p>
                    
                    <div class="flex justify-between gap-2 mb-4">
                        <input v-for="(v, i) in confirmPinDigits" :key="i" :id="`confirm-pin-${i}`" 
                            v-model="confirmPinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                            @input="e => updatePin(i, (e.target as HTMLInputElement).value, true)"
                            @keydown.backspace="handleBackspace(i, true)"
                            class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                    </div>
                    <p v-if="pinError" class="text-destructive text-sm font-bold animate-shake">{{ pinError }}</p>
                </div>

                <div class="flex flex-col gap-4 mt-12">
                    <button @click="prevStep" class="text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors flex items-center justify-center gap-2">
                        <ArrowLeft class="w-4 h-4" />
                        Go Back
                    </button>
                </div>
            </div>

            <!-- Step 3: Wallet Setup -->
            <div v-if="step === 3" class="animate-fade-up">
                <h2 class="text-3xl font-black tracking-tight mb-2">Almost There!</h2>
                <p class="text-muted-foreground mb-8">Set up your first wallet to start tracking your finances.</p>
                
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Wallet Name</Label>
                        <div class="relative">
                            <Wallet class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="form.account_name" class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all" placeholder="e.g., Cash, GCash, Bank" />
                        </div>
                    </div>
                    
                    <div class="grid gap-2">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Currency</Label>
                        <div class="relative">
                            <Globe class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground z-10" />
                            <Select v-model="form.currency">
                                <SelectTrigger class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all w-full">
                                    <SelectValue placeholder="Select currency" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in currencies" :key="c.value" :value="c.value">
                                        {{ c.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Initial Balance</Label>
                        <div class="relative">
                            <Coins class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="form.initial_balance" type="number" step="0.01" class="h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background transition-all" placeholder="0.00" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 mt-12">
                    <Button @click="submit" :disabled="!form.account_name || form.processing" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-primary/20">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Complete Registration
                        <Check v-if="!form.processing" class="ml-2 w-5 h-5" />
                    </Button>
                    <button @click="prevStep" class="text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors flex items-center justify-center gap-2">
                        <ArrowLeft class="w-4 h-4" />
                        Go Back
                    </button>
                </div>

                <div v-if="Object.keys(form.errors).length > 0" class="mt-4 text-center">
                    <InputError v-for="(error, field) in form.errors" :key="field" :message="error" />
                </div>
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

.animate-shake {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}
</style>
