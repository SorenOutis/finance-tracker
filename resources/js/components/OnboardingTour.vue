<script setup lang="ts">
import { X } from 'lucide-vue-next';

interface OnboardingStep {
    title: string;
    description: string;
    targetClass?: string;
}

defineProps<{
    show: boolean;
    step: number;
    total: number;
    currentStep: OnboardingStep;
    tooltipStyle: Record<string, string>;
    tooltipSide: 'top' | 'bottom';
}>();

const emit = defineEmits<{
    next: [];
    prev: [];
    close: [];
}>();
</script>

<template>
    <!-- Backdrop -->
    <Transition
        enter-active-class="transition-opacity duration-500"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-500"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show && step > 0"
            class="fixed inset-0 bg-black/80 backdrop-blur-[2px] z-[50] pointer-events-auto"
            @click="emit('close')"
        ></div>
    </Transition>

    <!-- Tooltip -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="show"
                :style="tooltipStyle"
                class="fixed z-[200] w-full transition-all duration-500 ease-in-out px-6 sm:px-0"
            >
                <div
                    class="bg-white text-black shadow-[0_20px_50px_rgba(0,0,0,0.3)] rounded-xl overflow-visible mx-auto relative p-6 max-w-full"
                >
                    <!-- Arrow/Beak -->
                    <div
                        v-if="step !== 1 && step !== total"
                        :class="[
                            'absolute left-1/2 -translate-x-1/2 w-4 h-4 bg-white rotate-45 transition-all duration-500',
                            tooltipSide === 'top' ? '-top-2' : '-bottom-2',
                        ]"
                    ></div>

                    <!-- Close button -->
                    <button
                        @click="emit('close')"
                        class="absolute top-4 right-4 text-black/40 hover:text-black transition-colors"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <div class="text-left">
                        <div class="animate-fade-up">
                            <h2 class="text-xl font-bold tracking-tight mb-2 pr-6">
                                {{ currentStep.title }}
                            </h2>
                            <p class="text-sm text-black/70 leading-relaxed">
                                {{ currentStep.description }}
                            </p>
                        </div>

                        <div class="mt-8 flex items-center justify-between">
                            <span class="text-xs font-bold text-black/40">
                                {{ step }} of {{ total }}
                            </span>

                            <div class="flex gap-2">
                                <button
                                    v-if="step > 1"
                                    @click="emit('prev')"
                                    class="px-4 py-2 rounded-lg border border-black/10 text-xs font-bold hover:bg-black/5 transition-all"
                                >
                                    ← Previous
                                </button>
                                <button
                                    @click="emit('next')"
                                    class="px-6 py-2 rounded-lg bg-black text-white text-xs font-bold hover:bg-black/90 transition-all border-none"
                                >
                                    {{ step === total ? 'Get Started' : 'Next →' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
