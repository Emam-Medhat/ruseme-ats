<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-black tracking-tight text-zinc-900">
                {{ t('auth.login_title') || 'Welcome Back' }}
            </h2>
            <p class="text-zinc-500 text-xs mt-1.5 leading-relaxed font-semibold">
                {{ t('auth.login_subtitle') || 'Access your premium ATS analysis dashboard' }}
            </p>
        </div>

        <div v-if="status" class="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/5 p-3.5 text-xs font-bold text-emerald-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
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
                        autofocus
                        autocomplete="username"
                        :placeholder="t('auth.email_placeholder') || 'name@company.com'"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <InputLabel for="password" :value="t('auth.password') || 'Password'" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-bold text-indigo-650 hover:text-indigo-500 transition-colors"
                    >
                        {{ t('auth.forgot_password') || 'Forgot password?' }}
                    </Link>
                </div>
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
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center group cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded border-zinc-200 bg-white text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-0 h-4.5 w-4.5 cursor-pointer"
                    />
                    <span class="ms-2.5 text-[11px] font-bold text-zinc-500 group-hover:text-zinc-700 transition-colors"
                        >{{ t('auth.remember_me') || 'Keep me signed in' }}</span
                    >
                </label>
            </div>

            <!-- Submit & Action -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-white shadow-xl shadow-indigo-600/10 hover:shadow-indigo-600/35 hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 disabled:opacity-50 disabled:pointer-events-none"
                    :disabled="form.processing"
                >
                    <span class="relative z-10 flex items-center justify-center gap-2">
                        <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                        <span>{{ t('auth.login_btn') || 'Sign In to Dashboard' }}</span>
                        <span class="group-hover:translate-x-1 transition-transform">➔</span>
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                </button>
            </div>

            <!-- Bottom Link -->
            <div class="text-center pt-3 text-[11px] text-zinc-550 font-semibold">
                {{ t('auth.no_account') || "Don't have an account?" }}
                <Link
                    :href="route('register')"
                    class="font-black text-indigo-600 hover:text-indigo-500 ml-1"
                >
                    {{ t('auth.register_link') || 'Create one for free' }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
