<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Profile Information</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Update your account's profile details and email address.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-5 max-w-xl">
            <div class="space-y-2">
                <label for="name" class="label">Name</label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    class="input"
                >
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <label for="email" class="label">Email Address</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    class="input"
                >
                <InputError :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="p-4 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                <p class="text-sm text-warning-700 dark:text-warning-400">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline hover:text-warning-800 dark:hover:text-warning-300 transition"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-success-600 dark:text-success-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span>Save Changes</span>
                </button>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition duration-200 ease-in opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-medium text-success-600 dark:text-success-400 flex items-center gap-1"
                    >
                        ✓ Saved successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
