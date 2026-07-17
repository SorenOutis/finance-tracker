import { ref, onMounted, onUnmounted } from 'vue';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

export function useSmoothScroll() {
    const lenis = ref<Lenis | null>(null);

    onMounted(() => {
        gsap.registerPlugin(ScrollTrigger);

        const instance = new Lenis({
            duration: 1.2,
            easing: (t: number) => Math.min(1, 1.001 - 2 ** (-10 * t)),
            orientation: 'vertical',
            gestureOrientation: 'vertical',
            smoothWheel: true,
            wheelMultiplier: 1,
            touchMultiplier: 1,
            infinite: false,
        });

        lenis.value = instance;

        // Sync Lenis with GSAP ScrollTrigger
        instance.on('scroll', ScrollTrigger.update);

        // RAF loop
        const raf = (time: number) => {
            instance.raf(time);
            requestAnimationFrame(raf);
        };

        requestAnimationFrame(raf);
    });

    onUnmounted(() => {
        if (lenis.value) {
            lenis.value.destroy();
        }
        // Clean up ScrollTrigger
        ScrollTrigger.getAll().forEach((t) => t.kill());
    });

    return {
        lenis,
    };
}
