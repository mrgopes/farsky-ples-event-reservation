<script setup lang="ts">
interface Props {
}

defineProps<Props>();
</script>

<template>
    <main class="max-w flex justify-center opacity-100 transition-opacity duration-750 starting:opacity-0">
        <div class="flex flex-col justify-center text-center dark:text-white z-10 gap-2 my-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <div>
                Rezervačný systém pre <b>Farský ples Čierna Voda</b> vyvinul
                <a href="https://hostnow.cz/" target="_blank" class="underline">Michal Barnáš</a>
                &copy; {{ new Date().getFullYear() }}
            </div>
            <div>
                Našiel si chybu? Nahlás ju na <a href="https://github.com/mrgopes/farsky-ples-event-reservation" target="_blank" class="underline">GitHube</a> alebo vytvor PR :)
            </div>
        </div>
    </main>
</template>
