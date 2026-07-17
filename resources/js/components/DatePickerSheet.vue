<script setup lang="ts">
import { ChevronLeft, ChevronRight, Calendar, X } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { useHaptic } from '@/composables/useHaptic';

const props = defineProps<{
    open: boolean;
    /** Current date string (YYYY-MM-DD) */
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'update:modelValue', value: string): void;
    (e: 'select', value: string): void;
}>();

const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];

// Parse current value
const today = new Date();
today.setHours(0, 0, 0, 0);

const initialDate = props.modelValue ? new Date(props.modelValue + 'T12:00:00') : new Date();
const viewYear = ref(initialDate.getFullYear());
const viewMonth = ref(initialDate.getMonth());
const selectedDay = ref(initialDate.getDate());

// Store the actual date being built
const buildYear = ref(initialDate.getFullYear());
const buildMonth = ref(initialDate.getMonth());
const buildDay = ref(initialDate.getDate());

// Sync when props change
watch(() => props.modelValue, (val) => {
    if (val) {
        const d = new Date(val + 'T12:00:00');
        buildYear.value = d.getFullYear();
        buildMonth.value = d.getMonth();
        buildDay.value = d.getDate();
        viewYear.value = d.getFullYear();
        viewMonth.value = d.getMonth();
        selectedDay.value = d.getDate();
    }
});

// Calendar grid generation
const daysInMonth = computed(() => new Date(viewYear.value, viewMonth.value + 1, 0).getDate());
const firstDayOfWeek = computed(() => new Date(viewYear.value, viewMonth.value, 1).getDay());

const calendarDays = computed(() => {
    const days: (number | null)[] = [];
    // Leading empty cells
    for (let i = 0; i < firstDayOfWeek.value; i++) {
        days.push(null);
    }
    // Actual days
    for (let d = 1; d <= daysInMonth.value; d++) {
        days.push(d);
    }
    return days;
});

const isToday = (day: number) => {
    return viewYear.value === today.getFullYear() &&
        viewMonth.value === today.getMonth() &&
        day === today.getDate();
};

const isSelected = (day: number) => {
    return buildYear.value === viewYear.value &&
        buildMonth.value === viewMonth.value &&
        buildDay.value === day;
};

// Navigation
const goToPrevMonth = () => {
    if (viewMonth.value === 0) {
        viewYear.value--;
        viewMonth.value = 11;
    } else {
        viewMonth.value--;
    }
};

const goToNextMonth = () => {
    if (viewMonth.value === 11) {
        viewYear.value++;
        viewMonth.value = 0;
    } else {
        viewMonth.value++;
    }
};

const goToToday = () => {
    haptic.medium();
    viewYear.value = today.getFullYear();
    viewMonth.value = today.getMonth();
    selectedDay.value = today.getDate();
    buildYear.value = today.getFullYear();
    buildMonth.value = today.getMonth();
    buildDay.value = today.getDate();
    confirmSelection(today.getFullYear(), today.getMonth(), today.getDate());
};

const goToYesterday = () => {
    haptic.medium();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    viewYear.value = yesterday.getFullYear();
    viewMonth.value = yesterday.getMonth();
    selectedDay.value = yesterday.getDate();
    buildYear.value = yesterday.getFullYear();
    buildMonth.value = yesterday.getMonth();
    buildDay.value = yesterday.getDate();
    confirmSelection(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate());
};

// Select a day
const selectDay = (day: number) => {
    selectedDay.value = day;
    buildYear.value = viewYear.value;
    buildMonth.value = viewMonth.value;
    buildDay.value = day;
};

const confirmSelection = (year?: number, month?: number, day?: number) => {
    if (!year && !month && !day) haptic.medium();
    const y = year ?? buildYear.value;
    const m = month ?? buildMonth.value;
    const d = day ?? buildDay.value;
    const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    emit('update:modelValue', dateStr);
    emit('select', dateStr);
    emit('update:open', false);
};

const formatMonthYear = computed(() =>
    `${monthNames[viewMonth.value]} ${viewYear.value}`
);

const haptic = useHaptic();

const close = () => emit('update:open', false);
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm md:hidden"
                @click="close"
            />
        </Transition>

        <!-- Sheet -->
        <Transition
            enter-active-class="transition duration-300 cubic-bezier(0.16, 1, 0.3, 1)"
            enter-from-class="translate-y-full"
            enter-to-class="translate-y-0"
            leave-active-class="transition duration-250 ease-in"
            leave-from-class="translate-y-0"
            leave-to-class="translate-y-full"
        >
            <div
                v-if="open"
                class="fixed bottom-0 left-0 right-0 z-50 mx-auto w-full max-w-lg rounded-t-[24px] bg-background border border-border/40 shadow-2xl md:hidden"
                style="padding-bottom: max(1rem, env(safe-area-inset-bottom));"
            >
                <!-- Drag handle -->
                <div class="flex justify-center pt-2">
                    <span class="h-1.5 w-10 rounded-full bg-muted" aria-hidden="true" />
                </div>

                <!-- Header -->
                <div class="flex items-center justify-between px-5 pt-3">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 rounded-lg bg-primary/10">
                            <Calendar class="h-4 w-4 text-primary" />
                        </div>
                        <span class="text-sm font-bold text-foreground">Select Date</span>
                    </div>
                    <button
                        @click="close"
                        class="rounded-full p-1.5 text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                        aria-label="Close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Quick selects -->
                <div class="flex gap-2 px-5 mt-4">
                    <button
                        @click="goToToday"
                        class="flex-1 rounded-xl border border-border/50 bg-muted/15 py-2.5 text-[11px] font-bold text-foreground transition-all hover:bg-muted/30 active:scale-95"
                    >
                        Today
                    </button>
                    <button
                        @click="goToYesterday"
                        class="flex-1 rounded-xl border border-border/50 bg-muted/15 py-2.5 text-[11px] font-bold text-foreground transition-all hover:bg-muted/30 active:scale-95"
                    >
                        Yesterday
                    </button>
                </div>

                <!-- Month/Year navigator -->
                <div class="flex items-center justify-between px-5 mt-4">
                    <button
                        @click="goToPrevMonth"
                        class="flex items-center justify-center rounded-lg h-9 w-9 text-muted-foreground transition-colors hover:bg-muted/40 hover:text-foreground"
                        aria-label="Previous month"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>

                    <span class="text-sm font-bold text-foreground">{{ formatMonthYear }}</span>

                    <button
                        @click="goToNextMonth"
                        class="flex items-center justify-center rounded-lg h-9 w-9 text-muted-foreground transition-colors hover:bg-muted/40 hover:text-foreground"
                        aria-label="Next month"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>

                <!-- Calendar grid -->
                <div class="px-5 pt-3 pb-2">
                    <!-- Day headers -->
                    <div class="grid grid-cols-7 gap-0 mb-1">
                        <div
                            v-for="header in dayHeaders"
                            :key="header"
                            class="flex items-center justify-center py-1.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/60"
                        >
                            {{ header }}
                        </div>
                    </div>

                    <!-- Calendar days -->
                    <div class="grid grid-cols-7 gap-0">
                        <div
                            v-for="(day, idx) in calendarDays"
                            :key="idx"
                            class="flex items-center justify-center"
                        >
                            <button
                                v-if="day !== null"
                                @click="selectDay(day)"
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-full text-[13px] font-semibold transition-all active:scale-90',
                                    isSelected(day)
                                        ? 'bg-primary text-primary-foreground shadow-sm'
                                        : isToday(day)
                                            ? 'border-2 border-primary/40 text-foreground'
                                            : 'text-muted-foreground hover:bg-muted/30 hover:text-foreground',
                                ]"
                            >
                                {{ day }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer actions -->
                <div class="flex items-center gap-3 px-5 pt-3 pb-4 border-t border-border/30">
                    <button
                        @click="close"
                        class="flex-1 rounded-xl border border-border bg-background py-3 text-[12px] font-bold text-foreground transition-colors hover:bg-muted/40"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmSelection()"
                        class="flex-1 rounded-xl bg-primary py-3 text-[12px] font-bold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
                    >
                        Set Date
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
