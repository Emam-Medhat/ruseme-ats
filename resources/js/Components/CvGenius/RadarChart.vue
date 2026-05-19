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
    chart = new Chart(canvasRef.value, {
        type: 'radar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Section score',
                data: props.values,
                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                pointBackgroundColor: '#4f46e5',
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
                    grid: { color: 'rgba(0,0,0,0.06)' },
                    pointLabels: { font: { size: 9 } },
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
