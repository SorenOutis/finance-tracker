<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Plus,
    Wallet,
    CalendarClock,
    ArrowDownRight,
    ArrowUpRight,
} from 'lucide-vue-next';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const open = ref(false);
const rootRef = ref<HTMLElement | null>(null);

type Accent = 'indigo' | 'amber' | 'rose' | 'emerald';

interface QuickAddOption {
    key: string;
    href: string;
    label: string;
    sub: string;
    icon: typeof Wallet;
    accent: Accent;
}

const quickAddOptions: QuickAddOption[] = [
    { key: 'wallets-add-modal',  href: '/wallets',  label: 'Wallet',  sub: 'Account or card',   icon: Wallet,         accent: 'indigo'  },
    { key: 'expenses-add-modal', href: '/expenses', label: 'Expense', sub: 'One-time spend',    icon: ArrowUpRight,   accent: 'amber'   },
    { key: 'bills-add-modal',    href: '/bills',    label: 'Bill',    sub: 'Recurring expense', icon: CalendarClock,  accent: 'rose'    },
    { key: 'incomes-add-modal',  href: '/incomes',  label: 'Income',  sub: 'Recurring earning', icon: ArrowDownRight, accent: 'emerald' },
];

const accentClasses: Record<Accent, { iconBg: string; iconText: string }> = {
    indigo:  { iconBg: 'bg-indigo-500/10',  iconText: 'text-indigo-500'  },
    amber:   { iconBg: 'bg-amber-500/10',   iconText: 'text-amber-500'   },
    rose:    { iconBg: 'bg-rose-500/10',    iconText: 'text-rose-500'    },
    emerald: { iconBg: 'bg-emerald-500/10', iconText: 'text-emerald-500' },
};

const triggerAdd = (option: QuickAddOption) => {
    open.value = false;
    try {
        localStorage.setItem(option.key, 'true');
    } catch {
        /* ignore */
    }
    router.visit(option.href);
};

const toggle = () => { open.value = !open.value; };
const close = () => { open.value = false; };

const onKey = (e: KeyboardEvent) => {
    if (e.key === 'Escape') close();
};
const onPointerDown = (e: MouseEvent) => {
    if (!open.value) return;
    const target = e.target as Node;
    if (rootRef.value && !rootRef.value.contains(target)) close();
};

onMounted(() => {
    window.addEventListener('keydown', onKey);
    document.addEventListener('mousedown', onPointerDown);
});
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    document.removeEventListener('mousedown', onPointerDown);
});
</script>

<template>
    <div class="hidden md:block">
        <!-- Soft backdrop dim -->
        <Transition
            enter-active-class="transition-opacity duration-400 ease-out"
            leave-active-class="transition-opacity duration-300 ease-in"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                @click="close"
                class="fixed inset-0 z-[55]"
                aria-hidden="true"
            >
                <div
                    class="absolute inset-0"
                    style="background: radial-gradient(circle at calc(100% - 3.5rem) calc(100% - 3.5rem), rgba(0,0,0,0.30), rgba(0,0,0,0.10) 35%, transparent 65%);"
                />
            </div>
        </Transition>

        <!-- Fixed 56x56 anchor: the FAB position never changes -->
        <div
            ref="rootRef"
            :data-open="open"
            class="qa-root fixed bottom-6 right-6 z-[60] h-14 w-14"
        >
            <!-- Glass shell: anchored to bottom-right of the anchor; morphs out from behind the FAB -->
            <div class="qa-shell pointer-events-auto absolute bottom-0 right-0 border border-white/20 dark:border-white/10 bg-background/55 backdrop-blur-2xl supports-[backdrop-filter]:bg-background/35">
                <!-- Glass top-left specular highlight -->
                <span
                    class="pointer-events-none absolute inset-0 rounded-[inherit]"
                    style="background: radial-gradient(120% 80% at 0% 0%, rgba(255,255,255,0.22), rgba(255,255,255,0) 55%);"
                    aria-hidden="true"
                />
                <!-- Tinted bottom-right glow -->
                <span
                    class="pointer-events-none absolute inset-0 rounded-[inherit] opacity-70"
                    style="background: radial-gradient(120% 100% at 100% 100%, hsl(var(--primary) / 0.18), transparent 55%);"
                    aria-hidden="true"
                />
                <!-- Inner glass hairline -->
                <span
                    class="pointer-events-none absolute inset-0 rounded-[inherit] ring-1 ring-inset ring-white/10"
                    aria-hidden="true"
                />

                <!-- Expanded content (rendered always; opacity is the only thing animated) -->
                <div class="qa-content absolute inset-0 flex flex-col p-4 pr-[4.5rem] pb-[4.5rem]">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="leading-tight">
                            <h3 class="text-[13px] font-semibold tracking-tight text-foreground">Quick Add</h3>
                            <p class="text-[10px] font-medium text-muted-foreground">Create something new</p>
                        </div>
                        <kbd class="inline-flex items-center rounded-md border border-border/60 bg-muted/50 px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                            Esc
                        </kbd>
                    </div>

                    <div class="grid flex-1 grid-cols-2 gap-2">
                        <button
                            v-for="(opt, i) in quickAddOptions"
                            :key="opt.key"
                            @click="triggerAdd(opt)"
                            :style="{ '--qa-i': i } as any"
                            :class="[
                                'qa-item group relative flex items-center gap-2 overflow-hidden rounded-2xl border border-border/40 bg-card/40 p-2.5 text-left',
                                'hover:-translate-y-0.5 hover:border-border hover:bg-card/70 active:translate-y-0 active:scale-[0.97]',
                            ]"
                        >
                            <div
                                :class="[
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110',
                                    accentClasses[opt.accent].iconBg,
                                    accentClasses[opt.accent].iconText,
                                ]"
                            >
                                <component :is="opt.icon" class="h-4 w-4" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-[11px] font-semibold tracking-tight text-foreground">{{ opt.label }}</p>
                                <p class="truncate text-[9px] leading-tight text-muted-foreground">{{ opt.sub }}</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- The FAB itself: never moves, never resizes — pinned to the anchor -->
            <button
                @click="toggle"
                :aria-expanded="open"
                aria-label="Quick add"
                class="qa-fab absolute inset-0 flex items-center justify-center rounded-full bg-primary text-primary-foreground shadow-[0_12px_30px_-10px_hsl(var(--primary)/0.65)] outline-none ring-4 ring-background hover:scale-105 active:scale-95"
            >
                <span
                    class="pointer-events-none absolute inset-0 rounded-full"
                    style="background: radial-gradient(circle at 30% 25%, rgba(255,255,255,0.4), transparent 55%);"
                    aria-hidden="true"
                />
                <Plus class="qa-plus relative h-5 w-5" />
            </button>
        </div>
    </div>
</template>

<style scoped>
/* ------------------------------------------------------------------ */
/* Glass shell: morphs from a 56x56 circle hidden behind the FAB into  */
/* a 320x296 rounded card. Uses transform-based scaling on a fixed-    */
/* sized panel to avoid layout thrash / flicker.                       */
/* ------------------------------------------------------------------ */

.qa-shell {
    /* Final (open) size */
    width: 320px;
    height: 296px;
    border-radius: 28px;
    /* Origin matches the FAB so it scales out from the corner          */
    transform-origin: bottom right;
    transform: scale(0.175); /* 56 / 320 ≈ 0.175 */
    opacity: 0;
    box-shadow: 0 24px 60px -18px rgba(0, 0, 0, 0.45);
    /* Single, unified easing */
    transition:
        transform 520ms cubic-bezier(0.22, 1, 0.36, 1),
        opacity   260ms ease-out,
        border-radius 520ms cubic-bezier(0.22, 1, 0.36, 1);
    will-change: transform, opacity;
    pointer-events: none;
}

.qa-root[data-open='true'] .qa-shell {
    transform: scale(1);
    opacity: 1;
    pointer-events: auto;
}

/* Content fades only after the shell has visually expanded            */
.qa-content {
    opacity: 0;
    transform: translateY(4px);
    transition:
        opacity   220ms ease-out,
        transform 260ms cubic-bezier(0.22, 1, 0.36, 1);
    pointer-events: none;
}
.qa-root[data-open='true'] .qa-content {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 220ms;
    pointer-events: auto;
}

/* Tile entrance: subtle stagger via --qa-i custom prop                 */
.qa-item {
    opacity: 0;
    transform: translateY(6px) scale(0.96);
    transition:
        opacity   260ms ease-out,
        transform 320ms cubic-bezier(0.22, 1, 0.36, 1),
        background-color 200ms ease,
        border-color 200ms ease,
        box-shadow 200ms ease;
}
.qa-root[data-open='true'] .qa-item {
    opacity: 1;
    transform: translateY(0) scale(1);
    transition-delay: calc(260ms + var(--qa-i, 0) * 55ms);
}

/* FAB icon rotates on open                                            */
.qa-plus {
    transition: transform 460ms cubic-bezier(0.22, 1, 0.36, 1);
}
.qa-root[data-open='true'] .qa-plus {
    transform: rotate(135deg);
}

/* FAB itself uses transform only, never resizes                       */
.qa-fab {
    transition:
        transform 260ms cubic-bezier(0.22, 1, 0.36, 1),
        box-shadow 260ms ease;
    will-change: transform;
}

@media (prefers-reduced-motion: reduce) {
    .qa-shell,
    .qa-content,
    .qa-item,
    .qa-plus,
    .qa-fab {
        transition: none !important;
    }
}
</style>
