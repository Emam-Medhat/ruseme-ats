<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-6 text-center sm:text-left">
            <h2 class="text-2xl font-black tracking-tight text-zinc-900">Verify Your Email</h2>
            <p class="mt-2 text-xs text-zinc-500 leading-relaxed font-semibold">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>
        </div>

        <div
            class="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/5 p-3.5 text-xs font-bold text-emerald-600"
            v-if="verificationLinkSent"
        >
            A new verification link has been sent to the email address you provided during registration.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                <PrimaryButton
                    class="w-full sm:w-auto uppercase font-black tracking-wider text-xs py-3 px-5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl active:scale-[0.98] transition-all shadow-md shadow-indigo-600/10"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs font-bold text-zinc-400 hover:text-zinc-600 underline transition-colors"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
