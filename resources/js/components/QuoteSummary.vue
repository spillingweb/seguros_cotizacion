<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useContractInsurance } from '@/composables/useInsurances'
import { useInsuranceStore } from '@/stores/insurance'
import AppButton from '@/components/ui/AppButton.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import type { Insurance } from '@/types'

interface Props {
  insurance: Insurance
}

defineProps<Props>()
const emit = defineEmits<{ contracted: [insurance: Insurance]; newQuote: [] }>()

const router = useRouter()
const store = useInsuranceStore()
const contractMutation = useContractInsurance()
const contractError = ref<string | null>(null)
const showConfirm = ref(false)

async function confirmContract(id: number) {
  contractError.value = null
  try {
    const updated = await contractMutation.mutateAsync(id)
    store.setCurrentQuote(updated)
    showConfirm.value = false
    emit('contracted', updated)
  } catch {
    contractError.value = 'No se pudo contratar el seguro. Inténtalo de nuevo.'
  }
}

function handleNewQuote() {
  store.clearCurrentQuote()
  emit('newQuote')
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' }).format(value)
}

function formatDate(dateStr: string): string {
  return new Date(`${dateStr}T00:00:00`).toLocaleDateString('es-EC', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>

<template>
  <div
    class="rounded-2xl border border-blue-100 bg-linear-to-br from-blue-50 to-white p-6 shadow-sm space-y-6"
    role="region"
    aria-label="Resumen de cotización"
  >
    <!-- Encabezado -->
    <div class="flex items-start justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-gray-900">Cotización generada</h2>
        <p class="text-sm text-gray-500 mt-0.5">Referencia #{{ String(insurance.id).padStart(6, '0') }}</p>
      </div>
      <StatusBadge :status="insurance.status" />
    </div>

    <!-- Detalles en grid -->
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="rounded-lg bg-white border border-gray-100 p-4">
        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Asegurado</dt>
        <dd class="text-sm font-semibold text-gray-900">{{ insurance.full_name }}</dd>
        <dd class="text-xs text-gray-500">{{ insurance.identification_number }}</dd>
        <dd class="text-xs text-gray-500">{{ insurance.email }}</dd>
      </div>
      <div class="rounded-lg bg-white border border-gray-100 p-4">
        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Destino</dt>
        <dd class="text-sm font-semibold text-gray-900">{{ insurance.destination_country }}</dd>
        <dd class="text-xs text-gray-500">{{ insurance.destination_region }}</dd>
      </div>
      <div class="rounded-lg bg-white border border-gray-100 p-4">
        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Fechas del viaje</dt>
        <dd class="text-xs text-gray-700">Salida: <span class="font-medium">{{ formatDate(insurance.departure_date) }}</span></dd>
        <dd class="text-xs text-gray-700">Regreso: <span class="font-medium">{{ formatDate(insurance.return_date) }}</span></dd>
        <dd class="text-xs text-gray-700">Duración: <span class="font-medium">{{ insurance.days_count }} días</span></dd>
      </div>
      <div class="rounded-lg bg-blue-600 text-white p-4">
        <dt class="text-xs font-semibold uppercase tracking-wide text-blue-100 mb-1">Valor del seguro</dt>
        <dd class="text-2xl font-bold">{{ formatCurrency(insurance.total_amount) }}</dd>
        <dd class="text-xs text-blue-100 mt-1">
          Base {{ formatCurrency(insurance.base_rate) }} + Recargo {{ insurance.surcharge_percentage }}%
        </dd>
      </div>
    </dl>

    <!-- Error de contratación -->
    <div v-if="contractError" role="alert" class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
      {{ contractError }}
    </div>

    <!-- Confirmación de contratación -->
    <div
      v-if="showConfirm && insurance.status === 'quoted'"
      class="rounded-xl bg-amber-50 border border-amber-200 p-4 space-y-3"
      role="alertdialog"
      aria-labelledby="confirm-title"
    >
      <p id="confirm-title" class="text-sm font-semibold text-amber-800">
        ¿Confirmas la contratación del seguro?
      </p>
      <p class="text-xs text-amber-700">
        Total a pagar: <strong>{{ formatCurrency(insurance.total_amount) }}</strong>
      </p>
      <div class="flex gap-2">
        <AppButton
          size="sm"
          :loading="contractMutation.isPending.value"
          @click="confirmContract(insurance.id)"
        >
          Confirmar
        </AppButton>
        <AppButton variant="ghost" size="sm" @click="showConfirm = false">
          Cancelar
        </AppButton>
      </div>
    </div>

    <!-- Acciones -->
    <div class="flex flex-wrap gap-3" role="group" aria-label="Acciones de la cotización">
      <template v-if="insurance.status === 'quoted'">
        <AppButton
          v-if="!showConfirm"
          @click="showConfirm = true"
        >
          Contratar seguro
        </AppButton>
      </template>
      <template v-else>
        <div class="flex items-center gap-2 text-green-700">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
          </svg>
          <span class="text-sm font-semibold">¡Seguro contratado exitosamente!</span>
        </div>
      </template>

      <a
        :href="`/api/quotes/${insurance.id}/pdf`"
        target="_blank"
        rel="noopener"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
        :aria-label="`Descargar PDF de la cotización ${insurance.id}`"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
          <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" />
          <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" />
        </svg>
        Descargar PDF
      </a>

      <AppButton variant="secondary" @click="handleNewQuote">
        Nueva cotización
      </AppButton>

      <RouterLink
        to="/seguros"
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
      >
        Ver todas las contrataciones
      </RouterLink>
    </div>
  </div>
</template>
