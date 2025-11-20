<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Footer from '@/components/Footer.vue';

interface Event {
    id: number;
    title: string;
    start_time: string;
    url_slug: string;
    seats_total: number;
    registration_start?: string;
    registration_end?: string;
    user_id?: number;
    location: string;
    address: string;
}

const props = defineProps<{
    title?: string,
    event: Event;
}>();

</script>

<template>
    <Head title="Rezervácia">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="relative bg-transparent p-6 text-[#1b1b18] lg:justify-center lg:p-8 min-h-screen flex flex-col">
        <header class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
            <nav class="flex items-center gap-4">
<!--                <h1 class="text-white drop-shadow-lg">Rezervačný systém - Farnosť Čierna Voda</h1>-->
            </nav>
        </header>
        <div class="flex w-full items-start justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0 flex-1">
            <main class="w-full max-w-[335px] flex-col-reverse rounded-2xl lg:max-w-4xl lg:flex-row z-10">
                <div>
                    <h1 class="dark:text-white text-6xl font-extrabold text-center">{{ props.title ?? "Objednať lístok" }}</h1>
                </div>
                <slot/>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
        <Footer class="flex-shrink-0" />
    </div>
</template>
