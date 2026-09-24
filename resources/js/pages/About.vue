<script setup>
const reasons = [
    ['Unwavering Trust', 'Every worker on our platform undergoes a rigorous multi-step verification process to ensure your safety and peace of mind.'],
    ['Lightning Speed', 'Our geo-location technology connects you with the nearest available pro, reducing wait times from days to minutes.'],
    ['Guaranteed Smile', 'Quality is non-negotiable. If you’re not satisfied with the service, we work quickly to make it right.'],
];
</script>

<template>
    <div>
        <section class="container-page grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-20">
            <div>
                <h1 class="max-w-xl text-5xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl">
                    Connecting <span class="text-brand">Reliability</span> with Your Everyday Needs.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-muted">
                    SharpHand was born from a simple observation: finding trustworthy, skilled labor in our cities shouldn’t be a game of chance. Whether it’s a leaky pipe at midnight or a major home renovation, we are building Nigeria’s most trusted platform for skilled artisans and the people who need them.
                </p>
                <p class="mt-4 max-w-xl text-base leading-7 text-muted">
                    We are not just fixing homes — we are empowering local livelihoods by vetting every professional and streamlining the connection process.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <router-link to="/contact" class="btn-dark">Contact Our Team</router-link>
                    <router-link to="/" class="btn-outline">Read our story</router-link>
                </div>
            </div>
            <div class="flex justify-center lg:justify-end">
                <img
                    src="https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=900&q=80"
                    alt="Skilled worker holding tools"
                    class="h-[360px] w-[320px] rounded-[28px] object-cover shadow-card sm:h-[420px] sm:w-[380px]"
                >
            </div>
        </section>

        <section class="container-page py-16 text-center">
            <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Why SharpHand?</h2>
            <p class="mx-auto mt-3 max-w-2xl text-muted">We’ve redesigned the service industry from the ground up to prioritize the needs of both users and skilled workers.</p>
            <div class="mt-12 grid gap-5 text-left md:grid-cols-3">
                <article v-for="[title, copy] in reasons" :key="title" class="card p-6">
                    <div class="mb-5 inline-flex h-10 w-10 items-center justify-center rounded-full border border-brand/20 text-brand">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="8" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold">{{ title }}</h3>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ copy }}</p>
                </article>
            </div>
        </section>

        <section class="container-page">
            <div class="grid gap-6 rounded-[32px] bg-brand px-8 py-12 text-white sm:grid-cols-3">
                <div class="text-center">
                    <p class="text-4xl font-extrabold">12k+</p>
                    <p class="mt-2 text-sm text-white/80">Connected workers</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold">50k+</p>
                    <p class="mt-2 text-sm text-white/80">Happy customers</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold">85k+</p>
                    <p class="mt-2 text-sm text-white/80">Completed projects</p>
                </div>
            </div>
        </section>

        <section class="container-page py-16 pb-20">
            <div class="rounded-[32px] bg-brand-soft px-8 py-14 text-center">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Ready to experience reliable service?</h2>
                <p class="mx-auto mt-3 max-w-2xl text-muted">Download the SharpHand app today and join thousands of Nigerians finding trusted help in minutes.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <router-link to="/get-started" class="btn-dark">Get the App</router-link>
                    <router-link to="/contact" class="btn-outline">Contact sales</router-link>
                </div>
            </div>
        </section>
    </div>
</template>
