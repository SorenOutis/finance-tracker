import type { ChartOptions } from 'chart.js'
import { ref, computed } from 'vue'

export function useChartDefaults() {
  const isDark = ref(false)

  // Check for dark mode class
  const updateDarkMode = () => {
    isDark.value = document.documentElement.classList.contains('dark')
  }

  // Watch for theme changes
  if (typeof window !== 'undefined') {
    updateDarkMode()
    const observer = new MutationObserver(updateDarkMode)
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
  }

  // Chart colors using OKLCH
  const chartColors = {
    teal: 'oklch(0.6 0.118 184.704)',
    tealMuted: 'oklch(0.6 0.118 184.704 / 0.1)',
    azure: 'oklch(0.68 0.14 245)',
    azureMuted: 'oklch(0.68 0.14 245 / 0.1)',
    amber: 'oklch(0.76 0.14 75)',
    amberMuted: 'oklch(0.76 0.14 75 / 0.1)',
    slate: 'oklch(0.50 0.02 260)',
    slateMuted: 'oklch(0.50 0.02 260 / 0.1)',
    gain: 'oklch(0.76 0.16 162)',
    gainMuted: 'oklch(0.76 0.16 162 / 0.1)',
    loss: 'oklch(0.62 0.22 18)',
    lossMuted: 'oklch(0.62 0.22 18 / 0.1)',
    grid: 'oklch(0.24 0.01 260)',
    tick: 'oklch(0.50 0.015 260)',
  }

  // Theme-aware colors
  const themeColors = computed(() => ({
    text: isDark.value ? 'oklch(0.708 0 0)' : 'oklch(0.556 0 0)',
    lightText: isDark.value ? 'oklch(0.708 0 0)' : 'oklch(0.556 0 0)',
    bgCard: isDark.value ? 'oklch(0.145 0 0)' : 'oklch(1 0 0)',
    grid: isDark.value ? 'oklch(0.269 0 0 / 0.2)' : 'oklch(0.922 0 0 / 0.2)',
    tooltipBg: isDark.value ? 'oklch(0.145 0 0)' : 'oklch(0.205 0 0)',
    tooltipText: 'oklch(0.985 0 0)',
  }))

  // Build options with theme colors
  const getChartOptions = () => {
    const tc = themeColors.value
    return {
      common: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index' as const,
          intersect: false,
        },
        plugins: {
          legend: {
            display: true,
            position: 'bottom' as const,
            labels: {
              usePointStyle: true,
              padding: 15,
              font: {
                size: 12,
                weight: 'bold',
              },
              color: tc.text,
            },
          },
          tooltip: {
            backgroundColor: tc.tooltipBg,
            titleColor: tc.tooltipText,
            bodyColor: tc.tooltipText,
            borderColor: isDark.value ? 'oklch(0.269 0 0)' : 'oklch(0.922 0 0)',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 12,
            titleFont: {
              size: 12,
              weight: 'bold',
            },
            bodyFont: {
              size: 11,
            },
          },
          filler: {
            propagate: true,
          },
        },
        animation: {
          duration: 750,
          easing: 'easeInOutQuart' as const,
        },
      },
      lineSpecific: {
        scales: {
          y: {
            type: 'linear' as const,
            beginAtZero: true,
            ticks: {
              color: tc.text,
              callback: function (value: any) {
                if (typeof value === 'number') {
                  return '$' + value.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })
                }
                return value
              },
            },
            grid: {
              color: tc.grid,
              drawBorder: false,
            },
          },
          x: {
            ticks: {
              color: tc.text,
            },
            grid: {
              display: false,
              drawBorder: false,
            },
          },
        },
      },
      barSpecific: {
        indexAxis: 'x' as const,
        scales: {
          y: {
            type: 'linear' as const,
            beginAtZero: true,
            ticks: {
              color: tc.text,
              callback: function (value: any) {
                if (typeof value === 'number') {
                  return '$' + value.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })
                }
                return value
              },
            },
            grid: {
              color: tc.grid,
              drawBorder: false,
            },
          },
          x: {
            ticks: {
              color: tc.text,
            },
            grid: {
              display: false,
              drawBorder: false,
            },
          },
        },
      },
      doughnutSpecific: {
        plugins: {
          legend: {
            position: 'bottom' as const,
            labels: {
              usePointStyle: true,
              padding: 10,
              font: {
                size: 12,
                weight: 'bold',
              },
              color: tc.text,
            },
          },
          tooltip: {
            backgroundColor: tc.tooltipBg,
            titleColor: tc.tooltipText,
            bodyColor: tc.tooltipText,
            borderColor: isDark.value ? 'oklch(0.269 0 0)' : 'oklch(0.922 0 0)',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 12,
            callbacks: {
              label: function (context: any) {
                let label = context.label || ''
                if (context.parsed) {
                  label += ': $' + context.parsed.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                  })
                }
                return label
              },
            },
          },
        },
      },
    }
  }

  // Computed options that rebuild when theme changes
  const lineChartOptions = computed((): ChartOptions<'line'> => {
    const opts = getChartOptions()
    return {
      ...opts.common,
      ...opts.lineSpecific,
    } as ChartOptions<'line'>
  })

  const barChartOptions = computed((): ChartOptions<'bar'> => {
    const opts = getChartOptions()
    return {
      ...opts.common,
      ...opts.barSpecific,
    } as ChartOptions<'bar'>
  })

  const doughnutChartOptions = computed((): ChartOptions<'doughnut'> => {
    const opts = getChartOptions()
    return {
      responsive: true,
      maintainAspectRatio: false,
      ...opts.doughnutSpecific,
      animation: {
        duration: 750,
        easing: 'easeInOutQuart' as const,
      },
    } as ChartOptions<'doughnut'>
  })

  return {
    chartColors,
    themeColors,
    lineChartOptions,
    barChartOptions,
    doughnutChartOptions,
    isDark,
    updateDarkMode,
  }
}
