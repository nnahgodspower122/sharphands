<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';
import Flash from '../../components/Flash.vue';

const router = useRouter();
const users = ref([]);
const workers = ref([]);
const services = ref([]);
const error = ref('');
const form = reactive({
    user_id: '',
    worker_id: '',
    service_id: '',
    amount: 10000,
    status: 'pending',
});

onMounted(async () => {
    const { data } = await api.get('/admin/options');
    users.value = data.users;
    workers.value = data.workers;
    services.value = data.services;
    form.user_id = data.users[0]?.id || '';
    form.worker_id = data.workers[0]?.id || '';
    form.service_id = data.services[0]?.id || '';
});

async function submit() {
    error.value = '';

    try {
        await api.post('/admin/bookings/manual', form);
        router.push('/admin/dashboard');
    } catch (e) {
        error.value = e.response?.data?.message || 'Could not create that booking.';
    }
}
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight">Manual Booking</h1>
            <p class="mt-1 text-sm text-muted">Create a connection when a customer calls in directly.</p>
        </div>
        <form class="card max-w-xl space-y-4 p-6" @submit.prevent="submit">
            <Flash :error="error" />
            <label class="block text-sm font-medium">User
                <select v-model="form.user_id" class="field mt-2">
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} · {{ user.email }}</option>
                </select>
            </label>
            <label class="block text-sm font-medium">Worker
                <select v-model="form.worker_id" class="field mt-2">
                    <option v-for="worker in workers" :key="worker.id" :value="worker.id">{{ worker.name }} · {{ worker.service?.name }}</option>
                </select>
            </label>
            <label class="block text-sm font-medium">Service
                <select v-model="form.service_id" class="field mt-2">
                    <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }}</option>
                </select>
            </label>
            <label class="block text-sm font-medium">Amount (₦)
                <input v-model="form.amount" type="number" class="field mt-2">
            </label>
            <label class="block text-sm font-medium">Status
                <select v-model="form.status" class="field mt-2">
                    <option value="pending">pending</option>
                    <option value="in_progress">in progress</option>
                    <option value="completed">completed</option>
                </select>
            </label>
            <button class="btn-primary">Create booking</button>
        </form>
    </div>
</template>
