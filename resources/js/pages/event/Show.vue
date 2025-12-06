<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import EventLayout from '@/layouts/EventLayout.vue';
import { create } from "@/routes/order";
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { marked } from 'marked';

interface Ticket {
  id: number;
  title: string;
  price: number;
  reservations: number;
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
  multiple_reservations_per_ticket: boolean;
  description?: string;
}

interface Location {
  id: number;
  name: string;
  address: string;
}

const props = defineProps<{ event: Event; tickets: Ticket[]; location: Location, places_left: number }>();

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
  if (!props.location.address) return '';
  return props.location.address.replace(/\r?\\n|\r/g, ', ');
});

const isWithinRegistrationWindow = computed(() => {
  const now = new Date();

  if (props.event.registration_start) {
    const startDate = new Date(props.event.registration_start);
    if (now < startDate) return false;
  }

  if (props.event.registration_end) {
    const endDate = new Date(props.event.registration_end);
    if (now > endDate) return false;
  }

  return true;
});

const registrationNotStarted = computed(() => {
  if (!props.event.registration_start) return false;
  const now = new Date();
  const startDate = new Date(props.event.registration_start);
  return now < startDate;
});

const registrationEnded = computed(() => {
  if (!props.event.registration_end) return false;
  const now = new Date();
  const endDate = new Date(props.event.registration_end);
  return now > endDate;
});

const isSoldOut = computed(() => {
  return props.places_left <= 0;
});

const canPurchase = computed(() => {
  return isWithinRegistrationWindow.value && !isSoldOut.value && props.tickets.length > 0;
});

const buttonText = computed(() => {
  if (registrationNotStarted.value) {
    return 'Predaj ešte nezačal';
  }
  if (registrationEnded.value) {
    return 'Predaj lístkov sa skončil';
  }
  if (isSoldOut.value) {
    return 'Vypredané';
  }
  if (props.tickets.length === 0) {
    return 'Žiadne dostupné lístky';
  }
  return 'Kúpiť lístok na toto podujatie';
});

const renderedDescription = computed(() => {
  if (!props.event.description) return '';
  return marked.parse(props.event.description, { async: false }) as string;
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
                <div
                    v-html="renderedDescription"
                    class="mt-3 dark:text-gray-200 markdown-content"
                ></div>
            </div>

            <div>
                <h2 class="dark:text-white text-3xl font-bold">Dostupné lístky</h2>
                <div class="flex flex-col gap-3 mt-3">
                    <Card v-for="ticket in props.tickets" v-bind:key="'ticket-' + ticket.id">
                        <CardContent>
                            <div class="flex justify-between">
                                <div>
                                    <i class="fas fa-ticket mr-2"></i>
                                    <div class="inline-flex gap-2">
                                        <p class="mb-0">
                                            {{ ticket.title }}
                                        </p>
                                        <p class="text-md text-gray-500 dark:text-gray-400 mb-0" v-if="event.multiple_reservations_per_ticket">
                                            {{ ticket.reservations }}
                                            {{ ticket.reservations == 1 ? 'miesto' : (
                                            ticket.reservations >= 5 ? 'miest' : 'miesta'
                                        ) }}
                                        </p>
                                    </div>
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
                :class="'mt-4 ' + (!canPurchase ? ' disabled opacity-50  cursor-default' : ' cusror-pointer')"
                :tabindex="4"
                :disabled="!canPurchase"
                :href="canPurchase ? create.url(props.event.url_slug) : ''"
                :as="Link"
            >
                {{ buttonText }}
            </Button>
        </div>
    </EventLayout>
</template>
