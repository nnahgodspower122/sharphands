<script setup>
defineProps({
    light: { type: Boolean, default: false },
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <router-link to="/" class="inline-flex items-center gap-2 font-semibold tracking-tight" :class="light ? 'text-white' : 'text-ink'">
        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand text-white">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 13.5 6.2 11a2.1 2.1 0 0 0-3.2.4 2 2 0 0 0 .3 2.7l4.7 4.2a3 3 0 0 0 3.9.1L18 13m0 0 1.8-2.5a2.1 2.1 0 0 0-.4-3 2 2 0 0 0-2.7.2L14 10m4 3-4.2 1.2" />
            </svg>
        </span>
        <span v-if="!compact">SharpHand.ng</span>
    </router-link>
</template>
