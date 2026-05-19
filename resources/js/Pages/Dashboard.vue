<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useAnalysisLoader } from '@/composables/useAnalysisLoader';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const loader = useAnalysisLoader();

const form = useForm({
    resume: null,
});

const fileInput = ref(null);
const dragging = ref(false);

const handleFileUpload = (e) => {
    const file = e.target.files[0] || e.dataTransfer.files[0];
    console.log("[CVGenius] File selected:", file);
    if (file) {
        const allowed = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        if (allowed.includes(file.type) || /\.(pdf|docx)$/i.test(file.name)) {
            form.resume = file;
        } else {
            alert('Please upload a PDF or DOCX file.');
        }
    }
};

const submitResume = () => {
    console.log("[CVGenius] Submitting resume for AI analysis...", form.resume);
    loader.start();
    form.post(route('resumes.upload'), {
        preserveScroll: true,
        onSuccess: () => loader.finish(),
        onError: () => loader.stop(),
        onFinish: () => loader.stop(),
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold leading-tight text-gray-900 dark:text-white">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Banner -->
                <div class="bg-indigo-600 rounded-3xl p-8 mb-8 text-white flex flex-col md:flex-row items-center justify-between shadow-lg shadow-indigo-500/20 relative overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                    <div class="relative z-10">
                        <h3 class="text-2xl font-extrabold mb-2">Welcome back, {{ $page.props.auth.user.name }}!</h3>
                        <p class="text-indigo-100">Ready to optimize your career? Upload your latest resume to get instant AI feedback.</p>
                    </div>
                    <div class="relative z-10 mt-6 md:mt-0 bg-white/10 rounded-full px-6 py-2 backdrop-blur-md border border-white/20">
                        <span class="font-bold">{{ $page.props.auth.user.credits ?? 1 }}</span> Credits Available
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2">
                        <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-3xl p-8 border border-gray-100 dark:border-zinc-800">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Upload New Resume</h4>
                            
                            <form @submit.prevent="submitResume">
                                <div 
                                    class="border-2 border-dashed rounded-2xl p-12 text-center transition-colors relative"
                                    :class="dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-gray-300 dark:border-zinc-700 hover:border-indigo-400 dark:hover:border-indigo-500'"
                                    @dragover.prevent="dragging = true"
                                    @dragleave.prevent="dragging = false"
                                    @drop.prevent="e => { dragging = false; handleFileUpload(e) }"
                                >
                                    <input 
                                        type="file" 
                                        accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        @change="handleFileUpload"
                                    >
                                    
                                    <div v-if="!form.resume" class="flex flex-col items-center pointer-events-none">
                                        <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        </div>
                                        <p class="text-gray-900 dark:text-white font-semibold text-lg">Click to upload or drag and drop</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">PDF or DOCX (Max 5MB)</p>
                                    </div>
                                    
                                    <div v-else class="flex flex-col items-center pointer-events-none">
                                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-gray-900 dark:text-white font-semibold text-lg">{{ form.resume.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ (form.resume.size / 1024 / 1024).toFixed(2) }} MB</p>
                                    </div>
                                </div>

                                <div v-if="form.errors.resume" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm rounded-2xl text-center font-medium">
                                    {{ form.errors.resume }}
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button 
                                        type="submit" 
                                        :disabled="!form.resume || form.processing"
                                        class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-full font-bold transition shadow-lg shadow-indigo-500/30 flex items-center gap-2"
                                    >
                                        <span v-if="form.processing">Analyzing...</span>
                                        <span v-else>Score My Resume</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar / Past Resumes -->
                    <div class="md:col-span-1">
                        <div class="bg-white dark:bg-zinc-900 shadow-sm rounded-3xl p-8 border border-gray-100 dark:border-zinc-800 h-full">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Recent Scans</h4>
                            
                            <div v-if="!$page.props.resumes || $page.props.resumes.length === 0" class="text-center py-8">
                                <div class="w-12 h-12 bg-gray-100 dark:bg-zinc-800 rounded-full mx-auto flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No resumes analyzed yet.</p>
                            </div>
                            
                            <ul v-else class="space-y-4">
                                <li 
                                    v-for="resume in $page.props.resumes" 
                                    :key="resume.id" 
                                    class="flex items-center justify-between gap-2 p-2 hover:bg-gray-50 dark:hover:bg-zinc-800/40 rounded-2xl transition"
                                >
                                    <Link 
                                        :href="route('resumes.report', resume.id)"
                                        class="flex items-center gap-3 cursor-pointer grow truncate"
                                    >
                                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center font-black shrink-0">
                                            {{ resume.score }}
                                        </div>
                                        <div class="grow truncate">
                                            <p class="text-xs font-extrabold text-gray-900 dark:text-white truncate max-w-[120px]">{{ resume.title }}</p>
                                            <p class="text-[10px] text-gray-400 dark:text-zinc-500 font-semibold">{{ new Date(resume.created_at).toLocaleDateString() }}</p>
                                        </div>
                                    </Link>
                                    
                                    <Link 
                                        :href="route('resumes.target', resume.id)"
                                        class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400 rounded-full transition shrink-0"
                                    >
                                        Match JD
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
