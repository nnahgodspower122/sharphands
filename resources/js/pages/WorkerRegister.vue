<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import Flash from '../components/Flash.vue';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();
const services = ref([]);
const error = ref('');
const form = reactive({
    name: '',
    email: '',
    phone: '',
    service_id: '',
    latitude: null,
    longitude: null,
    password: '',
    password_confirmation: '',
});

onMounted(async () => {
    const { data } = await api.get('/services');
    services.value = data.data;
    form.service_id = data.data[0]?.id || '';

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            form.latitude = pos.coords.latitude.toFixed(6);
            form.longitude = pos.coords.longitude.toFixed(6);
        });
    }
});

async function submit() {
    error.value = '';

    try {
        const { data } = await api.post('/worker/register', form);
        auth.setSession(data.user);
        router.push('/worker/dashboard');
    } catch (e) {
        error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Could not create your worker account.';
    }
}
</script>

<template>
    <section class="container-page flex justify-center py-16">
        <div class="card w-full max-w-md p-8">
            <Flash :error="error" />
            <h1 class="text-2xl font-bold">Join as a worker</h1>
            <p class="mt-2 text-sm text-muted">Create your profile. An admin will verify you before customers can see you on the map.</p>
            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <label class="block text-sm font-medium">Full name
                    <input v-model="form.name" class="field mt-2">
                </label>
                <label class="block text-sm font-medium">Email address
                    <input v-model="form.email" type="email" class="field mt-2">
                </label>
                <label class="block text-sm font-medium">Phone
                    <input v-model="form.phone" class="field mt-2">
                </label>
                <label class="block text-sm font-medium">Service
                    <select v-model="form.service_id" class="field mt-2">
                        <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                    </select>
                </label>
                <label class="block text-sm font-medium">Password
                    <input v-model="form.password" type="password" class="field mt-2">
                </label>
                <label class="block text-sm font-medium">Confirm password
                    <input v-model="form.password_confirmation" type="password" class="field mt-2">
                </label>
                <button class="btn-primary w-full">Create worker account</button>
            </form>
        </div>
    </section>
</template>
