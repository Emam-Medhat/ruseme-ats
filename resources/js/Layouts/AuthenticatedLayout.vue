<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout>
        <template v-if="$slots.header" #pageHeader>
            <slot name="header" />
        </template>
        <slot />
    </AppLayout>
</template>
