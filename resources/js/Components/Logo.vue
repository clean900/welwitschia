<script setup>
defineProps({ size: { type: Number, default: 36 } });
</script>

<template>
    <!-- Placeholder do "W" Welwitschia (verde/rosa). Substituir por /img/logo.png quando disponível. -->
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="wg" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse">
                <stop stop-color="#16a34a" />
                <stop offset="1" stop-color="#0e7a3a" />
            </linearGradient>
            <linearGradient id="wp" x1="32" y1="8" x2="32" y2="56" gradientUnits="userSpaceOnUse">
                <stop stop-color="#f472b6" />
                <stop offset="1" stop-color="#db2777" />
            </linearGradient>
        </defs>
        <path d="M8 14 L20 50 L32 24 L44 50 L56 14" stroke="url(#wg)" stroke-width="7"
            stroke-linecap="round" stroke-linejoin="round" />
        <path d="M22 30 C28 38 36 38 42 30" stroke="url(#wp)" stroke-width="6" stroke-linecap="round" fill="none" />
    </svg>
</template>
