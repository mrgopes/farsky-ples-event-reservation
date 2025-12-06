<script lang="ts" setup>
import { ref, onMounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.min.css';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import * as reservation from '@/routes/reservation';
import { dashboard } from '@/routes';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    user: User;
}>();

const qrCodeInput = ref('');
const error = ref('');
const isScanning = ref(false);

const handleScan = () => {
    if (!qrCodeInput.value.trim()) {
        error.value = 'Prosím, zadajte alebo naskenujte QR kód';
        return;
    }

    error.value = '';
    isScanning.value = true;

    // Navigate to the reservation details page
    router.visit(reservation.show(qrCodeInput.value.trim()).url, {
        onError: () => {
            isScanning.value = false;
            error.value = 'QR kód nebol nájdený alebo nemáte oprávnenie na jeho zobrazenie';
        },
        onFinish: () => {
            isScanning.value = false;
        }
    });
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        handleScan();
    }
};

onMounted(() => {
    // Auto-focus on the input field
    const input = document.getElementById('qr-input');
    if (input) {
        input.focus();
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Head title="Skenovanie QR kódov" />

        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            Skenovanie rezervácií
                        </h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            {{ props.user.name }}
                        </span>
                        <Link
                            :href="dashboard().url"
                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto w-24 h-24 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-qrcode text-4xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Skenovanie QR kódu
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        Naskenujte alebo zadajte QR kód z rezervácie
                    </p>
                </div>

                <!-- Error Alert -->
                <Alert v-if="error" class="mb-6 border-red-600 bg-red-100 dark:bg-red-900">
                    <AlertTitle class="text-red-800 dark:text-red-200">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Chyba
                    </AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300">
                        {{ error }}
                    </AlertDescription>
                </Alert>

                <!-- QR Code Input -->
                <div class="space-y-4">
                    <div>
                        <label
                            for="qr-input"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                        >
                            QR kód
                        </label>
                        <Input
                            id="qr-input"
                            v-model="qrCodeInput"
                            type="text"
                            placeholder="Naskenujte alebo zadajte QR kód..."
                            @keypress="handleKeyPress"
                            :disabled="isScanning"
                            class="text-lg"
                        />
                    </div>

                    <Button
                        @click="handleScan"
                        :disabled="isScanning || !qrCodeInput.trim()"
                        class="w-full py-6 text-lg"
                    >
                        <i v-if="isScanning" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-search mr-2"></i>
                        {{ isScanning ? 'Vyhľadávam...' : 'Vyhľadať rezerváciu' }}
                    </Button>
                </div>

                <!-- Instructions -->
                <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Návod na použitie:
                    </h3>
                    <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1 list-disc list-inside">
                        <li>Kliknite do poľa a naskenujte QR kód pomocou čítačky</li>
                        <li>Alebo manuálne zadajte kód z rezervácie</li>
                        <li>Stlačte Enter alebo kliknite na tlačidlo Vyhľadať</li>
                        <li>Uvidíte detaily rezervácie a objednávky</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styling if needed */
</style>
