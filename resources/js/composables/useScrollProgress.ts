import { ref, onMounted, onUnmounted } from 'vue'

export function useScrollProgress(threshold = 300) {
    const scrollProgress = ref(0)
    const showBackToTop = ref(false)

    let ticking = false

    function handleScroll() {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const scrollTop = window.scrollY
                const docHeight = document.documentElement.scrollHeight - window.innerHeight
                const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0
                scrollProgress.value = Math.min(progress, 100)
                showBackToTop.value = scrollTop > threshold
                ticking = false
            })
            ticking = true
        }
    }

    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }

    onMounted(() => {
        window.addEventListener('scroll', handleScroll, { passive: true })
        handleScroll()
    })

    onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll)
    })

    return {
        scrollProgress,
        showBackToTop,
        scrollToTop,
    }
}
