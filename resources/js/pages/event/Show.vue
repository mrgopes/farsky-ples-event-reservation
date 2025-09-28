<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import EventLayout from '@/layouts/EventLayout.vue';
import { create } from "@/routes/order";
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.min.css';

interface Ticket {
  id: number;
  title: string;
  price: number;
}

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
}

const props = defineProps<{ event: Event; tickets: Ticket[] }>();

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

const formattedLocation = computed(() => {
  if (!props.event.location) return '';
  return props.event.location.replace(/\r?\\n|\r/g, ', ');
});

</script>

<template>
    <EventLayout>
        <div class="flex flex-col gap-8">
            <div>
                <h1 class="dark:text-white text-6xl font-extrabold">{{ props.event.title }}</h1>
                <p class="dark:text-gray-200 font-bold mt-3">
                    <span><i class="fas fa-calendar-alt mr-2"></i>{{ formattedStartTime }}</span>
                    <span class="ml-4"><i class="fas fa-map-marker-alt mr-2"></i>{{ formattedLocation }}</span>
                </p>
            </div>

            <div>
                <h2 class="dark:text-white text-3xl font-bold">O akcii</h2>
                <p class="dark:text-gray-200">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec et eros sit amet leo fermentum porta sit amet eu magna. Suspendisse potenti. Sed eleifend ac nibh ac auctor. Cras pellentesque felis metus, et ullamcorper magna dapibus quis. Pellentesque id consequat diam. Phasellus lacinia ullamcorper nisi at porta. Nam in velit ut tellus faucibus malesuada quis ac libero. </p>
            </div>

            <div>
                <h2 class="dark:text-white text-3xl font-bold">Dostupné lístky</h2>
                <div class="flex flex-col gap-3 mt-3">
                    <Card v-for="ticket in props.tickets" v-bind:key="'ticket-' + ticket.id">
                        <CardContent>
                            <div class="flex justify-between">
                                <div>
                                    <i class="fas fa-ticket mr-2"></i>
                                    {{ ticket.title }}
                                </div>
                                <div>
                                    {{ ticket.price }} €
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <div v-if="props.tickets.length == 0">
                        <Card class="border-none">
                            <CardContent>
                                <div>
                                    <i class="fas fa-ticket mr-2"></i>
                                    Momentálne nie sú dostupné žiadne lístky.
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex w-full justify-center mt-8">
            <Button
                class="mt-4 cursor-pointer"
                :tabindex="4"
                :disabled="false"
                :href="create.url(props.event.url_slug)"
                :as="Link"
            >
                <span v-if="!false">
                    Kúpiť lístok na toto podujatie
                </span>
                <span v-else>
                    Vypredané
                </span>
            </Button>
        </div>
    </EventLayout>
</template>
