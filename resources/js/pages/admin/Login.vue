<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../api';
import Flash from '../../components/Flash.vue';
import Logo from '../../components/Logo.vue';
import { useAuth } from '../../stores/auth';

const router = useRouter();
const auth = useAuth();
const show = ref(false);
const error = ref('');
const form = reactive({
    email: 'admin@sharphand.ng',
    password: '',
});

async function submit() {
    error.value = '';

    try {
        const { data } = await api.post('/login', form);

        if (data.user.role !== 'admin') {
            error.value = 'This portal is for administrators only.';
            return;
        }

        auth.setSession(data.user);
        router.push('/admin/dashboard');
    } catch (e) {
        error.value = e.response?.data?.message || 'Those credentials do not match our records.';
    }
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-emerald-50/70 to-white">
        <div class="mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-5 py-12">
            <Logo />
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight">Admin Portal</h1>
            <p class="mt-2 text-center text-sm text-muted">Sign in to manage the Nigerian skilled worker marketplace.</p>
            <div class="card mt-8 w-full p-8">
                <Flash :error="error" />
                <h2 class="text-lg font-semibold">Welcome Back</h2>
                <p class="mt-1 text-sm text-muted">Enter your credentials to access the administrative panel.</p>
                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <label class="block text-xs font-semibold tracking-wide text-slate-500">
                        EMAIL ADDRESS
                        <input v-model="form.email" type="email" class="field mt-2" placeholder="admin@sharphand.ng">
                    </label>
                    <label class="block text-xs font-semibold tracking-wide text-slate-500">
                        <span class="flex items-center justify-between">
                            PASSWORD
                            <span class="font-medium normal-case tracking-normal text-brand">Forgot?</span>
                        </span>
                        <div class="relative mt-2">
                            <input v-model="form.password" :type="show ? 'text' : 'password'" class="field pr-12" placeholder="••••••••">
                            <button type="button" class="absolute inset-y-0 right-4 text-slate-400" @click="show = !show">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </label>
                    <button class="btn-primary w-full py-3">Sign In</button>
                </form>
                <router-link to="/" class="mt-5 block text-center text-sm font-medium text-brand">← Back to SharpHand.ng Home</router-link>
            </div>
            <div class="mt-8 flex gap-6 text-xs text-muted">
                <span>● Secure SSL encryption</span>
                <span>● Internal access only</span>
            </div>
        </div>
    </div>
</template>
