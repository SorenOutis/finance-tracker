<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import {
    Tags, Plus, Trash2, Pencil, Search, X, Check, ChevronRight,
    ShoppingCart, Utensils, MapPin, Heart, Home, Zap, Gift, TrendingUp,
    MoreHorizontal, ArrowDownRight, ArrowUpRight, CreditCard, CalendarClock, ReceiptText,
    BookOpen, Film, Briefcase, Laptop, Building
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { useToast } from '@/composables/useToast';

interface Category {
    id: number;
    name: string;
    type: 'income' | 'expense';
    color: string | null;
    icon: string | null;
    sort_order: number;
}

interface Props {
    incomeCategories: Category[];
    expenseCategories: Category[];
}

const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Categories', href: '/categories' }];

const typeTab = ref<'income' | 'expense'>('expense');
const showForm = ref(false);
const editingCat = ref<Category | null>(null);
const searchQuery = ref('');

const availableIcons = ['ShoppingCart', 'Utensils', 'MapPin', 'Heart', 'Home', 'Zap', 'Gift', 'TrendingUp', 'BookOpen', 'Film', 'Briefcase', 'Laptop', 'Building', 'MoreHorizontal'] as const;
const colorPalette = ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6', '#F97316', '#6B7280'];

const form = useForm({
    name: '',
    type: 'expense' as 'income' | 'expense',
    color: '#6366F1',
    icon: 'MoreHorizontal',
    sort_order: 0,
});

const { addToast } = useToast();

const openAddForm = (type: 'income' | 'expense') => {
    editingCat.value = null;
    form.reset();
    form.type = type;
    form.color = '#6366F1';
    showForm.value = true;
};

const openEditForm = (cat: Category) => {
    editingCat.value = cat;
    form.name = cat.name;
    form.type = cat.type;
    form.color = cat.color || '#6366F1';
    form.icon = cat.icon || 'MoreHorizontal';
    form.sort_order = cat.sort_order;
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editingCat.value = null;
    form.reset();
};

const saveCategory = () => {
    if (!form.name.trim()) return;

    if (editingCat.value) {
        form.patch(`/categories/${editingCat.value.id}`, {
            onSuccess: () => { closeForm(); addToast({ type: 'success', title: 'Category Updated', message: form.name }); },
        });
    } else {
        form.post('/categories', {
            onSuccess: () => { closeForm(); addToast({ type: 'success', title: 'Category Created', message: form.name }); },
        });
    }
};

const deleteCategory = (cat: Category) => {
    if (!confirm(`Delete "${cat.name}" category?`)) return;
    router.delete(`/categories/${cat.id}`, { preserveScroll: true, onSuccess: () => { addToast({ type: 'info', title: 'Category Deleted', message: `"${cat.name}" removed` }); } });
};

const seedDefaults = () => {
    router.post('/categories/seed-defaults', {}, { preserveScroll: true });
};

const allCategories = computed(() =>
    typeTab.value === 'income' ? props.incomeCategories : props.expenseCategories
);

const filteredCategories = computed(() => {
    if (!searchQuery.value) return allCategories.value;
    const q = searchQuery.value.toLowerCase();
    return allCategories.value.filter(c => c.name.toLowerCase().includes(q));
});

const iconComponents: Record<string, any> = {
    ShoppingCart, Utensils, MapPin, Heart, Home, Zap, Gift, TrendingUp,
    BookOpen: BookOpen, Film: Film, Briefcase: Briefcase,
    Laptop: Laptop, Building: Building, ShoppingBag: ShoppingCart, MoreHorizontal,
    ArrowDownRight, ArrowUpRight, CreditCard, CalendarClock, ReceiptText
};

const getIcon = (iconName: string | null) => {
    return iconComponents[iconName || 'MoreHorizontal'] || MoreHorizontal;
};
</script>

<template>
    <Head title="Categories" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 animate-fade-up stagger-1">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="p-1 rounded-lg bg-primary/10 text-primary"><Tags class="w-3.5 h-3.5" /></div>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Management</span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight font-display">Custom <span class="text-primary">Categories</span></h1>
                        <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium">Create and manage your own income and expense categories</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="seedDefaults" class="rounded-lg border border-border/40 bg-card px-3 py-2 text-[10px] font-bold hover:bg-accent/30 transition-all">Seed Defaults</button>
                        <button @click="openAddForm(typeTab)" class="flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-[10px] font-bold text-primary-foreground hover:bg-primary/90 transition-all shadow-lg shadow-primary/20"><Plus class="w-3.5 h-3.5" /> Add {{ typeTab === 'income' ? 'Income' : 'Expense' }}</button>
                    </div>
                </div>
            </div>

            <!-- Type Tabs + Search -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 animate-fade-up stagger-2">
                <div class="flex items-center gap-1 rounded-xl bg-muted/30 border border-border/30 p-0.5">
                    <button @click="typeTab = 'expense'" class="px-4 py-2 rounded-lg text-[10px] font-bold transition-all" :class="typeTab === 'expense' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">Expenses</button>
                    <button @click="typeTab = 'income'" class="px-4 py-2 rounded-lg text-[10px] font-bold transition-all" :class="typeTab === 'income' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">Income</button>
                </div>
                <div class="relative max-w-[240px]">
                    <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Search categories..." class="w-full rounded-lg border border-border/40 bg-muted/20 pl-7 pr-2.5 py-1.5 text-[10px] font-medium outline-none focus:border-primary/40" />
                </div>
            </div>

            <!-- Category Grid -->
            <div v-if="filteredCategories.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 animate-fade-up stagger-3">
                <div v-for="cat in filteredCategories" :key="cat.id" class="group relative p-3 rounded-xl border border-border/20 bg-card hover:border-border/40 hover:shadow-md transition-all cursor-pointer">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white" :style="{ backgroundColor: cat.color || '#6366F1' }">
                            <component :is="getIcon(cat.icon)" class="w-4 h-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold truncate">{{ cat.name }}</p>
                            <p class="text-[8px] uppercase tracking-wider text-muted-foreground/60 font-bold">{{ cat.type }}</p>
                        </div>
                    </div>
                    <div class="absolute top-2 right-2 flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEditForm(cat)" class="p-1 rounded hover:bg-muted/40 transition-colors"><Pencil class="w-3 h-3 text-muted-foreground" /></button>
                        <button @click="deleteCategory(cat)" class="p-1 rounded hover:bg-destructive/10 transition-colors"><Trash2 class="w-3 h-3 text-destructive/70" /></button>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: cat.color || '#6366F1' }"></span>
                        <span class="text-[8px] text-muted-foreground">Order: {{ cat.sort_order }}</span>
                    </div>
                </div>
            </div>
            <div v-else class="flex flex-col items-center justify-center py-16 animate-fade-up stagger-3">
                <div class="p-3 rounded-full bg-muted/30 mb-3"><Tags class="h-8 w-8 text-muted-foreground/30" /></div>
                <h3 class="text-sm font-bold mb-1">No categories yet</h3>
                <p class="text-[11px] text-muted-foreground mb-3">Create your first category to get started</p>
                <button @click="openAddForm(typeTab)" class="flex items-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-[10px] font-bold text-primary-foreground"><Plus class="w-3.5 h-3.5" /> Add Category</button>
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
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold">{{ editingCat ? 'Edit Category' : 'Add Category' }}</h3>
                        <button @click="closeForm" class="p-1 rounded-lg hover:bg-muted/40 transition-colors"><X class="w-4 h-4 text-muted-foreground" /></button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Name</label>
                            <input v-model="form.name" type="text" placeholder="Category name" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] font-medium outline-none focus:border-primary/40" />
                        </div>
                        <div>
                            <label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Type</label>
                            <select v-model="form.type" class="w-full rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] font-medium outline-none focus:border-primary/40">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1.5">Color</label>
                            <div class="flex flex-wrap gap-1.5">
                                <button v-for="c in colorPalette" :key="c" @click="form.color = c" class="w-6 h-6 rounded-full border-2 transition-all" :class="form.color === c ? 'border-foreground scale-110' : 'border-transparent'" :style="{ backgroundColor: c }">
                                    <Check v-if="form.color === c" class="w-3 h-3 text-white mx-auto mt-0.5" />
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="text-[9px] font-bold uppercase tracking-wider text-muted-foreground/80 block mb-1">Sort Order</label>
                            <input v-model="form.sort_order" type="number" min="0" class="w-20 rounded-lg border border-border/40 bg-background px-3 py-2 text-[12px] font-medium outline-none focus:border-primary/40" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-border/20">
                        <button @click="closeForm" class="px-3 py-1.5 rounded-lg text-[10px] font-semibold border border-border/40 hover:bg-muted/50 transition-all">Cancel</button>
                        <button @click="saveCategory" class="px-4 py-1.5 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground hover:bg-primary/90 transition-all" :disabled="!form.name.trim()">{{ editingCat ? 'Save Changes' : 'Create Category' }}</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
