<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    PiggyBank, Plus, Trash2, Target, Wallet, Home, ShoppingCart,
    Heart, MapPin, ShieldCheck, MoreHorizontal, ChevronRight, X,
    ArrowDownRight, ArrowUpRight, CreditCard, CalendarClock, ReceiptText, Check
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { formatCurrency } from '@/utils/currency';

interface AccountRef { id: number; name: string; type: string; color: string | null; }
interface Envelope {
    id: number; name: string; target_amount: number; current_amount: number;
    color: string | null; icon: string | null; deadline: string | null;
    account_id: number | null; sort_order: number; progress: number;
}

interface Props {
    envelopes: Envelope[];
    accounts: AccountRef[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Envelopes', href: '/envelopes' }];

const showForm = ref(false);
const showAllocate = ref(false);
const allocateEnv = ref<Envelope | null>(null);
const editingEnv = ref<Envelope | null>(null);

const colorPalette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#3B82F6', '#14B8A6', '#F97316'];
const iconOptions = ['PiggyBank', 'Home', 'ShoppingCart', 'Heart', 'MapPin', 'ShieldCheck', 'Target', 'Wallet'];

const iconMap: Record<string, any> = { PiggyBank, Home, ShoppingCart, Heart, MapPin, ShieldCheck, Target, Wallet };

const form = useForm({
    name: '', target_amount: '', current_amount: '', color: '#6366F1',
    icon: 'PiggyBank', deadline: '', account_id: '',
});

const allocateForm = useForm({ amount: '', from_account_id: '' });

const totalSaved = computed(() => props.envelopes.reduce((s, e) => s + e.current_amount, 0));
const totalTarget = computed(() => props.envelopes.reduce((s, e) => s + e.target_amount, 0));

const openAddForm = () => {
    editingEnv.value = null;
    form.reset(); form.color = '#6366F1'; form.icon = 'PiggyBank';
    showForm.value = true;
};

const openEditForm = (env: Envelope) => {
    editingEnv.value = env;
    form.name = env.name; form.target_amount = String(env.target_amount);
    form.current_amount = String(env.current_amount); form.color = env.color || '#6366F1';
    form.icon = env.icon || 'PiggyBank'; form.deadline = env.deadline || '';
    form.account_id = env.account_id ? String(env.account_id) : '';
    showForm.value = true;
};

const closeForm = () => { showForm.value = false; editingEnv.value = null; form.reset(); };

const saveEnvelope = () => {
    if (!form.name || !form.target_amount) return;
    if (editingEnv.value) {
        form.patch(`/envelopes/${editingEnv.value.id}`, { onSuccess: () => closeForm() });
    } else {
        form.post('/envelopes', { onSuccess: () => closeForm() });
    }
};

const deleteEnv = (id: number) => {
    if (!confirm('Delete this envelope?')) return;
    router.delete(`/envelopes/${id}`, { preserveScroll: true });
};

const openAllocate = (env: Envelope) => {
    allocateEnv.value = env;
    allocateForm.reset();
    allocateForm.from_account_id = env.account_id ? String(env.account_id) : '';
    showAllocate.value = true;
};

const doAllocate = () => {
    if (!allocateEnv.value || !allocateForm.amount) return;
    allocateForm.post(`/envelopes/${allocateEnv.value.id}/allocate`, {
        onSuccess: () => { showAllocate.value = false; allocateEnv.value = null; }
    });
};

const getIcon = (icon: string | null) => iconMap[icon || 'PiggyBank'] || PiggyBank;
</script>

<template>
    <Head title="Envelopes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 animate-fade-up stagger-1">
                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary"><PiggyBank class="w-3.5 h-3.5" /></div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Savings</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight font-display">Savings <span class="text-primary">Envelopes</span></h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium">Allocate money toward specific goals and track your progress</p>
                    </div>
                    <button @click="openAddForm" class="flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-[10px] font-bold text-primary-foreground hover:bg-primary/90 transition-all shadow-lg shadow-primary/20"><Plus class="w-3.5 h-3.5" /> New Envelope</button>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    <Link href="/wallets" class="flex items-center gap-1 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-2.5 py-1 text-[10px] font-bold transition-all"><CreditCard class="w-3 h-3" /> Wallets <ChevronRight class="w-2.5 h-2.5" /></Link>
                    <Link href="/dashboard" class="flex items-center gap-1 rounded-full border border-border/60 bg-muted/30 hover:bg-muted/60 px-2.5 py-1 text-[10px] font-bold transition-all"><Target class="w-3 h-3" /> Goals <ChevronRight class="w-2.5 h-2.5" /></Link>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-3 animate-fade-up stagger-2">
                <div class="surface-card p-3 text-center"><p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Envelopes</p><p class="text-lg font-black mt-1">{{ props.envelopes.length }}</p></div>
                <div class="surface-card p-3 text-center"><p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Saved</p><p class="text-lg font-black mt-1 text-emerald-500">{{ formatCurrency(totalSaved) }}</p></div>
                <div class="surface-card p-3 text-center"><p class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground">Progress</p><p class="text-lg font-black mt-1">{{ totalTarget > 0 ? Math.round((totalSaved / totalTarget) * 100) : 0 }}%</p></div>
            </div>

            <!-- Envelope Grid -->
            <div v-if="props.envelopes.length" class="grid grid-cols-2 lg:grid-cols-3 gap-3 animate-fade-up stagger-3">
                <div v-for="env in props.envelopes" :key="env.id" class="group relative p-4 rounded-xl border border-border/20 bg-card hover:shadow-md transition-all">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" :style="{ backgroundColor: env.color || '#6366F1' }">
                                <component :is="getIcon(env.icon)" class="w-4 h-4" />
                            </div>
                            <div>
                                <p class="text-[11px] font-bold truncate max-w-[100px]">{{ env.name }}</p>
                                <p v-if="env.deadline" class="text-[8px] text-muted-foreground">Due {{ env.deadline }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openAllocate(env)" class="p-1 rounded hover:bg-emerald-500/10 transition-colors" title="Add money"><ArrowDownRight class="w-3 h-3 text-emerald-500" /></button>
                            <button @click="openEditForm(env)" class="p-1 rounded hover:bg-muted/40 transition-colors"><svg class="w-3 h-3 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
                            <button @click="deleteEnv(env.id)" class="p-1 rounded hover:bg-destructive/10 transition-colors"><Trash2 class="w-3 h-3 text-destructive/70" /></button>
                        </div>
                    </div>
                    <p class="text-lg font-black font-mono" :style="{ color: env.color || '#6366F1' }">{{ formatCurrency(env.current_amount) }}</p>
                    <div class="mt-2 h-2 rounded-full bg-muted/30 overflow-hidden">
                        <div class="h-full rounded-full transition-all" :style="{ width: Math.min(100, (env.current_amount / Math.max(1, env.target_amount)) * 100) + '%', backgroundColor: env.color || '#6366F1' }"></div>
                    </div>
                    <div class="flex justify-between mt-1"><span class="text-[9px] text-muted-foreground">{{ Math.round((env.current_amount / Math.max(1, env.target_amount)) * 100) }}%</span><span class="text-[9px] text-muted-foreground font-mono">{{ formatCurrency(env.target_amount) }}</span></div>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-16 animate-fade-up stagger-3">
                <div class="p-3 rounded-full bg-muted/30 mb-3"><PiggyBank class="h-8 w-8 text-muted-foreground/30" /></div>
                <h3 class="text-sm font-bold mb-1">No envelopes yet</h3>
                <p class="text-[11px] text-muted-foreground mb-3">Create savings envelopes to allocate money toward your goals</p>
                <button @click="openAddForm" class="flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-[10px] font-bold text-primary-foreground"><Plus class="w-3.5 h-3.5" /> New Envelope</button>
            </div>
        </div>
    </AppLayout>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
        <Transition enter-active-class="transition duration-200" leave-active-class="transition duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="showForm" @click="closeForm" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" leave-active-class="transition duration-200 ease-in" enter-from-class="opacity-0 scale-95 translate-y-4" leave-to-class="opacity-0 scale-95 translate-y-4">
            <div v-if="showForm" class="fixed inset-0 z-[51] flex items-center justify-center p-4">
                <div class="w-full max-w-md rounded-2xl border border-border/40 bg-card shadow-2xl p-5">
                    <div class="flex items-center justify-between mb-4"><h3 class="text-sm font-bold">{{ editingEnv ? 'Edit Envelope' : 'New Envelope' }}</h3><button @click="closeForm" class="p-1 rounded-lg hover:bg-muted/40"><X class="w-4 h-4 text-muted-foreground" /></button></div>
                    <div class="space-y-3">
                        <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Name</label><input v-model="form.name" placeholder="e.g. Emergency Fund" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40" /></div>
                        <div class="grid grid-cols-2 gap-2">
                            <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Target</label><input v-model="form.target_amount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40" /></div>
                            <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Current</label><input v-model="form.current_amount" type="number" min="0" step="0.01" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40" /></div>
                        </div>
                        <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Deadline (optional)</label><input v-model="form.deadline" type="date" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40" /></div>
                        <div>
                            <label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1.5">Color</label>
                            <div class="flex gap-1.5 flex-wrap">
                                <button v-for="c in colorPalette" :key="c" @click="form.color = c" class="w-6 h-6 rounded-full border-2 transition-all" :class="form.color === c ? 'border-foreground scale-110' : 'border-transparent'" :style="{ backgroundColor: c }"><Check v-if="form.color === c" class="w-3 h-3 text-white mx-auto mt-0.5" /></button>
                            </div>
                        </div>
                        <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Wallet (optional)</label><select v-model="form.account_id" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40"><option value="">No wallet</option><option v-for="a in props.accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-border/20">
                        <button @click="closeForm" class="px-3 py-1.5 rounded-lg text-[10px] font-semibold border border-border/40 hover:bg-muted/50">Cancel</button>
                        <button @click="saveEnvelope" class="px-4 py-1.5 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground hover:bg-primary/90" :disabled="!form.name || !form.target_amount">{{ editingEnv ? 'Save' : 'Create' }}</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Allocate Modal -->
    <Teleport to="body">
        <Transition enter-active-class="transition duration-200" leave-active-class="transition duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="showAllocate" @click="showAllocate = false" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"></div>
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" leave-active-class="transition duration-200 ease-in" enter-from-class="opacity-0 scale-95 translate-y-4" leave-to-class="opacity-0 scale-95 translate-y-4">
            <div v-if="showAllocate && allocateEnv" class="fixed inset-0 z-[51] flex items-center justify-center p-4">
                <div class="w-full max-w-sm rounded-2xl border border-border/40 bg-card shadow-2xl p-5">
                    <div class="flex items-center justify-between mb-4"><h3 class="text-sm font-bold">Add to "{{ allocateEnv.name }}"</h3><button @click="showAllocate = false" class="p-1 rounded-lg hover:bg-muted/40"><X class="w-4 h-4 text-muted-foreground" /></button></div>
                    <div class="space-y-3">
                        <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Amount</label><input v-model="allocateForm.amount" type="number" min="0" step="0.01" placeholder="0.00" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40" /></div>
                        <div><label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">From Wallet (optional)</label><select v-model="allocateForm.from_account_id" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] outline-none focus:border-primary/40"><option value="">No wallet</option><option v-for="a in props.accounts" :key="a.id" :value="a.id">{{ a.name }}</option></select></div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-border/20">
                        <button @click="showAllocate = false" class="px-3 py-1.5 rounded-lg text-[10px] font-semibold border border-border/40 hover:bg-muted/50">Cancel</button>
                        <button @click="doAllocate" class="px-4 py-1.5 rounded-lg text-[10px] font-bold bg-emerald-500 text-white hover:bg-emerald-600 transition-all" :disabled="!allocateForm.amount">Allocate</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
