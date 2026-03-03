<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { analytics } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { formatCurrency } from '@/utils/currency';

interface TrendItem {
    month: string;
    income: number;
    expenses: number;
}

interface CategoryItem {
    name: string;
    amount: number;
    percentage: number;
}

interface Props {
    trendData: TrendItem[];
    categoryData: CategoryItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Analytics',
        href: analytics(),
    },
];

const categoryDataWithColor = computed(() =>
    props.categoryData.map((item, index) => ({
        ...item,
        color: '#a855f7',
    }))
);
</script>

<template>
    <Head title="Analytics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Income vs Expenses Trend -->
             <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                 <h2 class="mb-1 text-xl font-bold">Income vs Expenses Trend</h2>
                 <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Monthly comparison throughout the year</p>

                <!-- SVG Line Chart -->
                <svg viewBox="0 0 1200 350" class="w-full">
                    <!-- Grid lines -->
                    <line x1="60" y1="50" x2="60" y2="300" stroke="#374151" stroke-width="2" />
                    <line x1="60" y1="300" x2="1150" y2="300" stroke="#374151" stroke-width="2" />

                    <!-- Y-axis labels -->
                    <text x="40" y="55" font-size="12" fill="#9CA3AF" text-anchor="end">6000</text>
                    <text x="40" y="105" font-size="12" fill="#9CA3AF" text-anchor="end">4500</text>
                    <text x="40" y="155" font-size="12" fill="#9CA3AF" text-anchor="end">3000</text>
                    <text x="40" y="205" font-size="12" fill="#9CA3AF" text-anchor="end">1500</text>
                    <text x="40" y="305" font-size="12" fill="#9CA3AF" text-anchor="end">0</text>

                    <!-- X-axis labels -->
                    <text x="90" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jan</text>
                    <text x="185" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Feb</text>
                    <text x="280" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Mar</text>
                    <text x="375" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Apr</text>
                    <text x="470" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">May</text>
                    <text x="565" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jun</text>
                    <text x="660" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jul</text>
                    <text x="755" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Aug</text>
                    <text x="850" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Sep</text>
                    <text x="945" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Oct</text>
                    <text x="1040" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Nov</text>
                    <text x="1135" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Dec</text>

                    <!-- Income line (cyan/teal) -->
                    <polyline
                        points="90,300 185,300 280,300 375,300 470,300 565,300 660,300 755,300 850,300 945,300 1040,300 1135,300"
                        fill="none"
                        stroke="#06b6d4"
                        stroke-width="3"
                        stroke-linejoin="round"
                        stroke-linecap="round"
                    />

                    <!-- Income data points -->
                    <circle cx="90" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="185" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="280" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="375" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="470" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="565" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="660" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="755" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="850" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="945" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="1040" cy="300" r="5" fill="#06b6d4" />
                    <circle cx="1135" cy="300" r="5" fill="#06b6d4" />

                    <!-- Expenses line (red) -->
                    <polyline
                        points="90,300 185,300 280,300 375,300 470,300 565,300 660,300 755,300 850,300 945,300 1040,300 1135,300"
                        fill="none"
                        stroke="#ef4444"
                        stroke-width="3"
                        stroke-linejoin="round"
                        stroke-linecap="round"
                    />

                    <!-- Expenses data points -->
                    <circle cx="90" cy="300" r="5" fill="#ef4444" />
                    <circle cx="185" cy="300" r="5" fill="#ef4444" />
                    <circle cx="280" cy="300" r="5" fill="#ef4444" />
                    <circle cx="375" cy="300" r="5" fill="#ef4444" />
                    <circle cx="470" cy="300" r="5" fill="#ef4444" />
                    <circle cx="565" cy="300" r="5" fill="#ef4444" />
                    <circle cx="660" cy="300" r="5" fill="#ef4444" />
                    <circle cx="755" cy="300" r="5" fill="#ef4444" />
                    <circle cx="850" cy="300" r="5" fill="#ef4444" />
                    <circle cx="945" cy="300" r="5" fill="#ef4444" />
                    <circle cx="1040" cy="300" r="5" fill="#ef4444" />
                    <circle cx="1135" cy="300" r="5" fill="#ef4444" />
                </svg>

                <!-- Legend -->
                <div class="mt-6 flex justify-center gap-8">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full" style="background-color: #06b6d4" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">income</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded-full" style="background-color: #ef4444" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">expenses</span>
                    </div>
                </div>
                </div>

            <!-- Monthly Net Income -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                <h2 class="mb-1 text-xl font-bold">Monthly Net Income</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Your earnings after expenses</p>

                <!-- SVG Bar Chart -->
                <svg viewBox="0 0 1200 350" class="w-full">
                    <!-- Grid lines -->
                    <line x1="60" y1="50" x2="60" y2="300" stroke="#374151" stroke-width="2" />
                    <line x1="60" y1="300" x2="1150" y2="300" stroke="#374151" stroke-width="2" />

                    <!-- Y-axis labels -->
                    <text x="40" y="55" font-size="12" fill="#9CA3AF" text-anchor="end">3000</text>
                    <text x="40" y="155" font-size="12" fill="#9CA3AF" text-anchor="end">1500</text>
                    <text x="40" y="255" font-size="12" fill="#9CA3AF" text-anchor="end">0</text>

                    <!-- X-axis labels -->
                    <text x="90" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jan</text>
                    <text x="185" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Feb</text>
                    <text x="280" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Mar</text>
                    <text x="375" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Apr</text>
                    <text x="470" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">May</text>
                    <text x="565" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jun</text>
                    <text x="660" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Jul</text>
                    <text x="755" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Aug</text>
                    <text x="850" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Sep</text>
                    <text x="945" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Oct</text>
                    <text x="1040" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Nov</text>
                    <text x="1135" y="325" font-size="12" fill="#9CA3AF" text-anchor="middle">Dec</text>

                    <!-- Bars -->
                    <rect x="75" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="170" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="265" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="360" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="455" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="550" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="645" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="740" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="835" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="930" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="1025" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                    <rect x="1120" y="300" width="30" height="0" fill="#a855f7" rx="4" />
                </svg>
            </div>

            <!-- Expenses by Category -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-black dark:border-slate-600 dark:bg-black dark:text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="mb-1 text-xl font-bold">Expenses by Category</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Breakdown of your spending</p>
                    </div>
                    <div v-if="props.categoryData.length > 0" class="text-right">
                         <p class="text-sm text-gray-600 dark:text-gray-400">{{ props.categoryData[0].name }}</p>
                         <p class="text-2xl font-bold">{{ formatCurrency(props.categoryData[0].amount) }}</p>
                     </div>
                </div>

                <!-- Pie Chart Placeholder -->
                <div class="mt-8 flex justify-center">
                    <svg viewBox="0 0 200 200" class="h-64 w-64">
                        <!-- Circle background -->
                        <circle cx="100" cy="100" r="80" fill="#a855f7" opacity="0.9" />
                        <!-- Inner circle for donut effect -->
                        <circle cx="100" cy="100" r="50" fill="#000000" />
                        <!-- Percentage text -->
                        <text
                            x="100"
                            y="110"
                            font-size="20"
                            fill="#a855f7"
                            text-anchor="middle"
                            opacity="0.6"
                        >
                            100%
                        </text>
                    </svg>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
