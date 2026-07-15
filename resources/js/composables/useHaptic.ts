/**
 * Composable for haptic feedback on mobile devices.
 * Uses navigator.vibrate() with different intensity patterns.
 * Safely checks for browser support before vibrating.
 */

export interface HapticOptions {
    /** Whether to use haptic at all (can be disabled globally) */
    enabled?: boolean;
}

const patterns = {
    /** Light tap - for buttons, toggles, selections */
    light: 5,
    /** Medium tap - for confirmations, submits */
    medium: 10,
    /** Heavy tap - for destructive actions, deletes */
    heavy: [20, 30, 15] as number[],
    /** Success / confirmation burst */
    success: [10, 50, 15] as number[],
    /** Error / rejection buzz */
    error: [30, 20, 30] as number[],
    /** Sheet dismiss - quick slide haptic */
    dismiss: [8] as number[],
    /** Refresh / pull trigger */
    refresh: [12, 30, 12] as number[],
};

type PatternKey = keyof typeof patterns;

export function useHaptic(options: HapticOptions = {}) {
    const { enabled = true } = options;

    const canVibrate = typeof navigator !== 'undefined' && 'vibrate' in navigator;

    const vibrate = (pattern: PatternKey) => {
        if (!enabled || !canVibrate) return;
        try {
            navigator.vibrate(patterns[pattern]);
        } catch {
            // Silently fail - haptic is a progressive enhancement
        }
    };

    return {
        /** Light tap (5ms) - for buttons, toggles */
        light: () => vibrate('light'),
        /** Medium tap (10ms) - for confirms, submits */
        medium: () => vibrate('medium'),
        /** Heavy buzz (20-30-15ms) - for destructive actions */
        heavy: () => vibrate('heavy'),
        /** Success burst (10-50-15ms) - for successful completions */
        success: () => vibrate('success'),
        /** Error buzz (30-20-30ms) - for errors */
        error: () => vibrate('error'),
        /** Sheet dismiss (8ms) - quick slide haptic */
        dismiss: () => vibrate('dismiss'),
        /** Refresh trigger (12-30-12ms) - pull to refresh feel */
        refresh: () => vibrate('refresh'),
        /** Raw vibrate call with custom pattern */
        raw: (ms: number | number[]) => {
            if (!enabled || !canVibrate) return;
            try { navigator.vibrate(ms); } catch { /* noop */ }
        },
        /** Whether haptic is supported */
        supported: canVibrate,
    };
}
