<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from './layouts/AppLayout.vue';
import AdminLayout from './layouts/AdminLayout.vue';

const route = useRoute();
const layout = computed(() => {
    if (route.meta.admin) {
        return AdminLayout;
    }

    if (route.name === 'admin-login') {
        return 'div';
    }

    return AppLayout;
});
</script>

<template>
    <component :is="layout">
        <router-view />
    </component>
</template>
