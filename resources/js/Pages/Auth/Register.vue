<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-black tracking-tight text-zinc-900">
                {{ t('auth.register_title') || 'Create Account' }}
            </h2>
            <p class="text-zinc-500 text-xs mt-1.5 leading-relaxed font-semibold">
                {{ t('auth.register_subtitle') || 'Get started with the absolute gold-standard of ATS optimization' }}
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Name Input -->
            <div class="space-y-2">
                <InputLabel for="name" :value="t('auth.name') || 'Full Name'" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 text-xs">
                        👤
                    </span>
                    <input
                        id="name"
                        type="text"
                        class="pl-10 block w-full rounded-xl border border-zinc-200 bg-zinc-50/40 py-3 text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all text-xs font-semibold shadow-inner"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        :placeholder="t('auth.name_placeholder') || 'John Doe'"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <!-- Email Input -->
            <div class="space-y-2">
                <InputLabel for="email" :value="t('auth.email') || 'Email Address'" />
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
                        autocomplete="username"
                        :placeholder="t('auth.email_placeholder') || 'name@company.com'"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
                <InputLabel for="password" :value="t('auth.password') || 'Password'" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 text-xs">
                        🔒
                    </span>
                    <input
                        id="password"
                        type="password"
                        class="pl-10 block w-full rounded-xl border border-zinc-200 bg-zinc-50/40 py-3 text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all text-xs font-semibold shadow-inner"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <!-- Confirm Password Input -->
            <div class="space-y-2">
                <InputLabel for="password_confirmation" :value="t('auth.confirm_password') || 'Confirm Password'" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-zinc-400 text-xs">
                        🔒
                    </span>
                    <input
                        id="password_confirmation"
                        type="password"
                        class="pl-10 block w-full rounded-xl border border-zinc-200 bg-zinc-50/40 py-3 text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/15 focus:outline-none transition-all text-xs font-semibold shadow-inner"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit & Action -->
            <div class="pt-4">
                <button
                    type="submit"
                    class="w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-white shadow-xl shadow-indigo-600/10 hover:shadow-indigo-600/35 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 disabled:opacity-50 disabled:pointer-events-none"
                    :disabled="form.processing"
                >
                    <span class="relative z-10 flex items-center justify-center gap-2">
                        <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                        <span>{{ t('auth.register_btn') || 'Create Premium Account' }}</span>
                        <span class="group-hover:translate-x-1 transition-transform">➔</span>
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                </button>
            </div>

            <!-- Bottom Link -->
            <div class="text-center pt-3 text-[11px] text-zinc-550 font-semibold">
                {{ t('auth.has_account') || 'Already have an account?' }}
                <Link
                    :href="route('login')"
                    class="font-black text-indigo-600 hover:text-indigo-500 ml-1"
                >
                    {{ t('auth.login_link') || 'Sign in' }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
