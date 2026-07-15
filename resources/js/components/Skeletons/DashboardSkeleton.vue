<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
import StatCardSkeleton from './StatCardSkeleton.vue';
</script>

<template>
    <div class="flex h-full flex-col gap-4 md:gap-6 p-3 sm:p-8 animate-pulse">
        <!-- Hero section -->
        <div class="rounded-[24px] bg-card border border-border/50 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <Skeleton class="h-6 w-6 rounded-lg" />
                        <Skeleton class="h-3 w-20" />
                    </div>
                    <Skeleton class="h-7 w-48 mb-2" />
                    <Skeleton class="h-4 w-72" />
                </div>
                <Skeleton class="h-14 w-56 rounded-xl" />
            </div>
        </div>

        <!-- Stat cards (2x2 grid) -->
        <div class="grid grid-cols-2 gap-2.5 md:grid-cols-4 lg:gap-4">
            <div v-for="i in 4" :key="i">
                <StatCardSkeleton />
            </div>
        </div>

        <!-- Wallet strip -->
        <div>
            <div class="flex items-center justify-between mb-2 px-1">
                <Skeleton class="h-3 w-24" />
                <Skeleton class="h-3 w-16" />
            </div>
            <div class="flex gap-2.5 overflow-hidden">
                <div v-for="i in 3" :key="'wallet-'+i" class="shrink-0 w-[190px] sm:w-[260px] rounded-[16px] sm:rounded-[18px] p-3.5 sm:p-5 bg-muted/60">
                    <Skeleton class="h-3 w-16 mb-3" />
                    <Skeleton class="h-5 w-28 mb-4" />
                    <Skeleton class="h-7 w-32 mb-3" />
                    <Skeleton class="h-3 w-20 mb-2" />
                    <div class="flex gap-2 mt-2 pt-2 border-t border-border/20">
                        <Skeleton class="h-4 w-16 rounded-full" />
                        <Skeleton class="h-4 w-16 rounded-full" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Incomes strip -->
        <div>
            <div class="flex items-center justify-between mb-2 px-1">
                <Skeleton class="h-3 w-24" />
                <Skeleton class="h-3 w-16" />
            </div>
            <div class="flex gap-2.5 overflow-hidden">
                <div v-for="i in 2" :key="'inc-'+i" class="shrink-0 w-[170px] sm:w-[240px] rounded-[16px] sm:rounded-[18px] p-3.5 bg-muted/60">
                    <Skeleton class="h-4 w-24 mb-3" />
                    <Skeleton class="h-7 w-28 mb-2" />
                    <div class="flex justify-between pt-2 border-t border-border/20">
                        <Skeleton class="h-3 w-16" />
                        <Skeleton class="h-3 w-20" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Bills strip -->
        <div>
            <div class="flex items-center justify-between mb-2 px-1">
                <Skeleton class="h-3 w-24" />
                <Skeleton class="h-3 w-16" />
            </div>
            <div class="flex gap-2.5 overflow-hidden">
                <div v-for="i in 2" :key="'bill-'+i" class="shrink-0 w-[170px] sm:w-[240px] rounded-[16px] sm:rounded-[18px] p-3.5 bg-muted/60">
                    <Skeleton class="h-4 w-24 mb-3" />
                    <Skeleton class="h-7 w-28 mb-2" />
                    <div class="flex justify-between pt-2 border-t border-border/20">
                        <Skeleton class="h-3 w-16" />
                        <Skeleton class="h-3 w-20" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction chips + Income vs Expense -->
        <div class="grid gap-2.5 md:grid-cols-2">
            <div class="p-3.5 rounded-[18px] border border-border/40 bg-card flex flex-wrap gap-1.5">
                <Skeleton v-for="i in 4" :key="'chip-'+i" class="h-6 w-24 rounded-full" />
            </div>
            <div class="p-3.5 rounded-[18px] border border-border/40 bg-card">
                <Skeleton class="h-3 w-32 mb-2" />
                <Skeleton class="h-2 w-full rounded-full mb-1" />
                <div class="flex justify-between">
                    <Skeleton class="h-3 w-20" />
                    <Skeleton class="h-3 w-20" />
                </div>
            </div>
        </div>

        <!-- Rings row (3 col) -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5">
            <div v-for="i in 3" :key="'ring-'+i" class="p-3.5 sm:p-4 rounded-[18px] border border-border/40 bg-card flex flex-col items-center">
                <Skeleton class="h-3 w-24 mb-3" />
                <Skeleton class="h-20 w-20 sm:h-24 sm:w-24 rounded-full" />
                <Skeleton class="h-3 w-16 mt-2" />
            </div>
        </div>

        <!-- Sparkline + Top Expense + Streak -->
        <div class="grid gap-2.5 md:grid-cols-3">
            <div class="p-3.5 sm:p-4 rounded-[18px] border border-border/40 bg-card md:col-span-2">
                <Skeleton class="h-5 w-32 mb-1" />
                <Skeleton class="h-3 w-24 mb-3" />
                <Skeleton class="h-[120px] sm:h-[140px] w-full rounded-lg" />
            </div>
            <div class="grid grid-cols-2 gap-2.5 md:block md:space-y-3">
                <div class="p-3.5 sm:p-4 rounded-[18px] border border-border/40 bg-card">
                    <Skeleton class="h-3 w-24 mb-2" />
                    <Skeleton class="h-6 w-28 mb-1" />
                    <Skeleton class="h-4 w-20 mb-1" />
                    <Skeleton class="h-3 w-32" />
                </div>
                <div class="p-3.5 sm:p-4 rounded-[18px] border border-border/40 bg-card">
                    <Skeleton class="h-3 w-24 mb-2" />
                    <Skeleton class="h-8 w-12 mb-1" />
                    <Skeleton class="h-3 w-36" />
                </div>
            </div>
        </div>
    </div>
</template>
