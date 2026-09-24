<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import Flash from '../components/Flash.vue';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();
const form = reactive({ email: '', password: '' });
const error = ref('');

async function submit() {
    error.value = '';

    try {
        const { data } = await api.post('/login', form);
        auth.setSession(data.user);

        if (data.user.role === 'admin') {
            router.push('/admin/dashboard');
        } else if (data.user.role === 'worker') {
            router.push('/worker/dashboard');
        } else {
            router.push('/get-started');
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Those credentials do not match our records.';
    }
}
</script>

<template>
    <section class="container-page flex justify-center py-16">
        <div class="card w-full max-w-md p-8">
            <Flash :error="error" />
            <h1 class="text-2xl font-bold">Welcome back</h1>
            <p class="mt-2 text-sm text-muted">Sign in to find nearby workers or manage your bookings.</p>
            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <label class="block text-sm font-medium">Email address
                    <input v-model="form.email" type="email" class="field mt-2" placeholder="you@email.com">
                </label>
                <label class="block text-sm font-medium">Password
                    <input v-model="form.password" type="password" class="field mt-2" placeholder="••••••••">
                </label>
                <button class="btn-primary w-full">Sign in</button>
            </form>
            <p class="mt-6 text-center text-sm text-muted">
                New here? <router-link to="/register" class="font-semibold text-ink">Create an account</router-link>
                or <router-link to="/worker/register" class="font-semibold text-ink">join as a worker</router-link>.
            </p>
        </div>
    </section>
</template>
