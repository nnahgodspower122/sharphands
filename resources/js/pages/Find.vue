<script setup>
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import { useAuth } from '../stores/auth';

const DEFAULT_LAT = 4.8156;
const DEFAULT_LNG = 7.0498;
const DEFAULT_LABEL = 'GRA, Port Harcourt';

const route = useRoute();
const router = useRouter();
const auth = useAuth();

const services = ref([]);
const workers = ref([]);
const search = ref('');
const locationLabel = ref(DEFAULT_LABEL);
const lat = ref(Number(route.query.lat) || DEFAULT_LAT);
const lng = ref(Number(route.query.lng) || DEFAULT_LNG);
const selectedServiceId = ref(route.query.service_id ? Number(route.query.service_id) : null);
const selectedWorker = ref(null);
const showAll = ref(false);
const loading = ref(false);
const error = ref('');
const modalOpen = ref(false);
const connected = ref(null);
const form = reactive({ name: auth.state.user?.name || '', phone: auth.state.user?.phone || '' });

let map;
let userMarker;
let workerMarkers = [];
let mapReady = false;

const selectedService = computed(() => services.value.find((item) => item.id === selectedServiceId.value) || null);

const filteredServices = computed(() => {
    const query = search.value.trim().toLowerCase();
    const list = query
        ? services.value.filter((item) => item.name.toLowerCase().includes(query) || (item.description || '').toLowerCase().includes(query))
        : services.value;

    return showAll.value || selectedServiceId.value || query ? list : list.slice(0, 5);
});

const tiles = computed(() => (selectedService.value ? [selectedService.value] : filteredServices.value));

function iconFor(service) {
    const slug = service?.slug || service?.name?.toLowerCase() || '';

    if (slug.includes('plumb')) {
        return { key: 'plumbing', bg: 'bg-sky-50 text-sky-500', paths: ['M12 3v6m0 6v6M7 8h10M8 16h8M6 12h12'] };
    }

    if (slug.includes('electr')) {
        return {
            key: 'electrical',
            bg: 'bg-amber-50 text-amber-600',
            paths: [
                'M8.2 8.4c0-2 1.7-3.5 3.8-3.5s3.8 1.5 3.8 3.5',
                'M7.4 8.4h9.2',
                'M12 8.8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3',
                'M8.2 13.6v5h7.6v-5c0-1.2-1.7-2.1-3.8-2.1s-3.8.9-3.8 2.1z',
                'M8.6 16.2h6.8',
                'M18.4 11v2.4M17.3 13.4h2.2M17.6 13.4v2.1a1 1 0 0 0 2 0v-2.1M17.4 16.8h2.4',
            ],
        };
    }

    if (slug.includes('tailor') || slug.includes('sew')) {
        return {
            key: 'tailoring',
            bg: 'bg-violet-50 text-violet-600',
            paths: [
                'M3.5 17h16.5v3.2H3.5z',
                'M5.2 10.2h9.2v6.8H5.2z',
                'M14.4 7.8h5.2v5.2h-3.1V10H14.4z',
                'M17.8 13v5.4',
                'M8.2 13.6a1.7 1.7 0 1 1 0 .02',
                'M19.6 7.6c0-2-1.4-3.4-3.3-3.4',
            ],
        };
    }

    if (slug.includes('clean')) {
        return { key: 'cleaning', bg: 'bg-emerald-50 text-brand', paths: ['M4 20h16M8 16V8a4 4 0 0 1 8 0v8'] };
    }

    return {
        key: 'carpentry',
        bg: 'bg-orange-50 text-orange-500',
        paths: [
            'M3.6 8.8h9.2v4.2H3.6z',
            'M12.8 9.6 16.4 6',
            'M12.8 12.2 16.4 16',
            'M8.2 13 11.8 21',
        ],
    };
}

function initials(name) {
    const parts = (name || 'W').split(/\s+/);

    return `${parts[0]?.[0] || 'W'}${parts[1]?.[0] || ''}`.toUpperCase();
}

function syncQuery() {
    const query = {
        lat: lat.value,
        lng: lng.value,
    };

    if (selectedServiceId.value) {
        query.service_id = selectedServiceId.value;
    }

    router.replace({ name: 'find', query });
}

async function loadLeaflet() {
    if (window.L) {
        return window.L;
    }

    await new Promise((resolve) => {
        if (!document.querySelector('link[data-leaflet]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.setAttribute('data-leaflet', 'true');
            document.head.appendChild(link);
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = resolve;
        document.body.appendChild(script);
    });

    return window.L;
}

function userIcon(L) {
    return L.divIcon({
        className: 'find-pin',
        html: '<span class="find-pin-you"></span>',
        iconSize: [22, 22],
        iconAnchor: [11, 11],
    });
}

function workerIcon(L, worker, active) {
    const icon = iconFor(worker.service);

    return L.divIcon({
        className: 'find-pin',
        html: `<span class="find-pin-worker find-pin-${icon.key} ${active ? 'is-active' : ''}" title="${worker.name}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                ${(icon.paths || []).map((d) => `<path stroke-linecap="round" stroke-linejoin="round" d="${d}"></path>`).join('')}
            </svg>
        </span>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18],
    });
}

function drawWorkers(L) {
    workerMarkers.forEach((marker) => marker.remove());
    workerMarkers = [];

    visibleWorkers.value.forEach((worker, index) => {
        if (!worker.latitude || !worker.longitude) {
            return;
        }

        const jitter = (index % 5) * 0.00018;
        const marker = L.marker([Number(worker.latitude) + jitter, Number(worker.longitude) + jitter], {
            icon: workerIcon(L, worker, selectedWorker.value?.id === worker.id),
        }).addTo(map);

        marker.on('click', () => {
            selectedWorker.value = worker;
        });

        workerMarkers.push(marker);
    });
}

async function initMap() {
    const L = await loadLeaflet();

    map = L.map('find-map', { zoomControl: false, attributionControl: false }).setView([lat.value, lng.value], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);
    L.control.attribution({ prefix: false, position: 'topright' }).addTo(map);
    userMarker = L.marker([lat.value, lng.value], { icon: userIcon(L) }).addTo(map);
    mapReady = true;
    await nextTick();
    map.invalidateSize();
}

function recenter() {
    if (!map) {
        return;
    }

    map.setView([lat.value, lng.value], 13);
    userMarker?.setLatLng([lat.value, lng.value]);
}

async function loadServices() {
    const { data } = await api.get('/services/nearby', { params: { lat: lat.value, lng: lng.value } });
    services.value = data.data;

    if (!selectedServiceId.value && route.query.service) {
        const match = services.value.find((item) => item.slug === route.query.service || item.name.toLowerCase() === String(route.query.service).toLowerCase());
        if (match) {
            selectedServiceId.value = match.id;
        }
    }
}

const PAGE_SIZE = 5;
const listLimit = ref(PAGE_SIZE);
const listLoading = ref(false);

const visibleWorkers = computed(() => {
    if (!selectedServiceId.value) {
        return workers.value;
    }

    return workers.value.filter((worker) => Number(worker.service_id || worker.service?.id) === Number(selectedServiceId.value));
});

const featuredWorker = computed(() => visibleWorkers.value[0] || null);
const pagedWorkers = computed(() => visibleWorkers.value.slice(0, listLimit.value));
const hasMoreWorkers = computed(() => listLimit.value < visibleWorkers.value.length);
const closestKm = computed(() => featuredWorker.value?.distance_km ?? '—');

function resetWorkerPage() {
    listLimit.value = PAGE_SIZE;
}

function onWorkerListScroll(event) {
    const el = event.target;

    if (!hasMoreWorkers.value || listLoading.value) {
        return;
    }

    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 64) {
        listLoading.value = true;
        listLimit.value += PAGE_SIZE;
        nextTick(() => {
            listLoading.value = false;
        });
    }
}

async function loadWorkers() {
    loading.value = true;
    error.value = '';

    try {
        const { data } = await api.get('/workers/nearby', {
            params: { lat: lat.value, lng: lng.value },
        });
        workers.value = data.data;
        if (selectedServiceId.value) {
            selectedWorker.value = visibleWorkers.value[0] || null;
        } else {
            selectedWorker.value = null;
        }

        if (mapReady && window.L) {
            drawWorkers(window.L);
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not load nearby workers.';
        workers.value = [];
    } finally {
        loading.value = false;
    }
}

function chooseService(service) {
    selectedServiceId.value = service.id;
    showAll.value = false;
    connected.value = null;
    resetWorkerPage();
    selectedWorker.value = visibleWorkers.value[0] || null;
    syncQuery();
}

function clearService() {
    selectedServiceId.value = null;
    selectedWorker.value = null;
    connected.value = null;
    resetWorkerPage();
    syncQuery();
    if (mapReady && window.L) {
        drawWorkers(window.L);
    }
}

function openConnect(worker) {
    selectedWorker.value = worker;
    error.value = '';
    connected.value = null;
    form.name = auth.state.user?.name || form.name;
    form.phone = auth.state.user?.phone || form.phone;
    modalOpen.value = true;
}

async function connect() {
    error.value = '';

    if (!auth.isAuthenticated.value && (!form.name.trim() || !form.phone.trim())) {
        error.value = 'Please enter your name and phone number.';
        return;
    }

    try {
        const payload = {
            service_id: selectedServiceId.value || selectedWorker.value?.service_id || selectedWorker.value?.service?.id,
            worker_id: selectedWorker.value?.id,
            latitude: lat.value,
            longitude: lng.value,
        };

        if (!auth.isAuthenticated.value) {
            payload.name = form.name.trim();
            payload.phone = form.phone.trim();
        }

        const { data } = await api.post('/book', payload);
        connected.value = data;
        workers.value = workers.value.filter((item) => item.id !== selectedWorker.value?.id);
        if (mapReady && window.L) {
            drawWorkers(window.L);
        }
    } catch (e) {
        error.value = e.response?.data?.errors?.service_id?.[0]
            || e.response?.data?.message
            || 'No available worker was found nearby.';
    }
}

function locate() {
    if (!navigator.geolocation) {
        return;
    }

    navigator.geolocation.getCurrentPosition((position) => {
        lat.value = Number(position.coords.latitude.toFixed(6));
        lng.value = Number(position.coords.longitude.toFixed(6));
        locationLabel.value = 'Your current location';
        userMarker?.setLatLng([lat.value, lng.value]);
        map?.setView([lat.value, lng.value], 13);
        syncQuery();
    });
}

watch([lat, lng], () => {
    loadWorkers();
    if (services.value.length) {
        loadServices();
    }
});

watch(selectedServiceId, () => {
    resetWorkerPage();
    if (mapReady && window.L) {
        drawWorkers(window.L);
    }
});

watch(selectedWorker, () => {
    if (mapReady && window.L) {
        drawWorkers(window.L);
    }
});

onMounted(async () => {
    await Promise.all([initMap(), loadServices()]);
    await loadWorkers();
    locate();
});

onUnmounted(() => {
    map?.remove();
});
</script>

<template>
    <section class="relative h-[calc(100vh-4rem)] overflow-hidden bg-slate-100">
        <div id="find-map" class="absolute inset-0 z-0"></div>

        <div class="pointer-events-none absolute inset-x-0 top-0 z-10 p-4 md:p-6">
            <div class="pointer-events-auto mx-auto flex max-w-xl items-center gap-3">
                <label class="relative flex-1">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3-3" />
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        class="w-full rounded-full border border-white/80 bg-white py-3 pl-11 pr-12 text-sm shadow-card outline-none placeholder:text-slate-400"
                        placeholder="Search services or location"
                    >
                </label>
                <button type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-card" aria-label="Use my location" @click="locate">
                    <svg class="h-5 w-5 text-brand" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3" />
                        <path stroke-linecap="round" d="M12 5v-2m0 18v-2m7-7h2M3 12h2" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 right-0 z-10 md:left-6 md:right-auto md:bottom-6 md:w-[420px]">
            <div class="rounded-t-[32px] bg-white px-5 pb-6 pt-5 shadow-[0_-12px_40px_rgba(15,23,42,0.12)] md:rounded-[32px] md:shadow-card">
                <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-slate-200 md:hidden"></div>

                <template v-if="!selectedService">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">What service do you need?</h1>
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-muted">
                                <svg class="h-4 w-4 text-brand" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 0 0-6 6c0 4.4 6 10 6 10s6-5.6 6-10a6 6 0 0 0-6-6Zm0 8.2A2.2 2.2 0 1 1 10 5.8a2.2 2.2 0 0 1 0 4.4Z"/></svg>
                                {{ locationLabel }}
                            </p>
                        </div>
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-50" aria-label="Recenter map" @click="recenter">
                            <svg class="h-4 w-4 text-ink" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M12 19V5m0 0 5 5M12 5l-5 5" />
                            </svg>
                        </button>
                    </div>

                    <div class="mt-6 grid grid-cols-4 gap-3">
                        <button
                            v-for="service in tiles"
                            :key="service.id"
                            type="button"
                            class="text-center"
                            @click="chooseService(service)"
                        >
                            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl" :class="iconFor(service).bg">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path v-for="(d, index) in iconFor(service).paths" :key="index" stroke-linecap="round" stroke-linejoin="round" :d="d" />
                                </svg>
                            </span>
                            <span class="mt-2 block text-xs font-semibold">{{ service.name }}</span>
                            <span class="mt-0.5 block text-[11px] text-muted">{{ service.nearby_count || 0 }} nearby</span>
                        </button>
                    </div>

                    <div class="mt-5 flex items-center justify-between rounded-2xl bg-brand-soft px-4 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-ink">Fast matching</p>
                            <p class="text-muted">Average connection time: 2 minutes</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand">Live</span>
                    </div>

                    <button type="button" class="btn-primary mt-5 w-full" @click="showAll = true">Explore all services</button>
                </template>

                <template v-else>
                    <div class="flex items-center justify-between gap-3">
                        <button type="button" class="text-sm font-semibold text-muted" @click="clearService">← Services</button>
                        <span class="rounded-full bg-brand px-3 py-1 text-xs font-semibold text-white">Available now</span>
                    </div>

                    <div class="mt-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight">{{ selectedService.name }}</h2>
                            <p class="mt-1 text-sm text-muted">
                                Found {{ visibleWorkers.length }} worker{{ visibleWorkers.length === 1 ? '' : 's' }} ready to help nearby.
                            </p>
                        </div>
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" :class="iconFor(selectedService).bg">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path v-for="(d, index) in iconFor(selectedService).paths" :key="index" stroke-linecap="round" stroke-linejoin="round" :d="d" />
                            </svg>
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-2xl bg-slate-50 px-3 py-2">
                            <p class="text-muted">Closest</p>
                            <p class="mt-0.5 font-semibold">{{ closestKm }} km</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-3 py-2">
                            <p class="text-muted">Verified</p>
                            <p class="mt-0.5 font-semibold">Pro level</p>
                        </div>
                    </div>

                    <div v-if="error && !modalOpen" class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</div>

                    <div
                        class="mt-4 max-h-[38vh] space-y-2 overflow-y-auto pr-1 md:max-h-[280px]"
                        @scroll.passive="onWorkerListScroll"
                    >
                        <article
                            v-for="(worker, index) in pagedWorkers"
                            :key="worker.id"
                            class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl border px-3 py-3"
                            :class="selectedWorker?.id === worker.id ? 'border-brand bg-brand-soft/70' : 'border-line bg-white'"
                            @click="selectedWorker = worker"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="relative inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold">
                                    {{ initials(worker.name) }}
                                    <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-brand"></span>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate font-semibold">{{ worker.name }}</p>
                                        <span v-if="index === 0" class="shrink-0 rounded-full bg-brand-soft px-2 py-0.5 text-[10px] font-semibold text-brand">Best match</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-muted">{{ worker.distance_km }} km away · Available</p>
                                </div>
                            </div>
                            <button type="button" class="btn-primary shrink-0 px-3 py-2 text-xs" @click.stop="openConnect(worker)">Connect</button>
                        </article>

                        <p v-if="hasMoreWorkers" class="py-2 text-center text-xs text-muted">
                            {{ listLoading ? 'Loading more…' : 'Scroll to see more workers' }}
                        </p>
                        <p v-else-if="pagedWorkers.length" class="py-2 text-center text-xs text-muted">
                            That’s everyone nearby for this service.
                        </p>
                        <p v-if="!visibleWorkers.length" class="rounded-2xl bg-slate-50 px-4 py-6 text-center text-sm text-muted">
                            No verified workers are available nearby yet. Try another service or check back soon.
                        </p>
                    </div>
                </template>
            </div>
        </div>

        <div v-if="modalOpen" class="fixed inset-0 z-30 flex items-end justify-center bg-ink/40 p-4 sm:items-center">
            <div class="w-full max-w-md rounded-[28px] bg-white p-6 shadow-card">
                <div v-if="connected" class="text-center">
                    <p class="text-sm font-semibold text-brand">You’re connected</p>
                    <h3 class="mt-2 text-2xl font-bold">{{ connected.worker.name }}</h3>
                    <p class="mt-2 text-sm text-muted">{{ connected.message }}</p>
                    <a :href="'tel:' + connected.worker.phone" class="btn-primary mt-6 w-full">Call {{ connected.worker.phone }}</a>
                    <button type="button" class="mt-3 w-full text-sm font-semibold text-muted" @click="modalOpen = false; connected = null">Close</button>
                </div>
                <form v-else class="space-y-4" @submit.prevent="connect">
                    <div>
                        <h3 class="text-xl font-bold">Connect with {{ selectedWorker?.name }}</h3>
                        <p class="mt-1 text-sm text-muted">We’ll share their number after we have your name and phone.</p>
                    </div>
                    <div v-if="error" class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ error }}</div>
                    <label class="block text-sm font-medium">Your name
                        <input v-model="form.name" class="field mt-2" placeholder="Amina Balogun" required>
                    </label>
                    <label class="block text-sm font-medium">Phone
                        <input v-model="form.phone" class="field mt-2" placeholder="+234 800 000 0000" required>
                    </label>
                    <div class="flex gap-3">
                        <button type="button" class="btn-outline flex-1" @click="modalOpen = false">Cancel</button>
                        <button type="submit" class="btn-primary flex-1">Connect</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<style>
.find-pin {
    background: transparent;
    border: 0;
}
.find-pin-you {
    display: block;
    width: 18px;
    height: 18px;
    border-radius: 999px;
    background: #0b8f63;
    box-shadow: 0 0 0 8px rgba(11, 143, 99, 0.18);
}
.find-pin-worker {
    display: flex;
    width: 36px;
    height: 36px;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: #fff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16);
    border: 2px solid currentColor;
}
.find-pin-worker svg {
    width: 18px;
    height: 18px;
}
.find-pin-plumbing { color: #0ea5e9; }
.find-pin-electrical { color: #d97706; }
.find-pin-tailoring { color: #7c3aed; }
.find-pin-cleaning { color: #0b8f63; }
.find-pin-carpentry { color: #f97316; }
.find-pin-worker.is-active {
    background: currentColor;
}
.find-pin-worker.is-active svg {
    color: #fff;
    stroke: #fff;
}
</style>
