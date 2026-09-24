<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../api';
import Flash from '../components/Flash.vue';

const bookings = ref([]);
const status = ref('');
const error = ref('');
const reviews = reactive({});

function money(amount) {
    return `₦${Number(amount || 0).toLocaleString()}`;
}

function formatDate(value) {
    return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function badge(value) {
    return {
        completed: 'bg-emerald-50 text-emerald-700',
        pending: 'bg-amber-50 text-amber-700',
        in_progress: 'bg-blue-50 text-blue-700',
        cancelled: 'bg-red-50 text-red-700',
    }[value] || 'bg-slate-100 text-slate-700';
}

onMounted(async () => {
    const { data } = await api.get('/bookings');
    bookings.value = data.data.data || [];
    bookings.value.forEach((booking) => {
        reviews[booking.id] = { score: 5, review: '' };
    });
});

async function rate(booking) {
    error.value = '';

    try {
        const payload = reviews[booking.id] || { score: 5, review: '' };
        const { data } = await api.post(`/bookings/${booking.id}/rate`, payload);
        status.value = data.message;
        booking.rating = data.rating;
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not save your rating.';
    }
}
</script>

<template>
    <section class="container-page py-12">
        <Flash :status="status" :error="error" />
        <div class="mb-8 flex items-end justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">My bookings</h1>
                <p class="mt-2 text-muted">Track connections and rate completed jobs.</p>
            </div>
            <router-link to="/get-started" class="btn-primary">New booking</router-link>
        </div>
        <div class="space-y-4">
            <article v-for="booking in bookings" :key="booking.id" class="card p-5">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-semibold">{{ booking.worker?.name }} · {{ booking.service?.name }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ money(booking.amount) }} · {{ formatDate(booking.created_at) }}</p>
                        <p class="mt-2 text-sm">Call {{ booking.worker?.phone }}</p>
                    </div>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badge(booking.status)">
                        {{ String(booking.status).replace('_', ' ') }}
                    </span>
                </div>
                <form v-if="booking.status === 'completed' && !booking.rating" class="mt-4 flex flex-col gap-3 sm:flex-row" @submit.prevent="rate(booking)">
                    <select v-model="reviews[booking.id].score" class="field sm:w-28" @focus="reviews[booking.id] ||= { score: 5, review: '' }">
                        <option v-for="n in [5,4,3,2,1]" :key="n" :value="n">{{ n }} star{{ n > 1 ? 's' : '' }}</option>
                    </select>
                    <input v-model="reviews[booking.id].review" class="field" placeholder="How was the work?" @focus="reviews[booking.id] ||= { score: 5, review: '' }">
                    <button class="btn-dark">Rate</button>
                </form>
            </article>
            <div v-if="!bookings.length" class="card p-8 text-center text-muted">You have not connected with a worker yet.</div>
        </div>
    </section>
</template>
