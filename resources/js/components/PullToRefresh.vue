<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { RefreshCw, ArrowDown } from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useHaptic } from '@/composables/useHaptic';

const props = withDefaults(defineProps<{
    threshold?: number;
    maxPull?: number;
    pullIndicatorHeight?: number;
    disabled?: boolean;
}>(), {
    threshold: 60,       // px to pull before release triggers refresh
    maxPull: 100,        // max pull distance (clamped)
    pullIndicatorHeight: 40,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'refresh'): void;
}>();

const contentRef = ref<HTMLElement | null>(null);
const pullDistance = ref(0);
const phase = ref<'idle' | 'pulling' | 'ready' | 'refreshing'>('idle');
const startY = ref(0);
const scrollParent = ref<HTMLElement | Window | null>(null);

const indicatorStyle = computed(() => ({
    height: `${Math.min(pullDistance.value, props.pullIndicatorHeight)}px`,
    opacity: Math.min(1, pullDistance.value / props.pullIndicatorHeight),
    transition: phase.value === 'refreshing' ? 'height 0.3s ease' : 'none',
    overflow: 'hidden',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
}));

const contentStyle = computed(() => {
    const d = phase.value === 'refreshing'
        ? props.pullIndicatorHeight
        : Math.max(0, pullDistance.value);

    return {
        transform: `translateY(${d}px)`,
        transition: phase.value === 'idle' || phase.value === 'refreshing'
            ? 'transform 0.3s cubic-bezier(0.22, 1, 0.36, 1)'
            : 'none',
    };
});

const isAtTop = (): boolean => {
    if (!scrollParent.value) return true;
    if (scrollParent.value instanceof Window) {
        return window.scrollY <= 0;
    }
    return (scrollParent.value as HTMLElement).scrollTop <= 0;
};

const findScrollParent = (el: HTMLElement | null): HTMLElement | Window | null => {
    if (!el) return null;
    let current: HTMLElement | null = el.parentElement;
    while (current) {
        const style = window.getComputedStyle(current);
        const overflow = style.overflowY || style.overflow;
        if (overflow === 'auto' || overflow === 'scroll') {
            return current;
        }
        current = current.parentElement;
    }
    return window;
};

const haptic = useHaptic();

const doRefresh = () => {
    haptic.refresh();
    phase.value = 'refreshing';
    pullDistance.value = props.pullIndicatorHeight;

    emit('refresh');

    // Auto-complete after 1.5s fallback (the emit callback should set completed sooner)
    setTimeout(() => {
        if (phase.value === 'refreshing') {
            completeRefresh();
        }
    }, 1500);
};

const completeRefresh = () => {
    phase.value = 'idle';
    pullDistance.value = 0;
};

const onTouchStart = (e: TouchEvent) => {
    if (props.disabled || phase.value === 'refreshing') return;
    if (!isAtTop()) return;

    startY.value = e.touches[0].clientY;
    phase.value = 'pulling';
};

const onTouchMove = (e: TouchEvent) => {
    if (props.disabled || phase.value === 'refreshing') return;
    if (phase.value === 'idle') return;

    const dy = e.touches[0].clientY - startY.value;

    // Only handle pull-down gesture
    if (dy <= 5) {
        if (!isAtTop()) {
            phase.value = 'idle';
            pullDistance.value = 0;
        }
        return;
    }

    // Check we're still at top (user might have scrolled down during the gesture)
    if (!isAtTop()) {
        phase.value = 'idle';
        pullDistance.value = 0;
        return;
    }

    // Prevent default to stop the page from scrolling while pulling
    e.preventDefault();

    // Apply resistance: easier to start, harder to pull further
    const eased = dy * (1 - Math.min(dy / 400, 0.5));
    pullDistance.value = Math.min(eased, props.maxPull);

    if (pullDistance.value >= props.threshold) {
        phase.value = 'ready';
    } else {
        phase.value = 'pulling';
    }
};

const onTouchEnd = () => {
    if (props.disabled || phase.value === 'refreshing') return;

    if (phase.value === 'ready' && pullDistance.value >= props.threshold) {
        doRefresh();
    } else {
        phase.value = 'idle';
        pullDistance.value = 0;
    }
};

const onScroll = () => {
    // If user scrolls away from top while idle, ensure state is clean
    if (phase.value === 'idle' && pullDistance.value !== 0) {
        pullDistance.value = 0;
    }
};

onMounted(() => {
    scrollParent.value = findScrollParent(contentRef.value);
    if (scrollParent.value) {
        scrollParent.value.addEventListener('scroll', onScroll, { passive: true });
    }
});

onUnmounted(() => {
    if (scrollParent.value) {
        scrollParent.value.removeEventListener('scroll', onScroll);
    }
});

defineExpose({ completeRefresh });
</script>

<template>
    <div
        ref="contentRef"
        class="ptr-root select-none"
        @touchstart="onTouchStart"
        @touchmove="onTouchMove"
        @touchend="onTouchEnd"
        @touchcancel="phase = 'idle'; pullDistance = 0"
    >
        <!-- Pull indicator -->
        <div
            :style="indicatorStyle"
            class="ptr-indicator flex items-center justify-center pointer-events-none"
        >
            <div v-if="phase === 'pulling'" class="flex items-center gap-1.5 text-muted-foreground">
                <ArrowDown
                    class="w-3.5 h-3.5 transition-transform"
                    :style="{ transform: `rotate(${Math.min(pullDistance / threshold * 180, 180)}deg)` }"
                />
                <span class="text-[10px] font-semibold">Pull to refresh</span>
            </div>
            <div v-else-if="phase === 'ready'" class="flex items-center gap-1.5 text-primary animate-bounce">
                <ArrowDown class="w-3.5 h-3.5" />
                <span class="text-[10px] font-bold">Release to refresh</span>
            </div>
            <div v-else-if="phase === 'refreshing'" class="flex items-center gap-1.5 text-primary">
                <RefreshCw class="w-3.5 h-3.5 animate-spin" />
                <span class="text-[10px] font-semibold">Refreshing...</span>
            </div>
        </div>

        <!-- Content -->
        <div :style="contentStyle">
            <slot />
        </div>
    </div>
</template>
