<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Account Settings" />

    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Account Settings</h1>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage your profile and account preferences</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Profile Information Card -->
                <div class="card p-6 md:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Update Password Card -->
                <div class="card p-6 md:p-8">
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account Card -->
                <div class="card p-6 md:p-8 border-danger-200 dark:border-danger-800">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
