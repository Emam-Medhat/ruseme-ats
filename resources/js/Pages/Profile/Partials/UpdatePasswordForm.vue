<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Update Password</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Ensure your account is using a long, random password to stay secure.</p>
        </header>

        <form @submit.prevent="updatePassword" class="space-y-5 max-w-xl">
            <div class="space-y-2">
                <label for="current_password" class="label">Current Password</label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    class="input"
                >
                <InputError :message="form.errors.current_password" />
            </div>

            <div class="space-y-2">
                <label for="password" class="label">New Password</label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="input"
                >
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <label for="password_confirmation" class="label">Confirm New Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="input"
                >
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" :disabled="form.processing" class="btn-primary">
                    <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    <span>Update Password</span>
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
                        ✓ Password updated successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
