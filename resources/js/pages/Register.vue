<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import Flash from '../components/Flash.vue';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();
const form = reactive({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});
const error = ref('');

async function submit() {
    error.value = '';

    try {
        const { data } = await api.post('/register', form);
        auth.setSession(data.user);
        router.push('/get-started');
    } catch (e) {
        error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {})[0]?.[0] || 'Could not create your account.';
    }
}
</script>

<template>
    <section class="container-page flex justify-center py-16">
        <div class="card w-full max-w-md p-8">
            <Flash :error="error" />
            <h1 class="text-2xl font-bold">Create an account</h1>
            <p class="mt-2 text-sm text-muted">Optional — you can also find and connect with workers from Get Started without signing up.</p>
            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <label class="block text-sm font-medium">Full name
                    <input v-model="form.name" class="field mt-2" placeholder="Amina Balogun">
                </label>
                <label class="block text-sm font-medium">Email address
                    <input v-model="form.email" type="email" class="field mt-2" placeholder="you@email.com">
                </label>
                <label class="block text-sm font-medium">Phone
                    <input v-model="form.phone" class="field mt-2" placeholder="+234 800 000 0000">
                </label>
                <label class="block text-sm font-medium">Password
                    <input v-model="form.password" type="password" class="field mt-2">
                </label>
                <label class="block text-sm font-medium">Confirm password
                    <input v-model="form.password_confirmation" type="password" class="field mt-2">
                </label>
                <button class="btn-primary w-full">Create account</button>
            </form>
            <p class="mt-6 text-center text-sm text-muted">
                Already have an account? <router-link to="/login" class="font-semibold text-ink">Sign in</router-link><br>
                Are you a skilled worker? <router-link to="/worker/register" class="font-semibold text-ink">Register here</router-link>
            </p>
        </div>
    </section>
</template>
