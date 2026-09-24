<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api';
import Flash from '../../components/Flash.vue';

const stats = ref({ users: 0, workers: 0, bookings: 0, revenue: 0 });
const bookings = ref([]);
const queue = ref([]);
const performance = ref({ user_growth: 0, verification: 0, revenue: 0, revenue_target: 1500000 });
const search = ref('');
const status = ref('');
const error = ref('');

function money(amount) {
    return `₦${Number(amount || 0).toLocaleString()}`;
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function initials(name) {
    const parts = (name || 'U').split(/\s+/);
    return `${parts[0]?.[0] || 'U'}${parts[1]?.[0] || ''}`.toUpperCase();
}

function badge(value) {
    return {
        completed: 'bg-emerald-50 text-emerald-700',
        pending: 'bg-amber-50 text-amber-700',
        in_progress: 'bg-blue-50 text-blue-700',
        cancelled: 'bg-red-50 text-red-700',
    }[value] || 'bg-slate-100';
}

async function load() {
    const { data } = await api.get('/admin/dashboard', { params: { q: search.value } });
    stats.value = data.stats;
    bookings.value = data.bookings.data || [];
    queue.value = data.queue || [];
    performance.value = data.performance;
}

async function approve(worker) {
    const { data } = await api.post(`/admin/workers/${worker.id}/approve`);
    status.value = data.message;
    await load();
}

async function reject(worker) {
    const { data } = await api.post(`/admin/workers/${worker.id}/reject`);
    status.value = data.message;
    await load();
}

async function exportReport() {
    const { data } = await api.get('/admin/export', { responseType: 'blob' });
    const url = window.URL.createObjectURL(data);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'sharphand-bookings.csv';
    link.click();
    window.URL.revokeObjectURL(url);
}

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-center">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Admin Overview</h1>
                <p class="mt-1 text-sm text-muted">Welcome back. Here is what is happening at SharpHand today.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button class="btn-outline" @click="exportReport">Export Report</button>
                <router-link to="/admin/bookings/create" class="btn-primary">+ Manual Booking</router-link>
            </div>
        </div>
        <Flash :status="status" :error="error" />
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="card p-5">
                <p class="text-sm text-muted">Total Active Users</p>
                <p class="mt-3 text-3xl font-bold">{{ Number(stats.users).toLocaleString() }}</p>
                <p class="mt-2 text-xs text-brand">+12.5% from last month</p>
            </article>
            <article class="card p-5">
                <p class="text-sm text-muted">Verified Workers</p>
                <p class="mt-3 text-3xl font-bold">{{ Number(stats.workers).toLocaleString() }}</p>
                <p class="mt-2 text-xs text-brand">+4.2% from last week</p>
            </article>
            <article class="card p-5">
                <p class="text-sm text-muted">Active Bookings</p>
                <p class="mt-3 text-3xl font-bold">{{ Number(stats.bookings).toLocaleString() }}</p>
                <p class="mt-2 text-xs text-brand">+18.1% in the last 24h</p>
            </article>
            <article class="card p-5">
                <p class="text-sm text-muted">Monthly Revenue</p>
                <p class="mt-3 text-3xl font-bold">{{ money(stats.revenue) }}</p>
                <p class="mt-2 text-xs text-brand">+22.4% vs target</p>
            </article>
        </div>

        <section class="card mt-6 overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-semibold">Recent Bookings</h2>
                <form class="w-full sm:w-72" @submit.prevent="load">
                    <input v-model="search" class="field" placeholder="Search bookings...">
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-5 py-3 font-medium">User Details</th>
                            <th class="px-5 py-3 font-medium">Worker / Service</th>
                            <th class="px-5 py-3 font-medium">Date</th>
                            <th class="px-5 py-3 font-medium">Amount</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in bookings" :key="booking.id" class="border-t border-line">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold">{{ initials(booking.user?.name || booking.guest_name) }}</span>
                                    <div>
                                        <p class="font-medium">{{ booking.user?.name || booking.guest_name || 'Guest' }}</p>
                                        <p class="text-xs text-muted">{{ booking.user?.email || booking.guest_phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium">{{ booking.worker?.name }}</p>
                                <p class="text-xs text-muted">{{ booking.service?.name }}</p>
                            </td>
                            <td class="px-5 py-4 text-muted">{{ formatDate(booking.created_at) }}</td>
                            <td class="px-5 py-4">{{ money(booking.amount) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badge(booking.status)">
                                    {{ String(booking.status).replace('_', ' ') }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <section class="card p-5">
                <h2 class="font-semibold">Worker Verification Queue</h2>
                <div class="mt-5 space-y-4">
                    <div v-for="worker in queue" :key="worker.id" class="flex flex-col justify-between gap-3 rounded-2xl border border-line px-4 py-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold">{{ initials(worker.name) }}</span>
                            <div>
                                <p class="font-medium">{{ worker.name }}</p>
                                <p class="text-xs text-muted">{{ worker.service?.name }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="text-sm font-semibold text-red-500" @click="reject(worker)">Reject</button>
                            <button class="btn-primary px-4 py-2" @click="approve(worker)">Approve</button>
                        </div>
                    </div>
                    <p v-if="!queue.length" class="text-sm text-muted">No workers waiting for verification.</p>
                </div>
            </section>
            <section class="card p-5">
                <h2 class="font-semibold">Quick Performance</h2>
                <div class="mt-6 space-y-5 text-sm">
                    <div>
                        <div class="mb-2 flex justify-between"><span>User Growth</span><span>{{ performance.user_growth }}%</span></div>
                        <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-brand" :style="{ width: performance.user_growth + '%' }"></div></div>
                    </div>
                    <div>
                        <div class="mb-2 flex justify-between"><span>Verification Rate</span><span>{{ performance.verification }}%</span></div>
                        <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-sky-400" :style="{ width: performance.verification + '%' }"></div></div>
                    </div>
                    <div>
                        <div class="mb-2 flex justify-between"><span>Revenue Target</span><span>{{ money(performance.revenue) }} / {{ money(performance.revenue_target) }}</span></div>
                        <div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-emerald-300" :style="{ width: Math.min(100, Math.round((performance.revenue / Math.max(performance.revenue_target, 1)) * 100)) + '%' }"></div></div>
                    </div>
                    <div class="rounded-2xl bg-brand-soft p-4 text-sm leading-6 text-emerald-900">
                        System insight: Worker sign-ups in Lagos have increased since the latest campaign. Keep approving verified artisans to keep wait times low.
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
