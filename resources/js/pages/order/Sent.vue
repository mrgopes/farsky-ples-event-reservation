<script lang="ts" setup>
import OrderLayout from '@/layouts/OrderLayout.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

// import { create } from '@/routes/reservation';

interface Ticket {
    id: number;
    title: string;
    price: number;
    reservations: number;
    amount: number;
}

interface Reservation {
    id: number;
    seat_number: number;
    guest_name: string;
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
    address: string;
    bank_account: string;
    contact_name:  string;
    contact_email: string;
    contact_phone: string;
}

interface Order {
    id: number;
    name: string;
    email: string;
    phone: string;
    status: string;
    variable_symbol: string;
    payment_note: string;
}

const props = defineProps<{
    event: Event;
    tickets: Ticket[];
    order: Order;
    reservations: Reservation[];
}>();

// Access flash messages provided by Inertia shared props
const page = usePage();
const flashSuccess = computed(
    () => (page.props as any).flash?.success as string | undefined,
);

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
    if (!props.event.address) return '';
    return props.event.address
        .split(/\r?\\n|\r/g)
        .map((part) => part.trim())
        .filter(Boolean)
        .join(', ');
});

const totalPrice = computed(() => {
    return props.tickets.reduce(
        (sum, ticket) => sum + ticket.price * (ticket.amount ?? 1),
        0,
    );
});
</script>

<template>
    <OrderLayout :event="props.event" title="Objednávka">
        <div class="flex flex-col gap-8">
            <div>
                <h2 class="mt-6 text-3xl font-bold dark:text-white">
                    Podujatie
                </h2>
                <Card class="mt-2">
                    <CardContent>
                        <div class="flex justify-between">
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
                                <span class="ml-4"
                                    ><i class="fas fa-map-marker-alt mr-2"></i
                                    >{{ formattedLocation }}</span
                                >
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            <div>
                <Alert
                    v-if="flashSuccess"
                    class="border-green-600 bg-green-200 dark:bg-green-900"
                >
                    <AlertTitle>
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ flashSuccess }}
                    </AlertTitle>
                </Alert>
            </div>
            <div class="flex flex-col lg:flex-row">
                <div class="flex-1">
                    <h2 class="mb-4 text-2xl font-bold dark:text-white">
                        Vaše údaje
                    </h2>
                    <p class="dark:text-white">
                        <strong>Meno:</strong> {{ props.order.name }}<br />
                        <strong>Email:</strong> {{ props.order.email }}<br />
                        <strong>Telefón:</strong> {{ props.order.phone }}
                    </p>
                    <h2 class="mt-4 mb-4 text-2xl font-bold dark:text-white">
                        Vybrané lístky
                    </h2>
                    <div
                        v-for="ticket in props.tickets"
                        :key="'summary-ticket-' + ticket.id"
                        class="dark:text-white"
                    >
                        {{ ticket.title }} x {{ ticket.amount ?? 1 }} - {{
                            ticket.price * ticket.amount
                        }} €
                    </div>
                    <h2 class="mt-4 mb-4 text-2xl font-bold dark:text-white">
                        Vybrané miesta
                    </h2>
                    <ul class="list-disc pl-5 dark:text-white">
                        <li
                            v-for="reservation in props.reservations"
                            :key="'summary-seat-' + reservation.seat_number"
                        >
                            Miesto {{ reservation.seat_number }} -
                            {{ reservation.guest_name }}
                        </li>
                    </ul>
                </div>
                <div class="flex-1 dark:text-white">
                    <div v-if="props.order.status == 'pending'">
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Stav objednávky
                        </h2>
                        <p class="mb-6">Čaká sa na platbu</p>
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Detaily platby
                        </h2>
                        <p class="mb-6">
                            <strong>IBAN:</strong> {{ props.event.bank_account }} <br />
                            <strong>Variabilny symbol:</strong> {{ props.order.variable_symbol }} <br />
                            <strong>Poznamka:</strong> {{ props.order.payment_note }} <br />
                            <br />
                            <strong>Suma:</strong> {{ totalPrice }} €
                        </p>
                        <Alert class="">
                            <AlertTitle class="mb-1 font-bold"
                                ><i class="fas fa-info-circle mr-2"></i>
                                Potvrdenie platby môže trvať až 7
                                dní</AlertTitle
                            >
                            <AlertDescription>
                                Platby sú overované manuálne. Dáme Vám vedieť
                                emailom, keď bude Vaša platba overená. V prípade
                                otázok kontaktujte organizátora: <br/>
                                {{ props.event.contact_name }} -
                                {{ props.event.contact_email }}
                                {{ props.event.contact_phone }}
                            </AlertDescription>
                        </Alert>
                    </div>
                    <div v-if="props.order.status == 'paid'">
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Stav objednávky
                        </h2>
                        <p class="mb-6">Zaplatená</p>
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            QR kód na vstup
                        </h2>
                        ...
                    </div>
                    <div v-if="props.order.status == 'cancelled'">
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Stav objednávky
                        </h2>
                        <p class="mb-6">Objednávka bola zrušená</p>
                    </div>
                </div>
            </div>
        </div>
    </OrderLayout>
</template>

<style>
/* Hide number input arrows for Chrome, Safari, Edge */
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Hide number input arrows for Firefox */
input[type='number'] {
    appearance: textfield;
    -moz-appearance: textfield;
}
</style>
