<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import LoadingScreen from '@/components/LoadingScreen.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { ref, computed } from 'vue';
import { ArrowRight, ArrowLeft, Check, Smartphone, Lock, User, Mail, Sparkles } from 'lucide-vue-next';

const step = ref(1);
const totalSteps = 4;
const authMethod = ref<'pin' | 'pattern' | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const pin = ref(['', '', '', '', '', '']);
const confirmPin = ref(['', '', '', '', '', '']);

// Pattern state
const pattern = ref<number[]>([]);
const isDrawing = ref(false);
const isConfirming = ref(false);
const currentPos = ref({ x: 0, y: 0 });
const patternContainer = ref<HTMLElement | null>(null);
const patternError = ref('');

const dots = computed(() => {
    const d = [];
    for (let i = 0; i < 3; i++) {
        for (let j = 0; j < 3; j++) {
            d.push({ id: i * 3 + j, x: j * 100 + 50, y: i * 100 + 50 });
        }
    }
    return d;
});

const startDrawing = (e: MouseEvent | TouchEvent) => {
    e.preventDefault();
    isDrawing.value = true;
    patternError.value = '';
    pattern.value = [];
    updateCurrentPos(e);
    handleMove(e);
};

const handleMove = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value) return;
    updateCurrentPos(e);
    
    dots.value.forEach(dot => {
        const dx = currentPos.value.x - dot.x;
        const dy = currentPos.value.y - dot.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 30 && !pattern.value.includes(dot.id)) {
            pattern.value.push(dot.id);
            // Haptic feedback if available
            if ('vibrate' in navigator) navigator.vibrate(10);
        }
    });
};

const stopDrawing = () => {
    if (!isDrawing.value) return;
    isDrawing.value = false;
    
    if (pattern.value.length < 3) {
        pattern.value = [];
        patternError.value = 'Pattern must connect at least 3 dots';
        return;
    }
    
    const patternStr = pattern.value.join('');
    
    if (!isConfirming.value) {
        form.password = patternStr;
        isConfirming.value = true;
        pattern.value = [];
    } else {
        form.password_confirmation = patternStr;
        if (form.password !== form.password_confirmation) {
            isConfirming.value = false;
            form.password = '';
            form.password_confirmation = '';
            pattern.value = [];
            patternError.value = 'Patterns do not match. Please try again.';
        }
    }
};

const updateCurrentPos = (e: MouseEvent | TouchEvent) => {
    const rect = patternContainer.value?.getBoundingClientRect();
    if (!rect) return;
    
    const clientX = 'touches' in e ? e.touches[0].clientX : (e as MouseEvent).clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : (e as MouseEvent).clientY;
    
    currentPos.value = {
        x: (clientX - rect.left) * (300 / rect.width),
        y: (clientY - rect.top) * (300 / rect.height)
    };
};

const nextStep = () => {
    if (step.value === 2) {
        if (!form.name || !form.email) return;
    }
    if (step.value < totalSteps) step.value++;
};

const selectAuthMethod = (method: 'pin' | 'pattern') => {
    authMethod.value = method;
    form.password = '';
    form.password_confirmation = '';
    isConfirming.value = false;
    pattern.value = [];
    patternError.value = '';
    pin.value = ['', '', '', '', '', ''];
    confirmPin.value = ['', '', '', '', '', ''];
    nextStep();
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const updatePin = (index: number, val: string, isConfirm: boolean = false) => {
    const target = isConfirm ? confirmPin : pin;
    if (val.length > 1) val = val[0];
    if (!/^\d*$/.test(val)) return;
    
    target.value[index] = val;
    
    if (val && index < 5) {
        const nextInput = document.getElementById(`${isConfirm ? 'confirm-' : ''}pin-${index + 1}`);
        nextInput?.focus();
    }
    
    form.password = pin.value.join('');
    form.password_confirmation = confirmPin.value.join('');
};

const handleBackspace = (index: number, isConfirm: boolean = false) => {
    const target = isConfirm ? confirmPin : pin;
    if (!target.value[index] && index > 0) {
        const prevInput = document.getElementById(`${isConfirm ? 'confirm-' : ''}pin-${index - 1}`);
        prevInput?.focus();
    }
};

const submit = () => {
    if (form.password !== form.password_confirmation) {
        alert('Security codes do not match');
        return;
    }
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
</script>

<template>
    <div class="min-h-screen bg-background flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-hidden">
        <!-- Background Orbs -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

        <Head title="Welcome" />

        <div class="w-full max-w-[400px] z-10">
            <!-- Progress Dots -->
            <div class="flex justify-center gap-2 mb-12">
                <div v-for="i in totalSteps" :key="i" 
                    :class="['h-1.5 rounded-full transition-all duration-500', 
                        step === i ? 'w-8 bg-primary' : 'w-2 bg-muted']">
                </div>
            </div>

            <!-- Step 1: Welcome -->
            <div v-if="step === 1" class="animate-fade-up flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-[24px] flex items-center justify-center mb-8">
                    <Sparkles class="w-10 h-10 text-primary" />
                </div>
                <h1 class="text-4xl font-black tracking-tight mb-4">Welcome to Finance Tracker</h1>
                <p class="text-muted-foreground text-lg mb-12 leading-relaxed">
                    The simplest way to manage your wallets, bills, and monthly income in one place.
                </p>
                <Button @click="nextStep" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-primary/20">
                    Get Started
                    <ArrowRight class="ml-2 w-5 h-5" />
                </Button>
                <p class="mt-8 text-sm text-muted-foreground">
                    Already have an account? 
                    <TextLink :href="login()" class="text-primary font-bold hover:underline">Log in</TextLink>
                </p>
            </div>

            <!-- Step 2: Information -->
            <div v-if="step === 2" class="animate-fade-up">
                <h2 class="text-3xl font-black tracking-tight mb-2">About You</h2>
                <p class="text-muted-foreground mb-8">Tell us a bit about yourself to get started.</p>
                
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
                    <Button @click="nextStep" :disabled="!form.name || !form.email" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold">
                        Continue
                        <ArrowRight class="ml-2 w-5 h-5" />
                    </Button>
                    <button @click="prevStep" class="text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors">
                        Go Back
                    </button>
                </div>
            </div>

            <!-- Step 3: Choose Auth Method -->
            <div v-if="step === 3" class="animate-fade-up">
                <h2 class="text-3xl font-black tracking-tight mb-2">Security Method</h2>
                <p class="text-muted-foreground mb-8">Choose how you want to secure your account.</p>

                <div class="grid gap-4">
                    <button @click="selectAuthMethod('pin')" class="flex items-center gap-4 p-5 rounded-[24px] bg-muted/30 border-2 border-transparent hover:border-primary/50 hover:bg-primary/5 transition-all text-left group">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                            <Smartphone class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="font-black text-lg">Security PIN</p>
                            <p class="text-xs text-muted-foreground">Use a 6-digit numeric code</p>
                        </div>
                    </button>

                    <button @click="selectAuthMethod('pattern')" class="flex items-center gap-4 p-5 rounded-[24px] bg-muted/30 border-2 border-transparent hover:border-primary/50 hover:bg-primary/5 transition-all text-left group">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                            <Lock class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="font-black text-lg">Lock Pattern</p>
                            <p class="text-xs text-muted-foreground">Draw a custom security pattern</p>
                        </div>
                    </button>
                </div>

                <div class="flex flex-col gap-4 mt-12">
                    <button @click="prevStep" class="text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors">
                        Go Back
                    </button>
                </div>
            </div>

            <!-- Step 4: Security Setup -->
            <div v-if="step === 4" class="animate-fade-up">
                <div v-if="authMethod === 'pin'">
                    <h2 class="text-3xl font-black tracking-tight mb-2">Create PIN</h2>
                    <p class="text-muted-foreground mb-8">Set a 6-digit PIN to secure your data.</p>
                    <div class="space-y-8">
                        <div>
                            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1 block mb-3">New PIN</Label>
                            <div class="flex justify-between gap-2">
                                <input v-for="(v, i) in pin" :key="i" :id="`pin-${i}`" 
                                    v-model="pin[i]" type="password" inputmode="numeric" maxlength="1"
                                    @input="e => updatePin(i, (e.target as HTMLInputElement).value, false)"
                                    @keydown.backspace="handleBackspace(i, false)"
                                    class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                            </div>
                        </div>

                        <div>
                            <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1 block mb-3">Confirm PIN</Label>
                            <div class="flex justify-between gap-2">
                                <input v-for="(v, i) in confirmPin" :key="i" :id="`confirm-pin-${i}`" 
                                    v-model="confirmPin[i]" type="password" inputmode="numeric" maxlength="1"
                                    @input="e => updatePin(i, (e.target as HTMLInputElement).value, true)"
                                    @keydown.backspace="handleBackspace(i, true)"
                                    class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="authMethod === 'pattern'">
                    <h2 class="text-3xl font-black tracking-tight mb-2">{{ isConfirming ? 'Confirm Pattern' : 'Create Pattern' }}</h2>
                    <p class="text-muted-foreground mb-8">{{ isConfirming ? 'Draw your pattern again to confirm.' : 'Draw your security pattern below.' }}</p>
                    
                    <div class="relative w-[300px] h-[300px] mx-auto bg-muted/10 rounded-[32px] overflow-hidden touch-none"
                         ref="patternContainer"
                         @mousedown="startDrawing"
                         @mousemove="handleMove"
                         @mouseup="stopDrawing"
                         @mouseleave="stopDrawing"
                         @touchstart="startDrawing"
                         @touchmove="handleMove"
                         @touchend="stopDrawing">
                        
                        <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 300 300">
                            <!-- Lines connecting selected dots -->
                            <polyline 
                                :points="pattern.map(id => `${dots[id].x},${dots[id].y}`).join(' ')" 
                                fill="none" 
                                class="stroke-primary" 
                                stroke-width="4" 
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                            />
                            <!-- Line from last dot to current cursor -->
                            <line v-if="isDrawing && pattern.length > 0"
                                  :x1="dots[pattern[pattern.length - 1]].x"
                                  :y1="dots[pattern[pattern.length - 1]].y"
                                  :x2="currentPos.x"
                                  :y2="currentPos.y"
                                  class="stroke-primary" 
                                  stroke-width="4" 
                                  stroke-linecap="round" />
                        </svg>

                        <!-- Dots -->
                        <div v-for="dot in dots" :key="dot.id"
                             class="absolute rounded-full transition-all duration-200"
                             :class="pattern.includes(dot.id) ? 'w-4 h-4 bg-primary' : 'w-2 h-2 bg-muted-foreground/30'"
                             :style="{ left: `${dot.x}px`, top: `${dot.y}px`, transform: 'translate(-50%, -50%)' }">
                            <div v-if="pattern.includes(dot.id)" class="absolute inset-0 rounded-full animate-ping bg-primary/20" style="width: 300%; height: 300%; left: -100%; top: -100%;"></div>
                        </div>
                    </div>
                    
                    <p v-if="patternError" class="text-destructive text-sm mt-4 text-center font-bold">{{ patternError }}</p>
                </div>

                <div class="flex flex-col gap-4 mt-12">
                    <Button @click="submit" :disabled="!form.password || form.password !== form.password_confirmation || (authMethod === 'pin' && form.password.length !== 6) || form.processing" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Complete Registration
                        <Check v-if="!form.processing" class="ml-2 w-5 h-5" />
                    </Button>
                    <button @click="prevStep" class="text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors">
                        Go Back
                    </button>
                </div>
                
                <div v-if="form.errors.password" class="mt-4 text-center">
                    <InputError :message="form.errors.password" />
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
</style>
