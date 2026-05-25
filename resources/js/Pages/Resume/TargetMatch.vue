<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    resume: Object,
    matchResult: Object, // Will contain: match_score, summary, missing_keywords, tailoring_suggestions
});

const form = useForm({
    job_title: '',
    job_url: '',
    job_description: '',
});

const submit = () => {
    form.post(route('resumes.target.process', props.resume.id), {
        onSuccess: () => {
            // Success matches
        }
    });
};

const getScoreColor = (score) => {
    if (score >= 80) return 'text-emerald-500 dark:text-emerald-400';
    if (score >= 60) return 'text-amber-500 dark:text-amber-400';
    return 'text-rose-500 dark:text-rose-400';
};

const getScoreBg = (score) => {
    if (score >= 80) return 'bg-emerald-500';
    if (score >= 60) return 'bg-amber-500';
    return 'bg-rose-500';
};

const getScoreRing = (score) => {
    if (score >= 80) return 'stroke-emerald-500';
    if (score >= 60) return 'stroke-amber-500';
    return 'stroke-rose-500';
};

const getImportanceClass = (importance) => {
    const imp = String(importance).toUpperCase();
    if (imp === 'HIGH') return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
    if (imp === 'MEDIUM') return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
    return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
};

const getImportanceIcon = (importance) => {
    const imp = String(importance).toUpperCase();
    if (imp === 'HIGH') return '🔴';
    if (imp === 'MEDIUM') return '🟡';
    return '🟢';
};

// UI interactions
const appliedSuggestions = ref({});
const dismissedSuggestions = ref({});

const applySuggestion = (index) => {
    appliedSuggestions.value[index] = true;
};

const dismissSuggestion = (index) => {
    dismissedSuggestions.value[index] = true;
};
</script>

<template>
    <Head :title="`Target to Job - ${resume.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-extrabold tracking-tight text-zinc-900 dark:text-white flex items-center gap-2">
                    <Link :href="route('dashboard')" class="text-zinc-400 hover:text-indigo-500 dark:hover:text-indigo-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"></path></svg>
                    </Link>
                    <span class="text-zinc-300 dark:text-zinc-800">/</span>
                    <span class="bg-gradient-to-r from-indigo-500 to-violet-500 bg-clip-text text-transparent font-black">Target Job Matcher</span>
                </h2>
                <Link :href="route('resumes.report', resume.id)" class="inline-flex px-4.5 py-2.5 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 text-zinc-700 dark:text-zinc-300 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all shadow-sm">
                    ← Back to Report
                </Link>
            </div>
        </template>

        <div class="py-10 min-h-screen">
            <div class="mx-auto max-w-4xl px-6 sm:px-8">

                <!-- ============================================ -->
                <!-- IF MATCH RESULT IS READY                     -->
                <!-- ============================================ -->
                <div v-if="matchResult" class="space-y-8">

                    <!-- ── Compatibility Score Hero ── -->
                    <div class="relative bg-white dark:bg-zinc-900/40 rounded-3xl overflow-hidden shadow-sm border border-zinc-200/85 dark:border-zinc-800/80">
                        <!-- Gradient top accent -->
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500"></div>
                        
                        <div class="p-8 md:p-10 flex flex-col md:flex-row items-center gap-8">
                            <!-- Score Radial Ring -->
                            <div class="relative w-36 h-36 flex items-center justify-center shrink-0">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" class="stroke-zinc-100 dark:stroke-zinc-800/60" stroke-width="2.5"></circle>
                                    <circle
                                        cx="18" cy="18" r="15.9155" fill="none"
                                        :class="getScoreRing(matchResult.match_score)"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        :stroke-dasharray="`${matchResult.match_score}, 100`"
                                        style="transition: stroke-dasharray 1.5s ease-in-out;"
                                    ></circle>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-black text-zinc-900 dark:text-white leading-none">{{ matchResult.match_score }}%</span>
                                    <span class="text-[8px] text-zinc-400 dark:text-zinc-500 uppercase tracking-widest font-black mt-1.5 leading-none">Match Score</span>
                                </div>
                            </div>

                            <div class="text-center md:text-left flex-1 min-w-0">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/5 text-indigo-500 text-[9px] font-black rounded-full mb-3 uppercase tracking-wider border border-indigo-500/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                    Compatibility Audit Complete
                                </span>
                                <h3 class="text-xl font-black text-zinc-900 dark:text-white mb-2 tracking-tight truncate">{{ resume.title }}</h3>
                                <p class="text-zinc-500 dark:text-zinc-400 text-xs font-semibold leading-relaxed">
                                    {{ matchResult.summary }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Missing skills tags ── -->
                    <div v-if="(matchResult.missingSkills && matchResult.missingSkills.length) || (matchResult.keywordGaps && matchResult.keywordGaps.length)">
                        <div class="bg-white dark:bg-zinc-900/40 rounded-3xl p-8 shadow-sm border border-zinc-200/85 dark:border-zinc-800/80 space-y-5">
                            <h4 class="text-sm font-black text-zinc-900 dark:text-white flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-500 text-xs font-bold border border-rose-500/20">⚠</span>
                                Skills & Keyword Gaps
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="skill in (matchResult.missingSkills || [])" :key="'s-'+skill"
                                      class="px-3 py-1.5 rounded-xl text-[10.5px] font-bold bg-rose-500/5 text-rose-500 border border-rose-500/15 shadow-sm">
                                    {{ skill }}
                                </span>
                                <span v-for="kw in (matchResult.keywordGaps || [])" :key="'k-'+kw"
                                      class="px-3 py-1.5 rounded-xl text-[10.5px] font-bold bg-amber-500/5 text-amber-500 border border-amber-500/15 shadow-sm">
                                    {{ typeof kw === 'string' ? kw : kw.keyword }}
                                </span>
                            </div>
                            <div class="pt-2">
                                <Link :href="route('resumes.report', resume.id)" class="inline-flex px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:shadow-lg hover:shadow-indigo-500/25 transition-all hover:scale-[1.01] active:scale-[0.99]">
                                    Improve Resume →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- ── Missing Keywords Section ── -->
                    <div v-if="matchResult.missing_keywords && matchResult.missing_keywords.length > 0">
                        <div class="bg-white dark:bg-zinc-900/40 rounded-3xl overflow-hidden shadow-sm border border-zinc-200/85 dark:border-zinc-800/80">
                            <!-- Header -->
                            <div class="px-8 py-5 border-b border-zinc-150 dark:border-zinc-800/80 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center text-white text-xs shadow-lg shadow-rose-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">Missing Core Keywords & Skills</h4>
                                    <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold mt-0.5">ATS filters look for these keywords. Add them into your experience.</p>
                                </div>
                            </div>
                            
                            <div class="p-6 space-y-3">
                                <div
                                    v-for="(kw, idx) in matchResult.missing_keywords"
                                    :key="idx"
                                    class="p-4 bg-zinc-50/40 dark:bg-zinc-950/20 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-zinc-200/80 dark:border-zinc-800/50 hover:border-indigo-500/20 dark:hover:border-indigo-500/10 transition-colors"
                                >
                                    <div class="space-y-1.5 min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs">{{ getImportanceIcon(kw.importance) }}</span>
                                            <span class="text-sm font-extrabold text-zinc-800 dark:text-white tracking-tight">{{ kw.keyword }}</span>
                                        </div>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 leading-relaxed font-semibold">{{ kw.reason }}</p>
                                    </div>
                                    <span :class="`px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider border self-start sm:self-center shrink-0 ${getImportanceClass(kw.importance)}`">
                                        {{ kw.importance }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Dynamic Tailoring Suggestions ── -->
                    <div v-if="matchResult.tailoring_suggestions && matchResult.tailoring_suggestions.length > 0">
                        <div class="mb-5 flex items-center gap-3 px-1">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xs shadow-lg shadow-indigo-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">AI Tailoring Suggestions</h4>
                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold mt-0.5">Original vs rewritten bullets optimized for your target role.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(rec, index) in matchResult.tailoring_suggestions"
                                :key="index"
                                v-show="!dismissedSuggestions[index]"
                                class="bg-white dark:bg-zinc-900/40 rounded-3xl shadow-sm border border-zinc-200/85 dark:border-zinc-800/80 overflow-hidden transition-all duration-300 hover:shadow-md"
                            >
                                <!-- Suggestion Header -->
                                <div class="px-6 py-4 border-b border-zinc-150 dark:border-zinc-800/80 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-900/20">
                                    <span class="px-2.5 py-1 bg-zinc-100 dark:bg-zinc-800 text-[9px] font-black text-zinc-500 dark:text-zinc-400 rounded-lg uppercase tracking-wider border border-zinc-200/80 dark:border-zinc-700">{{ rec.section }}</span>
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        Optimization Available
                                    </span>
                                </div>

                                <div class="p-6 space-y-4">
                                    <!-- Original -->
                                    <div>
                                        <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 block mb-1.5 uppercase tracking-wider">Original</span>
                                        <p class="text-zinc-600 dark:text-zinc-300 text-xs font-semibold line-through decoration-rose-500/40 p-3.5 bg-rose-500/5 rounded-xl border border-rose-500/10 leading-relaxed font-mono">
                                            "{{ rec.original_bullet }}"
                                        </p>
                                    </div>

                                    <!-- Suggested Rewrite -->
                                    <div class="p-4 bg-gradient-to-br from-indigo-500/10 via-violet-500/5 to-white border border-indigo-100/80 rounded-2xl shadow-sm">
                                        <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-2 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"></path></svg>
                                            AI Optimized Version
                                        </span>
                                        <p class="text-zinc-950 dark:text-indigo-200 text-xs font-black leading-relaxed font-mono">
                                            {{ rec.suggested_bullet }}
                                        </p>
                                        <p class="text-zinc-400 dark:text-zinc-500 text-[11px] mt-2.5 italic leading-relaxed font-semibold flex items-start gap-1">
                                            <span>💡</span>
                                            <span>{{ rec.feedback }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="px-6 py-4 border-t border-zinc-150 dark:border-zinc-800/80 flex items-center gap-3 justify-end bg-zinc-50/50 dark:bg-zinc-900/20">
                                    <button
                                        @click="dismissSuggestion(index)"
                                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 text-xs font-bold transition-colors px-3.5 py-1.5 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-850"
                                    >
                                        Dismiss
                                    </button>
                                    <button
                                        @click="applySuggestion(index)"
                                        :disabled="appliedSuggestions[index]"
                                        :class="[
                                            'px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm border border-transparent',
                                            appliedSuggestions[index]
                                                ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500'
                                                : 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:shadow-lg hover:scale-[1.01] active:scale-[0.99]'
                                        ]"
                                    >
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        {{ appliedSuggestions[index] ? 'Applied ✓' : 'Apply Rewrite' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Re-compare button -->
                    <div class="flex justify-center pt-4">
                        <button
                            @click="matchResult = null"
                            class="px-6 py-3 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800/80 text-zinc-650 dark:text-zinc-300 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all shadow-sm hover:shadow-md"
                        >
                            ← Compare Another Description
                        </button>
                    </div>
                </div>

                <!-- ============================================ -->
                <!-- FORM INPUT STATE                             -->
                <!-- ============================================ -->
                <div v-else class="bg-white dark:bg-zinc-900/40 rounded-3xl shadow-lg border border-zinc-200/85 dark:border-zinc-800/80 overflow-hidden">
                    <!-- Card Top Gradient Accent -->
                    <div class="h-1 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500"></div>

                    <div class="p-8 md:p-10">
                        <!-- Heading -->
                        <div class="mb-8 text-center sm:text-left">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-500/5 text-indigo-500 text-[9px] font-black rounded-full mb-4 uppercase tracking-wider border border-indigo-500/10">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                ATS Alignment Optimizer
                            </span>
                            <h3 class="text-2xl font-black text-zinc-900 dark:text-white mb-2 tracking-tight">Target to Specific Job</h3>
                            <p class="text-zinc-500 dark:text-zinc-400 text-xs font-semibold leading-relaxed max-w-xl">
                                Paste the target Job Title and the full Description below. AI will parse the requirements, run an alignment comparison against your CV, identify keyword gaps, and write personalized bullet optimizations.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- LinkedIn / Job URL -->
                            <div class="space-y-2">
                                <label for="job_url" class="block text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                                    Job URL <span class="text-zinc-400 dark:text-zinc-650 font-normal normal-case tracking-normal">(optional)</span>
                                </label>
                                <input
                                    id="job_url"
                                    type="url"
                                    v-model="form.job_url"
                                    placeholder="https://linkedin.com/jobs/view/..."
                                    class="w-full px-4 py-3 bg-zinc-50/40 dark:bg-zinc-950/50 border border-zinc-200 dark:border-zinc-800/60 rounded-xl text-zinc-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500/15 focus:border-indigo-500 transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600"
                                />
                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-semibold leading-relaxed">LinkedIn URLs require the pasted description — direct scraping is not supported.</p>
                            </div>

                            <!-- Job Title -->
                            <div class="space-y-2">
                                <label for="job_title" class="block text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Target Job Title</label>
                                <input
                                    id="job_title"
                                    type="text"
                                    v-model="form.job_title"
                                    placeholder="e.g. Senior Full-Stack Laravel Developer"
                                    class="w-full px-4 py-3 bg-zinc-50/40 dark:bg-zinc-950/50 border border-zinc-200 dark:border-zinc-800/60 rounded-xl text-zinc-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500/15 focus:border-indigo-500 transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600"
                                    required
                                />
                                <span v-if="form.errors.job_title" class="text-xs text-rose-500 block font-bold">{{ form.errors.job_title }}</span>
                            </div>

                            <!-- Job Description -->
                            <div class="space-y-2">
                                <label for="job_description" class="block text-[10px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Job Description</label>
                                <textarea
                                    id="job_description"
                                    v-model="form.job_description"
                                    rows="9"
                                    placeholder="Paste the complete job description details, qualifications, and requirements here..."
                                    class="w-full px-4 py-3 bg-zinc-50/40 dark:bg-zinc-950/50 border border-zinc-200 dark:border-zinc-800/60 rounded-xl text-zinc-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-indigo-500/15 focus:border-indigo-500 transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600 font-sans resize-none"
                                    required
                                ></textarea>
                                <div class="flex items-center justify-between">
                                    <span v-if="form.errors.job_description" class="text-xs text-rose-500 block font-bold">{{ form.errors.job_description }}</span>
                                    <span class="text-[10px] font-black text-zinc-400 dark:text-zinc-500 ml-auto flex items-center gap-1">
                                        🪙 Requires 1 Credit
                                    </span>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="pt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full sm:w-auto relative group overflow-hidden px-8 py-3.5 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-xl shadow-indigo-600/10 hover:shadow-indigo-600/35 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center gap-2"
                                >
                                    <span class="relative z-10 flex items-center gap-2">
                                        <span v-if="form.processing" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        <span v-if="form.processing">Analyzing Alignment…</span>
                                        <span v-else>Score Compatibility & Suggest Keywords</span>
                                        <span v-if="!form.processing" class="group-hover:translate-x-1 transition-transform">→</span>
                                    </span>
                                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
