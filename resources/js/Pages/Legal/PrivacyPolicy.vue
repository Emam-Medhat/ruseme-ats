<script setup>
import { Head } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
</script>

<template>
    <Head title="Privacy Policy" />

    <GuestLayout>
        <div class="py-12 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl p-8 sm:p-12 border border-neutral-200 dark:border-neutral-800">
                <h1 class="text-3xl sm:text-4xl font-black text-neutral-900 dark:text-white mb-8">
                    Privacy Policy
                </h1>

                <div class="space-y-6 text-neutral-700 dark:text-neutral-300 leading-relaxed">
                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">1. Introduction</h2>
                        <p>
                            Welcome to CV Genius AI. We respect your privacy and are committed to protecting your personal data.
                            This privacy policy will inform you about how we look after your personal data when you visit our website
                            and tell you about your privacy rights and how the law protects you.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">2. Data We Collect</h2>
                        <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                        <ul class="list-disc pl-6 space-y-2 mt-3">
                            <li><strong>Identity Data:</strong> includes first name, last name, username or similar identifier.</li>
                            <li><strong>Contact Data:</strong> includes email address.</li>
                            <li><strong>Technical Data:</strong> includes internet protocol (IP) address, browser type and version, time zone setting and location, browser plug-in types and versions, operating system and platform.</li>
                            <li><strong>Usage Data:</strong> includes information about how you use our website and services.</li>
                            <li><strong>Document Data:</strong> includes resumes and other documents you upload to our service.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">3. How We Use Your Data</h2>
                        <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                        <ul class="list-disc pl-6 space-y-2 mt-3">
                            <li>To provide and maintain our service.</li>
                            <li>To notify you about changes to our service.</li>
                            <li>To provide customer support.</li>
                            <li>To provide analysis or valuable information so that we can improve the service.</li>
                            <li>To monitor the usage of the service.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">4. Data Security</h2>
                        <p>
                            We have put in place appropriate security measures to prevent your personal data from being accidentally lost,
                            used or accessed in an unauthorized way, altered or disclosed. In addition, we limit access to your personal
                            data to those who have a business need to know.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-white mb-3">5. Contact Us</h2>
                        <p>
                            If you have any questions about this privacy policy or our privacy practices, please contact us.
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
