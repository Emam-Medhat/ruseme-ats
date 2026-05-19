<script setup>
defineProps({
    visible: { type: Boolean, default: false },
    progress: { type: Number, default: 0 },
    steps: { type: Array, default: () => [] },
    stepState: { type: Function, required: true },
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#070b14]/97 px-4 backdrop-blur-md"
            >
                <div class="w-full max-w-md text-center">
                    <!-- Scanner icon -->
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-cyan-500/30 bg-cyan-500/5 shadow-[0_0_40px_rgba(34,211,238,0.15)]">
                        <div class="relative h-10 w-10 animate-pulse">
                            <span class="absolute inset-0 rounded border-2 border-cyan-400/80" />
                            <span class="absolute left-1/2 top-1/2 h-0.5 w-full -translate-x-1/2 -translate-y-1/2 bg-cyan-400" />
                            <span class="absolute left-0 top-0 h-3 w-3 border-l-2 border-t-2 border-cyan-400" />
                            <span class="absolute right-0 top-0 h-3 w-3 border-r-2 border-t-2 border-cyan-400" />
                            <span class="absolute bottom-0 left-0 h-3 w-3 border-b-2 border-l-2 border-cyan-400" />
                            <span class="absolute bottom-0 right-0 h-3 w-3 border-b-2 border-r-2 border-cyan-400" />
                        </div>
                    </div>

                    <h2 class="text-2xl font-extrabold text-white tracking-tight">Auditing your CV...</h2>
                    <p class="mt-1 text-sm text-zinc-400">This usually takes a few seconds.</p>

                    <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-5 text-left backdrop-blur-xl shadow-2xl">
                        <div class="mb-4 h-2 overflow-hidden rounded-full bg-zinc-800">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-500 shadow-[0_0_12px_rgba(34,211,238,0.6)] transition-all duration-700 ease-out"
                                :style="{ width: `${progress}%` }"
                            />
                        </div>

                        <ul class="space-y-3">
                            <li
                                v-for="(step, idx) in steps"
                                :key="step.id"
                                class="flex items-center gap-3 text-sm transition-colors"
                                :class="stepState(idx) === 'pending' ? 'text-zinc-600' : 'text-zinc-200'"
                            >
                                <span v-if="stepState(idx) === 'done'" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span v-else-if="stepState(idx) === 'active'" class="flex h-5 w-5 shrink-0 items-center justify-center">
                                    <svg class="h-5 w-5 animate-spin text-cyan-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </span>
                                <span v-else class="h-5 w-5 shrink-0 rounded-full border border-zinc-700" />
                                <span>{{ step.label }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
