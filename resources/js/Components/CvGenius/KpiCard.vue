<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: Number, default: 0 },
    accent: { type: String, default: '#4f46e5' },
});
</script>

<template>
    <div class="rounded-xl border border-[#e5e3df] bg-white p-3 shadow-sm">
        <p class="text-[9px] font-bold uppercase tracking-wider text-[#6b7280]">{{ label }}</p>
        <p class="text-2xl font-extrabold mt-1" :style="{ color: accent }">{{ value }}</p>
    </div>
</template>
