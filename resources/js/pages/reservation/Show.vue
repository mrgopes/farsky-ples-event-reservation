<script lang="ts" setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import EventCard from '@/components/order/EventCard.vue';
import QrcodeVue from 'qrcode.vue';
import { Button } from '@/components/ui/button';
import * as eventRoutes from '@/routes/event';
import { dashboard } from '@/routes';

interface Ticket {
    id: number;
    title: string;
    price: number;
    amount: number;
}

interface Reservation {
    id: number;
    seat_number: number;
    guest_name: string;
    qr_code: string;
}

interface Event {
    id: number;
    title: string;
    start_time: string;
    url_slug: string;
    seats_total: number;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    bank_account: string;
    location: string;
    multiple_reservations_per_ticket: boolean;
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

interface Location {
    id: number;
    name: string;
    address: string;
}

const props = defineProps<{
    reservation: Reservation;
    order: Order;
    event: Event;
    location: Location;
    tickets: Ticket[];
    reservations: Reservation[];
    userRole: string;
}>();

const totalPrice = computed(() => {
    return props.tickets.reduce(
        (sum, ticket) => sum + ticket.price * (ticket.amount ?? 1),
        0,
    );
});

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'paid':
            return 'Zaplatená';
        case 'pending':
            return 'Čaká sa na platbu';
        case 'cancelled':
            return 'Zrušená';
        default:
            return status;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Detail rezervácie" />

        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="eventRoutes.manage(props.event.url_slug).url"
                            class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                        >
                            Správa podujatia
                        </Link>
                        <Link
                            :href="dashboard().url"
                            class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                        >
                            Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8">
                <!-- Event Info -->
                <EventCard :event="props.event" :location="props.location" />

                <!-- Status Alert -->
                <Alert
                    v-if="props.order.status === 'paid'"
                    class="border-green-600 bg-green-200 dark:bg-green-900"
                >
                    <AlertTitle class="text-green-800 dark:text-green-200">
                        <i class="fas fa-check-circle mr-2"></i>
                        Platná rezervácia
                    </AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        Táto rezervácia je zaplatená a platná pre vstup.
                    </AlertDescription>
                </Alert>

                <Alert
                    v-else-if="props.order.status === 'pending'"
                    class="border-yellow-600 bg-yellow-200 dark:bg-yellow-900"
                >
                    <AlertTitle class="text-yellow-800 dark:text-yellow-200">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Čaká sa na platbu
                    </AlertTitle>
                    <AlertDescription class="text-yellow-700 dark:text-yellow-300">
                        Táto objednávka ešte nebola zaplatená.
                    </AlertDescription>
                </Alert>

                <Alert
                    v-else-if="props.order.status === 'cancelled'"
                    class="border-red-600 bg-red-200 dark:bg-red-900"
                >
                    <AlertTitle class="text-red-800 dark:text-red-200">
                        <i class="fas fa-times-circle mr-2"></i>
                        Zrušená objednávka
                    </AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300">
                        Táto objednávka bola zrušená a nie je platná pre vstup.
                    </AlertDescription>
                </Alert>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column: Scanned Reservation & Order Details -->
                    <div class="space-y-6">
                        <!-- Scanned Reservation Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold dark:text-white mb-4 flex items-center">
                                <i class="fas fa-qrcode mr-2 text-blue-600"></i>
                                Naskenovaná rezervácia
                            </h2>
                            <div class="space-y-3 dark:text-white">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-white p-4 rounded-lg">
                                        <QrcodeVue
                                            :value="props.reservation.qr_code"
                                            :size="150"
                                        />
                                    </div>
                                </div>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Miesto:</strong>
                                    <span class="text-xl font-bold ml-2">{{ props.reservation.seat_number }}</span>
                                </p>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Meno hosťa:</strong>
                                    <span class="ml-2">{{ props.reservation.guest_name }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold dark:text-white mb-4">
                                Informácie o objednávke
                            </h2>
                            <div class="space-y-3 dark:text-white">
                                <div>
                                    <strong class="text-gray-700 dark:text-gray-300">Stav:</strong>
                                    <span
                                        :class="[
                                            'ml-2 px-3 py-1 rounded-full text-sm font-semibold',
                                            getStatusBadgeClass(props.order.status)
                                        ]"
                                    >
                                        {{ getStatusText(props.order.status) }}
                                    </span>
                                </div>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Meno:</strong>
                                    <span class="ml-2">{{ props.order.name }}</span>
                                </p>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Email:</strong>
                                    <span class="ml-2">{{ props.order.email }}</span>
                                </p>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Telefón:</strong>
                                    <span class="ml-2">{{ props.order.phone }}</span>
                                </p>
                                <p>
                                    <strong class="text-gray-700 dark:text-gray-300">Variabilný symbol:</strong>
                                    <span class="ml-2">{{ props.order.variable_symbol }}</span>
                                </p>
                                <p v-if="props.order.payment_note">
                                    <strong class="text-gray-700 dark:text-gray-300">Poznámka:</strong>
                                    <span class="ml-2">{{ props.order.payment_note }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="space-y-6">
                        <!-- Tickets -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold dark:text-white mb-4">
                                Vybrané lístky
                            </h2>
                            <div class="space-y-2">
                                <div
                                    v-for="ticket in props.tickets"
                                    :key="'ticket-' + ticket.id"
                                    class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700 last:border-0"
                                >
                                    <span class="dark:text-white">
                                        {{ ticket.title }} × {{ ticket.amount }}
                                    </span>
                                    <span class="font-semibold dark:text-white">
                                        {{ ticket.price * ticket.amount }} €
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-3 text-lg font-bold border-t-2 border-gray-300 dark:border-gray-600 mt-3">
                                    <span class="dark:text-white">Celkom:</span>
                                    <span class="dark:text-white">{{ totalPrice }} €</span>
                                </div>
                            </div>
                        </div>

                        <!-- All Reservations in Order -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold dark:text-white mb-4">
                                Všetky miesta v objednávke
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Táto objednávka obsahuje celkom {{ props.reservations.length }}
                                {{ props.reservations.length === 1 ? 'miesto' : props.reservations.length < 5 ? 'miesta' : 'miest' }}
                            </p>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <div
                                    v-for="res in props.reservations"
                                    :key="'res-' + res.id"
                                    :class="[
                                        'p-3 rounded-lg border-2',
                                        res.id === props.reservation.id
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                                            : 'border-gray-200 dark:border-gray-700'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="dark:text-white">
                                            <span class="font-bold">Miesto {{ res.seat_number }}</span>
                                            <span class="mx-2">-</span>
                                            <span>{{ res.guest_name }}</span>
                                        </div>
                                        <div v-if="res.id === props.reservation.id">
                                            <span class="text-xs bg-blue-600 text-white px-2 py-1 rounded">
                                                Aktuálne
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styling if needed */
</style>
