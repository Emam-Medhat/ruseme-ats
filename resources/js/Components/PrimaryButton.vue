<template>
    <button
        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-md shadow-indigo-500/10 transition-all duration-200 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-500/20 hover:scale-[1.01] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-indigo-500/50 disabled:opacity-50 disabled:pointer-events-none"
    >
        <slot />
    </button>
</template>
