<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    resume: { type: Object, required: true },
    report: { type: Object, required: true },
    analysis: { type: Object, default: null },
});

const analysis = computed(() => props.analysis ?? null);
const hasAnalysis = computed(() => analysis.value !== null && typeof analysis.value === 'object');

const num = (v, fallback = 0) => {
    const n = Number(v);
    return Number.isFinite(n) ? Math.min(100, Math.max(0, n)) : fallback;
};

const overallScore = computed(() => num(props.report?.overall_score ?? analysis.value?.overall_score, 0));
const impactScore = computed(() => num(analysis.value?.impact_score ?? props.report?.impact_score?.score, 0));
const brevityScore = computed(() => num(analysis.value?.brevity_score ?? props.report?.brevity_score?.score, 0));
const styleScore = computed(() => num(analysis.value?.style_score ?? props.report?.style_score?.score, 0));
const atsScore = computed(() => num(analysis.value?.ats_compatibility_score ?? props.report?.ats_score, 0));

const subScores = computed(() => [
    { label: 'Impact', value: impactScore.value },
    { label: 'Brevity', value: brevityScore.value },
    { label: 'Style', value: styleScore.value },
    { label: 'ATS', value: atsScore.value },
]);

const topRecommendations = computed(() => analysis.value?.top_recommendations ?? []);
const missingKeywords = computed(() => analysis.value?.missing_keywords ?? []);
const strengths = computed(() => analysis.value?.strengths ?? []);
const criticalIssues = computed(() => analysis.value?.critical_issues ?? []);
const roadmap = computed(() => analysis.value?.roadmap ?? []);
const wordAnalysis = computed(() => analysis.value?.word_analysis ?? null);

const sectionKeys = ['experience', 'skills', 'education', 'summary', 'projects', 'languages', 'certifications'];

const visibleSections = computed(() => {
    const sections = analysis.value?.sections ?? {};
    return sectionKeys
        .filter((key) => sections[key]?.found === true)
        .map((key) => ({
            key,
            name: key.charAt(0).toUpperCase() + key.slice(1),
            ...sections[key],
        }));
});

const issuesCount = computed(() => {
    let n = criticalIssues.value.length;
    topRecommendations.value.forEach(() => {});
    visibleSections.value.forEach((s) => {
        n += (s.issues || []).length;
    });
    n += topRecommendations.value.filter((r) => String(r.priority).toLowerCase() === 'high').length;
    return n;
});

const ringProgress = ref(0);
onMounted(() => {
    requestAnimationFrame(() => {
        ringProgress.value = overallScore.value;
    });
});

const ringDashoffset = computed(() => 100 - ringProgress.value);

const openSections = ref({});
const toggleSection = (key) => {
    openSections.value[key] = !openSections.value[key];
};

const appliedIds = ref([]);
const dismissedIds = ref([]);

const applySuggestion = (id) => {
    const n = Number(id);
    if (!appliedIds.value.includes(n)) {
        appliedIds.value.push(n);
    }
};
const dismissSuggestion = (id) => {
    dismissedIds.value.push(Number(id));
};

const appliedCount = computed(() => appliedIds.value.length);
const selectedTemplate = ref('clean');

const downloadUrl = (template) => {
    const params = new URLSearchParams();
    appliedIds.value.forEach((id) => params.append('applied[]', id));
    params.set('template', template);
    return route('resumes.download', { resume: props.resume.id, template }) + '?' + params.toString();
};

const scoreColorClass = (s) => {
    if (s >= 75) return 'text-[#1D9E75]';
    if (s >= 50) return 'text-[#EF9F27]';
    return 'text-[#E24B4A]';
};

const scoreBarClass = (s) => {
    if (s >= 75) return 'bg-[#1D9E75]';
    if (s >= 50) return 'bg-[#EF9F27]';
    return 'bg-[#E24B4A]';
};

const ringStroke = (s) => {
    if (s >= 75) return '#1D9E75';
    if (s >= 50) return '#EF9F27';
    return '#E24B4A';
};

const priorityClass = (p) => {
    const v = String(p || '').toLowerCase();
    if (v === 'high' || v === 'critical') return 'bg-[#E24B4A]/20 text-[#E24B4A] border-[#E24B4A]/40';
    if (v === 'medium' || v === 'important') return 'bg-[#EF9F27]/20 text-[#EF9F27] border-[#EF9F27]/40';
    return 'bg-[#1D9E75]/20 text-[#1D9E75] border-[#1D9E75]/40';
};

const keywordClass = (imp) => {
    const v = String(imp || '').toLowerCase();
    if (v === 'critical') return 'bg-[#E24B4A]/20 text-rose-200 border-[#E24B4A]/50';
    if (v === 'important') return 'bg-[#EF9F27]/20 text-amber-200 border-[#EF9F27]/50';
    return 'bg-[#1D9E75]/20 text-emerald-200 border-[#1D9E75]/50';
};

const scoreTier = computed(() => {
    const s = overallScore.value;
    if (s >= 80) return 'Excellent';
    if (s >= 65) return 'Good';
    if (s >= 50) return 'Average';
    return 'Critical';
});

const analyzedDate = computed(() => {
    const d = props.report?.created_at;
    return d ? new Date(d).toLocaleDateString() : '—';
});

const languageLabel = computed(() => {
    const lang = analysis.value?.language_detected || 'english';
    return String(lang).charAt(0).toUpperCase() + String(lang).slice(1);
});

const targetScore = computed(() =>
    Math.min(100, overallScore.value + (roadmap.value[0]?.points_gain ?? 12))
);
</script>

<template>
    <Head :title="`Report — ${resume?.filename || resume?.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-zinc-400">
                    <Link :href="route('dashboard')" class="hover:text-[#7F77DD]">&larr; Dashboard</Link>
                    <span>/</span>
                    <span class="text-white">Analysis Report</span>
                </div>
                <Link
                    :href="route('resumes.target', resume.id)"
                    class="rounded-full bg-[#7F77DD] px-4 py-2 text-xs font-bold text-white hover:bg-[#534AB7]"
                >
                    Target to Job
                </Link>
            </div>
        </template>

        <div class="min-h-screen bg-[#0F0F1A] pb-20 text-white">
            <div class="mx-auto max-w-5xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
                <!-- Error state -->
                <section
                    v-if="!hasAnalysis"
                    class="rounded-2xl border border-[#E24B4A]/40 bg-[#1A1A2E] p-8 text-center"
                >
                    <h2 class="text-lg font-bold text-[#E24B4A]">Could not load analysis data</h2>
                    <p class="mt-2 text-sm text-white/60">
                        Re-upload your resume to generate a fresh report with the new scoring engine.
                    </p>
                    <Link
                        :href="route('dashboard')"
                        class="mt-4 inline-block rounded-full bg-[#7F77DD] px-5 py-2 text-sm font-bold"
                    >
                        Back to Dashboard
                    </Link>
                </section>

                <template v-else>
                    <!-- SCORE HERO -->
                    <section class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-8">
                        <div class="flex flex-col gap-8 md:flex-row md:items-center">
                            <div class="relative mx-auto h-44 w-44 shrink-0 md:mx-0">
                                <svg class="h-full w-full -rotate-90" viewBox="0 0 36 36">
                                    <path
                                        stroke="#2a2a40"
                                        stroke-width="3"
                                        fill="none"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                    <path
                                        :stroke="ringStroke(overallScore)"
                                        stroke-width="3"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="100, 100"
                                        :stroke-dashoffset="ringDashoffset"
                                        style="transition: stroke-dashoffset 1.5s ease-in-out"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-5xl font-black" :class="scoreColorClass(overallScore)">
                                        {{ overallScore }}
                                    </span>
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-white/40">
                                        Overall Score
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h1 class="text-2xl font-black">{{ resume.filename || resume.title }}</h1>
                                <span
                                    v-if="analysis?.candidate_level"
                                    class="mt-2 inline-block rounded-full bg-[#7F77DD]/30 px-3 py-1 text-xs font-black uppercase text-[#EEEDFE]"
                                >
                                    {{ analysis.candidate_level }}
                                </span>
                                <div class="mt-6 grid grid-cols-2 gap-4">
                                    <div v-for="sub in subScores" :key="sub.label">
                                        <div class="mb-1 flex justify-between text-xs font-bold text-white/50">
                                            <span>{{ sub.label }}</span>
                                            <span :class="scoreColorClass(sub.value)">{{ sub.value }}</span>
                                        </div>
                                        <div class="h-2 overflow-hidden rounded-full bg-white/10">
                                            <div
                                                class="h-full rounded-full transition-all duration-1000 ease-out"
                                                :class="scoreBarClass(sub.value)"
                                                :style="{ width: sub.value + '%' }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-4 text-xs text-white/50">
                                    {{ issuesCount }} issues found · Analyzed on {{ analyzedDate }} ·
                                    {{ languageLabel }} detected · Tier: {{ scoreTier }}
                                </p>
                                <div v-if="analysis?.ai_providers_list && analysis.ai_providers_list.length" class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-white/40">Analyzed using:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-for="prov in analysis.ai_providers_list" :key="prov" class="inline-flex items-center gap-1 rounded bg-[#7F77DD]/10 px-2.5 py-0.5 font-semibold text-xs text-[#EEEDFE] border border-[#7F77DD]/25">
                                            <span class="h-1.5 w-1.5 rounded-full bg-[#1D9E75]"></span>
                                            {{ prov }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SUMMARY -->
                    <section
                        v-if="analysis?.summary_feedback"
                        class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-6"
                    >
                        <h2 class="mb-3 text-sm font-black uppercase tracking-wider text-[#7F77DD]">
                            Recruiter summary
                        </h2>
                        <blockquote class="border-l-4 border-[#7F77DD] pl-4 text-sm leading-relaxed text-white/80">
                            {{ analysis.summary_feedback }}
                        </blockquote>
                    </section>

                    <!-- WORD ANALYSIS -->
                    <section v-if="wordAnalysis" class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-4 text-center">
                            <p class="text-2xl font-black text-[#7F77DD]">{{ wordAnalysis.total_bullets ?? 0 }}</p>
                            <p class="mt-1 text-[10px] font-bold uppercase text-white/40">Total bullets</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-4 text-center">
                            <p class="text-2xl font-black text-[#1D9E75]">{{ wordAnalysis.quantified_bullets ?? 0 }}</p>
                            <p class="mt-1 text-[10px] font-bold uppercase text-white/40">Quantified</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-4 text-center">
                            <p class="text-2xl font-black text-[#EF9F27]">
                                {{ wordAnalysis.quantification_rate ?? 0 }}%
                            </p>
                            <p class="mt-1 text-[10px] font-bold uppercase text-white/40">Quant rate</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-4 text-center">
                            <p class="text-2xl font-black text-[#E24B4A]">
                                {{ (wordAnalysis.weak_verbs_found || []).length }}
                            </p>
                            <p class="mt-1 text-[10px] font-bold uppercase text-white/40">Weak verbs</p>
                        </div>
                    </section>

                    <!-- STRENGTHS + CRITICAL -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <section
                            v-if="strengths.length"
                            class="rounded-2xl border border-[#1D9E75]/30 bg-[#1D9E75]/10 p-6"
                        >
                            <h3 class="mb-3 font-bold text-[#1D9E75]">Strengths</h3>
                            <ul class="space-y-2 text-sm text-white/80">
                                <li v-for="(s, i) in strengths" :key="i" class="flex gap-2">
                                    <span>✓</span><span>{{ s }}</span>
                                </li>
                            </ul>
                        </section>
                        <section
                            v-if="criticalIssues.length"
                            class="rounded-2xl border border-[#E24B4A]/30 bg-[#E24B4A]/10 p-6"
                        >
                            <h3 class="mb-3 font-bold text-[#E24B4A]">Critical issues</h3>
                            <ul class="space-y-2 text-sm text-white/80">
                                <li v-for="(c, i) in criticalIssues" :key="i" class="flex gap-2">
                                    <span>✕</span><span>{{ c }}</span>
                                </li>
                            </ul>
                        </section>
                    </div>

                    <!-- ROADMAP -->
                    <section v-if="roadmap.length" class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-6">
                        <h3 class="text-lg font-black">Your improvement roadmap</h3>
                        <p class="mt-1 text-xs text-white/50">
                            Current tier: <strong class="text-white">{{ scoreTier }}</strong> ·
                            {{ overallScore }} → {{ targetScore }} pts possible
                        </p>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                            <div
                                class="h-full rounded-full bg-[#7F77DD] transition-all duration-1000"
                                :style="{ width: overallScore + '%' }"
                            />
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div
                                v-for="step in roadmap.slice(0, 3)"
                                :key="step.step"
                                class="rounded-xl border border-white/10 p-4"
                            >
                                <div
                                    class="mb-2 flex h-8 w-8 items-center justify-center rounded-full bg-[#7F77DD] text-sm font-black"
                                >
                                    {{ step.step }}
                                </div>
                                <h4 class="font-bold">{{ step.title }}</h4>
                                <p class="mt-1 text-xs text-white/50">{{ step.description }}</p>
                                <span class="mt-2 inline-block rounded-full bg-[#1D9E75]/20 px-2 py-0.5 text-[10px] font-bold text-[#1D9E75]">
                                    +{{ step.points_gain }} pts
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- RECOMMENDATIONS -->
                    <section v-if="topRecommendations.length">
                        <h3 class="mb-4 text-lg font-black">
                            AI recommendations ({{ topRecommendations.length }})
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="rec in topRecommendations"
                                :key="rec.id"
                                v-show="!dismissedIds.includes(Number(rec.id))"
                                class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-6"
                            >
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span
                                        :class="[
                                            'rounded-full border px-2 py-0.5 text-[10px] font-black uppercase',
                                            priorityClass(rec.priority),
                                        ]"
                                    >
                                        {{ rec.priority }}
                                    </span>
                                    <span class="text-xs uppercase text-white/40">{{ rec.category }}</span>
                                    <span
                                        v-if="rec.score_impact"
                                        class="ml-auto rounded-full bg-[#7F77DD]/20 px-2 py-0.5 text-[10px] font-bold text-[#7F77DD]"
                                    >
                                        +{{ rec.score_impact }} pts
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-white/90">{{ rec.issue }}</p>
                                <p
                                    v-if="rec.original_line"
                                    class="mt-3 border-l-4 border-[#E24B4A] bg-[#E24B4A]/10 p-3 text-sm text-white/70"
                                >
                                    {{ rec.original_line }}
                                </p>
                                <p
                                    v-if="rec.improved_line"
                                    class="mt-2 border-l-4 border-[#1D9E75] bg-[#1D9E75]/10 p-3 text-sm font-medium text-white/90"
                                >
                                    {{ rec.improved_line }}
                                </p>
                                <p v-if="rec.reason" class="mt-2 text-xs italic text-white/40">{{ rec.reason }}</p>
                                <div class="mt-4 flex justify-end gap-3">
                                    <button
                                        type="button"
                                        class="text-xs font-bold text-white/40 hover:text-white"
                                        @click="dismissSuggestion(rec.id)"
                                    >
                                        Dismiss
                                    </button>
                                    <button
                                        type="button"
                                        :class="[
                                            'rounded-full px-4 py-2 text-xs font-bold transition',
                                            appliedIds.includes(Number(rec.id))
                                                ? 'bg-[#1D9E75] text-white'
                                                : 'bg-[#7F77DD] text-white hover:bg-[#534AB7]',
                                        ]"
                                        @click="applySuggestion(rec.id)"
                                    >
                                        {{ appliedIds.includes(Number(rec.id)) ? '✓ Applied' : 'Apply suggestion' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SECTIONS -->
                    <section v-if="visibleSections.length">
                        <h3 class="mb-4 text-lg font-black">Section-by-section analysis</h3>
                        <div class="space-y-3">
                            <div
                                v-for="sec in visibleSections"
                                :key="sec.key"
                                class="overflow-hidden rounded-2xl border border-white/10 bg-[#1A1A2E]"
                            >
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between px-5 py-4 text-left"
                                    @click="toggleSection(sec.key)"
                                >
                                    <span class="font-bold">{{ sec.name }}</span>
                                    <span class="text-sm font-black" :class="scoreColorClass(sec.score ?? 0)">
                                        {{ sec.score ?? '—' }}/100
                                    </span>
                                </button>
                                <Transition
                                    enter-active-class="transition-all duration-300 ease-out"
                                    enter-from-class="max-h-0 opacity-0"
                                    enter-to-class="max-h-[800px] opacity-100"
                                    leave-active-class="transition-all duration-200 ease-in"
                                    leave-from-class="max-h-[800px] opacity-100"
                                    leave-to-class="max-h-0 opacity-0"
                                >
                                    <div
                                        v-show="openSections[sec.key]"
                                        class="border-t border-white/10 px-5 pb-5 pt-2"
                                    >
                                        <div v-if="sec.key === 'skills'" class="mb-3 flex flex-wrap gap-2">
                                            <span
                                                v-for="(sk, i) in sec.skills_found || []"
                                                :key="'f' + i"
                                                class="rounded-full bg-[#1D9E75]/20 px-2 py-0.5 text-xs text-[#1D9E75]"
                                            >
                                                {{ sk }}
                                            </span>
                                            <span
                                                v-for="(sk, i) in sec.missing_critical || []"
                                                :key="'m' + i"
                                                class="rounded-full bg-[#E24B4A]/20 px-2 py-0.5 text-xs text-[#E24B4A]"
                                            >
                                                Missing: {{ sk }}
                                            </span>
                                        </div>
                                        <ul v-if="sec.languages_listed?.length" class="mb-3 text-sm text-white/70">
                                            <li v-for="(lang, i) in sec.languages_listed" :key="i">• {{ lang }}</li>
                                        </ul>
                                        <ul v-if="sec.issues?.length" class="mb-3 space-y-2 text-sm">
                                            <li
                                                v-for="(issue, i) in sec.issues"
                                                :key="i"
                                                class="flex gap-2 text-[#E24B4A]"
                                            >
                                                <span>✕</span><span class="text-white/70">{{ issue }}</span>
                                            </li>
                                        </ul>
                                        <ul v-if="sec.suggestions?.length" class="space-y-2 text-sm">
                                            <li
                                                v-for="(sug, i) in sec.suggestions"
                                                :key="i"
                                                class="flex gap-2 text-[#7F77DD]"
                                            >
                                                <span>→</span><span class="text-white/70">{{ sug }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </section>

                    <!-- KEYWORDS -->
                    <section v-if="missingKeywords.length" class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-6">
                        <h3 class="text-lg font-black">
                            Missing keywords ({{ missingKeywords.length }})
                        </h3>
                        <p class="mt-1 text-xs text-white/50">Add these to pass ATS scanners</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-for="(kw, i) in missingKeywords"
                                :key="i"
                                :title="kw.suggested_context"
                                :class="[
                                    'cursor-help rounded-full border px-3 py-1 text-xs font-bold',
                                    keywordClass(kw.importance),
                                ]"
                            >
                                {{ kw.keyword }}
                                <span class="ml-1 opacity-60">({{ kw.importance }})</span>
                            </span>
                        </div>
                    </section>

                    <!-- DOWNLOAD -->
                    <section class="rounded-2xl border border-white/10 bg-[#1A1A2E] p-8">
                        <h3 class="text-xl font-black">Download your improved CV</h3>
                        <template v-if="appliedCount > 0">
                            <p class="mt-1 text-sm text-white/50">
                                {{ appliedCount }} improvement(s) applied · ATS-optimized
                            </p>
                            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                                <button
                                    v-for="tpl in [
                                        { id: 'clean', name: 'Clean Minimal' },
                                        { id: 'modern', name: 'Modern Pro' },
                                        { id: 'executive', name: 'Executive' },
                                    ]"
                                    :key="tpl.id"
                                    type="button"
                                    :class="[
                                        'rounded-xl border p-4 text-left transition',
                                        selectedTemplate === tpl.id
                                            ? 'border-[#7F77DD] bg-[#7F77DD]/10'
                                            : 'border-white/10 hover:border-white/20',
                                    ]"
                                    @click="selectedTemplate = tpl.id"
                                >
                                    <span class="text-[10px] font-black uppercase text-[#1D9E75]">ATS-Friendly</span>
                                    <p class="mt-2 font-bold">{{ tpl.name }}</p>
                                </button>
                            </div>
                            <a
                                :href="downloadUrl(selectedTemplate)"
                                class="mt-6 inline-flex rounded-full bg-[#7F77DD] px-8 py-3 text-sm font-bold hover:bg-[#534AB7]"
                            >
                                Download PDF
                            </a>
                        </template>
                        <template v-else>
                            <p class="mt-2 text-sm text-white/50">
                                Apply suggestions above to unlock your improved CV download.
                            </p>
                            <button
                                type="button"
                                disabled
                                class="mt-4 cursor-not-allowed rounded-full bg-white/10 px-8 py-3 text-sm font-bold text-white/30"
                            >
                                Download PDF (apply suggestions first)
                            </button>
                        </template>
                    </section>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
