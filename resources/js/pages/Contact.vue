<script setup>
import { reactive, ref } from 'vue';
import api from '../api';
import Flash from '../components/Flash.vue';

const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    subject: '',
    message: '',
});
const status = ref('');
const error = ref('');
const sending = ref(false);

const details = [
    ['Email Us', 'support@sharphand.ng', 'Expect a response within 24 hours'],
    ['Call Us', '+234 (0) 802 832 6954', 'Mon – Fri, 9am – 6pm WAT'],
    ['Main Office', '12 Tech Hub Avenue', 'GRA, Port Harcourt, Nigeria'],
    ['Business Hours', 'Open 24/7 for bookings', 'Support: Mon–Sat, 8am–8pm'],
];

async function submit() {
    sending.value = true;
    status.value = '';
    error.value = '';

    try {
        const { data } = await api.post('/contact', form);
        status.value = data.message;
        Object.assign(form, { first_name: '', last_name: '', email: '', subject: '', message: '' });
    } catch (e) {
        error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Could not send your message.';
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <div>
        <section class="bg-slate-50 py-16 text-center">
            <p class="inline-flex items-center gap-2 text-sm font-medium text-brand">
                <span class="h-2 w-2 rounded-full bg-brand"></span>
                Support available 24/7
            </p>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight sm:text-5xl">
                Get in Touch with <span class="text-brand">SharpHand</span>
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-muted">
                Whether you’re a skilled worker looking to join our platform or a user in need of assistance, our team is ready to help you every step of the way.
            </p>
        </section>

        <section class="container-page grid gap-8 py-14 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="card p-6 sm:p-8">
                <Flash :status="status" :error="error" />
                <div class="mb-6">
                    <h2 class="text-xl font-semibold">Send us a Message</h2>
                    <p class="mt-1 text-sm text-muted">Fill out the form below and we’ll connect you with the right support person.</p>
                </div>
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium">First Name
                            <input v-model="form.first_name" class="field mt-2" placeholder="John">
                        </label>
                        <label class="block text-sm font-medium">Last Name
                            <input v-model="form.last_name" class="field mt-2" placeholder="Doe">
                        </label>
                    </div>
                    <label class="block text-sm font-medium">Email Address
                        <input v-model="form.email" type="email" class="field mt-2" placeholder="john@example.com">
                    </label>
                    <label class="block text-sm font-medium">Subject
                        <input v-model="form.subject" class="field mt-2" placeholder="How can we help you?">
                    </label>
                    <label class="block text-sm font-medium">Message
                        <textarea v-model="form.message" class="field-area mt-2" placeholder="Type your message here..."></textarea>
                    </label>
                    <button class="btn-primary w-full" :disabled="sending">{{ sending ? 'Sending…' : 'Send Message' }}</button>
                </form>
            </div>
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Contact Details</h2>
                <article v-for="[title, primary, secondary] in details" :key="title" class="card p-5">
                    <p class="text-sm font-semibold">{{ title }}</p>
                    <p class="mt-1 text-sm">{{ primary }}</p>
                    <p class="text-sm text-muted">{{ secondary }}</p>
                </article>
                <article class="rounded-[24px] bg-brand-soft p-5">
                    <p class="font-semibold">Need immediate help?</p>
                    <p class="mt-2 text-sm leading-6 text-muted">Check out our FAQ page for quick answers to common questions about bookings, payments, and worker verification.</p>
                </article>
            </div>
        </section>

        <section class="bg-brand">
            <div class="container-page flex flex-col items-center justify-between gap-4 py-10 text-center text-white sm:flex-row sm:text-left">
                <div>
                    <h2 class="text-2xl font-bold">Ready to find a professional?</h2>
                    <p class="mt-1 text-white/85">Download the SharpHand app today and get your tasks done instantly.</p>
                </div>
                <router-link to="/" class="btn bg-white text-ink hover:bg-slate-100">Back to Home</router-link>
            </div>
        </section>
    </div>
</template>
