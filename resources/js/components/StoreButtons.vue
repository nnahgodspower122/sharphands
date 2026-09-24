<script setup>
defineProps({
    light: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex flex-wrap gap-3">
        <router-link to="/register" :class="light ? 'bg-white text-ink hover:bg-slate-100' : 'bg-ink text-white hover:bg-black'" class="inline-flex items-center gap-3 rounded-2xl px-4 py-3 transition">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M16.7 12.8c0-2.4 2-3.6 2.1-3.7-1.1-1.7-2.9-1.9-3.5-1.9-1.5-.2-2.9.9-3.7.9s-1.9-.9-3.2-.8c-1.6.1-3.1 1-4 2.5-1.7 2.9-.4 7.3 1.2 9.6.8 1.2 1.8 2.5 3 2.4 1.2-.1 1.7-.8 3.1-.8s1.8.8 3.2.7c1.3-.1 2.1-1.2 2.9-2.3.9-1.3 1.3-2.6 1.3-2.6s-2.3-.9-2.4-3.5ZM14.3 6.3c.7-.8 1.1-1.9 1-3-.9 0-2.1.6-2.8 1.4-.6.7-1.2 1.9-1 3 1 .1 2.1-.5 2.8-1.4Z"/>
            </svg>
            <span class="text-left leading-tight">
                <span class="block text-[10px] uppercase tracking-wide opacity-70">Download on the</span>
                <span class="block text-sm font-semibold">App Store</span>
            </span>
        </router-link>
        <router-link to="/register" :class="light ? 'bg-white text-ink hover:bg-slate-100' : 'bg-ink text-white hover:bg-black'" class="inline-flex items-center gap-3 rounded-2xl px-4 py-3 transition">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3.6 2.3 13.8 12 3.6 21.7c-.4-.3-.6-.8-.6-1.4V3.7c0-.6.2-1.1.6-1.4Zm1.5-.9 11 6.3-2.5 2.4L5.1 1.4Zm12.4 7.1 2.8 1.6c.9.5.9 1.9 0 2.5l-3 1.7-2.6-2.5 2.8-3.3ZM5.1 22.6l8.6-8.2 2.5 2.4-11.1 6.4v-.6Z"/>
            </svg>
            <span class="text-left leading-tight">
                <span class="block text-[10px] uppercase tracking-wide opacity-70">Get it on</span>
                <span class="block text-sm font-semibold">Google Play</span>
            </span>
        </router-link>
    </div>
</template>
