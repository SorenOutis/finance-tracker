<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { useHaptic } from '@/composables/useHaptic';

const props = withDefaults(defineProps<{
    threshold?: number;
    snapThreshold?: number;
    confirmLabel?: string;
    disabled?: boolean;
}>(), {
    threshold: 10,       // px before swipe starts
    snapThreshold: 80,   // px past which it auto-opens
    confirmLabel: 'Delete',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'delete'): void;
}>();

const containerRef = ref<HTMLElement | null>(null);
const contentRef = ref<HTMLElement | null>(null);
const actionWidth = ref(90);  // action area width in px
const offsetX = ref(0);
const startX = ref(0);
const startY = ref(0);
const isSwiping = ref(false);
const isOpen = ref(false);
const confirming = ref(false);

const contentStyle = computed(() => {
    if (!isSwiping.value && !isOpen.value) return {};
    return {
        transform: `translateX(${Math.min(0, offsetX.value)}px)`,
        transition: isSwiping.value ? 'none' : 'transform 0.3s cubic-bezier(0.22, 1, 0.36, 1)',
    };
});

const actionStyle = computed(() => ({
    width: `${actionWidth.value}px`,
    transform: `translateX(${Math.min(0, offsetX.value + actionWidth.value)}px)`,
    transition: isSwiping.value ? 'none' : 'transform 0.3s cubic-bezier(0.22, 1, 0.36, 1)',
}));

const close = () => {
    isOpen.value = false;
    offsetX.value = 0;
    confirming.value = false;
};

const onTouchStart = (e: TouchEvent) => {
    if (props.disabled) return;
    const touch = e.touches[0];
    startX.value = touch.clientX;
    startY.value = touch.clientY;
    isSwiping.value = false;
};

const onTouchMove = (e: TouchEvent) => {
    if (props.disabled) return;
    const touch = e.touches[0];
    const dx = touch.clientX - startX.value;
    const dy = touch.clientY - startY.value;

    // Only start horizontal swipe after threshold is exceeded
    if (!isSwiping.value) {
        if (Math.abs(dx) < props.threshold || Math.abs(dx) < Math.abs(dy)) {
            return; // Let vertical scroll pass through
        }
        isSwiping.value = true;
    }

    // If already open, allow closing by swiping right
    if (isOpen.value) {
        const newOffset = -actionWidth.value + dx;
        offsetX.value = Math.min(0, newOffset);
    } else {
        // Only allow swiping left (negative direction)
        offsetX.value = Math.max(-actionWidth.value - 20, Math.min(0, dx - props.threshold));
    }
};

const onTouchEnd = () => {
    if (!isSwiping.value) return;
    isSwiping.value = false;

    if (isOpen.value) {
        // If swiped more than halfway back, close
        if (offsetX.value > -actionWidth.value / 2) {
            close();
        } else {
            // Snap back open
            isOpen.value = true;
            offsetX.value = -actionWidth.value;
        }
    } else {
        // If swiped past snap threshold, open
        if (offsetX.value < -props.snapThreshold) {
            isOpen.value = true;
            offsetX.value = -actionWidth.value;
        } else {
            // Snap closed
            close();
        }
    }
};

const haptic = useHaptic();

const confirmDelete = () => {
    if (confirming.value) {
        haptic.heavy();
        emit('delete');
        close();
    } else {
        haptic.light();
        confirming.value = true;
        // Auto-reset confirm after 3 seconds
        setTimeout(() => { confirming.value = false; }, 3000);
    }
};

// Expose close for parent use
defineExpose({ close });
</script>

<template>
    <div
        ref="containerRef"
        class="swipe-container relative overflow-hidden"
        @touchstart="onTouchStart"
        @touchmove="onTouchMove"
        @touchend="onTouchEnd"
        @touchcancel="close"
        style="touch-action: pan-y;"
    >
        <!-- Action area behind the content -->
        <div
            ref="actionRef"
            :style="actionStyle"
            class="absolute inset-y-0 right-0 z-0 flex items-stretch"
        >
            <button
                @click="confirmDelete"
                :class="[
                    'flex-1 flex items-center justify-center gap-1.5 font-bold text-white transition-all',
                    confirming
                        ? 'bg-red-600 text-[11px] px-3'
                        : 'bg-red-500 hover:bg-red-600 text-[10px]'
                ]"
            >
                <Trash2 class="w-4 h-4 shrink-0" />
                <span class="whitespace-nowrap">{{ confirming ? 'Confirm?' : confirmLabel }}</span>
            </button>
        </div>

        <!-- Content (slides left to reveal action) -->
        <div
            ref="contentRef"
            :style="contentStyle"
            class="relative z-10 bg-inherit"
        >
            <slot />
        </div>
    </div>
</template>
