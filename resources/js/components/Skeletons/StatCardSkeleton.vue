<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="group p-3.5 rounded-[18px] sm:p-4 sm:rounded-[20px] border border-border/40 bg-card animate-pulse">
        <div class="flex items-center justify-between mb-2">
            <Skeleton class="h-3 w-24" />
            <Skeleton class="h-3 w-12 rounded-full" />
        </div>
        <Skeleton class="h-7 w-28 mt-2" />
    </div>
</template>
