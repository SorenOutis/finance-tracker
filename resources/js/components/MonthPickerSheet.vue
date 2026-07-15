<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight, Calendar, X } from 'lucide-vue-next';
import { useHaptic } from '@/composables/useHaptic';

const props = defineProps<{
    open: boolean;
    /** Current selected date string (YYYY-MM-DD) */
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'update:modelValue', value: string): void;
    (e: 'select', value: string): void;
}>();

const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Parse current value to extract year/month
const currentDate = computed(() => new Date(props.modelValue || Date.now()));
const selectedYear = ref(currentDate.value.getFullYear());
const selectedMonth = ref(currentDate.value.getMonth()); // 0-indexed
const viewYear = ref(selectedYear.value);

// Watch for model value changes from outside
watch(() => props.modelValue, (val) => {
    if (val) {
        const d = new Date(val);
        selectedYear.value = d.getFullYear();
        selectedMonth.value = d.getMonth();
        viewYear.value = d.getFullYear();
    }
});

// Compute visible years around the current year
const yearRangeStart = computed(() => {
    const base = Math.floor(viewYear.value / 12) * 12;
    return Math.max(base, 2020);
});

const yearRangeEnd = computed(() => Math.min(yearRangeStart.value + 11, 2035));

const years = computed(() => {
    const arr: number[] = [];
    for (let y = yearRangeStart.value; y <= yearRangeEnd.value; y++) {
        arr.push(y);
    }
    return arr;
});

const goToPrevYearGroup = () => {
    viewYear.value = Math.max(viewYear.value - 12, 2020);
};

const goToNextYearGroup = () => {
    viewYear.value = Math.min(viewYear.value + 12, 2035);
};

const selectMonth = (monthIndex: number) => {
    selectedMonth.value = monthIndex;
};

const haptic = useHaptic();

const confirmSelection = () => {
    haptic.medium();
    // Build YYYY-MM-DD string for the first of the selected month
    const y = selectedYear.value;
    const m = String(selectedMonth.value + 1).padStart(2, '0');
    const dateStr = `${y}-${m}-01`;

    emit('update:modelValue', dateStr);
    emit('select', dateStr);
    emit('update:open', false);
};

const close = () => {
    emit('update:open', false);
};

// Check if a month index is currently selected
const isSelected = (monthIndex: number) => {
    return selectedMonth.value === monthIndex;
};

// Check if a month is "current" (today's month)
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();

const isCurrentMonth = (year: number, monthIndex: number) => {
    return year === currentYear && monthIndex === currentMonth;
};
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
                        <span class="text-sm font-bold text-foreground">Select Month</span>
                    </div>
                    <button
                        @click="close"
                        class="rounded-full p-1.5 text-muted-foreground transition-colors hover:bg-muted/60 hover:text-foreground"
                        aria-label="Close"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Year navigator -->
                <div class="flex items-center justify-between px-5 mt-4">
                    <button
                        @click="goToPrevYearGroup"
                        :disabled="yearRangeStart <= 2020"
                        class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/40 hover:text-foreground disabled:opacity-30 disabled:pointer-events-none"
                    >
                        <ChevronLeft class="h-4 w-4" />
                        <span class="hidden sm:inline">Previous</span>
                    </button>

                    <div class="flex gap-1">
                        <span class="text-sm font-bold text-foreground">{{ yearRangeStart }}</span>
                        <span class="text-sm font-bold text-muted-foreground">—</span>
                        <span class="text-sm font-bold text-foreground">{{ yearRangeEnd }}</span>
                    </div>

                    <button
                        @click="goToNextYearGroup"
                        :disabled="yearRangeEnd >= 2035"
                        class="flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-muted-foreground transition-all hover:bg-muted/40 hover:text-foreground disabled:opacity-30 disabled:pointer-events-none"
                    >
                        <span class="hidden sm:inline">Next</span>
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>

                <!-- Scrollable body -->
                <div class="px-5 pt-4 pb-2">
                    <!-- Year chips -->
                    <div class="flex gap-1.5 mb-4 overflow-x-auto pb-1 scrollbar-hide -mx-5 px-5">
                        <button
                            v-for="year in years"
                            :key="year"
                            @click="selectedYear = year; viewYear = year"
                            :class="[
                                'shrink-0 rounded-full px-3 py-1 text-[11px] font-bold transition-all active:scale-95',
                                selectedYear === year
                                    ? 'bg-primary text-primary-foreground shadow-sm'
                                    : 'border border-border/40 bg-muted/20 text-muted-foreground hover:border-border hover:bg-muted/40',
                            ]"
                        >
                            {{ year }}
                        </button>
                    </div>

                    <!-- Month grid -->
                    <div class="grid grid-cols-4 gap-2 pb-2">
                        <button
                            v-for="(month, idx) in shortMonths"
                            :key="idx"
                            @click="selectMonth(idx)"
                            :class="[
                                'relative flex flex-col items-center justify-center rounded-2xl border py-3 transition-all active:scale-95',
                                selectedMonth === idx
                                    ? 'border-primary/40 bg-primary/5'
                                    : 'border-border/30 bg-muted/10 hover:border-border hover:bg-muted/30',
                            ]"
                        >
                            <!-- Current month dot indicator -->
                            <span
                                v-if="isCurrentMonth(selectedYear, idx)"
                                class="absolute -top-0.5 right-1.5 h-1.5 w-1.5 rounded-full bg-primary"
                            />
                            <span
                                :class="[
                                    'text-[11px] font-bold transition-colors',
                                    selectedMonth === idx
                                        ? 'text-primary'
                                        : 'text-foreground',
                                ]"
                            >
                                {{ month }}
                            </span>
                        </button>
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
                        @click="confirmSelection"
                        class="flex-1 rounded-xl bg-primary py-3 text-[12px] font-bold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:bg-primary/90 active:scale-[0.98]"
                    >
                        View Month
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
