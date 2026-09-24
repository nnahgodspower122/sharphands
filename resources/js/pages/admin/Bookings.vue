<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api';
import Flash from '../../components/Flash.vue';

const bookings = ref([]);
const search = ref('');
const filter = ref('');
const status = ref('');

async function load() {
    const { data } = await api.get('/admin/bookings', { params: { q: search.value, status: filter.value } });
    bookings.value = data.data.data || [];
}

async function update(booking) {
    const { data } = await api.patch(`/admin/bookings/${booking.id}`, { status: booking.status });
    status.value = data.message;
}

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Bookings</h1>
                <p class="mt-1 text-sm text-muted">Monitor every connection made on SharpHand.</p>
            </div>
            <router-link to="/admin/bookings/create" class="btn-primary">+ Manual Booking</router-link>
        </div>
        <Flash :status="status" />
        <form class="mb-5 flex max-w-xl gap-3" @submit.prevent="load">
            <input v-model="search" class="field" placeholder="Search bookings...">
            <select v-model="filter" class="field w-40" @change="load">
                <option value="">All statuses</option>
                <option value="pending">pending</option>
                <option value="in_progress">in progress</option>
                <option value="completed">completed</option>
                <option value="cancelled">cancelled</option>
            </select>
        </form>
        <div class="card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-muted">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Worker</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Update</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="booking in bookings" :key="booking.id" class="border-t border-line">
                        <td class="px-5 py-4">{{ booking.user?.name || booking.guest_name || 'Guest' }}</td>
                        <td class="px-5 py-4">{{ booking.worker?.name }} · {{ booking.service?.name }}</td>
                        <td class="px-5 py-4">₦{{ Number(booking.amount).toLocaleString() }}</td>
                        <td class="px-5 py-4">{{ String(booking.status).replace('_', ' ') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex gap-2">
                                <select v-model="booking.status" class="field">
                                    <option value="pending">pending</option>
                                    <option value="in_progress">in progress</option>
                                    <option value="completed">completed</option>
                                    <option value="cancelled">cancelled</option>
                                </select>
                                <button class="btn-outline" @click="update(booking)">Save</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
