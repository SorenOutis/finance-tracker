import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable to track Inertia navigation loading state.
 * Returns `isLoading` ref that is true during page transitions and router.reload calls.
 * Also includes a brief artificial delay on mount for a polished initial skeleton flash.
 *
 * @param initialDelay - Show skeleton for this many ms on mount (0 = skip). Default 400.
 */
export function usePageLoading(initialDelay = 400) {
    const isLoading = ref(true);
    let removeStart: (() => void) | null = null;
    let removeFinish: (() => void) | null = null;
    let initialTimer: ReturnType<typeof setTimeout> | null = null;

    const onStart = () => {
        if (initialTimer) {
            clearTimeout(initialTimer);
            initialTimer = null;
        }
        isLoading.value = true;
    };

    const onFinish = () => {
        isLoading.value = false;
    };

    onMounted(() => {
        // Register Inertia navigation events
        removeStart = router.on('start', onStart);
        removeFinish = router.on('finish', onFinish);

        // Brief initial flash of skeleton for perceived performance
        if (initialDelay > 0) {
            isLoading.value = true;
            initialTimer = setTimeout(() => {
                isLoading.value = false;
                initialTimer = null;
            }, initialDelay);
        } else {
            isLoading.value = false;
        }
    });

    onUnmounted(() => {
        if (initialTimer) {
            clearTimeout(initialTimer);
        }
        if (removeStart) removeStart();
        if (removeFinish) removeFinish();
    });

    return { isLoading };
}
