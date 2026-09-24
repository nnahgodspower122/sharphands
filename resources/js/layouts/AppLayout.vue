<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Logo from '../components/Logo.vue';
import { useAuth } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuth();
const open = ref(false);

function isActive(name) {
    return route.name === name;
}

async function logout() {
    await auth.logout();
    open.value = false;
    router.push('/');
}

function dashboardTo() {
    if (auth.isAdmin.value) {
        return '/admin/dashboard';
    }

    if (auth.isWorker.value) {
        return '/worker/dashboard';
    }

    return '/bookings';
}
</script>

<template>
    <div>
        <header class="sticky top-0 z-40 border-b border-line/80 bg-white/90 backdrop-blur">
            <div class="container-page flex h-16 items-center justify-between">
                <Logo />
                <nav class="hidden items-center gap-8 md:flex">
                    <router-link
                        to="/"
                        class="cursor-pointer border-b-2 pb-0.5 text-sm font-bold transition"
                        :class="isActive('home') ? 'border-brand text-brand' : 'border-transparent text-ink hover:text-brand'"
                    >Home</router-link>
                    <router-link
                        to="/about"
                        class="cursor-pointer border-b-2 pb-0.5 text-sm font-bold transition"
                        :class="isActive('about') ? 'border-brand text-brand' : 'border-transparent text-ink hover:text-brand'"
                    >About</router-link>
                    <router-link
                        to="/contact"
                        class="cursor-pointer border-b-2 pb-0.5 text-sm font-bold transition"
                        :class="isActive('contact') ? 'border-brand text-brand' : 'border-transparent text-ink hover:text-brand'"
                    >Contact</router-link>
                </nav>
                <div class="hidden items-center gap-3 md:flex">
                    <template v-if="auth.isAuthenticated.value">
                        <router-link :to="dashboardTo()" class="btn-outline">{{ auth.isAdmin.value || auth.isWorker.value ? 'Dashboard' : 'My bookings' }}</router-link>
                        <button class="btn-primary" @click="logout">Logout</button>
                    </template>
                    <router-link v-else to="/get-started" class="btn-primary">Get Started</router-link>
                </div>
                <button type="button" class="md:hidden" aria-label="Toggle menu" @click="open = !open">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
            <div v-if="open" class="container-page pb-4 md:hidden">
                <div class="flex flex-col gap-3 rounded-3xl border border-line p-4">
                    <router-link
                        to="/"
                        class="w-fit cursor-pointer border-b-2 pb-0.5 text-sm font-bold"
                        :class="isActive('home') ? 'border-brand text-brand' : 'border-transparent text-ink'"
                        @click="open = false"
                    >Home</router-link>
                    <router-link
                        to="/about"
                        class="w-fit cursor-pointer border-b-2 pb-0.5 text-sm font-bold"
                        :class="isActive('about') ? 'border-brand text-brand' : 'border-transparent text-ink'"
                        @click="open = false"
                    >About</router-link>
                    <router-link
                        to="/contact"
                        class="w-fit cursor-pointer border-b-2 pb-0.5 text-sm font-bold"
                        :class="isActive('contact') ? 'border-brand text-brand' : 'border-transparent text-ink'"
                        @click="open = false"
                    >Contact</router-link>
                    <router-link v-if="auth.isAuthenticated.value" :to="dashboardTo()" @click="open = false">Dashboard</router-link>
                    <router-link v-else to="/get-started" class="btn-primary" @click="open = false">Get Started</router-link>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer v-if="route.name !== 'find'" class="bg-black text-white">
            <div class="container-page grid gap-10 py-14 md:grid-cols-4">
                <div>
                    <Logo light />
                    <p class="mt-4 max-w-xs text-sm leading-6 text-white/70">Connecting you with the most reliable local professionals in Nigeria. Quality service, just a tap away.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Quick Links</h4>
                    <div class="mt-4 flex flex-col gap-2 text-sm text-white/70">
                        <router-link to="/" class="hover:text-white">Home</router-link>
                        <router-link to="/about" class="hover:text-white">About Us</router-link>
                        <router-link to="/contact" class="hover:text-white">Contact</router-link>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Services</h4>
                    <div class="mt-4 flex flex-col gap-2 text-sm text-white/70">
                        <router-link to="/get-started?service=plumbing" class="hover:text-white">Plumbing</router-link>
                        <router-link to="/get-started?service=electrical" class="hover:text-white">Electrical Works</router-link>
                        <router-link to="/get-started?service=cleaning" class="hover:text-white">Home Cleaning</router-link>
                        <router-link to="/get-started?service=carpentry" class="hover:text-white">Carpentry</router-link>
                        <router-link to="/get-started?service=tailoring" class="hover:text-white">Tailoring</router-link>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Follow Us</h4>
                    <div class="mt-4 flex gap-3 text-white/70">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20">f</span>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20">in</span>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20">ig</span>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20">x</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 py-5 text-center text-xs text-white/50">© {{ new Date().getFullYear() }} SharpHand.ng. All rights reserved.</div>
        </footer>
    </div>
</template>
