<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import LoadingScreen from '@/components/LoadingScreen.vue';
import { ref, onMounted, computed } from 'vue';
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
const page = usePage();
const rememberedEmail = ref(localStorage.getItem('remembered-email') || (page.props.defaultEmail as string) || '');
const isEmailHidden = ref(!!rememberedEmail.value);
const authMethod = ref<'pin' | 'pattern'>(localStorage.getItem('preferred-auth-method') as any || 'pin');

// Pattern state
const pattern = ref<number[]>([]);
const isDrawing = ref(false);
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
    
    form.password = pattern.value.join('');
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

const toggleAuthMethod = () => {
    authMethod.value = authMethod.value === 'pin' ? 'pattern' : 'pin';
    localStorage.setItem('preferred-auth-method', authMethod.value);
    form.password = '';
    pin.value = ['', '', '', '', '', ''];
    pattern.value = [];
    patternError.value = '';
};

onMounted(() => {
    if (rememberedEmail.value) {
        form.email = rememberedEmail.value;
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
        onSuccess: () => {
            localStorage.setItem('remembered-email', form.email);
        },
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
            <p class="text-muted-foreground mb-12">
                {{ isEmailHidden ? 'Enter your PIN to verify your identity.' : 'Enter your email and PIN to access your account.' }}
            </p>

            <div v-if="status" class="mb-8 p-4 rounded-xl bg-green-500/10 text-green-600 text-sm font-bold">
                {{ status }}
            </div>

            <div class="grid gap-8 text-left">
                <div v-if="!isEmailHidden" class="grid gap-2 animate-fade-up">
                    <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">Email Address</Label>
                    <div class="relative">
                        <Mail class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <input v-model="form.email" type="email" required autofocus
                            class="w-full h-12 pl-11 rounded-xl bg-muted/30 border-transparent focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none" 
                            placeholder="email@example.com" />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div v-else class="flex flex-col items-center gap-2 py-4 animate-fade-up">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 border border-primary/20">
                        <Mail class="w-3 h-3 text-primary" />
                        <span class="text-sm font-bold text-foreground">{{ rememberedEmail }}</span>
                    </div>
                    <button @click="switchAccount" class="text-[10px] font-bold text-muted-foreground hover:text-primary transition-colors uppercase tracking-widest">
                        Switch Account
                    </button>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground ml-1">
                            {{ authMethod === 'pin' ? 'Security PIN' : 'Security Pattern' }}
                        </Label>
                        <button @click="toggleAuthMethod" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-wider">
                            Use {{ authMethod === 'pin' ? 'Pattern' : 'PIN' }}
                        </button>
                    </div>

                    <div v-if="authMethod === 'pin'" class="flex justify-between gap-2 animate-fade-up">
                        <input v-for="(v, i) in pin" :key="i" :id="`pin-${i}`" 
                            v-model="pin[i]" type="password" inputmode="numeric" maxlength="1"
                            @input="e => updatePin(i, (e.target as HTMLInputElement).value)"
                            @keydown.backspace="handleBackspace(i)"
                            class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                    </div>

                    <div v-else class="flex flex-col items-center animate-fade-up">
                        <div ref="patternContainer" 
                            class="relative w-full max-w-[280px] aspect-square bg-muted/20 rounded-3xl border border-border/40 touch-none cursor-crosshair overflow-hidden shadow-inner"
                            @mousedown="startDrawing"
                            @mousemove="handleMove"
                            @mouseup="stopDrawing"
                            @mouseleave="stopDrawing"
                            @touchstart="startDrawing"
                            @touchmove="handleMove"
                            @touchend="stopDrawing">
                            
                            <svg viewBox="0 0 300 300" class="w-full h-full relative z-10 pointer-events-none">
                                <path v-if="pattern.length > 0"
                                    :d="`M ${dots.find(d => d.id === pattern[0])?.x} ${dots.find(d => d.id === pattern[0])?.y} ` + 
                                       pattern.slice(1).map(id => `L ${dots.find(d => d.id === id)?.x} ${dots.find(d => d.id === id)?.y}`).join(' ') +
                                       (isDrawing ? ` L ${currentPos.x} ${currentPos.y}` : '')"
                                    fill="none" class="stroke-primary" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" />
                                
                                <g v-for="dot in dots" :key="dot.id">
                                    <circle :cx="dot.x" :cy="dot.y" r="10" 
                                        :class="pattern.includes(dot.id) ? 'fill-primary' : 'fill-muted-foreground/30'"
                                        class="transition-colors duration-200" />
                                    <circle v-if="pattern.includes(dot.id)" :cx="dot.x" :cy="dot.y" r="22" 
                                        class="fill-primary/20 animate-pulse" />
                                </g>
                            </svg>
                        </div>
                        <p v-if="patternError" class="mt-4 text-xs font-bold text-destructive animate-shake text-center">{{ patternError }}</p>
                        <p v-else-if="pattern.length > 0 && !isDrawing" class="mt-4 text-[10px] font-bold text-primary uppercase tracking-widest animate-pulse">Pattern Captured</p>
                    </div>

                    <InputError :message="form.errors.password" class="mt-2 text-center" />
                </div>

                <div class="flex flex-col gap-4 mt-4">
                    <Button @click="submit" :disabled="(authMethod === 'pin' ? form.password.length !== 6 : form.password.length < 3) || !form.email || form.processing" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold">
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
