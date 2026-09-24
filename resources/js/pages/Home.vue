<script setup>
import { onMounted, ref } from 'vue';
import PhoneMockup from '../components/PhoneMockup.vue';
import StoreButtons from '../components/StoreButtons.vue';
import api from '../api';

const services = ref([]);

const steps = [
    ['1', 'Choose a service', 'Pick the type of work you need from a list of professional categories.'],
    ['2', 'Find nearby workers', 'See verified workers around you, ranked by distance and availability.'],
    ['3', 'Connect instantly', 'Get the worker’s phone number and get the job done — no extra steps.'],
];

const testimonials = [
    ['I was skeptical about finding an electrician online, but a verified worker arrived in 30 minutes and the price was fair.', 'Amina Balogun', 'Homeowner, Ikeja'],
    ['The plumbing service I booked was prompt and reliable. It is much easier than paging through directories.', 'Tunde Obi', 'Small business owner'],
    ['Needed a carpenter for a new apartment. The quality was excellent and I got connected instantly.', 'Chioma Nneka', 'Lagos Island'],
];

onMounted(async () => {
    try {
        const { data } = await api.get('/services');
        services.value = data.data;
    } catch {
        services.value = [];
    }
});
</script>

<template>
    <div>
        <section class="container-page grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-20">
            <div>
                <h1 class="max-w-xl text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">
                    Find trusted <span class="text-brand">workers</span> near you instantly
                </h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-muted">
                    The fastest way to connect with verified plumbers, electricians, and cleaners in your neighborhood. Quality service guaranteed.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#get-the-app" class="btn-dark">Download the app</a>
                    <router-link to="/get-started" class="btn-outline">Get started on the web</router-link>
                </div>
                <div class="mt-10 flex gap-10 text-sm">
                    <div>
                        <p class="text-2xl font-bold">10k+</p>
                        <p class="text-muted">skilled workers</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">500+</p>
                        <p class="text-muted">neighborhoods</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-center lg:justify-end">
                <PhoneMockup src="/images/app/home.png" alt="SharpHand app home screen" />
            </div>
        </section>

        <section class="container-page py-16 text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Services you can count on</h2>
            <p class="mx-auto mt-3 max-w-2xl text-muted">We bridge the gap between skilled people you can trust and the jobs that cannot wait.</p>
            <div class="mt-12 grid gap-5 text-left sm:grid-cols-2 lg:grid-cols-4">
                <article v-for="service in services" :key="service.id" class="card p-6">
                    <div class="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-soft text-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">{{ service.name }}</h3>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ service.description }}</p>
                    <router-link :to="{ path: '/get-started', query: { service_id: service.id } }" class="mt-6 inline-flex text-sm font-semibold text-ink">Book now →</router-link>
                </article>
            </div>
        </section>

        <section class="container-page py-16 text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Simple. Fast. Reliable.</h2>
            <p class="mt-3 text-muted">Three easy steps to get your project started today.</p>
            <div class="mt-12 grid gap-8 text-left md:grid-cols-3">
                <article v-for="[step, title, copy] in steps" :key="step" class="px-2">
                    <div class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-soft font-semibold text-brand">{{ step }}</div>
                    <h3 class="text-lg font-semibold">{{ title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ copy }}</p>
                </article>
            </div>
        </section>

        <section class="container-page py-16">
            <div class="mb-10 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">What our customers are saying</h2>
                    <p class="mt-3 max-w-xl text-muted">Join thousands of people who have learned they can trust SharpHand.</p>
                </div>
                <router-link to="/about" class="hidden btn-outline sm:inline-flex">More stories</router-link>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <article v-for="[quote, name, role] in testimonials" :key="name" class="card p-6">
                    <div class="mb-4 flex text-brand">★★★★★</div>
                    <p class="text-sm leading-6 text-slate-600">“{{ quote }}”</p>
                    <div class="mt-6 flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold">{{ name[0] }}</span>
                        <div>
                            <p class="text-sm font-semibold">{{ name }}</p>
                            <p class="text-xs text-muted">{{ role }}</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section id="get-the-app" class="container-page pb-20">
            <div class="overflow-hidden rounded-[32px] bg-brand px-6 py-12 text-white sm:px-10 lg:px-14">
                <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/80">Available on mobile</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Get SharpHand on your phone</h2>
                        <p class="mt-4 max-w-xl text-white/85">Find trusted workers near you, see who’s available, and connect in a few taps. Download the SharpHand app for iOS or Android.</p>
                        <StoreButtons light class="mt-8" />
                        <p class="mt-4 text-sm text-white/70">Or <router-link to="/get-started" class="underline underline-offset-4">get started on the web</router-link> if you prefer.</p>
                    </div>
                    <div class="relative mx-auto flex h-[420px] w-full max-w-[420px] items-end justify-center sm:h-[460px]">
                        <div class="absolute left-0 top-8 hidden sm:block">
                            <PhoneMockup src="/images/app/onboarding.png" alt="SharpHand app onboarding" tilt class="w-[180px] sm:w-[200px] opacity-90" />
                        </div>
                        <div class="relative z-10">
                            <PhoneMockup src="/images/app/plumbers.png" alt="SharpHand app nearby plumbers" class="w-[210px] sm:w-[230px]" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
