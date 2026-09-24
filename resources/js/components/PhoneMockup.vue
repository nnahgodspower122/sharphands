<script setup>
defineProps({
    src: { type: String, required: true },
    alt: { type: String, default: 'SharpHand mobile app' },
    tilt: { type: Boolean, default: false },
});
</script>

<template>
    <div class="relative w-[230px] sm:w-[260px]" :class="tilt ? '-rotate-3' : ''">
        <div class="rounded-[2.7rem] bg-zinc-950 p-[10px] shadow-[0_28px_70px_rgba(15,23,42,0.28)] ring-1 ring-black/40">
            <div class="relative overflow-hidden rounded-[2.15rem] bg-white">
                <div class="absolute left-1/2 top-2 z-10 h-5 w-24 -translate-x-1/2 rounded-full bg-zinc-950"></div>
                <img :src="src" :alt="alt" class="block w-full">
            </div>
        </div>
    </div>
</template>
