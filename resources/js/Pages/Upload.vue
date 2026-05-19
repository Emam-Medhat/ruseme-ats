<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAnalysisLoader } from '@/composables/useAnalysisLoader';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const form = useForm({ resume: null });
const loader = useAnalysisLoader();

const dragging   = ref(false);
const inputRef   = ref(null);
const fileError  = ref('');

// ── File validation ──────────────────────────────────────────────────────────
const ALLOWED_TYPES = [
    'application/pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
const MAX_SIZE_MB = 5;

const validateAndSet = (file) => {
    fileError.value = '';
    if (!file) return;
    const typeOk = ALLOWED_TYPES.includes(file.type) || /\.(pdf|docx)$/i.test(file.name);
    if (!typeOk) { fileError.value = 'Only PDF or DOCX files are accepted.'; return; }
    if (file.size > MAX_SIZE_MB * 1024 * 1024) { fileError.value = `File must be under ${MAX_SIZE_MB} MB.`; return; }
    form.resume = file;
};

const onDrop = (e) => { dragging.value = false; validateAndSet(e.dataTransfer.files?.[0]); };
const onPick = (e) => validateAndSet(e.target.files?.[0]);
const removeFile = () => { form.resume = null; fileError.value = ''; };

const fileSizeMB = computed(() =>
    form.resume ? (form.resume.size / (1024 * 1024)).toFixed(2) : '0'
);
const fileExt = computed(() =>
    form.resume?.name?.split('.').pop()?.toUpperCase() ?? ''
);

// ── Submit ───────────────────────────────────────────────────────────────────
const submit = () => {
    if (!form.resume || form.processing) return;
    loader.start();
    form.post(route('resumes.upload'), {
        forceFormData: true,
        onSuccess: () => loader.finish(),
        onError:   () => loader.stop(),
        onFinish:  () => loader.stop(),
    });
};

// ── Feature cards ────────────────────────────────────────────────────────────
const features = [
    { icon: '⚡', title: 'Instant ATS Score',   desc: 'Get your ATS compatibility score in under 60 seconds.' },
    { icon: '🎯', title: 'Line-by-Line Fixes',  desc: 'Exact bullet improvements with side-by-side rewrites.' },
    { icon: '✦',  title: 'AI Magic Rewrite',    desc: '3 executive-level alternatives for every weak bullet.' },
    { icon: '📊', title: '6 Score Dimensions',  desc: 'Impact, Brevity, Style, ATS, Recruiter & Keywords.' },
];
</script>

<template>
    <Head title="Upload Resume — CV Genius AI" />

    <AppLayout>
        <!-- ── Page shell ─────────────────────────────────────────────── -->
        <div class="min-h-screen bg-gradient-to-br from-[#f8f7ff] via-[#f3f4f6] to-[#ede9fe] flex flex-col">

            <!-- ── Hero header ────────────────────────────────────────── -->
            <div class="relative overflow-hidden bg-gradient-to-r from-[#4f46e5] via-[#6d28d9] to-[#7c3aed] pt-16 pb-32 text-white text-center px-4">
                <!-- Decorative blobs -->
                <div class="absolute top-0 left-0 w-80 h-80 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl pointer-events-none" />
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-300/10 rounded-full translate-x-1/3 translate-y-1/3 blur-3xl pointer-events-none" />

                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black tracking-widest uppercase bg-white/10 border border-white/20 text-indigo-200 px-3 py-1 rounded-full mb-5 backdrop-blur-sm">
                        ⚡ AI-Powered · Instant Results · Free to Start
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        Upload Your Resume<br />
                        <span class="text-yellow-300">Get Your ATS Score in 60s</span>
                    </h1>
                    <p class="mt-3 text-indigo-200 text-sm leading-relaxed max-w-lg mx-auto">
                        Our AI scans every line of your CV, flags weak bullets, and gives you
                        executive-level rewrites — just like a $500/hr career coach.
                    </p>
                </div>
            </div>

            <!-- ── Main card (overlap hero) ───────────────────────────── -->
            <div class="flex-1 -mt-20 pb-16 px-4">
                <div class="max-w-2xl mx-auto">

                    <!-- Upload card -->
                    <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-200/50 border border-indigo-100 overflow-hidden">

                        <!-- Card header bar -->
                        <div class="bg-gradient-to-r from-indigo-50 to-violet-50 border-b border-indigo-100 px-7 py-4 flex items-center gap-3">
                            <div class="w-8 h-8 bg-[#4f46e5] rounded-xl flex items-center justify-center text-white text-sm shadow-md shadow-indigo-500/30">
                                📄
                            </div>
                            <div>
                                <p class="text-xs font-black text-[#1a1a2e] tracking-tight">Upload Your Resume</p>
                                <p class="text-[10px] text-[#6b7280]">PDF or DOCX · Max 5 MB · Scanned instantly by AI</p>
                            </div>
                            <div class="ml-auto flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-[10px] font-bold text-emerald-600">AI Online</span>
                            </div>
                        </div>

                        <div class="p-7 space-y-5">

                            <!-- ── Drop zone ── -->
                            <div
                                v-if="!form.resume"
                                class="relative rounded-2xl border-2 border-dashed transition-all duration-300 cursor-pointer group"
                                :class="dragging
                                    ? 'border-[#4f46e5] bg-indigo-50 scale-[1.01]'
                                    : 'border-indigo-200 bg-gradient-to-br from-indigo-50/50 to-violet-50/30 hover:border-[#4f46e5]/60 hover:bg-indigo-50/60'"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="onDrop"
                                @click="inputRef?.click()"
                            >
                                <input
                                    ref="inputRef"
                                    type="file"
                                    accept=".pdf,.docx"
                                    class="hidden"
                                    @change="onPick"
                                />

                                <div class="py-14 px-6 flex flex-col items-center text-center">
                                    <!-- Animated upload icon -->
                                    <div class="relative mb-5">
                                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-[#4f46e5] to-[#7c3aed] flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-300">
                                            <svg class="w-9 h-9 text-white" :class="dragging ? 'animate-bounce' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.338-2.32 5.75 5.75 0 011.605 11.084" />
                                            </svg>
                                        </div>
                                        <!-- Pulse rings -->
                                        <div v-if="dragging" class="absolute inset-0 rounded-2xl border-2 border-[#4f46e5]/40 animate-ping pointer-events-none" />
                                    </div>

                                    <p class="text-base font-black text-[#1a1a2e] mb-1">
                                        {{ dragging ? 'Drop it here!' : 'Drag & drop your resume' }}
                                    </p>
                                    <p class="text-xs text-[#6b7280] mb-5">
                                        or click to browse from your computer
                                    </p>

                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-indigo-100 rounded-full text-[10px] font-bold text-[#4f46e5] shadow-sm">
                                            📄 PDF
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-indigo-100 rounded-full text-[10px] font-bold text-[#6d28d9] shadow-sm">
                                            📝 DOCX
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-indigo-100 rounded-full text-[10px] font-bold text-[#6b7280] shadow-sm">
                                            📦 Max 5 MB
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- ── File Selected State ── -->
                            <div
                                v-else
                                class="rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 p-5 flex items-center gap-4"
                            >
                                <!-- File type badge -->
                                <div class="w-14 h-14 rounded-xl bg-white border border-emerald-100 shadow flex items-center justify-center shrink-0">
                                    <span class="text-xl font-black" :class="fileExt === 'PDF' ? 'text-rose-500' : 'text-blue-500'">
                                        {{ fileExt }}
                                    </span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-[#1a1a2e] truncate">{{ form.resume.name }}</p>
                                    <p class="text-[11px] text-[#6b7280] mt-0.5">{{ fileSizeMB }} MB · Ready for AI analysis</p>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                        <span class="text-[10px] font-bold text-emerald-600">File validated ✓</span>
                                    </div>
                                </div>

                                <!-- Change file btn -->
                                <button
                                    type="button"
                                    @click="removeFile"
                                    class="shrink-0 w-8 h-8 rounded-full bg-white border border-rose-100 text-rose-400 hover:text-rose-600 hover:border-rose-300 flex items-center justify-center transition-all shadow-sm"
                                    title="Remove file"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- ── Error messages ── -->
                            <p v-if="fileError" class="flex items-center gap-2 text-xs text-rose-600 bg-rose-50 border border-rose-200 px-4 py-2.5 rounded-xl font-medium">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ fileError }}
                            </p>
                            <p v-if="form.errors.resume" class="flex items-center gap-2 text-xs text-rose-600 bg-rose-50 border border-rose-200 px-4 py-2.5 rounded-xl font-medium">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ form.errors.resume }}
                            </p>
                            <p v-if="form.errors.credits" class="flex items-center gap-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 px-4 py-2.5 rounded-xl font-medium">
                                🪙 {{ form.errors.credits }}
                            </p>

                            <!-- ── Submit CTA ── -->
                            <button
                                type="button"
                                :disabled="!form.resume || form.processing"
                                @click="submit"
                                class="w-full py-4 rounded-2xl text-sm font-black text-white transition-all duration-300 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="form.resume && !form.processing
                                    ? 'bg-gradient-to-r from-[#4f46e5] to-[#7c3aed] hover:from-[#3f37c9] hover:to-[#6d28d9] shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 active:translate-y-0'
                                    : 'bg-gradient-to-r from-gray-300 to-gray-400'"
                            >
                                <!-- Shimmer effect -->
                                <span v-if="form.resume && !form.processing" class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 -translate-x-full group-hover:translate-x-full transition-transform duration-700 pointer-events-none" />

                                <span class="relative flex items-center justify-center gap-2.5">
                                    <!-- Processing spinner -->
                                    <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <!-- Ready icon -->
                                    <svg v-else-if="form.resume" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                    </svg>
                                    <!-- Upload icon (no file) -->
                                    <svg v-else class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3" />
                                    </svg>

                                    <span>
                                        {{ form.processing
                                            ? 'Running AI Analysis…'
                                            : form.resume
                                                ? '✦ Analyze My Resume Now'
                                                : 'Upload a file to continue'
                                        }}
                                    </span>
                                </span>
                            </button>

                            <!-- Privacy note -->
                            <p class="text-[10px] text-center text-[#9ca3af] leading-relaxed">
                                🔒 Your resume is processed securely and never shared.
                                Analysis completes in under 60 seconds.
                            </p>
                        </div>
                    </div>

                    <!-- ── Feature cards grid ──────────────────────────── -->
                    <div class="mt-8 grid grid-cols-2 gap-3">
                        <div
                            v-for="feat in features"
                            :key="feat.title"
                            class="bg-white/80 backdrop-blur-sm border border-indigo-100 rounded-2xl p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 hover:-translate-y-0.5"
                        >
                            <div class="text-xl mb-2">{{ feat.icon }}</div>
                            <p class="text-xs font-black text-[#1a1a2e] mb-0.5">{{ feat.title }}</p>
                            <p class="text-[10px] text-[#6b7280] leading-relaxed">{{ feat.desc }}</p>
                        </div>
                    </div>

                    <!-- ── Social proof strip ────────────────────────────── -->
                    <div class="mt-6 bg-white/70 backdrop-blur-sm border border-indigo-100 rounded-2xl px-6 py-4 flex flex-wrap items-center justify-center gap-6 shadow-sm">
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-[#4f46e5]">98%</span>
                            <span class="text-[9px] text-[#6b7280] uppercase tracking-wider font-bold">Accuracy Rate</span>
                        </div>
                        <div class="w-px h-8 bg-indigo-100" />
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-[#4f46e5]">&lt; 60s</span>
                            <span class="text-[9px] text-[#6b7280] uppercase tracking-wider font-bold">Analysis Time</span>
                        </div>
                        <div class="w-px h-8 bg-indigo-100" />
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-[#4f46e5]">10K+</span>
                            <span class="text-[9px] text-[#6b7280] uppercase tracking-wider font-bold">Resumes Scanned</span>
                        </div>
                        <div class="w-px h-8 bg-indigo-100" />
                        <div class="flex flex-col items-center">
                            <span class="text-lg font-black text-[#4f46e5]">Free</span>
                            <span class="text-[9px] text-[#6b7280] uppercase tracking-wider font-bold">To Get Started</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
