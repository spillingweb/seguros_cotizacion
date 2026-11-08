<script setup lang="ts">
import { RouterLink, useRoute } from "vue-router";
import NavLink from "./NavLink.vue";
import AppButton from "./ui/AppButton.vue";

const route = useRoute();
</script>

<template>
    <footer class="bg-primary text-primary-foreground py-4">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 flex flex-col sm:flex-row gap-2 justify-between items-center">

            <!-- Navegación -->
            <ul class="flex sm:order-last">
                <li>
                    <AppButton asChild variant="link" class="text-primary-foreground">
                        <RouterLink to="/">Cotizar</RouterLink>
                    </AppButton>
                </li>
                <li>
                    <AppButton asChild variant="link" class="text-primary-foreground">
                        <RouterLink to="/seguros">Contrataciones</RouterLink>
                    </AppButton>
                </li>
            </ul>

            <p class="text-center text-xs text-balance">
                &copy; 2024 Gestión Segura. Todos los derechos reservados.
            </p>
        </div>
    </footer>
</template>