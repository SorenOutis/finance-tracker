<script setup lang="ts">
import {
    Camera, ScanLine, Upload, FileText, X, Image,
    Check, ChevronRight, Trash2, AlertCircle
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const emit = defineEmits<{
    (e: 'captured', data: { photoBlob: Blob; previewUrl: string; }): void;
    (e: 'clear'): void;
}>();

const props = defineProps<{
    receiptPreview?: string | null;
}>();

// State
const mode = ref<'idle' | 'camera' | 'gallery' | 'preview'>('idle');
const stream = ref<MediaStream | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const capturedImage = ref<string | null>(props.receiptPreview || null);
const capturedBlob = ref<Blob | null>(null);
const hasCamera = ref(false);
const errorMsg = ref('');
const isScanning = ref(false);

// Check camera availability
onMounted(async () => {
    try {
        const devices = await navigator.mediaDevices?.enumerateDevices();
        hasCamera.value = devices?.some(d => d.kind === 'videoinput') ?? false;
    } catch {
        hasCamera.value = false;
    }
});

// Cleanup camera on unmount
onUnmounted(() => {
    stopCamera();
});

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(t => t.stop());
        stream.value = null;
    }
};

const startCamera = async () => {
    errorMsg.value = '';
    try {
        // Prefer rear camera on mobile, fallback to any camera
        const constraints: MediaStreamConstraints = {
            video: {
                facingMode: 'environment',
                width: { ideal: 1920 },
                height: { ideal: 1080 },
            },
            audio: false,
        };

        stream.value = await navigator.mediaDevices.getUserMedia(constraints);
        mode.value = 'camera';

        // Wait for next tick to ensure video element is mounted
        setTimeout(() => {
            if (videoRef.value) {
                videoRef.value.srcObject = stream.value;
                videoRef.value.play();
            }
        }, 50);
    } catch (err: any) {
        errorMsg.value = err.message?.includes('NotAllowed') 
            ? 'Camera permission denied. Please allow camera access or upload a photo instead.'
            : 'Could not access camera. Try uploading a photo instead.';
        mode.value = 'idle';
    }
};

const capturePhoto = () => {
    if (!videoRef.value || !canvasRef.value) return;

    const video = videoRef.value;
    const canvas = canvasRef.value;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    ctx.drawImage(video, 0, 0);

    canvas.toBlob((blob) => {
        if (!blob) return;
        // Convert Blob to File so Laravel's hasFile() can detect it properly
        const file = new File([blob], `receipt-${Date.now()}.jpg`, { type: 'image/jpeg' });
        capturedBlob.value = file;
        capturedImage.value = canvas.toDataURL('image/jpeg', 0.85);
        mode.value = 'preview';
        stopCamera();
        emit('captured', {
            photoBlob: file,
            previewUrl: capturedImage.value,
        });
    }, 'image/jpeg', 0.85);
};

const handleFileUpload = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (!input.files?.length) return;

    const file = input.files[0];

    // Validate file type
    if (!file.type.startsWith('image/')) {
        errorMsg.value = 'Please select an image file.';
        return;
    }

    // Validate file size (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        errorMsg.value = 'Image too large. Please select a file under 10MB.';
        return;
    }

    errorMsg.value = '';
    capturedBlob.value = file;
    capturedImage.value = URL.createObjectURL(file);
    mode.value = 'preview';
    emit('captured', {
        photoBlob: file,
        previewUrl: capturedImage.value,
    });

    // Reset input so the same file can be re-selected
    input.value = '';
};

const clearPhoto = () => {
    capturedImage.value = null;
    capturedBlob.value = null;
    mode.value = 'idle';
    emit('clear');
};

const retake = () => {
    clearPhoto();
    if (hasCamera.value) {
        startCamera();
    } else {
        mode.value = 'idle';
    }
};

const openGallery = () => {
    // Trigger hidden file input
    document.getElementById('receipt-file-input')?.click();
};

// Watch for external clearing
watch(() => props.receiptPreview, (val) => {
    if (val === null || val === undefined) {
        capturedImage.value = null;
        capturedBlob.value = null;
        mode.value = 'idle';
    } else if (val) {
        capturedImage.value = val;
        mode.value = 'preview';
    }
});
</script>

<template>
    <div class="space-y-2">
        <!-- Hidden file input -->
        <input
            id="receipt-file-input"
            type="file"
            accept="image/*"
            capture="environment"
            @change="handleFileUpload"
            class="hidden"
        />

        <!-- Error message -->
        <div v-if="errorMsg" class="flex items-start gap-2 p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20">
            <AlertCircle class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" />
            <p class="text-[10px] text-rose-600 font-medium">{{ errorMsg }}</p>
        </div>

        <!-- IDLE STATE: Show action buttons -->
        <div v-if="mode === 'idle'" class="flex items-center gap-2">
            <button
                v-if="hasCamera"
                @click="startCamera"
                class="flex items-center gap-1.5 rounded-xl border border-border/40 bg-muted/20 px-3 py-2 text-[10px] font-bold hover:bg-muted/40 transition-all active:scale-95"
            >
                <Camera class="w-3.5 h-3.5" /> Scan Receipt
            </button>
            <button
                @click="openGallery"
                class="flex items-center gap-1.5 rounded-xl border border-border/40 bg-muted/20 px-3 py-2 text-[10px] font-bold hover:bg-muted/40 transition-all active:scale-95"
            >
                <Image class="w-3.5 h-3.5" /> Upload Photo
            </button>
        </div>

        <!-- CAMERA MODE: Live viewfinder -->
        <div v-if="mode === 'camera'" class="relative rounded-xl bg-gray-900 overflow-hidden">
            <video
                ref="videoRef"
                autoplay
                playsinline
                class="w-full aspect-[4/3] object-cover"
            ></video>

            <!-- Viewfinder frame overlay -->
            <div class="absolute inset-4 border-2 border-dashed border-white/30 rounded-xl pointer-events-none"></div>

            <!-- Scanning line animation -->
            <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-teal-400/60 to-transparent animate-pulse pointer-events-none"></div>

            <!-- Instructions -->
            <div class="absolute top-3 left-1/2 -translate-x-1/2 bg-black/50 backdrop-blur-sm px-3 py-1 rounded-full">
                <p class="text-white/80 text-[9px] font-semibold">Position receipt within frame</p>
            </div>

            <!-- Bottom controls -->
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 flex items-center justify-center gap-6">
                <button @click="stopCamera(); mode = 'idle';" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition-colors">
                    <X class="w-5 h-5" />
                    <span class="text-[8px] font-semibold">Cancel</span>
                </button>
                <button @click="capturePhoto" class="flex flex-col items-center gap-1">
                    <div class="w-14 h-14 rounded-full border-4 border-white flex items-center justify-center hover:scale-105 transition-transform">
                        <div class="w-11 h-11 rounded-full bg-white/20 hover:bg-white/30 transition-colors"></div>
                    </div>
                    <span class="text-[8px] font-semibold text-white">Capture</span>
                </button>
                <button @click="openGallery" class="flex flex-col items-center gap-1 text-white/70 hover:text-white transition-colors">
                    <Image class="w-5 h-5" />
                    <span class="text-[8px] font-semibold">Gallery</span>
                </button>
            </div>

            <!-- Hidden canvas for capturing -->
            <canvas ref="canvasRef" class="hidden"></canvas>
        </div>

        <!-- PREVIEW MODE: Show captured/uploaded photo -->
        <div v-if="mode === 'preview' && capturedImage" class="relative rounded-xl overflow-hidden border border-border/30">
            <img :src="capturedImage" alt="Receipt" class="w-full max-h-[280px] object-contain bg-muted/20" />

            <!-- Action overlay -->
            <div class="absolute top-2 right-2 flex gap-1">
                <button @click="retake" class="p-1.5 rounded-lg bg-black/50 backdrop-blur-sm text-white hover:bg-black/70 transition-colors" title="Retake">
                    <Camera class="w-3.5 h-3.5" />
                </button>
                <button @click="clearPhoto" class="p-1.5 rounded-lg bg-black/50 backdrop-blur-sm text-white hover:bg-red-500/70 transition-colors" title="Remove">
                    <Trash2 class="w-3.5 h-3.5" />
                </button>
            </div>

            <!-- Badge -->
            <div class="absolute bottom-2 left-2 flex items-center gap-1 px-2 py-1 rounded-full bg-black/50 backdrop-blur-sm">
                <Check class="w-3 h-3 text-emerald-400" />
                <span class="text-[9px] font-bold text-white">Receipt attached</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Override dark input styles for the video element */
video::-webkit-media-controls { display: none !important; }
</style>
