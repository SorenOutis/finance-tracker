<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{ count?: number }>(), { count: 6 });
</script>

<template>
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3 animate-fade-up stagger-3">
        <div
            v-for="i in count"
            :key="i"
            class="relative overflow-hidden rounded-[18px] sm:rounded-[24px] p-3 sm:p-5 bg-muted/40 animate-pulse"
        >
            <!-- Card header -->
            <div class="flex items-start justify-between mb-3 sm:mb-4">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <Skeleton class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg sm:rounded-xl" />
                    <div class="min-w-0">
                        <Skeleton class="h-4 w-20 sm:w-28 mb-1" />
                        <Skeleton class="h-3 w-14 sm:w-20" />
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="space-y-3 sm:space-y-4">
                <Skeleton class="h-7 w-24 sm:w-32" />
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between pt-3 sm:pt-4 border-t border-border/20">
                    <div>
                        <Skeleton class="h-3 w-16 mb-1" />
                        <Skeleton class="h-4 w-24" />
                    </div>
                    <Skeleton class="h-5 w-16 rounded-full" />
                </div>
            </div>
        </div>
    </div>
</template>
