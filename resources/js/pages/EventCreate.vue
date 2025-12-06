<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Location {
    id: number;
    name: string;
    address: string;
    places_total: number;
}

defineProps<{
    locations: Location[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Vytvoriť podujatie',
        href: '/event/create',
    },
];

const form = useForm({
    title: '',
    url_slug: '',
    description: '',
    start_time: '',
    registration_start: '',
    registration_end: '',
    seats_total: '',
    location_id: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    bank_account: '',
    multiple_reservations_per_ticket: false,
});

const generateSlug = () => {
    if (form.title) {
        form.url_slug = form.title
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const submit = () => {
    form.post('/event', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Vytvoriť podujatie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <h1 class="text-3xl font-bold mb-6">Vytvoriť podujatie</h1>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">Základné informácie</h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="title">Názov podujatia *</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    @blur="generateSlug"
                                    required
                                    :class="{ 'border-red-500': form.errors.title }"
                                />
                                <p v-if="form.errors.title" class="text-sm text-red-500">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="url_slug">URL slug *</Label>
                                <Input
                                    id="url_slug"
                                    v-model="form.url_slug"
                                    required
                                    :class="{ 'border-red-500': form.errors.url_slug }"
                                />
                                <p v-if="form.errors.url_slug" class="text-sm text-red-500">
                                    {{ form.errors.url_slug }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Popis</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                show-markdown
                                :class="form.errors.description ? 'border-red-500' : ''"
                            />
                            <p v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">Detaily podujatia</h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_time">Začiatok podujatia *</Label>
                                <Input
                                    id="start_time"
                                    v-model="form.start_time"
                                    type="datetime-local"
                                    required
                                    :class="{ 'border-red-500': form.errors.start_time }"
                                />
                                <p v-if="form.errors.start_time" class="text-sm text-red-500">
                                    {{ form.errors.start_time }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="seats_total">Celkový počet miest</Label>
                                <Input
                                    id="seats_total"
                                    v-model="form.seats_total"
                                    type="number"
                                    min="1"
                                    :class="{ 'border-red-500': form.errors.seats_total }"
                                    placeholder="Automaticky z lokality"
                                />
                                <p v-if="form.errors.seats_total" class="text-sm text-red-500">
                                    {{ form.errors.seats_total }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="registration_start">Začiatok registrácie *</Label>
                                <Input
                                    id="registration_start"
                                    v-model="form.registration_start"
                                    type="datetime-local"
                                    required
                                    :class="{ 'border-red-500': form.errors.registration_start }"
                                />
                                <p v-if="form.errors.registration_start" class="text-sm text-red-500">
                                    {{ form.errors.registration_start }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="registration_end">Koniec registrácie *</Label>
                                <Input
                                    id="registration_end"
                                    v-model="form.registration_end"
                                    type="datetime-local"
                                    required
                                    :class="{ 'border-red-500': form.errors.registration_end }"
                                />
                                <p v-if="form.errors.registration_end" class="text-sm text-red-500">
                                    {{ form.errors.registration_end }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="location_id">Lokalita *</Label>
                            <select
                                id="location_id"
                                v-model="form.location_id"
                                required
                                :class="[
                                    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                                    { 'border-red-500': form.errors.location_id }
                                ]"
                            >
                                <option value="">Vyberte lokalitu</option>
                                <option
                                    v-for="location in locations"
                                    :key="location.id"
                                    :value="location.id"
                                >
                                    {{ location.name }} - {{ location.address }}
                                </option>
                            </select>
                            <p v-if="form.errors.location_id" class="text-sm text-red-500">
                                {{ form.errors.location_id }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                id="multiple_reservations"
                                v-model="form.multiple_reservations_per_ticket"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="multiple_reservations" class="cursor-pointer font-normal">
                                Povoliť viacero rezervácií na jeden lístok
                            </Label>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">Kontaktné informácie</h2>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="contact_name">Kontaktné meno *</Label>
                                <Input
                                    id="contact_name"
                                    v-model="form.contact_name"
                                    required
                                    :class="{ 'border-red-500': form.errors.contact_name }"
                                />
                                <p v-if="form.errors.contact_name" class="text-sm text-red-500">
                                    {{ form.errors.contact_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="contact_email">Kontaktný email *</Label>
                                <Input
                                    id="contact_email"
                                    v-model="form.contact_email"
                                    type="email"
                                    required
                                    :class="{ 'border-red-500': form.errors.contact_email }"
                                />
                                <p v-if="form.errors.contact_email" class="text-sm text-red-500">
                                    {{ form.errors.contact_email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="contact_phone">Kontaktný telefón</Label>
                                <Input
                                    id="contact_phone"
                                    v-model="form.contact_phone"
                                    type="tel"
                                    :class="{ 'border-red-500': form.errors.contact_phone }"
                                />
                                <p v-if="form.errors.contact_phone" class="text-sm text-red-500">
                                    {{ form.errors.contact_phone }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="bank_account">Bankový účet *</Label>
                                <Input
                                    id="bank_account"
                                    v-model="form.bank_account"
                                    required
                                    :class="{ 'border-red-500': form.errors.bank_account }"
                                />
                                <p v-if="form.errors.bank_account" class="text-sm text-red-500">
                                    {{ form.errors.bank_account }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-4 pt-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-blue-600 hover:bg-blue-700 text-white cursor-pointer"
                        >
                            {{ form.processing ? 'Vytvára sa...' : 'Vytvoriť podujatie' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/dashboard')"
                            :disabled="form.processing"
                        >
                            Zrušiť
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
