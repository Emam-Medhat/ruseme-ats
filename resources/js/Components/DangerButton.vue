<template>
    <button
        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-gradient-to-r from-rose-600 to-red-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-md shadow-rose-500/10 transition-all duration-200 hover:from-rose-500 hover:to-red-500 hover:shadow-lg hover:shadow-rose-500/20 hover:scale-[1.01] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-rose-500/50 disabled:opacity-50 disabled:pointer-events-none"
    >
        <slot />
    </button>
</template>
