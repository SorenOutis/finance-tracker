import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export interface OnboardingStep {
    title: string;
    description: string;
    /** CSS selector for the target element (e.g. '.stats-grid', '#hero-section') */
    targetSelector?: string;
}

/** Manages step navigation, tooltip positioning, and highlight class generation for onboarding tours. */
export function useOnboarding(steps: OnboardingStep[]) {
    const showOnboarding = ref(false);
    const onboardingStep = ref(1);
    const totalSteps = steps.length;

    const tooltipStyle = ref({ top: '50%', left: '50%', transform: 'translate(-50%, -50%)' });
    const tooltipSide = ref<'top' | 'bottom'>('bottom');

    const currentStep = computed(() => steps[onboardingStep.value - 1]);

    /** Recompute tooltip position relative to the current step's target. Falls back to center if no target. */
    const updateTooltipPosition = () => {
        if (!showOnboarding.value) return;

        const step = steps[onboardingStep.value - 1];
        if (!step?.targetSelector || onboardingStep.value === 1 || onboardingStep.value === totalSteps) {
            tooltipStyle.value = { top: '50%', left: '50%', transform: 'translate(-50%, -50%)' };
            tooltipSide.value = 'bottom';
            return;
        }

        const target = document.querySelector(step.targetSelector);
        if (target) {
            const rect = target.getBoundingClientRect();
            const isSmallScreen = window.innerWidth < 640;
            const spaceBelow = window.innerHeight - rect.bottom;
            const positionAbove = spaceBelow < 250;
            tooltipSide.value = positionAbove ? 'bottom' : 'top';

            const topPx = positionAbove ? `${rect.top - 20}px` : `${rect.bottom + 20}px`;
            const leftVal = isSmallScreen ? '50%' : `${rect.left + rect.width / 2}px`;
            const xform = `translate(-50%, ${positionAbove ? '-100%' : '0'})`;

            tooltipStyle.value = {
                top: topPx,
                left: leftVal,
                transform: xform as any,
                maxWidth: isSmallScreen ? 'calc(100vw - 48px)' : '320px',
                position: 'fixed' as any,
            };
        }
    };

    const completeOnboarding = () => {
        showOnboarding.value = false;
        const routeFunc = (window as any).route;
        const url = routeFunc ? routeFunc('onboarding.complete') : '/onboarding/complete';
        router.post(url, {}, {
            onError: () => {
                // Silently fail — the tour is already hidden locally
            },
        });
    };

    const nextStep = () => {
        if (onboardingStep.value < totalSteps) {
            onboardingStep.value++;
        } else {
            completeOnboarding();
        }
    };

    const prevStep = () => {
        if (onboardingStep.value > 1) {
            onboardingStep.value--;
        }
    };

    watch(onboardingStep, () => {
        setTimeout(() => {
            const step = steps[onboardingStep.value - 1];
            if (step?.targetSelector) {
                const target = document.querySelector(step.targetSelector);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(updateTooltipPosition, 400);
                    return;
                }
            }
            updateTooltipPosition();
        }, 100);
    });

    /** Returns a class string that highlights the element for the given step index. */
    const highlightClass = (stepIndex: number) => {
        if (!showOnboarding.value || onboardingStep.value !== stepIndex) return '';
        return 'relative z-[100] ring-4 ring-primary ring-offset-4 ring-offset-background rounded-[20px] transition-all duration-500 shadow-[0_0_50px_rgba(var(--primary),0.3)] bg-background scale-[1.02] pointer-events-auto';
    };

    return {
        showOnboarding,
        onboardingStep,
        totalSteps,
        currentStep,
        tooltipStyle,
        tooltipSide,
        completeOnboarding,
        nextStep,
        prevStep,
        highlightClass,
        updateTooltipPosition,
        steps,
    };
}
