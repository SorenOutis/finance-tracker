import { ref  } from 'vue';
import type {Ref} from 'vue';

export interface Toast {
    id: string;
    type: 'success' | 'warning' | 'info' | 'error';
    title: string;
    message?: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let counter = 0;

export function useToast() {
    const addToast = (toast: Omit<Toast, 'id'>) => {
        const id = `toast-${++counter}-${Date.now()}`;
        const newToast: Toast = { ...toast, id };
        toasts.value.push(newToast);

        const duration = toast.duration ?? 3000;
        setTimeout(() => {
            removeToast(id);
        }, duration);

        return id;
    };

    const removeToast = (id: string) => {
        const idx = toasts.value.findIndex(t => t.id === id);
        if (idx !== -1) {
            toasts.value.splice(idx, 1);
        }
    };

    const clearAll = () => {
        toasts.value = [];
    };

    return {
        toasts: toasts as Ref<Toast[]>,
        addToast,
        removeToast,
        clearAll,
    };
}
