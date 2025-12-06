<script lang="ts" setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import EventCard from '@/components/order/EventCard.vue';
import QrcodeVue from 'qrcode.vue';
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
    additional_info: string;
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
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
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
    <div class="min-h-screen bg-background">
        <Head title="Detail rezervácie" />

        <!-- Navigation -->
        <nav class="bg-card border-b border-sidebar-border/70 dark:border-sidebar-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="eventRoutes.manage(props.event.url_slug).url"
                            class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Správa podujatia
                        </Link>
                        <Link
                            :href="dashboard().url"
                            class="text-sm text-muted-foreground hover:text-foreground transition-colors"
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
                    class="border-green-600 bg-green-100 dark:bg-green-900/30"
                >
                    <AlertTitle class="text-green-800 dark:text-green-300">
                        <i class="fas fa-check-circle mr-2"></i>
                        Platná rezervácia
                    </AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-400">
                        Táto rezervácia je zaplatená a platná pre vstup.
                    </AlertDescription>
                </Alert>

                <Alert
                    v-else-if="props.order.status === 'pending'"
                    class="border-yellow-600 bg-yellow-100 dark:bg-yellow-900/30"
                >
                    <AlertTitle class="text-yellow-800 dark:text-yellow-300">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Čaká sa na platbu
                    </AlertTitle>
                    <AlertDescription class="text-yellow-700 dark:text-yellow-400">
                        Táto objednávka ešte nebola zaplatená.
                    </AlertDescription>
                </Alert>

                <Alert
                    v-else-if="props.order.status === 'cancelled'"
                    class="border-red-600 bg-red-100 dark:bg-red-900/30"
                >
                    <AlertTitle class="text-red-800 dark:text-red-300">
                        <i class="fas fa-times-circle mr-2"></i>
                        Zrušená objednávka
                    </AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-400">
                        Táto objednávka bola zrušená a nie je platná pre vstup.
                    </AlertDescription>
                </Alert>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column: Scanned Reservation & Order Details -->
                    <div class="space-y-6">
                        <!-- Scanned Reservation Card -->
                        <div class="bg-card rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-6">
                            <h2 class="text-2xl font-bold text-foreground mb-4 flex items-center">
                                <i class="fas fa-qrcode mr-2 text-muted-foreground"></i>
                                Naskenovaná rezervácia
                            </h2>
                            <div class="space-y-3 text-foreground">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-white p-4 rounded-lg border border-sidebar-border/70">
                                        <QrcodeVue
                                            :value="props.reservation.qr_code"
                                            :size="150"
                                        />
                                    </div>
                                </div>
                                <p>
                                    <strong class="text-muted-foreground">Miesto:</strong>
                                    <span class="text-xl font-bold ml-2">{{ props.reservation.seat_number }}</span>
                                </p>
                                <p>
                                    <strong class="text-muted-foreground">Meno hosťa:</strong>
                                    <span class="ml-2">{{ props.reservation.guest_name }}</span>
                                </p>
                                <p v-if="reservation.additional_info != '' && reservation.additional_info != null">
                                    <strong class="text-muted-foreground">Info:</strong>
                                    <span class="ml-2">{{ props.reservation.additional_info }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="bg-card rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-6">
                            <h2 class="text-2xl font-bold text-foreground mb-4">
                                Informácie o objednávke
                            </h2>
                            <div class="space-y-3 text-foreground">
                                <div>
                                    <strong class="text-muted-foreground">Stav:</strong>
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
                                    <strong class="text-muted-foreground">Meno:</strong>
                                    <span class="ml-2">{{ props.order.name }}</span>
                                </p>
                                <p>
                                    <strong class="text-muted-foreground">Email:</strong>
                                    <span class="ml-2">{{ props.order.email }}</span>
                                </p>
                                <p>
                                    <strong class="text-muted-foreground">Telefón:</strong>
                                    <span class="ml-2">{{ props.order.phone }}</span>
                                </p>
                                <p>
                                    <strong class="text-muted-foreground">Variabilný symbol:</strong>
                                    <span class="ml-2">{{ props.order.variable_symbol }}</span>
                                </p>
                                <p v-if="props.order.payment_note">
                                    <strong class="text-muted-foreground">Poznámka:</strong>
                                    <span class="ml-2">{{ props.order.payment_note }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="space-y-6">
                        <!-- Tickets -->
                        <div class="bg-card rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-6">
                            <h2 class="text-2xl font-bold text-foreground mb-4">
                                Vybrané lístky
                            </h2>
                            <div class="space-y-2">
                                <div
                                    v-for="ticket in props.tickets"
                                    :key="'ticket-' + ticket.id"
                                    class="flex justify-between items-center py-2 border-b border-sidebar-border/50 last:border-0"
                                >
                                    <span class="text-foreground">
                                        {{ ticket.title }} × {{ ticket.amount }}
                                    </span>
                                    <span class="font-semibold text-foreground">
                                        {{ ticket.price * ticket.amount }} €
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-3 text-lg font-bold border-t-2 border-sidebar-border mt-3">
                                    <span class="text-foreground">Celkom:</span>
                                    <span class="text-foreground">{{ totalPrice }} €</span>
                                </div>
                            </div>
                        </div>

                        <!-- All Reservations in Order -->
                        <div class="bg-card rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-6">
                            <h2 class="text-2xl font-bold text-foreground mb-4">
                                Všetky miesta v objednávke
                            </h2>
                            <p class="text-sm text-muted-foreground mb-4">
                                Táto objednávka obsahuje celkom {{ props.reservations.length }}
                                {{ props.reservations.length === 1 ? 'miesto' : props.reservations.length < 5 ? 'miesta' : 'miest' }}
                            </p>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <div
                                    v-for="res in props.reservations"
                                    :key="'res-' + res.id"
                                    :class="[
                                        'p-3 rounded-lg border-2 transition-colors',
                                        res.id === props.reservation.id
                                            ? 'border-foreground bg-accent'
                                            : 'border-sidebar-border/50'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-foreground">
                                            <span class="font-bold">Miesto {{ res.seat_number }} <span v-if="res.additional_info != '' && res.additional_info != null">({{res.additional_info}})</span></span>
                                            <span class="mx-2">-</span>
                                            <span>{{ res.guest_name }}</span>
                                        </div>
                                        <div v-if="res.id === props.reservation.id">
                                            <span class="text-xs bg-foreground text-background px-2 py-1 rounded">
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
