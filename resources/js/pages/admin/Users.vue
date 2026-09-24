<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api';

const users = ref([]);
const search = ref('');

async function load() {
    const { data } = await api.get('/admin/users', { params: { q: search.value } });
    users.value = data.data.data || [];
}

onMounted(load);
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-3xl font-bold tracking-tight">Users</h1>
            <p class="mt-1 text-sm text-muted">Everyone who has registered to request a worker.</p>
        </div>
        <form class="mb-5 max-w-sm" @submit.prevent="load">
            <input v-model="search" class="field" placeholder="Search users...">
        </form>
        <div class="card overflow-hidden">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-muted">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-t border-line">
                        <td class="px-5 py-4 font-medium">{{ user.name }}</td>
                        <td class="px-5 py-4 text-muted">{{ user.email }}</td>
                        <td class="px-5 py-4">{{ user.phone }}</td>
                        <td class="px-5 py-4">{{ user.role }}</td>
                        <td class="px-5 py-4">{{ user.bookings_count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
