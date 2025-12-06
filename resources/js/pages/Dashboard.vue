<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarIcon, MapPinIcon, TicketIcon, PlusIcon } from 'lucide-vue-next';

interface Event {
    id: number;
    title: string;
    url_slug: string;
    start_time: string;
    registration_start: string;
    registration_end: string;
    seats_total: number;
    user_role: 'owner' | 'manager' | 'staff';
    location: {
        name: string;
        address: string;
    };
    tickets: Array<{ id: number }>;
    reserved_seats: number;
}

defineProps<{
    events: Event[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('sk-SK', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'owner':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'manager':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'staff':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border p-6"
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Vaše podujatia</h2>
                    <Link
                        href="/event/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-all hover:bg-blue-700"
                    >
                        <PlusIcon class="w-4 h-4" />
                        Vytvoriť podujatie
                    </Link>
                </div>

                <div v-if="events.length === 0" class="text-center py-12 text-muted-foreground">
                    <TicketIcon class="w-16 h-16 mx-auto mb-4 opacity-50" />
                    <p class="text-lg">Nemáte žiadne podujatia</p>
                </div>

                <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="event in events"
                        :key="event.id"
                        :href="`/event/${event.url_slug}/manage`"
                        class="block p-6 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border hover:shadow-lg transition-shadow bg-card"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-semibold line-clamp-2 flex-1">
                                {{ event.title }}
                            </h3>
                            <span
                                :class="getRoleBadgeClass(event.user_role)"
                                class="ml-2 px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap"
                            >
                                {{ event.user_role }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm text-muted-foreground">
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ formatDate(event.start_time) }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <MapPinIcon class="w-4 h-4 flex-shrink-0" />
                                <span class="truncate">{{ event.location.address }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <TicketIcon class="w-4 h-4 flex-shrink-0" />
                                <span>{{ event.reserved_seats }} / {{ event.seats_total }} miest</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-sidebar-border/50">
                            <p class="text-xs text-muted-foreground">
                                Registrácia: {{ formatDate(event.registration_start) }} - {{ formatDate(event.registration_end) }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
