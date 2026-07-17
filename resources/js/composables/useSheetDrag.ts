import { ref, computed } from 'vue';

interface UseSheetDragOptions {
    /** Distance in px that triggers dismiss (default: 120) */
    threshold?: number;
    /** Opacity fade rate: 0-1, higher = faster fade (default: 1.5) */
    fadeRate?: number;
    /** Spring-back animation duration in ms (default: 300) */
    snapDuration?: number;
}

export function useSheetDrag(options: UseSheetDragOptions = {}) {
    const { threshold = 120, fadeRate = 1.5, snapDuration = 300 } = options;

    // Haptic feedback on dismiss
    const sendHaptic = () => {
        try {
            if (typeof navigator !== 'undefined' && 'vibrate' in navigator) {
                navigator.vibrate(8);
            }
        } catch { /* noop */ }
    };

    const dragY = ref(0);
    const startY = ref(0);
    const isDragging = ref(false);
    const isDismissed = ref(false);

    /** Whether the sheet should be visible (internal control) */
    const visible = ref(true);

    let dismissCallback: (() => void) | null = null;

    const sheetStyle = computed(() => {
        if (!isDragging.value && !isDismissed.value) return {};

        const y = isDismissed.value ? dragY.value : dragY.value;

        return {
            transform: `translateY(${Math.max(0, y)}px)`,
            transition: isDragging.value
                ? 'none'
                : `transform ${snapDuration}ms cubic-bezier(0.22, 1, 0.36, 1)`,
            willChange: 'transform' as const,
        };
    });

    const overlayStyle = computed(() => {
        if (!isDragging.value && !isDismissed.value) return {};

        const y = isDismissed.value ? dragY.value : dragY.value;
        const opacity = Math.max(0, 1 - (y * fadeRate) / 100);

        return {
            opacity,
            transition: isDragging.value
                ? 'none'
                : `opacity ${snapDuration}ms cubic-bezier(0.22, 1, 0.36, 1)`,
        };
    });

    const onHandleTouchStart = (e: TouchEvent | MouseEvent) => {
        // Ignore if not a touch event or not left mouse button
        if ('button' in e && e.button !== 0) return;

        const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
        startY.value = clientY;
        dragY.value = 0;
        isDragging.value = true;
        isDismissed.value = false;
    };

    const onHandleTouchMove = (e: TouchEvent | MouseEvent) => {
        if (!isDragging.value) return;

        const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
        const dy = clientY - startY.value;

        // Only allow downward drag (positive dy)
        if (dy < 0) {
            // Small upward movement is fine, just don't resist
            dragY.value = dy * 0.15; // slight resistance when pulling up
            return;
        }

        // Apply resistance for smoother feel
        const resisted = dy * (1 - Math.min(dy / 600, 0.6));
        dragY.value = resisted;

        // Prevent default to stop scrolling while dragging the sheet
        if (dy > 5) {
            e.preventDefault();
        }
    };

    const onHandleTouchEnd = (): void => {
        if (!isDragging.value) return;

        isDragging.value = false;

        // Get final velocity
        const finalDy = dragY.value;

        // Check if we should dismiss
        if (finalDy >= threshold) {
            // Dismiss: haptic + animate out, then close
            sendHaptic();
            isDismissed.value = true;
            // Animate a bit further than current position for smooth exit
            dragY.value = finalDy + 100;

            setTimeout(() => {
                visible.value = false;
                isDismissed.value = false;
                dragY.value = 0;
                if (dismissCallback) {
                    dismissCallback();
                    dismissCallback = null;
                }
            }, snapDuration);
        } else {
            // Snap back
            dragY.value = 0;
            setTimeout(() => {
                if (!isDragging.value && !isDismissed.value) {
                    dragY.value = 0;
                }
            }, snapDuration);
        }
    };

    const registerDismiss = (callback: () => void) => {
        dismissCallback = callback;
    };

    return {
        sheetStyle,
        overlayStyle,
        isDragging,
        visible,
        onHandleTouchStart,
        onHandleTouchMove,
        onHandleTouchEnd,
        registerDismiss,
        dragY,
    };
}
