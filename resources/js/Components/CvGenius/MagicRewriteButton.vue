<script setup>
/**
 * MagicRewriteButton.vue — Premium trigger button for the ✦ AI Magic Rewrite feature.
 *
 * Emits: open({ bulletText, issueId })
 *
 * Accessibility:
 *  - Descriptive aria-label derived from bullet text
 *  - Keyboard: space / enter handled natively via <button>
 */

const props = defineProps({
    /** The original weak bullet point text to be rewritten. */
    bulletText: {
        type: String,
        required: true,
    },
    /** Optional identifier for the checklist issue this bullet belongs to. */
    issueId: {
        type: [String, Number],
        default: null,
    },
    /** Visual size variant. */
    size: {
        type: String,
        default: 'sm',
        validator: (v) => ['xs', 'sm', 'md'].includes(v),
    },
})

const emit = defineEmits(['open'])

const handleClick = () => {
    emit('open', { bulletText: props.bulletText, issueId: props.issueId })
}
</script>

<template>
    <button
        type="button"
        :aria-label="`Rewrite with AI: ${bulletText.slice(0, 60)}…`"
        :class="[
            // Base layout
            'inline-flex items-center gap-1.5 font-black text-white rounded-full',
            'bg-gradient-to-r from-violet-600 to-indigo-600',
            'hover:from-violet-500 hover:to-indigo-500',
            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-950',
            'active:scale-[0.96] transition-all duration-200',
            'shadow-md shadow-indigo-500/25 border border-violet-500/20',
            'group relative overflow-hidden shrink-0 select-none',
            // Size variants
            size === 'xs' ? 'text-[10px] px-2.5 py-1' : '',
            size === 'sm' ? 'text-xs px-3.5 py-1.5' : '',
            size === 'md' ? 'text-sm px-4 py-2' : '',
        ]"
        @click="handleClick"
    >
        <!-- Shimmer hover overlay -->
        <span
            class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 pointer-events-none"
            aria-hidden="true"
        />

        <!-- Sparkle icon -->
        <svg
            class="shrink-0 text-yellow-300 group-hover:rotate-[20deg] transition-transform duration-300"
            :class="size === 'xs' ? 'w-3 h-3' : 'w-3.5 h-3.5'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
            />
        </svg>

        <span class="relative">✦ Magic Rewrite</span>
    </button>
</template>
