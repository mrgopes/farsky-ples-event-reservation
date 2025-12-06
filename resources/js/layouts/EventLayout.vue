<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Footer from '@/components/Footer.vue';

defineProps<{
    background?: string;
}>();
</script>

<template>
    <Head title="Rezervácia">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="relative flex min-h-screen flex-col items-center bg-transparent p-6 text-[#1b1b18] lg:justify-center lg:p-8"
    >
        <img :src="$props.background ?? '/images/default-background.jpg'" alt="Background" class="absolute inset-0 w-full h-full object-cover opacity-60 z-0 pointer-events-none" />
        <div class="absolute inset-0 z-0 pointer-events-none bg-gradient-to-t dark:from-black/100 dark:via-black/80 from-white/100 via-white/80 to-transparent"></div>
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center gap-4">
<!--                <h1 class="text-white drop-shadow-lg">Rezervačný systém - Farnosť Čierna Voda</h1>-->
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="w-full max-w-[335px] flex-col-reverse rounded-2xl lg:max-w-4xl lg:flex-row  z-10"
            >
                <slot/>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
        <Footer/>
    </div>
</template>
