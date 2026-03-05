import { ref, watch, onMounted } from 'vue';

export function useNumberAnimation(sourceValue: () => number, duration: number = 1000) {
    // Start at 0 for initial load effect
    const displayValue = ref(0);
    
    const animateTo = (target: number) => {
        const startValue = displayValue.value;
        const change = target - startValue;
        if (change === 0) {
            displayValue.value = target;
            return;
        }
        
        const startTime = performance.now();
        
        const updateValue = (currentTime: number) => {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            
            // easeOutQuart for smooth deceleration
            const easeOutProgress = 1 - Math.pow(1 - progress, 4);
            
            displayValue.value = startValue + (change * easeOutProgress);
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            } else {
                displayValue.value = target;
            }
        };
        
        requestAnimationFrame(updateValue);
    };

    onMounted(() => {
        animateTo(sourceValue());
    });
    
    watch(sourceValue, (newVal) => {
        animateTo(newVal);
    });

    return displayValue;
}
