<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{ groupCount?: number; itemsPerGroup?: number }>(), {
    groupCount: 3,
    itemsPerGroup: 3,
});
</script>

<template>
    <div class="flex flex-col gap-4 animate-pulse">
        <!-- Stat strip -->
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
            <div v-for="i in 3" :key="i" class="rounded-2xl border border-border/50 bg-card p-3">
                <Skeleton class="h-3 w-16 mb-2" />
                <Skeleton class="h-5 w-20" />
            </div>
        </div>

        <!-- Search bar -->
        <Skeleton class="h-12 w-full rounded-2xl" />

        <!-- Grouped lists -->
        <template v-for="g in groupCount" :key="g">
            <div class="flex items-center justify-between mb-1 px-1">
                <Skeleton class="h-3 w-24" />
                <Skeleton class="h-3 w-12" />
            </div>

            <div class="flex flex-col gap-1.5">
                <div
                    v-for="j in itemsPerGroup"
                    :key="`${g}-${j}`"
                    class="flex items-center gap-3 rounded-2xl border border-border/40 bg-card p-3"
                >
                    <Skeleton class="h-10 w-10 rounded-xl shrink-0" />
                    <div class="flex-1 min-w-0">
                        <Skeleton class="h-4 w-28 mb-1" />
                        <Skeleton class="h-3 w-40" />
                    </div>
                    <Skeleton class="h-5 w-16 shrink-0" />
                </div>
            </div>
        </template>
    </div>
</template>
