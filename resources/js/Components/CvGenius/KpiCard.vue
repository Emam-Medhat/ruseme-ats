<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: Number, default: 0 },
    accent: { type: String, default: '#4f46e5' },
});
</script>

<template>
    <div class="rounded-2xl border border-zinc-200/80 bg-white/70 dark:bg-zinc-900/40 p-4 shadow-sm backdrop-blur-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 hover:border-zinc-300/80 dark:border-zinc-850 dark:hover:border-zinc-800">
        <p class="text-[9.5px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">{{ label }}</p>
        <p class="text-3xl font-black mt-2 tracking-tight" :style="{ color: accent }">{{ value }}</p>
    </div>
</template>
