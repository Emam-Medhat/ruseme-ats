<script setup>
/**
 * MagicRewriteModal.vue — Production-grade AI Magic Rewrite overlay.
 *
 * Architecture:
 *  - All API/retry logic delegated to useMagicRewrite composable
 *  - Focus trap (Tab / Shift+Tab cycling) via composable helper
 *  - Escape key closes modal
 *  - AbortController cancels in-flight request on close
 *  - Body scroll locked while open (restored on unmount / close)
 *  - Staggered card entrance via CSS animation delays
 *
 * Emits:
 *  - close          — user dismissed the modal
 *  - apply-suggestion({ suggestion, issueId }) — user selected a version
 */

import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import RewriteSuggestionCard from './RewriteSuggestionCard.vue'
import { useMagicRewrite } from '@/composables/useMagicRewrite.js'

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    bulletText: { type: String, required: true },
    issueId: { type: [String, Number], default: null },
})

const emit = defineEmits(['close', 'apply-suggestion'])

// ─── Composable ─────────────────────────────────────────────────────────────

const {
    suggestions,
    loading,
    error,
    retryCount,
    fetchSuggestions,
    abort,
    reset,
    trapFocus,
    focusFirst,
} = useMagicRewrite()

// ─── Modal panel ref (for focus-trap) ───────────────────────────────────────

const modalPanelRef = ref(null)

// ─── Open / close lifecycle ──────────────────────────────────────────────────

watch(
    () => props.isOpen,
    async (opened) => {
        if (opened) {
            document.body.style.overflow = 'hidden'
            await fetchSuggestions(props.bulletText)
            await focusFirst(modalPanelRef.value)
        } else {
            abort()
            document.body.style.overflow = ''
        }
    }
)

// ─── Keyboard handlers ───────────────────────────────────────────────────────

const handleKeyDown = (e) => {
    if (!props.isOpen) return

    if (e.key === 'Escape') {
        e.preventDefault()
        handleClose()
        return
    }

    if (e.key === 'Tab') {
        trapFocus(modalPanelRef.value, e)
    }
}

onMounted(() => window.addEventListener('keydown', handleKeyDown))
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeyDown)
    document.body.style.overflow = ''
    abort()
})

// ─── Actions ─────────────────────────────────────────────────────────────────

const handleClose = () => {
    reset()
    emit('close')
}

const handleSelect = (selectedText) => {
    emit('apply-suggestion', {
        suggestion: selectedText,
        issueId: props.issueId,
    })
    reset()
}

const handleRetry = () => fetchSuggestions(props.bulletText)
</script>

<template>
    <Teleport to="body">
        <!-- Outer backdrop transition -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                role="dialog"
                aria-modal="true"
                aria-labelledby="magic-rewrite-title"
                aria-describedby="magic-rewrite-desc"
                class="fixed inset-0 z-[150] flex items-center justify-center
                       bg-[#04060d]/88 p-4 sm:p-6
                       backdrop-blur-md overflow-y-auto"
                @click.self="handleClose"
            >
                <!-- Inner panel transition -->
                <Transition
                    enter-active-class="transition duration-350 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-5"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-5"
                >
                    <!-- Modal panel -->
                    <div
                        v-if="isOpen"
                        ref="modalPanelRef"
                        class="w-full max-w-5xl
                               bg-zinc-950/85
                               border border-violet-500/10
                               rounded-3xl p-6 sm:p-8
                               shadow-[0_0_90px_-12px_rgba(139,92,246,0.4)]
                               relative backdrop-blur-2xl
                               my-8 overflow-hidden"
                    >
                        <!-- ── Ambient glow orbs ── -->
                        <div
                            class="absolute -top-28 -left-28 w-96 h-96
                                   bg-violet-600/8 blur-[110px] rounded-full
                                   pointer-events-none animate-pulse-glow"
                            aria-hidden="true"
                        />
                        <div
                            class="absolute -bottom-28 -right-28 w-96 h-96
                                   bg-cyan-500/5 blur-[110px] rounded-full
                                   pointer-events-none animate-pulse-glow"
                            style="animation-delay: 2s"
                            aria-hidden="true"
                        />

                        <!-- ── Header ── -->
                        <div class="flex items-start justify-between mb-8 relative z-10">
                            <div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1
                                           bg-violet-500/10 border border-violet-500/20
                                           text-violet-400 text-[10px] font-black
                                           rounded-full mb-3 uppercase tracking-wider
                                           shadow-[0_0_18px_rgba(124,58,237,0.18)]"
                                >
                                    ✦ AI Bullet Optimizer
                                </span>
                                <h2
                                    id="magic-rewrite-title"
                                    class="text-2xl font-black text-white tracking-tight"
                                >
                                    AI Magic Rewrite
                                </h2>
                                <p
                                    id="magic-rewrite-desc"
                                    class="text-zinc-400 text-xs mt-1"
                                >
                                    Upgrade your resume bullet to executive-level impact and ATS compatibility instantly.
                                </p>
                            </div>

                            <!-- Close button -->
                            <button
                                type="button"
                                aria-label="Close AI Magic Rewrite"
                                class="p-2 shrink-0
                                       text-zinc-400 hover:text-white
                                       bg-zinc-900/50 hover:bg-zinc-800
                                       border border-zinc-800 hover:border-zinc-700
                                       rounded-full transition-all duration-300
                                       hover:rotate-90
                                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950
                                       shadow-sm"
                                @click="handleClose"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- ── Original bullet display ── -->
                        <div
                            class="mb-8 p-5
                                   bg-gradient-to-r from-violet-950/25 via-zinc-900/40 to-zinc-900/10
                                   border-l-4 border-l-violet-600 border border-zinc-800/80
                                   rounded-2xl relative overflow-hidden shadow-inner group"
                        >
                            <!-- Hover reflection -->
                            <span
                                class="absolute inset-0 bg-gradient-to-r from-violet-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                                aria-hidden="true"
                            />
                            <span
                                class="absolute top-0 right-0 px-3 py-1 bg-zinc-900 border-b border-l border-zinc-800/80 text-[9px] font-black text-zinc-500 rounded-bl-xl uppercase tracking-wider"
                                aria-hidden="true"
                            >
                                Original
                            </span>

                            <div class="text-[10px] font-black text-violet-400/80 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-ping" aria-hidden="true" />
                                Original Bullet Point:
                            </div>
                            <p class="text-zinc-200 text-sm font-medium leading-relaxed pr-20 group-hover:text-white transition-colors duration-300">
                                "{{ bulletText }}"
                            </p>
                        </div>

                        <!-- ════════════════════════════════════════════
                             LOADING STATE
                        ════════════════════════════════════════════ -->
                        <div
                            v-if="loading"
                            role="status"
                            aria-live="polite"
                            aria-label="Generating AI suggestions, please wait…"
                            class="py-12 flex flex-col items-center justify-center text-center"
                        >
                            <!-- Quantum triple-ring scanner -->
                            <div class="relative w-24 h-24 mb-7 flex items-center justify-center" aria-hidden="true">
                                <!-- Outer dashed orbit -->
                                <div class="absolute w-24 h-24 border-2 border-dashed border-violet-500/25 rounded-full animate-[spin_10s_linear_infinite]" />
                                <!-- Middle ring -->
                                <div class="absolute w-18 h-18 border-2 border-t-violet-500/70 border-b-violet-500/20 border-r-transparent border-l-transparent rounded-full animate-[spin_3s_linear_infinite]"
                                     style="width:4.5rem;height:4.5rem" />
                                <!-- Inner contra-rotating ring -->
                                <div class="absolute w-14 h-14 border-2 border-t-cyan-400 border-r-transparent border-b-cyan-400/30 border-l-transparent rounded-full animate-[spin_2s_linear_infinite_reverse]" />
                                <!-- Core glowing orb -->
                                <div class="absolute w-9 h-9 rounded-full bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center shadow-[0_0_28px_rgba(124,58,237,0.7)] animate-pulse">
                                    <svg class="w-4 h-4 text-yellow-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>

                            <h3 class="text-lg font-black text-white tracking-tight">Generating powerful executive bullet points…</h3>
                            <p class="text-zinc-400 text-xs mt-1.5 max-w-sm">
                                Tadween AI is scanning action verbs, applying the Google Resume Formula, and structuring ATS-optimised keywords.
                            </p>

                            <!-- Retry indicator -->
                            <p v-if="retryCount > 0" class="text-amber-400 text-[10px] font-bold mt-3 flex items-center gap-1.5" aria-live="polite">
                                <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89" />
                                </svg>
                                Retrying… attempt {{ retryCount }} of 2
                            </p>

                            <!-- Shimmer skeleton cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full mt-10" aria-hidden="true">
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="p-6 bg-zinc-900/20 border border-zinc-800/40 rounded-2xl space-y-4 animate-pulse relative overflow-hidden"
                                >
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.03] to-transparent -translate-x-full animate-[shimmer_1.8s_ease-in-out_infinite]" />
                                    <div class="h-4 bg-zinc-800/60 rounded-full w-28" />
                                    <div class="space-y-2.5">
                                        <div class="h-3 bg-zinc-800/40 rounded-full w-full" />
                                        <div class="h-3 bg-zinc-800/40 rounded-full w-5/6" />
                                        <div class="h-3 bg-zinc-800/40 rounded-full w-3/4" />
                                    </div>
                                    <div class="h-10 bg-zinc-800/50 rounded-xl w-full" />
                                </div>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════════
                             ERROR STATE
                        ════════════════════════════════════════════ -->
                        <div
                            v-else-if="error"
                            role="alert"
                            class="py-12 text-center max-w-lg mx-auto"
                        >
                            <div class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-500 shadow-[0_0_24px_rgba(239,68,68,0.25)]" aria-hidden="true">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-white">Analysis Interrupted</h3>
                            <p class="text-zinc-400 text-xs mt-2 leading-relaxed">{{ error }}</p>

                            <div class="flex items-center justify-center gap-3 mt-6">
                                <button
                                    type="button"
                                    class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600
                                           hover:from-violet-500 hover:to-indigo-500
                                           text-white rounded-full text-xs font-black
                                           shadow-lg shadow-indigo-500/20
                                           focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950
                                           transition-all active:scale-95
                                           flex items-center gap-1.5"
                                    @click="handleRetry"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3m-3-3v12" />
                                    </svg>
                                    Try Again
                                </button>
                                <button
                                    type="button"
                                    class="px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white rounded-full text-xs font-black border border-zinc-700 transition-all active:scale-95"
                                    @click="handleClose"
                                >
                                    Dismiss
                                </button>
                            </div>
                        </div>

                        <!-- ════════════════════════════════════════════
                             SUGGESTIONS GRID
                        ════════════════════════════════════════════ -->
                        <div
                            v-else-if="suggestions.length > 0"
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10"
                            role="list"
                            aria-label="AI-generated bullet point alternatives"
                        >
                            <RewriteSuggestionCard
                                v-for="(suggestion, idx) in suggestions"
                                :key="idx"
                                :suggestion="suggestion"
                                :index="idx"
                                class="animate-pop-in"
                                :style="{ animationDelay: `${idx * 120}ms`, opacity: 0 }"
                                @select="handleSelect"
                            />
                        </div>

                        <!-- Empty suggestions fallback -->
                        <div
                            v-else
                            class="py-10 text-center text-zinc-500 text-sm"
                            role="status"
                        >
                            No suggestions could be generated. Please try again.
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Card staggered pop-in entrance */
@keyframes slideUp {
    0%   { opacity: 0; transform: translateY(22px) scale(0.96); }
    100% { opacity: 1; transform: translateY(0)    scale(1);    }
}
.animate-pop-in {
    animation: slideUp 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Ambient glow orb pulse */
@keyframes pulseGlow {
    0%, 100% { opacity: 0.25; transform: scale(1); }
    50%       { opacity: 0.55; transform: scale(1.07); }
}
.animate-pulse-glow {
    animation: pulseGlow 5s ease-in-out infinite;
}

/* Skeleton shimmer sweep */
@keyframes shimmer {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
</style>
