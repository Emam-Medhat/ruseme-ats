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

const getImportanceClass = (importance) => {
    const imp = String(importance).toUpperCase();
    if (imp === 'HIGH') return 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-100 dark:border-rose-900/30';
    if (imp === 'MEDIUM') return 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/30';
    return 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30';
};

// UI interactions to wow the user
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
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <Link :href="route('dashboard')" class="text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition">&larr; Dashboard</Link>
                    <span class="text-gray-300 dark:text-zinc-700">/</span>
                    Target to Job Matcher
                </h2>
                <Link :href="route('resumes.report', resume.id)" class="px-4 py-2 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-semibold hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    &larr; Back to General Audit
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50/50 dark:bg-zinc-950 min-h-screen">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                
                <!-- IF MATCH RESULT IS READY -->
                <div v-if="matchResult" class="space-y-8">
                    <!-- Compatibility Overview Card -->
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-zinc-800/80 flex flex-col md:flex-row items-center gap-8">
                        <!-- Score Radial circular -->
                        <div class="relative w-36 h-36 flex items-center justify-center shrink-0">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-gray-100 dark:text-zinc-800" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                                <path :class="getScoreColor(matchResult.match_score)" :stroke-dasharray="`${matchResult.match_score}, 100`" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black text-gray-900 dark:text-white">{{ matchResult.match_score }}%</span>
                                <span class="text-[9px] text-gray-400 dark:text-zinc-500 uppercase tracking-widest font-extrabold mt-0.5">Match</span>
                            </div>
                        </div>

                        <div class="text-center md:text-left">
                            <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-full mb-3 uppercase tracking-wider">
                                Compatibility Audit Result
                            </span>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">{{ resume.title }}</h3>
                            <p class="text-gray-600 dark:text-zinc-400 text-sm leading-relaxed">
                                {{ matchResult.summary }}
                            </p>
                        </div>
                    </div>

                    <!-- Missing skills tags -->
                    <div v-if="(matchResult.missingSkills && matchResult.missingSkills.length) || (matchResult.keywordGaps && matchResult.keywordGaps.length)" class="space-y-4">
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">Skills & keyword gaps</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="skill in (matchResult.missingSkills || [])" :key="'s-'+skill"
                                  class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-950/30 dark:text-rose-400">
                                {{ skill }}
                            </span>
                            <span v-for="kw in (matchResult.keywordGaps || [])" :key="'k-'+kw"
                                  class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/30 dark:text-amber-400">
                                {{ typeof kw === 'string' ? kw : kw.keyword }}
                            </span>
                        </div>
                        <Link :href="route('resumes.report', resume.id)" class="inline-flex px-5 py-2.5 bg-indigo-600 text-white rounded-full text-xs font-bold hover:bg-indigo-700">
                            Improve Resume →
                        </Link>
                    </div>

                    <!-- Missing Keywords Section -->
                    <div v-if="matchResult.missing_keywords && matchResult.missing_keywords.length > 0" class="space-y-4">
                        <div class="ml-1">
                            <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Missing Core Keywords & Skills
                            </h4>
                            <p class="text-xs text-gray-400 mt-0.5">ATS filters look for these specific keywords in your CV. Try adding them into your experience description.</p>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden shadow-sm border border-gray-100 dark:border-zinc-800/80 p-6 space-y-3">
                            <div 
                                v-for="(kw, idx) in matchResult.missing_keywords" 
                                :key="idx"
                                class="p-4 bg-gray-50/50 dark:bg-zinc-800/30 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-gray-100/50 dark:border-zinc-800/50"
                            >
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-gray-800 dark:text-white block">{{ kw.keyword }}</span>
                                    <p class="text-xs text-gray-500 dark:text-zinc-400">{{ kw.reason }}</p>
                                </div>
                                <span :class="`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border self-start sm:self-center ${getImportanceClass(kw.importance)}`">
                                    {{ kw.importance }} Importance
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Tailoring Suggestions -->
                    <div v-if="matchResult.tailoring_suggestions && matchResult.tailoring_suggestions.length > 0" class="space-y-4">
                        <div class="ml-1">
                            <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Tailoring Suggestions (Original vs Rewritten)
                            </h4>
                            <p class="text-xs text-gray-400 mt-0.5">Tadween AI customized these weak bullets from your CV to incorporate the target job's keywords dynamically.</p>
                        </div>

                        <div class="space-y-4">
                            <div 
                                v-for="(rec, index) in matchResult.tailoring_suggestions" 
                                :key="index" 
                                v-show="!dismissedSuggestions[index]"
                                class="bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800/80 transition duration-300"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <span class="px-2.5 py-0.5 bg-gray-100 dark:bg-zinc-800 text-[10px] font-black text-gray-500 rounded uppercase tracking-wider">{{ rec.section }}</span>
                                    <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">REDRAFT REQUIRED</span>
                                </div>

                                <div class="space-y-4">
                                    <div class="text-sm">
                                        <span class="text-[10px] font-bold text-gray-400 block mb-1">Original Text from CV:</span>
                                        <p class="text-gray-700 dark:text-zinc-300 font-sans line-through decoration-rose-300 dark:decoration-rose-900/60 p-3 bg-rose-50/20 dark:bg-rose-900/5 rounded-xl border border-rose-100/30 dark:border-rose-900/10">
                                            "{{ rec.original_bullet }}"
                                        </p>
                                    </div>

                                    <div class="p-4 bg-indigo-50/50 dark:bg-indigo-950/15 border border-indigo-100/60 dark:border-indigo-900/30 rounded-2xl">
                                        <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1.5">TADWEEN CAN HELP DRAFT THIS IMPROVEMENT</span>
                                        <p class="text-indigo-950 dark:text-indigo-200 text-sm font-semibold flex items-start gap-2 leading-relaxed">
                                            <span class="text-indigo-500">&rarr;</span>
                                            {{ rec.suggested_bullet }}
                                        </p>
                                        <p class="text-gray-500 dark:text-zinc-400 text-xs mt-2 italic leading-relaxed">
                                            {{ rec.feedback }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-5 flex items-center gap-4 justify-end">
                                    <button 
                                        @click="dismissSuggestion(index)"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-300 text-xs font-bold transition flex items-center gap-1.5"
                                    >
                                        Dismiss
                                    </button>
                                    <button 
                                        @click="applySuggestion(index)"
                                        :disabled="appliedSuggestions[index]"
                                        :class="`px-5 py-2 rounded-full text-xs font-bold transition flex items-center gap-1.5 shadow-sm ${
                                            appliedSuggestions[index] 
                                            ? 'bg-emerald-500 text-white' 
                                            : 'bg-indigo-600 hover:bg-indigo-700 text-white'
                                        }`"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        {{ appliedSuggestions[index] ? 'Applied' : 'Apply' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center pt-4">
                        <button 
                            @click="matchResult = null"
                            class="px-6 py-3 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 text-gray-700 dark:text-zinc-300 rounded-full text-sm font-bold hover:bg-gray-50 dark:hover:bg-zinc-800 transition"
                        >
                            &larr; Compare Another Description
                        </button>
                    </div>
                </div>

                <!-- FORM INPUT STATE -->
                <div v-else class="bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-zinc-800/80">
                    <div class="mb-8 text-center sm:text-left">
                        <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 text-xs font-black rounded-full mb-3 uppercase tracking-wider">
                            ATS Alignment Optimizer
                        </span>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Target to Specific Job</h3>
                        <p class="text-gray-500 dark:text-zinc-400 text-sm leading-relaxed">
                            Paste the target Job Title and the full Description below. Tadween AI will parse the requirements, run an alignment comparison against your CV, identify keyword gaps, and write personalized bullet optimizations.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- LinkedIn / Job URL -->
                        <div>
                            <label for="job_url" class="block text-xs font-black text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-2">Job URL (optional)</label>
                            <input
                                id="job_url"
                                type="url"
                                v-model="form.job_url"
                                placeholder="https://linkedin.com/jobs/view/..."
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800/30 border border-gray-200 dark:border-zinc-700/60 rounded-2xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 transition"
                            />
                            <p class="text-[10px] text-gray-400 mt-1">LinkedIn URLs need pasted description — we cannot scrape them fully.</p>
                        </div>

                        <!-- Job Title -->
                        <div>
                            <label for="job_title" class="block text-xs font-black text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-2">Target Job Title</label>
                            <input 
                                id="job_title"
                                type="text"
                                v-model="form.job_title"
                                placeholder="e.g. Senior Full-Stack Laravel Developer"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800/30 border border-gray-200 dark:border-zinc-700/60 rounded-2xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:text-gray-400 dark:placeholder:text-zinc-600"
                                required
                            />
                            <span v-if="form.errors.job_title" class="text-xs text-rose-500 mt-1 block">{{ form.errors.job_title }}</span>
                        </div>

                        <!-- Job Description -->
                        <div>
                            <label for="job_description" class="block text-xs font-black text-gray-500 dark:text-zinc-400 uppercase tracking-widest mb-2">Job Description</label>
                            <textarea 
                                id="job_description"
                                v-model="form.job_description"
                                rows="10"
                                placeholder="Paste the complete job description details, qualifications, and requirements here..."
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-zinc-800/30 border border-gray-200 dark:border-zinc-700/60 rounded-2xl text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:text-gray-400 dark:placeholder:text-zinc-600 font-sans"
                            ></textarea>
                            <div class="flex items-center justify-between mt-1">
                                <span v-if="form.errors.job_description" class="text-xs text-rose-500 block">{{ form.errors.job_description }}</span>
                                <span class="text-[10px] font-bold text-gray-400 ml-auto">Requires 1 Credit</span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="pt-4 flex justify-end">
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full text-sm font-bold shadow-lg shadow-indigo-500/20 transition disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <span v-if="form.processing">Comparing Alignment...</span>
                                <span v-else>Score Compatibility & Suggest Keywords</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
