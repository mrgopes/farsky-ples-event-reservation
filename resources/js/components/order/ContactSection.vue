<script lang="ts" setup>
import '@fortawesome/fontawesome-free/css/all.min.css';

interface Event {
    contact_name: string;
    contact_email: string;
    contact_phone: string;
}

defineProps<{
    event: Event;
}>();
</script>

<template>
    <div class="mt-6">
        <h3 class="mb-2 text-lg font-semibold dark:text-white">
            Kontakt na organizátora
        </h3>
        <p class="dark:text-white">
            V prípade otázok kontaktujte:
        </p>
        <div class="mt-2 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <p class="dark:text-white">
                <strong>{{ event.contact_name }}</strong><br />
                <i class="fas fa-envelope mr-2"></i>{{ event.contact_email }}<br />
                <i class="fas fa-phone mr-2" v-if="event.contact_phone != null"></i>{{ event.contact_phone }}
            </p>
        </div>
    </div>
</template>

