<script setup>
import { Head } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
</script>

<template>
    <Head title="Terms of Service" />

    <GuestLayout>
        <div class="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl p-8 sm:p-12 border border-neutral-200 dark:border-neutral-800">
                <h1 class="text-3xl sm:text-4xl font-black text-neutral-900 dark:text-white mb-8">
                    Terms of Service
                </h1>

                <div class="space-y-6 text-neutral-700 dark:text-neutral-300 leading-relaxed">
                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">1. Agreement to Terms</h2>
                        <p>
                            By accessing or using our service, you agree to be bound by these Terms of Service. If you disagree with any part of the terms,
                            then you may not access the service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">2. Use of Service</h2>
                        <p>
                            Our service is intended for personal and professional use to help users improve their resumes. You agree to use the service
                            only for lawful purposes and in accordance with these terms.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">3. User Accounts</h2>
                        <p>
                            When you create an account with us, you must provide accurate, complete, and current information at all times.
                            Failure to do so constitutes a breach of the terms, which may result in immediate termination of your account.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">4. Intellectual Property</h2>
                        <p>
                            The service and its original content, features, and functionality are and will remain the exclusive property of
                            CV Genius AI and its licensors.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">5. Limitation of Liability</h2>
                        <p>
                            In no event shall CV Genius AI, nor its directors, employees, partners, agents, suppliers, or affiliates,
                            be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation,
                            loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to
                            access or use the service.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">6. Changes to Terms</h2>
                        <p>
                            We reserve the right, at our sole discretion, to modify or replace these terms at any time. We will provide notice of
                            any changes by posting the new terms on this page.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">7. Contact Us</h2>
                        <p>
                            If you have any questions about these Terms of Service, please contact us.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
