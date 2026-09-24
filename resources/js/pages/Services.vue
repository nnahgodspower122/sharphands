<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const services = ref([]);
const coords = ref({ lat: 6.5244, lng: 3.3792 });

onMounted(async () => {
    const { data } = await api.get('/services');
    services.value = data.data;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            coords.value = {
                lat: pos.coords.latitude.toFixed(6),
                lng: pos.coords.longitude.toFixed(6),
            };
        });
    }
});

function findNearby(service) {
    router.push({
        name: 'nearby',
        query: { service_id: service.id, lat: coords.value.lat, lng: coords.value.lng },
    });
}
</script>

<template>
    <section class="container-page py-14">
        <h1 class="text-3xl font-bold tracking-tight">What do you need done?</h1>
        <p class="mt-2 max-w-2xl text-muted">Select a service and we’ll show verified workers near you.</p>
        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <article v-for="service in services" :key="service.id" class="card p-6">
                <h2 class="text-lg font-semibold">{{ service.name }}</h2>
                <p class="mt-2 text-sm leading-6 text-muted">{{ service.description }}</p>
                <p class="mt-4 text-sm font-semibold">From ₦{{ Number(service.base_amount).toLocaleString() }}</p>
                <button class="btn-primary mt-6 w-full" @click="findNearby(service)">Find nearby workers</button>
            </article>
        </div>
    </section>
</template>
