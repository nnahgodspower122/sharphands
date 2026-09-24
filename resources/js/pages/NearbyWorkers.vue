<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import Flash from '../components/Flash.vue';

const route = useRoute();
const router = useRouter();
const workers = ref([]);
const services = ref([]);
const status = ref('');
const error = ref('');
const lat = computed(() => Number(route.query.lat || 6.5244));
const lng = computed(() => Number(route.query.lng || 3.3792));
const service = computed(() => services.value.find((item) => String(item.id) === String(route.query.service_id)));

function initials(name) {
    const parts = (name || 'W').split(/\s+/);
    return `${parts[0]?.[0] || 'W'}${parts[1]?.[0] || ''}`.toUpperCase();
}

async function loadMap() {
    if (window.L) {
        return window.L;
    }

    await new Promise((resolve) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = resolve;
        document.body.appendChild(script);
    });

    return window.L;
}

onMounted(async () => {
    const [{ data: serviceData }, { data: workerData }] = await Promise.all([
        api.get('/services'),
        api.get('/workers/nearby', { params: { service_id: route.query.service_id, lat: lat.value, lng: lng.value } }),
    ]);

    services.value = serviceData.data;
    workers.value = workerData.data;

    const L = await loadMap();
    const map = L.map('map').setView([lat.value, lng.value], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);
    L.marker([lat.value, lng.value]).addTo(map).bindPopup('You');
    workers.value.forEach((worker) => {
        if (worker.latitude && worker.longitude) {
            L.marker([worker.latitude, worker.longitude]).addTo(map).bindPopup(worker.name);
        }
    });
});

async function connect(worker) {
    error.value = '';

    try {
        const { data } = await api.post('/book', {
            service_id: route.query.service_id,
            worker_id: worker.id,
            latitude: lat.value,
            longitude: lng.value,
        });
        status.value = data.message;
        router.push('/bookings');
    } catch (e) {
        error.value = e.response?.data?.message || 'No available worker was found nearby.';
    }
}
</script>

<template>
    <section class="container-page py-10">
        <Flash :status="status" :error="error" />
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-brand">{{ service?.name }}</p>
                <h1 class="text-3xl font-bold tracking-tight">Nearby workers</h1>
                <p class="mt-2 text-sm text-muted">Verified professionals around your current location.</p>
            </div>
            <router-link to="/services" class="btn-outline">Change service</router-link>
        </div>
        <div id="map" class="h-[360px] overflow-hidden rounded-[28px] border border-line"></div>
        <div class="mt-8 grid gap-4">
            <article v-for="worker in workers" :key="worker.id" class="card flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center">
                <div class="flex items-center gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft font-semibold text-brand">{{ initials(worker.name) }}</span>
                    <div>
                        <h2 class="font-semibold">{{ worker.name }}</h2>
                        <p class="text-sm text-muted">{{ worker.service?.name }} · {{ worker.distance_km }} km away · {{ worker.status }}</p>
                    </div>
                </div>
                <button class="btn-primary" @click="connect(worker)">Connect instantly</button>
            </article>
            <div v-if="!workers.length" class="card p-8 text-center text-muted">
                No verified workers are available nearby yet. Try another service or check back soon.
            </div>
        </div>
    </section>
</template>
