<script setup>
import { computed } from 'vue'

/**
 * RewriteSuggestionCard.vue — Premium suggestion card for the AI Magic Rewrite modal.
 *
 * Accessibility:
 *  - Region role with descriptive aria-label
 *  - "Use This Version" button has descriptive aria-label
 *
 * Performance:
 *  - All computed properties memoized by Vue
 *  - No watchers or side-effects
 */

const props = defineProps({
    suggestion: {
        type: String,
        required: true,
    },
    index: {
        type: Number,
        default: 0,
    },
})

const emit = defineEmits(['select'])

// ─── Smart badge detection (all memoized) ───────────────────────────────────

/** True when the suggestion contains measurable numeric metrics. */
const isQuantified = computed(() =>
    /\b\d[\d,]*(?:\.\d+)?(?:\s?(?:%|x|k|m|\+|pts|points|percent|million|thousand|billion|hours?|days?|weeks?|users?|clients?|times?))\b/i.test(
        props.suggestion
    )
)

/** Detect the leading action verb (first word). */
const actionVerb = computed(() => {
    const match = props.suggestion.match(/^([A-Z][a-z]+)/)
    return match ? match[1] : null
})

/** True when suggestion appears optimised for ATS (contains at least one tech/role keyword). */
const isAtsOptimized = computed(() =>
    /\b(REST|API|SQL|AWS|cloud|CI\/CD|docker|kubernetes|agile|scrum|lean|OKR|KPI|SLA|SaaS|B2B|B2C|DevOps|Git|Jira|Confluence|Salesforce)\b/i.test(
        props.suggestion
    )
)

const selectSuggestion = () => emit('select', props.suggestion)
</script>

<template>
    <article
        role="region"
        :aria-label="`Suggestion ${index + 1}`"
        class="group flex flex-col justify-between p-6
               bg-gradient-to-b from-zinc-900/60 to-zinc-950/90
               border border-zinc-800 hover:border-violet-500/50
               rounded-2xl transition-all duration-500
               hover:-translate-y-2
               shadow-lg hover:shadow-[0_15px_40px_-15px_rgba(139,92,246,0.35)]
               backdrop-blur-md relative overflow-hidden"
    >
        <!-- Corner glow -->
        <span
            class="absolute top-0 right-0 w-36 h-36 bg-gradient-to-br from-violet-500/15 via-indigo-500/5 to-transparent rounded-full blur-2xl pointer-events-none group-hover:scale-150 transition-all duration-700"
            aria-hidden="true"
        />

        <div>
            <!-- Index + Badges row -->
            <div class="flex flex-wrap items-center gap-1.5 mb-4">
                <!-- Numbered badge -->
                <span
                    class="w-6 h-6 flex items-center justify-center rounded-lg
                           bg-violet-600/20 border border-violet-500/30
                           text-xs font-black text-violet-300
                           shadow-[0_0_10px_rgba(124,58,237,0.2)]
                           group-hover:shadow-[0_0_16px_rgba(124,58,237,0.55)]
                           transition-shadow duration-500"
                >
                    {{ index + 1 }}
                </span>

                <!-- Action Verb badge — only rendered when verb detected -->
                <span
                    v-if="actionVerb"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                           text-[9px] font-black tracking-wider uppercase
                           bg-emerald-500/10 border border-emerald-500/20 text-emerald-400"
                >
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ actionVerb }}
                </span>

                <!-- Quantified vs Result-Driven -->
                <span
                    v-if="isQuantified"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                           text-[9px] font-black tracking-wider uppercase
                           bg-cyan-500/10 border border-cyan-500/20 text-cyan-400
                           shadow-[0_0_10px_rgba(6,182,212,0.12)]"
                >
                    📊 Quantified
                </span>
                <span
                    v-else
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                           text-[9px] font-black tracking-wider uppercase
                           bg-amber-500/10 border border-amber-500/20 text-amber-400
                           shadow-[0_0_10px_rgba(245,158,11,0.12)]"
                >
                    📈 Result-Driven
                </span>

                <!-- ATS badge — conditional on keyword detection -->
                <span
                    v-if="isAtsOptimized"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                           text-[9px] font-black tracking-wider uppercase
                           bg-indigo-500/10 border border-indigo-500/20 text-indigo-400"
                >
                    ⚡ ATS Ready
                </span>
            </div>

            <!-- Suggestion text -->
            <p
                class="text-zinc-200 text-[13px] font-medium leading-relaxed mb-6
                       group-hover:text-white transition-colors duration-300"
            >
                "{{ suggestion }}"
            </p>
        </div>

        <!-- CTA Button -->
        <button
            type="button"
            :aria-label="`Use suggestion ${index + 1}: ${suggestion.slice(0, 60)}…`"
            class="w-full py-2.5 px-4 text-xs font-black rounded-xl
                   text-zinc-300 hover:text-white
                   bg-zinc-800/80 hover:bg-gradient-to-r hover:from-violet-600 hover:to-indigo-600
                   border border-zinc-700/60 hover:border-violet-500/50
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950
                   transition-all duration-300
                   flex items-center justify-center gap-2
                   group-hover:shadow-[0_0_20px_rgba(124,58,237,0.3)]
                   active:scale-[0.97]"
            @click="selectSuggestion"
        >
            <span>Use This Version</span>
            <svg
                class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1 duration-300"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </button>
    </article>
</template>
