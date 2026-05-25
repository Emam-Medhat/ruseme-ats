<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Chart, RadialLinearScale, PointElement, LineElement, Filler, RadarController, Tooltip, Legend } from 'chart.js';

Chart.register(RadialLinearScale, PointElement, LineElement, Filler, RadarController, Tooltip, Legend);

const props = defineProps({
    labels: { type: Array, default: () => [] },
    values: { type: Array, default: () => [] },
    height: { type: Number, default: 220 },
});

const canvasRef = ref(null);
let chart = null;

const render = () => {
    if (!canvasRef.value || !props.labels.length) return;
    chart?.destroy();
    
    // Check if system is dark mode
    const isDark = document.documentElement.classList.contains('dark');
                  
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
    const textColor = isDark ? '#a1a1aa' : '#71717a';

    chart = new Chart(canvasRef.value, {
        type: 'radar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Section score',
                data: props.values,
                backgroundColor: 'rgba(99, 102, 241, 0.12)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: isDark ? '#18181b' : '#ffffff',
                pointBorderWidth: 1.5,
                pointRadius: 4,
                pointHoverRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: { stepSize: 20, display: false },
                    grid: { color: gridColor },
                    angleLines: { color: gridColor },
                    pointLabels: { 
                        font: { size: 9, family: 'Inter', weight: '600' },
                        color: textColor
                    },
                },
            },
            plugins: { legend: { display: false } },
        },
    });
};

onMounted(render);
watch(() => [props.labels, props.values], render, { deep: true });
onUnmounted(() => chart?.destroy());
</script>

<template>
    <div :style="{ height: `${height}px` }" class="w-full">
        <canvas ref="canvasRef" />
    </div>
</template>
