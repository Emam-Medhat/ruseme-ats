<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-danger-600 dark:text-danger-400">Delete Account</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Permanently delete all of your scanned resumes, metrics, and credit details. This action is irreversible.</p>
        </header>

        <button @click="confirmUserDeletion" class="btn-danger">
            Delete Account
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 md:p-8 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-danger-100 dark:bg-danger-900/30 text-danger-600 dark:text-danger-400 flex items-center justify-center text-xl shrink-0">
                        ⚠️
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">
                            Are you absolutely sure?
                        </h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mt-0.5">
                            Irreversible Account Deletion Process
                        </p>
                    </div>
                </div>

                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Once your account is deleted, all resources and scan history will be permanently wiped. Please type your password below to confirm this request.
                </p>

                <div class="space-y-2">
                    <label for="password" class="label">Confirm Password</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="input"
                        placeholder="Enter your account password"
                        @keyup.enter="deleteUser"
                    >

                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="closeModal" class="btn-secondary">
                        Cancel
                    </button>

                    <button
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="btn-danger"
                    >
                        Confirm Deletion
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
