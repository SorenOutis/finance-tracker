<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Upload, FileSpreadsheet, CheckCheck, ChevronRight, ArrowLeft,
    Download, FileText, AlertCircle, Check, X
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Import', href: '/import' }];

// Read CSRF token from meta tag (standard Laravel approach)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const steps = ['Upload', 'Map Columns', 'Confirm'];
const currentStep = ref(1);
const file = ref<File | null>(null);
const hasHeader = ref(true);
const sourceType = ref('');

const mapping = ref({
    date: '',
    amount: '',
    description: '',
    category: '',
});

const previewData = ref<{
    headers: string[];
    sample: any[];
    total: number;
} | null>(null);

const importResult = ref<{
    imported: number;
    errors: string[];
    total: number;
} | null>(null);

const columns = ref<string[]>([]);
const isUploading = ref(false);
const isImporting = ref(false);
const errorMsg = ref('');

const detectColumn = (field: string) => {
    for (const col of columns.value) {
        const lower = col.toLowerCase();
        if (field === 'date' && (lower.includes('date') || lower.includes('time') || lower.includes('day'))) return col;
        if (field === 'amount' && (lower.includes('amount') || lower.includes('price') || lower.includes('cost') || lower.includes('value') || lower.includes('sum'))) return col;
        if (field === 'description' && (lower.includes('desc') || lower.includes('note') || lower.includes('detail') || lower.includes('name') || lower.includes('memo'))) return col;
        if (field === 'category' && (lower.includes('categ') || lower.includes('type') || lower.includes('class'))) return col;
    }
    return '';
};

const handleFileSelect = async (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    file.value = input.files[0];
    isUploading.value = true;
    errorMsg.value = '';

    const formData = new FormData();
    formData.append('file', file.value);
    formData.append('has_header', hasHeader.value ? '1' : '0');

    try {
        const resp = await fetch('/import/preview', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        });

        if (!resp.ok) {
            errorMsg.value = 'Failed to parse file. Please check the format.';
            isUploading.value = false;
            return;
        }

        const data = await resp.json();
        previewData.value = data;
        columns.value = data.headers || [];

        // Auto-detect column mappings
        mapping.value.date = detectColumn('date');
        mapping.value.amount = detectColumn('amount');
        mapping.value.description = detectColumn('description');
        mapping.value.category = detectColumn('category');

        currentStep.value = 2;
    } catch {
        errorMsg.value = 'Could not read file. Try a different format.';
    }

    isUploading.value = false;
};

const handleImport = async () => {
    if (!file.value) return;
    isImporting.value = true;
    errorMsg.value = '';

    const formData = new FormData();
    formData.append('mapping[date]', mapping.value.date);
    formData.append('mapping[amount]', mapping.value.amount);
    formData.append('mapping[description]', mapping.value.description);
    formData.append('mapping[category]', mapping.value.category);
    formData.append('has_header', hasHeader.value ? '1' : '0');

    try {
        const resp = await fetch('/import/process', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        });

        if (!resp.ok) {
            errorMsg.value = 'Import failed. Please try again.';
            isImporting.value = false;
            return;
        }

        importResult.value = await resp.json();
        currentStep.value = 3;
    } catch {
        errorMsg.value = 'Import failed. Please try again.';
    }

    isImporting.value = false;
};

const resetImport = () => {
    currentStep.value = 1;
    file.value = null;
    previewData.value = null;
    importResult.value = null;
    mapping.value = { date: '', amount: '', description: '', category: '' };
};
</script>

<template>
    <Head title="Import Data" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-5 md:gap-6 overflow-x-auto p-4 sm:p-8 pb-24 md:pb-8">
            <!-- Header -->
            <div class="relative overflow-hidden rounded-[24px] bg-card border border-border/50 p-4 sm:p-5 animate-fade-up stagger-1">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="p-1 rounded-lg bg-primary/10 text-primary"><Download class="w-3.5 h-3.5" /></div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/80">Data Import</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight font-display">Import <span class="text-primary">Transactions</span></h1>
                    <p class="mt-1 text-[11px] sm:text-xs text-muted-foreground font-medium">Import from CSV, OFX, or bank/GCash/Maya exports</p>
                </div>
            </div>

            <!-- Step Progress -->
            <div class="flex items-center gap-2 animate-fade-up stagger-2">
                <div v-for="(step, i) in steps" :key="step" class="flex items-center gap-2 flex-1">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[9px] font-bold transition-all" :class="(i + 1) <= currentStep ? 'bg-primary text-primary-foreground' : 'bg-muted/30 text-muted-foreground'">{{ i + 1 }}</div>
                    <span class="text-[9px] font-semibold hidden sm:block" :class="(i + 1) === currentStep ? 'text-foreground' : 'text-muted-foreground'">{{ step }}</span>
                    <div v-if="i < steps.length - 1" class="flex-1 h-0.5 rounded-full" :class="(i + 1) < currentStep ? 'bg-primary' : 'bg-muted/20'"></div>
                </div>
            </div>

            <!-- Step 1: Upload -->
            <div v-if="currentStep === 1" class="surface-card p-6 animate-fade-up stagger-3">
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-violet-500/10 border-2 border-dashed border-violet-500/30 flex items-center justify-center">
                        <Upload class="w-6 h-6 text-violet-500" />
                    </div>
                    <h3 class="text-sm font-bold mb-1">Upload your file</h3>
                    <p class="text-[10px] text-muted-foreground mb-4 max-w-xs mx-auto">CSV, OFX, QFX — or export from GCash, Maya, PayPal, or your bank</p>
                    
                    <label class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-[10px] font-bold text-primary-foreground cursor-pointer hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                        <Upload class="w-3.5 h-3.5" /> Choose File
                        <input type="file" accept=".csv,.txt,.ofx,.qfx" @change="handleFileSelect" class="hidden" />
                    </label>

                    <div v-if="isUploading" class="mt-4 flex items-center justify-center gap-2">
                        <div class="w-4 h-4 rounded-full border-2 border-primary/30 border-t-primary animate-spin"></div>
                        <span class="text-[10px] text-muted-foreground">Parsing file...</span>
                    </div>

                    <div class="mt-5 flex justify-center gap-3 flex-wrap">
                        <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">GCash CSV</span>
                        <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">Maya CSV</span>
                        <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">Bank OFX</span>
                        <span class="text-[9px] px-2 py-1 rounded-full bg-muted/30 text-muted-foreground">PayPal CSV</span>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-2">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" v-model="hasHeader" class="rounded border-border/40 text-primary" />
                            <span class="text-[10px] text-muted-foreground">File has header row</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Map Columns -->
            <div v-if="currentStep === 2 && previewData" class="surface-card p-5 animate-fade-up stagger-3">
                <p class="text-[10px] font-semibold text-muted-foreground mb-3">Map your file columns to tracker fields</p>
                
                <div v-if="errorMsg" class="mb-3 p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-[10px] font-medium text-rose-600 flex items-center gap-2">
                    <AlertCircle class="w-3.5 h-3.5 shrink-0" /> {{ errorMsg }}
                </div>

                <div class="space-y-2.5 mb-4">
                    <div v-for="field in ['date', 'amount', 'description', 'category']" :key="field" class="flex items-center gap-3 p-2.5 rounded-xl bg-muted/20 border border-border/20">
                        <span class="text-[10px] font-bold w-20 shrink-0 capitalize">{{ field }}</span>
                        <select v-model="mapping[field as keyof typeof mapping]" class="flex-1 rounded-lg border border-border/30 bg-background px-2.5 py-1.5 text-[10px] outline-none focus:border-primary/40">
                            <option value="">— Skip —</option>
                            <option v-for="col in columns" :key="col" :value="col">{{ col }}</option>
                        </select>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="overflow-x-auto rounded-xl border border-border/20 mb-4">
                    <table class="w-full text-[10px]">
                        <thead><tr class="bg-muted/30">
                            <th class="px-3 py-1.5 text-left font-semibold text-muted-foreground">#</th>
                            <th v-for="col in columns" :key="col" class="px-3 py-1.5 text-left font-semibold text-muted-foreground">{{ col }}</th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="(row, i) in previewData.sample" :key="i" class="border-t border-border/10">
                                <td class="px-3 py-1.5 text-muted-foreground">{{ i + 1 }}</td>
                                <td v-for="col in columns" :key="col" class="px-3 py-1.5">{{ row[col] || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-[9px] text-muted-foreground mb-4">Showing {{ previewData.sample.length }} of {{ previewData.total }} rows</p>

                <div class="flex items-center justify-between">
                    <button @click="currentStep = 1" class="px-3 py-1.5 rounded-lg text-[10px] font-semibold border border-border/30 hover:bg-muted/40 transition-all"><ArrowLeft class="w-3 h-3 inline" /> Back</button>
                    <button @click="handleImport" :disabled="isImporting || !mapping.date || !mapping.amount" class="px-4 py-1.5 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground hover:bg-primary/90 transition-all disabled:opacity-50">
                        {{ isImporting ? 'Importing...' : 'Import Data →' }}
                    </button>
                </div>
            </div>

            <!-- Step 3: Confirm -->
            <div v-if="currentStep === 3 && importResult" class="animate-fade-up stagger-3">
                <div class="surface-card p-6">
                    <div class="text-center py-6">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                            <CheckCheck class="w-6 h-6 text-emerald-500" />
                        </div>
                        <h3 class="text-base font-bold mb-1">Import Complete</h3>
                        <p class="text-[11px] text-muted-foreground">{{ importResult.imported }} of {{ importResult.total }} transactions imported</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="text-center p-3 rounded-xl bg-muted/20"><p class="text-lg font-bold text-emerald-500">{{ importResult.imported }}</p><p class="text-[9px] text-muted-foreground">Imported</p></div>
                        <div class="text-center p-3 rounded-xl bg-muted/20"><p class="text-lg font-bold">{{ importResult.total }}</p><p class="text-[9px] text-muted-foreground">Total</p></div>
                        <div class="text-center p-3 rounded-xl bg-muted/20"><p class="text-lg font-bold" :class="importResult.errors.length > 0 ? 'text-rose-500' : 'text-emerald-500'">{{ importResult.errors.length }}</p><p class="text-[9px] text-muted-foreground">Errors</p></div>
                    </div>
                    <div v-if="importResult.errors.length" class="mb-4 p-3 rounded-xl bg-rose-500/5 border border-rose-500/10">
                        <p class="text-[9px] font-bold text-rose-600 mb-1">Errors</p>
                        <p v-for="(err, i) in importResult.errors" :key="i" class="text-[9px] text-rose-500/80">{{ err }}</p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button @click="resetImport" class="px-4 py-2 rounded-lg text-[10px] font-semibold border border-border/30 hover:bg-muted/40 transition-all">Import Another</button>
                        <a href="/dashboard" class="px-4 py-2 rounded-lg text-[10px] font-bold bg-primary text-primary-foreground hover:bg-primary/90 transition-all">Go to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
