<script lang="ts" setup>
import OrderLayout from '@/layouts/OrderLayout.vue';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import EventCard from '@/components/order/EventCard.vue';
import QrcodeVue from 'qrcode.vue';
import { Button } from '@/components/ui/button';
import ContactSection from '@/components/order/ContactSection.vue';

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
    qr_code: string;
    additional_information?: string;
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
    bank_account: string;
    contact_name:  string;
    contact_email: string;
    contact_phone: string;
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
    qr_code: string;
}

interface Location {
    id: number;
    name: string;
    address: string;
}

const props = defineProps<{
    event: Event;
    tickets: Ticket[];
    order: Order;
    reservations: Reservation[];
    location: Location;
}>();

// Access flash messages provided by Inertia shared props
const page = usePage();
const flashSuccess = computed(
    () => (page.props as any).flash?.success as string | undefined,
);

const totalPrice = computed(() => {
    return props.tickets.reduce(
        (sum, ticket) => sum + ticket.price * (ticket.amount ?? 1),
        0,
    );
});

// Carousel state for QR codes
const currentQrIndex = ref(0);

const nextQr = () => {
    if (currentQrIndex.value < props.reservations.length - 1) {
        currentQrIndex.value++;
    }
};

const prevQr = () => {
    if (currentQrIndex.value > 0) {
        currentQrIndex.value--;
    }
};

const goToQr = (index: number) => {
    currentQrIndex.value = index;
};

const currentQrUrl = computed(() => {
    return `${window.location.origin}/reservation/${props.reservations[currentQrIndex.value].qr_code}`;
});
</script>

<template>
    <OrderLayout :event="props.event" :location="props.location" title="Objednávka">
        <div class="flex flex-col gap-8">
            <EventCard :event="props.event" :location="props.location" />
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
                            Miesto {{ reservation.seat_number }}
                            <span v-if="reservation.additional_information != '' && reservation.additional_information != null">({{ reservation.additional_information }})</span> -
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
                            <span v-if="props.order.payment_note != null"><strong>Poznamka:</strong> {{ props.order.payment_note }} <br /></span>
                            <br />
                            <strong>Suma:</strong> {{ totalPrice }} €
                        </p>
                        <p class="mb-6">
                            Do poznámky platby môžete uviesť Vaše meno a priezvisko. Párovanie platieb sa deje na základe variabilného symbolu.
                        </p>
                        <Alert class="">
                            <AlertTitle class="mb-1 font-bold"
                                ><i class="fas fa-info-circle mr-2"></i>
                                Prosíme, aby ste platbu vykonali čo najskôr
                                </AlertTitle
                            >
                            <AlertDescription>
                                Platby sú overované manuálne spravidla raz týždenne. Dáme Vám vedieť
                                emailom, keď bude Vaša platba overená.
                                <b>Objednávka bude zrušená, ak platba nebude prijatá do 5 dní od vytvorenia objednávky.</b>
                            </AlertDescription>
                        </Alert>
                    </div>
                    <div v-if="props.order.status == 'paid'">
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Stav objednávky
                        </h2>
                        <p class="mb-6">Zaplatená</p>
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            QR kódy na vstup
                        </h2>

                        <!-- QR Code Carousel -->
                        <div class="relative">
                            <!-- Current QR Code -->
                            <div class="flex flex-col items-center justify-center mb-4">
                                <div class="bg-white p-4 rounded-lg mb-4">
                                    <QrcodeVue
                                        :value="currentQrUrl"
                                        :size="200"
                                    />
                                </div>
                                <p class="text-center dark:text-white font-semibold mb-2">
                                    Miesto {{ props.reservations[currentQrIndex].seat_number }} -
                                    {{ props.reservations[currentQrIndex].guest_name }}
                                </p>
                                <p class="text-center text-sm dark:text-gray-300">
                                    {{ currentQrIndex + 1 }} z {{ props.reservations.length }}
                                </p>
                            </div>

                            <!-- Navigation Arrows -->
                            <div class="flex justify-center items-center gap-4 mb-4" v-if="props.reservations.length > 1">
                                <Button
                                    @click="prevQr"
                                    :disabled="currentQrIndex === 0"
                                    variant="outline"
                                    class="cursor-pointer"
                                    size="icon"
                                >
                                    <i class="fas fa-chevron-left"></i>
                                </Button>

                                <Button
                                    @click="nextQr"
                                    :disabled="currentQrIndex === props.reservations.length - 1"
                                    variant="outline"
                                    class="cursor-pointer"
                                    size="icon"
                                >
                                    <i class="fas fa-chevron-right"></i>
                                </Button>
                            </div>

                            <!-- Dot Indicators -->
                            <div class="flex justify-center gap-2 mb-4" v-if="props.reservations.length > 1">
                                <button
                                    v-for="(reservation, index) in props.reservations"
                                    :key="'dot-' + reservation.id"
                                    @click="goToQr(index)"
                                    :class="[
                                        'w-2 h-2 rounded-full transition-all',
                                        index === currentQrIndex
                                            ? 'bg-black dark:bg-white w-8'
                                            : 'bg-gray-400 dark:bg-gray-600'
                                    ]"
                                    :aria-label="`Prejsť na QR kód pre ${reservation.guest_name}`"
                                ></button>
                            </div>
                        </div>

                        <p class="dark:text-white text-center">
                            Týmito QR kódmi sa preukážete pri vstupe na podujatie. Každý hosť potrebuje svoj vlastný QR kód.
                        </p>
                    </div>
                    <div v-if="props.order.status == 'cancelled'">
                        <h2 class="mb-2 text-2xl font-bold dark:text-white">
                            Stav objednávky
                        </h2>
                        <p class="mb-6">Objednávka bola zrušená</p>
                    </div>
                    <ContactSection :event="props.event" />
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
