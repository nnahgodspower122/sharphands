<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Logo from '../components/Logo.vue';
import { useAuth } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuth();
const open = ref(false);

function itemClass(name) {
    return route.name === name
        ? 'bg-slate-100 font-semibold'
        : 'text-slate-600 hover:bg-slate-50';
}

async function logout() {
    await auth.logout();
    router.push('/admin');
}
</script>

<template>
    <div class="min-h-screen bg-[#f7f8fa]">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-white px-5">
            <div class="flex items-center gap-3">
                <button class="lg:hidden" aria-label="Open sidebar" @click="open = !open">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <Logo />
            </div>
            <div class="flex items-center gap-3 text-sm text-muted">
                <span>Admin Session</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-ink">{{ auth.initials.value }}</span>
            </div>
        </header>
        <div class="flex">
            <aside class="fixed inset-y-16 left-0 z-20 w-64 border-r border-line bg-white p-4 transition lg:static" :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
                <nav class="flex h-[calc(100vh-6rem)] flex-col justify-between">
                    <div class="space-y-1 text-sm">
                        <router-link to="/admin/dashboard" class="flex items-center gap-3 rounded-2xl px-3 py-2.5" :class="itemClass('admin-dashboard')">Overview</router-link>
                        <router-link to="/admin/users" class="flex items-center gap-3 rounded-2xl px-3 py-2.5" :class="itemClass('admin-users')">Users</router-link>
                        <router-link to="/admin/workers" class="flex items-center gap-3 rounded-2xl px-3 py-2.5" :class="itemClass('admin-workers')">Workers</router-link>
                        <router-link to="/admin/bookings" class="flex items-center gap-3 rounded-2xl px-3 py-2.5" :class="itemClass('admin-bookings')">Bookings</router-link>
                    </div>
                    <button class="w-full rounded-2xl px-3 py-2.5 text-left text-sm text-red-500 hover:bg-red-50" @click="logout">Logout</button>
                </nav>
            </aside>
            <main class="min-h-[calc(100vh-4rem)] flex-1 px-5 py-8 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
