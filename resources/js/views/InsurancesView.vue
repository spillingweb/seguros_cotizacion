<script setup lang="ts">
import InsuranceCardsList from "@/components/insurances/InsuranceCardsList.vue";
import InsuranceStats from "@/components/insurances/InsuranceStats.vue";
import InsuranceTable from "@/components/insurances/InsuranceTable.vue";
import { FileText } from "@lucide/vue";
</script>

<template>
    <div class="mx-auto max-w-5xl p-4 sm:py-8 sm:px-6">
        <div class="my-8">
            <div class="inline-flex items-center gap-2 text-chart-4 text-xs uppercase tracking-wider">
                <FileText :size="14" />
                Gestión de solicitudes
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Contrataciones</h1>
            <p class="mt-1 text-sm text-gray-600">
                Historial de cotizaciones y seguros contratados.
            </p>
        </div>
        <InsuranceStats />
        <InsuranceTable />
        <InsuranceCardsList />
    </div>
</template>
