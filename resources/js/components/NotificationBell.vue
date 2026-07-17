<script setup lang="ts">
import {
    Bell, BellRing, AlertTriangle, Target, TrendingUp,
    CheckCheck
} from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

interface Notification {
    id: string;
    type: string;
    title: string;
    message: string;
    severity: 'danger' | 'warning' | 'success' | 'info';
    action_url: string;
    created_at: string;
}

const showPanel = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);

const severityColors: Record<string, { bg: string; text: string; dot: string }> = {
    danger: { bg: 'bg-rose-500/10', text: 'text-rose-500', dot: 'bg-rose-500' },
    warning: { bg: 'bg-amber-500/10', text: 'text-amber-500', dot: 'bg-amber-500' },
    success: { bg: 'bg-emerald-500/10', text: 'text-emerald-500', dot: 'bg-emerald-500' },
    info: { bg: 'bg-blue-500/10', text: 'text-blue-500', dot: 'bg-blue-500' },
};

const typeIcons: Record<string, any> = {
    bill_due: AlertTriangle,
    budget_alert: AlertTriangle,
    goal_complete: CheckCheck,
    goal_milestone: Target,
    insight: TrendingUp,
};

const fetchNotifications = async () => {
    try {
        const resp = await fetch('/notifications');
        const data = await resp.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch {
        // Silent fail — notification bell shows 0
    }
};

const togglePanel = () => {
    showPanel.value = !showPanel.value;
    if (showPanel.value && !notifications.value.length) {
        fetchNotifications();
    }
};

const getTypeIcon = (type: string) => typeIcons[type] || BellRing;

const uniqueTypes = computed(() => {
    const types = new Set(notifications.value.map(n => n.type));
    return Array.from(types);
});

onMounted(() => {
    fetchNotifications();
    // Poll every 5 minutes
    setInterval(fetchNotifications, 5 * 60 * 1000);
});
</script>

<template>
    <div class="relative">
        <button @click.stop="togglePanel" class="relative p-1.5 rounded-lg bg-card border border-border/40 hover:bg-accent/30 transition-all cursor-pointer" aria-label="Notifications">
            <Bell class="w-3.5 h-3.5 text-muted-foreground" />
            <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-[7px] font-bold text-white flex items-center justify-center ring-2 ring-card">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div v-if="showPanel" class="absolute top-full right-0 mt-2 w-[360px] rounded-2xl border border-border/40 bg-card shadow-2xl z-50 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-border/20">
                    <div class="flex items-center gap-2">
                        <BellRing class="w-4 h-4 text-primary" />
                        <span class="text-sm font-bold">Notifications</span>
                        <span v-if="unreadCount" class="text-[9px] px-1.5 py-0.5 rounded-full bg-primary/10 text-primary font-bold">{{ unreadCount }}</span>
                    </div>
                    <button @click="fetchNotifications" class="text-[10px] font-semibold text-primary hover:underline">Refresh</button>
                </div>

                <div v-if="notifications.length" class="max-h-[360px] overflow-y-auto">
                    <div v-for="n in notifications" :key="n.id" class="flex items-start gap-3 px-4 py-3 hover:bg-accent/20 transition-colors cursor-pointer border-b border-border/10 last:border-0">
                        <div :class="['p-1.5 rounded-lg shrink-0', severityColors[n.severity]?.bg || 'bg-muted/30']">
                            <component :is="getTypeIcon(n.type)" :class="['w-3.5 h-3.5', severityColors[n.severity]?.text || 'text-muted-foreground']" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold text-foreground">{{ n.title }}</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">{{ n.message }}</p>
                        </div>
                        <div class="w-2 h-2 rounded-full shrink-0 mt-1.5" :class="severityColors[n.severity]?.dot || 'bg-muted-foreground'"></div>
                    </div>
                </div>
                <div v-else class="px-4 py-8 text-center">
                    <Bell class="w-8 h-8 text-muted-foreground/20 mx-auto mb-2" />
                    <p class="text-[11px] text-muted-foreground">No new notifications</p>
                </div>

                <div v-if="notifications.length" class="px-4 py-2.5 border-t border-border/20 bg-muted/20">
                    <p class="text-[9px] text-muted-foreground text-center">{{ uniqueTypes.length }} notification type{{ uniqueTypes.length !== 1 ? 's' : '' }}</p>
                </div>
            </div>
        </Transition>
    </div>
</template>
