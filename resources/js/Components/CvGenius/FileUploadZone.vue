<script setup>
import { ref } from 'vue';

const emit = defineEmits(['selected']);

const dragging = ref(false);
const inputRef = ref(null);

const onFiles = (files) => {
    const file = files?.[0];
    if (!file) return;
    const ok = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(file.type)
        || /\.(pdf|docx)$/i.test(file.name);
    if (!ok) {
        alert('Please upload a PDF or DOCX file.');
        return;
    }
    emit('selected', file);
};

const onDrop = (e) => {
    dragging.value = false;
    onFiles(e.dataTransfer.files);
};

defineExpose({ open: () => inputRef.value?.click() });
</script>

<template>
    <div
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop"
        @click="inputRef?.click()"
        :class="[
            'cursor-pointer rounded-2xl border-2 border-dashed p-10 text-center transition-all',
            dragging ? 'border-[#4f46e5] bg-[#4f46e5]/5' : 'border-[#e5e3df] bg-white hover:border-[#4f46e5]/50',
        ]"
    >
        <input ref="inputRef" type="file" accept=".pdf,.docx" class="hidden" @change="onFiles($event.target.files)" />
        <div class="text-4xl mb-3">📄</div>
        <p class="text-sm font-bold text-[#1a1a2e]">Drag & drop your resume</p>
        <p class="text-xs text-[#6b7280] mt-1">PDF or DOCX · up to 5MB</p>
    </div>
</template>
