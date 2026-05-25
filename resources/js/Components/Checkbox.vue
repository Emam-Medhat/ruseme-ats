<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        required: true,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded-md border-zinc-300 text-indigo-600 shadow-sm transition-all duration-200 focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-0 dark:border-zinc-800 dark:bg-zinc-950 dark:checked:bg-indigo-600"
    />
</template>
