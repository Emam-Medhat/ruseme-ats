<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-black tracking-tight text-zinc-900">Reset Password</h2>
            <p class="mt-2 text-xs text-zinc-500 leading-relaxed font-semibold">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link to choose a new one.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/5 p-3.5 text-xs font-bold text-emerald-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <InputLabel for="email" value="Email Address" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 text-xs">
                        ✉
                    </span>
                    <input
                        id="email"
                        type="email"
                        class="pl-10 block w-full rounded-xl border border-zinc-200 bg-zinc-50/40 py-3 text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all text-xs font-semibold shadow-inner"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@company.com"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full uppercase font-black tracking-wider text-xs py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl active:scale-[0.98] transition-all shadow-md shadow-indigo-600/10"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
