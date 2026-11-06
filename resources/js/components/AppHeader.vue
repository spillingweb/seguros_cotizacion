<script setup lang="ts">
import { RouterLink, useRoute } from "vue-router";
import NavLink from "./NavLink.vue";

const route = useRoute();
</script>

<template>
    <header class="bg-white text-gray-900 shadow-lg" role="banner">
        <nav
            class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6"
            aria-label="Navegación principal"
        >
            <RouterLink
                to="/"
                class="flex items-center gap-2 text-xl font-bold tracking-tight hover:opacity-90 focus-visible:outline-2 focus-visible:outline-white focus-visible:outline-offset-2 rounded"
                aria-label="Ir a inicio — Seguro de Viaje"
            >
                <img
                    src="../../images/gestion-segura-logo.png"
                    alt="Seguro de Viaje"
                    class="h-10 w-auto"
                />
            </RouterLink>

            <ul class="flex gap-4" role="list">
                <NavLink
                    to="/"
                    label="Cotizar"
                    activeRouteName="quote"
                />
                <NavLink
                    to="/seguros"
                    label="Contrataciones"
                    activeRouteName="insurances"
                />
            </ul>
        </nav>
    </header>
</template>
