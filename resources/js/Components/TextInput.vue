<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-xl border border-zinc-200 bg-zinc-50/40 px-4 py-2.5 text-sm text-zinc-900 shadow-inner placeholder-zinc-400 transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/15 dark:border-zinc-800 dark:bg-zinc-950/50 dark:text-zinc-100 dark:placeholder-zinc-600 dark:focus:border-indigo-500 dark:focus:bg-zinc-950"
        v-model="model"
        ref="input"
    />
</template>
