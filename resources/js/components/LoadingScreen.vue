<script setup lang="ts">
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const emit = defineEmits(['complete']);

const container = ref<HTMLElement | null>(null);
const textRef = ref<HTMLElement | null>(null);
const progressBarInner = ref<HTMLElement | null>(null);
const rippleRef = ref<HTMLElement | null>(null);

const text = "KOAMISHIN.ORG";
const characters = text.split("");

onMounted(() => {
    if (!container.value || !textRef.value || !progressBarInner.value || !rippleRef.value) return;

    const chars = textRef.value.querySelectorAll('.char');
    const tl = gsap.timeline({
        onComplete: () => {
            // Ripple effect logic
            gsap.to(rippleRef.value, {
                maskImage: 'radial-gradient(circle, transparent 0%, black 100%)',
                webkitMaskImage: 'radial-gradient(circle, transparent 0%, black 100%)',
                duration: 1,
                ease: 'power2.inOut',
                onComplete: () => {
                   emit('complete');
                }
            });
            
            // Reverse ripple (out to in) logic using clip-path for a cleaner effect
            gsap.to(container.value, {
                clipPath: 'circle(0% at 50% 50%)',
                duration: 1.2,
                ease: 'expo.inOut',
            });
        }
    });

    // Initial state
    gsap.set(chars, { opacity: 0, y: 10 });
    gsap.set(progressBarInner.value, { scaleX: 0 });

    // Loading Bar Progress
    tl.to(progressBarInner.value, {
        scaleX: 1,
        duration: 3,
        ease: "none",
        transformOrigin: "left center",
    });

    // Reveal characters synced with progress after a small initial delay
    chars.forEach((char, index) => {
        tl.to(char, {
            opacity: 1,
            y: 0,
            duration: 0.3,
            ease: "back.out(1.7)",
        }, (index * (2.5 / characters.length)) + 0.5); // 0.5s delay, slightly faster reveal
    });
});
</script>

<template>
    <div 
        ref="container" 
        class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-black overflow-hidden"
        style="clip-path: circle(150% at 50% 50%)"
    >
        <div ref="rippleRef" class="absolute inset-0 bg-transparent pointer-events-none"></div>
        
        <div class="relative flex flex-col items-center gap-6">
            <!-- Text Reveal -->
            <div ref="textRef" class="flex items-center text-4xl md:text-6xl font-black tracking-tighter text-white font-display select-none">
                <span 
                    v-for="(char, index) in characters" 
                    :key="index" 
                    class="char inline-block"
                    style="opacity: 0"
                >
                    {{ char }}
                </span>
            </div>


            <!-- Loading Bar -->
            <div class="relative w-64 md:w-80 h-1 bg-white/10 rounded-full overflow-hidden">
                <div 
                    ref="progressBarInner" 
                    class="absolute inset-y-0 left-0 w-full bg-white origin-left"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-display {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}
</style>
