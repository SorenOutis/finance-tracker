<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';
import { ArrowLeft, Smartphone, Check } from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Security settings',
        href: edit(),
    },
];

const setupStep = ref<'current' | 'new' | 'confirm'>('current');
const pinError = ref('');

// We'll use a manual form to handle the sequential sub-steps
const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const currentPinDigits = ref(['', '', '', '', '', '']);
const newPinDigits = ref(['', '', '', '', '', '']);
const confirmPinDigits = ref(['', '', '', '', '', '']);

const updatePin = (index: number, val: string, step: 'current' | 'new' | 'confirm') => {
    let target = currentPinDigits;
    let idPrefix = 'current-pin-';
    
    if (step === 'new') {
        target = newPinDigits;
        idPrefix = 'new-pin-';
    } else if (step === 'confirm') {
        target = confirmPinDigits;
        idPrefix = 'confirm-pin-';
    }

    if (val.length > 1) val = val[0];
    if (!/^\d*$/.test(val)) return;
    
    target.value[index] = val;
    
    if (val && index < 5) {
        const nextInput = document.getElementById(`${idPrefix}${index + 1}`);
        nextInput?.focus();
    }

    // Auto-advance
    if (target.value.every(d => d !== '')) {
        if (step === 'current') {
            form.current_password = currentPinDigits.value.join('');
            setTimeout(() => {
                setupStep.value = 'new';
                setTimeout(() => document.getElementById('new-pin-0')?.focus(), 100);
            }, 300);
        } else if (step === 'new') {
            form.password = newPinDigits.value.join('');
            setTimeout(() => {
                setupStep.value = 'confirm';
                setTimeout(() => document.getElementById('confirm-pin-0')?.focus(), 100);
            }, 300);
        } else if (step === 'confirm') {
            form.password_confirmation = confirmPinDigits.value.join('');
            if (form.password !== form.password_confirmation) {
                pinError.value = "PINs don't match. Try again.";
                confirmPinDigits.value = ['', '', '', '', '', ''];
                setTimeout(() => document.getElementById('confirm-pin-0')?.focus(), 100);
            }
        }
    }
};

const handleBackspace = (index: number, step: 'current' | 'new' | 'confirm') => {
    let target = currentPinDigits;
    let idPrefix = 'current-pin-';
    
    if (step === 'new') {
        target = newPinDigits;
        idPrefix = 'new-pin-';
    } else if (step === 'confirm') {
        target = confirmPinDigits;
        idPrefix = 'confirm-pin-';
    }

    if (!target.value[index] && index > 0) {
        const prevInput = document.getElementById(`${idPrefix}${index - 1}`);
        prevInput?.focus();
    }
};

const submit = () => {
    form.put('/user/password', {
        preserveScroll: true,
        onSuccess: () => {
            setupStep.value = 'current';
            currentPinDigits.value = ['', '', '', '', '', ''];
            newPinDigits.value = ['', '', '', '', '', ''];
            confirmPinDigits.value = ['', '', '', '', '', ''];
            form.reset();
        },
        onError: (errors) => {
            if (errors.current_password) {
                setupStep.value = 'current';
                currentPinDigits.value = ['', '', '', '', '', ''];
                setTimeout(() => document.getElementById('current-pin-0')?.focus(), 100);
            }
        }
    });
};

const goBack = () => {
    if (setupStep.value === 'new') {
        setupStep.value = 'current';
        newPinDigits.value = ['', '', '', '', '', ''];
        setTimeout(() => document.getElementById('current-pin-0')?.focus(), 100);
    } else if (setupStep.value === 'confirm') {
        setupStep.value = 'new';
        confirmPinDigits.value = ['', '', '', '', '', ''];
        setTimeout(() => document.getElementById('new-pin-0')?.focus(), 100);
    }
};

watch([currentPinDigits, newPinDigits, confirmPinDigits], () => {
    if (pinError.value) pinError.value = '';
}, { deep: true });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Security settings" />

        <h1 class="sr-only">Security settings</h1>

        <SettingsLayout>
            <div class="surface-card p-8 animate-fade-up flex flex-col items-center max-w-md mx-auto">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-6">
                    <Smartphone class="w-8 h-8 text-primary" />
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-black tracking-tight mb-2">
                        {{ setupStep === 'current' ? 'Enter Current PIN' : setupStep === 'new' ? 'Set New PIN' : 'Confirm New PIN' }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ setupStep === 'current' ? 'Verify your identity to change your security PIN.' : setupStep === 'new' ? 'Create a new 6-digit security PIN.' : 'Please re-enter your new PIN to confirm.' }}
                    </p>
                </div>

                <div class="w-full space-y-6">
                    <!-- Current PIN Step -->
                    <div v-if="setupStep === 'current'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="flex justify-between gap-2">
                            <input v-for="(v, i) in currentPinDigits" :key="i" :id="`current-pin-${i}`" 
                                v-model="currentPinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                                @input="e => updatePin(i, (e.target as HTMLInputElement).value, 'current')"
                                @keydown.backspace="handleBackspace(i, 'current')"
                                class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                        </div>
                        <InputError :message="form.errors.current_password" class="mt-4 text-center" />
                    </div>

                    <!-- New PIN Step -->
                    <div v-if="setupStep === 'new'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="flex justify-between gap-2">
                            <input v-for="(v, i) in newPinDigits" :key="i" :id="`new-pin-${i}`" 
                                v-model="newPinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                                @input="e => updatePin(i, (e.target as HTMLInputElement).value, 'new')"
                                @keydown.backspace="handleBackspace(i, 'new')"
                                class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                        </div>
                    </div>

                    <!-- Confirm PIN Step -->
                    <div v-if="setupStep === 'confirm'" class="animate-in fade-in slide-in-from-right-4 duration-300">
                        <div class="flex justify-between gap-2 mb-4">
                            <input v-for="(v, i) in confirmPinDigits" :key="i" :id="`confirm-pin-${i}`" 
                                v-model="confirmPinDigits[i]" type="password" inputmode="numeric" maxlength="1"
                                @input="e => updatePin(i, (e.target as HTMLInputElement).value, 'confirm')"
                                @keydown.backspace="handleBackspace(i, 'confirm')"
                                class="w-12 h-14 text-center text-2xl font-black rounded-xl bg-muted/30 border-2 border-transparent focus:border-primary focus:bg-background transition-all outline-none" />
                        </div>
                        <p v-if="pinError" class="text-destructive text-xs font-bold text-center animate-shake">{{ pinError }}</p>
                        
                        <Button @click="submit" :disabled="form.processing || !confirmPinDigits.every(d => d !== '')" size="lg" class="w-full h-14 rounded-2xl text-lg font-bold shadow-lg shadow-primary/20 mt-8">
                            <Spinner v-if="form.processing" class="mr-2" />
                            Update PIN
                            <Check v-if="!form.processing" class="ml-2 w-5 h-5" />
                        </Button>
                    </div>
                </div>

                <button v-if="setupStep !== 'current'" @click="goBack" class="mt-8 text-sm font-bold text-muted-foreground hover:text-foreground py-2 transition-colors flex items-center gap-2">
                    <ArrowLeft class="w-4 h-4" />
                    Go Back
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-show="form.recentlySuccessful" class="mt-4 text-sm font-bold text-green-600">
                        PIN updated successfully.
                    </p>
                </Transition>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
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
