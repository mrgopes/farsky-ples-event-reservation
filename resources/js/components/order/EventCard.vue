<script setup lang="ts">

import { Card, CardContent } from '@/components/ui/card';
import { computed } from 'vue';

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
    multiple_reservations_per_ticket: boolean;
}

interface Location {
    id: number;
    name: string;
    address: string;
}

const props = defineProps<{
    event: Event;
    location: Location;
}>();

const formattedStartTime = computed(() => {
    if (!props.event.start_time) return '';
    const date = new Date(props.event.start_time);
    return date.toLocaleString('sk-SK', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

const formattedAddress = computed(() => {
    if (!props.location.address) return '';
    return props.location.address
        .split(/\r?\\n|\r/g)
        .map((part) => part.trim())
        .filter(Boolean)
        .join(', ');
});
</script>

<template>
    <h2 class="mt-6 text-3xl font-bold dark:text-white">
        Podujatie
    </h2>
    <Card class="mt-2 mb-8">
        <CardContent>
            <div class="lg:flex lg:justify-between">
                <div>
                    <i class="fas fa-calendar mr-2"></i>
                    <div class="inline-block">
                        <h3 class="font-bold">
                            {{ props.event.title }}
                        </h3>
                    </div>
                </div>
                <div>
                <span
                ><i class="fas fa-calendar-alt mr-2"></i
                >{{ formattedStartTime }}</span
                >
                <br class="lg:hidden">
                <span class="lg:ml-4"
                ><i class="fas fa-map-marker-alt mr-2"></i
                >{{ formattedAddress }}</span
                >
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>

</style>
