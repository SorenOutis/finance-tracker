<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 animate-pulse">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <Skeleton class="h-6 w-6 rounded-lg" />
                    <Skeleton class="h-3 w-20" />
                </div>
                <Skeleton class="h-7 w-40 mb-2" />
                <Skeleton class="h-4 w-64" />
            </div>
            <Skeleton class="h-10 w-36 rounded-xl" />
        </div>
        <div class="flex flex-wrap gap-2 mt-3">
            <Skeleton v-for="i in 3" :key="i" class="h-8 w-28 rounded-full" />
        </div>
    </div>
</template>
