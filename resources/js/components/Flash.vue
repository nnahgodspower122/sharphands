<script setup>
defineProps({
    status: { type: String, default: '' },
    error: { type: String, default: '' },
});
</script>

<template>
    <div v-if="status" class="mb-6 rounded-2xl border border-emerald-100 bg-brand-soft px-4 py-3 text-sm text-emerald-800">
        {{ status }}
    </div>
    <div v-if="error" class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ error }}
    </div>
</template>
