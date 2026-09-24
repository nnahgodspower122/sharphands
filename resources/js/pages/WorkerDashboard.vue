<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../api';
import Flash from '../components/Flash.vue';

const worker = ref(null);
const bookings = ref([]);
const status = ref('');
const error = ref('');
const location = reactive({ latitude: '', longitude: '' });

onMounted(async () => {
    const { data } = await api.get('/worker/dashboard');
    worker.value = data.worker;
    bookings.value = data.bookings.data || [];
    location.latitude = data.worker.latitude || '';
    location.longitude = data.worker.longitude || '';
});

async function toggleAvailability() {
    const next = worker.value.status === 'available' ? 'busy' : 'available';
    const { data } = await api.post('/worker/availability', { status: next });
    worker.value = data.worker;
    status.value = 'Availability updated.';
}

async function useLocation() {
    navigator.geolocation.getCurrentPosition(async (pos) => {
        location.latitude = pos.coords.latitude.toFixed(6);
        location.longitude = pos.coords.longitude.toFixed(6);
        const { data } = await api.post('/worker/update-location', location);
        worker.value = data.worker;
        status.value = data.message;
    });
}

async function updateBooking(booking) {
    const { data } = await api.patch(`/worker/bookings/${booking.id}`, { status: booking.status });
    status.value = data.message;
}
</script>

<template>
    <section class="container-page py-12">
        <Flash :status="status" :error="error" />
        <div v-if="worker" class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Worker dashboard</h1>
            <p class="mt-2 text-muted">{{ worker.service?.name }} · {{ worker.verified ? 'Verified' : 'Awaiting verification' }}</p>
        </div>
        <div v-if="worker" class="grid gap-5 lg:grid-cols-3">
            <article class="card p-6">
                <h2 class="font-semibold">Availability</h2>
                <p class="mt-2 text-sm text-muted">Current status: {{ worker.status }}</p>
                <button class="btn-primary mt-4" @click="toggleAvailability">Mark {{ worker.status === 'available' ? 'busy' : 'available' }}</button>
            </article>
            <article class="card p-6 lg:col-span-2">
                <h2 class="font-semibold">Update live location</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                    <input v-model="location.latitude" class="field" placeholder="Latitude">
                    <input v-model="location.longitude" class="field" placeholder="Longitude">
                    <button class="btn-dark" @click="useLocation">Use my location</button>
                </div>
            </article>
        </div>
        <div class="mt-10">
            <h2 class="text-xl font-semibold">Incoming connections</h2>
            <div class="mt-4 space-y-4">
                <article v-for="booking in bookings" :key="booking.id" class="card flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center">
                    <div>
                        <p class="font-semibold">{{ booking.user?.name || booking.guest_name || 'Guest' }}</p>
                        <p class="text-sm text-muted">{{ booking.service?.name }} · ₦{{ Number(booking.amount).toLocaleString() }} · {{ booking.status }}</p>
                        <p v-if="booking.guest_phone || booking.user?.phone" class="mt-1 text-sm text-muted">{{ booking.guest_phone || booking.user?.phone }}</p>
                    </div>
                    <div class="flex gap-2">
                        <select v-model="booking.status" class="field">
                            <option value="pending">pending</option>
                            <option value="in_progress">in progress</option>
                            <option value="completed">completed</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                        <button class="btn-primary" @click="updateBooking(booking)">Update</button>
                    </div>
                </article>
                <div v-if="!bookings.length" class="card p-8 text-center text-muted">No incoming connections yet.</div>
            </div>
        </div>
    </section>
</template>
