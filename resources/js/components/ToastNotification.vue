<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { CheckCheck, AlertTriangle, Info, X, AlertCircle } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();

const typeConfig: Record<string, { icon: any; bg: string; border: string; iconBg: string; iconColor: string; titleColor: string; msgColor: string }> = {
    success: {
        icon: CheckCheck,
        bg: 'bg-emerald-500/10',
        border: 'border-emerald-500/20',
        iconBg: 'bg-emerald-500/20',
        iconColor: 'text-emerald-500',
        titleColor: 'text-emerald-600',
        msgColor: 'text-emerald-600/70',
    },
    warning: {
        icon: AlertTriangle,
        bg: 'bg-amber-500/10',
        border: 'border-amber-500/20',
        iconBg: 'bg-amber-500/20',
        iconColor: 'text-amber-500',
        titleColor: 'text-amber-600',
        msgColor: 'text-amber-600/70',
    },
    info: {
        icon: Info,
        bg: 'bg-blue-500/10',
        border: 'border-blue-500/20',
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-500',
        titleColor: 'text-blue-600',
        msgColor: 'text-blue-600/70',
    },
    error: {
        icon: AlertCircle,
        bg: 'bg-rose-500/10',
        border: 'border-rose-500/20',
        iconBg: 'bg-rose-500/20',
        iconColor: 'text-rose-500',
        titleColor: 'text-rose-600',
        msgColor: 'text-rose-600/70',
    },
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed sm:top-4 sm:bottom-auto bottom-4 right-4 z-[9999] flex flex-col sm:flex-col-reverse gap-2 pointer-events-none">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 sm:-translate-y-4 translate-y-4 scale-95"
                leave-active-class="transition duration-200 ease-in"
                leave-to-class="opacity-0 translate-x-4 scale-95"
                move-class="transition duration-300 ease-out"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto flex items-center gap-2.5 p-3 rounded-xl border shadow-lg max-w-[360px] w-full',
                        typeConfig[toast.type]?.bg || 'bg-muted/20',
                        typeConfig[toast.type]?.border || 'border-border/30',
                    ]"
                >
                    <div
                        :class="[
                            'p-1 rounded-lg shrink-0',
                            typeConfig[toast.type]?.iconBg || 'bg-muted/30',
                            typeConfig[toast.type]?.iconColor || 'text-muted-foreground',
                        ]"
                    >
                        <component :is="typeConfig[toast.type]?.icon || Info" class="w-3.5 h-3.5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            :class="[
                                'text-[10px] font-bold',
                                typeConfig[toast.type]?.titleColor || 'text-foreground',
                            ]"
                        >
                            {{ toast.title }}
                        </p>
                        <p
                            v-if="toast.message"
                            :class="[
                                'text-[9px] font-medium leading-tight',
                                typeConfig[toast.type]?.msgColor || 'text-muted-foreground',
                            ]"
                        >
                            {{ toast.message }}
                        </p>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="p-0.5 rounded hover:bg-black/5 dark:hover:bg-white/10 transition-colors shrink-0"
                    >
                        <X class="w-3 h-3 text-muted-foreground/60" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
