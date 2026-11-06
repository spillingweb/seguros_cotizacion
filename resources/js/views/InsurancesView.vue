<script setup lang="ts">
import InsuranceTable from "@/components/InsuranceTable.vue";
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div class="my-8">
            <h1 class="text-2xl font-bold text-gray-900">Contrataciones</h1>
            <p class="mt-1 text-sm text-gray-600">
                Historial de cotizaciones y seguros contratados.
            </p>
        </div>

        <InsuranceTable />
    </div>
</template>
