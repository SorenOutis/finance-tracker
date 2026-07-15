<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{ count?: number }>(), { count: 6 });
</script>

<template>
    <div class="flex flex-col gap-5 md:gap-6 animate-pulse">
        <!-- Hero header -->
        <div class="rounded-[24px] bg-card border border-border/50 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <Skeleton class="h-6 w-6 rounded-lg" />
                        <Skeleton class="h-3 w-20" />
                    </div>
                    <Skeleton class="h-7 w-40 mb-2" />
                    <Skeleton class="h-4 w-56" />
                </div>
                <Skeleton class="h-10 w-32 rounded-xl" />
            </div>
        </div>

        <!-- Wallet cards grid -->
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
            <div
                v-for="i in count"
                :key="i"
                class="rounded-[18px] sm:rounded-[24px] p-3 sm:p-5 bg-muted/40"
            >
                <div class="flex items-start justify-between mb-3 sm:mb-4">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <Skeleton class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg sm:rounded-xl" />
                        <div>
                            <Skeleton class="h-4 w-20 sm:w-28 mb-1" />
                            <Skeleton class="h-3 w-12 sm:w-16" />
                        </div>
                    </div>
                </div>
                <Skeleton class="h-6 w-28 sm:w-36 mb-3" />
                <Skeleton class="h-8 w-32 sm:w-44 mb-3" />
                <Skeleton class="h-3 w-24 mb-2" />
                <div class="flex gap-2 mt-2 pt-2 border-t border-border/20">
                    <Skeleton class="h-4 w-16 rounded-full" />
                    <Skeleton class="h-4 w-16 rounded-full" />
                </div>
            </div>
        </div>
    </div>
</template>
