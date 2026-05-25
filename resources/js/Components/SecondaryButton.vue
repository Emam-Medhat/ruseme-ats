<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-xl border border-zinc-200/85 bg-white dark:bg-zinc-900/70 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 shadow-sm transition-all duration-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:scale-[1.01] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:opacity-50 dark:border-zinc-800"
    >
        <slot />
    </button>
</template>
