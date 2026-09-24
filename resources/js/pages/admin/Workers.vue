<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api';
import Flash from '../../components/Flash.vue';

const workers = ref([]);
const search = ref('');
const status = ref('');

async function load() {
    const { data } = await api.get('/admin/workers', { params: { q: search.value } });
    workers.value = data.data.data || [];
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

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight">Workers</h1>
            <p class="mt-1 text-sm text-muted">Verify artisans and keep the marketplace trusted.</p>
        </div>
        <Flash :status="status" />
        <form class="mb-5 max-w-sm" @submit.prevent="load">
            <input v-model="search" class="field" placeholder="Search workers...">
        </form>
        <div class="card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-muted">
                    <tr>
                        <th class="px-5 py-3">Worker</th>
                        <th class="px-5 py-3">Service</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Verified</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="worker in workers" :key="worker.id" class="border-t border-line">
                        <td class="px-5 py-4">
                            <p class="font-medium">{{ worker.name }}</p>
                            <p class="text-xs text-muted">{{ worker.phone }}</p>
                        </td>
                        <td class="px-5 py-4">{{ worker.service?.name }}</td>
                        <td class="px-5 py-4">{{ worker.status }}</td>
                        <td class="px-5 py-4">{{ worker.verified ? 'Yes' : 'Pending' }}</td>
                        <td class="px-5 py-4">
                            <div v-if="!worker.verified" class="flex gap-2">
                                <button class="text-sm font-semibold text-brand" @click="approve(worker)">Approve</button>
                                <button class="text-sm font-semibold text-red-500" @click="reject(worker)">Reject</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
