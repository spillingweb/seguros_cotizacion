<script setup lang="ts">
import { ref, watch } from 'vue'
import { useInsurances } from '@/composables/useInsurances'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import type { Insurance } from '@/types'

const search = ref('')
const statusFilter = ref('')
const page = ref(1)

const debouncedSearch = ref('')
let debounceTimer: ReturnType<typeof setTimeout>

watch(search, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    debouncedSearch.value = val
    page.value = 1
  }, 300)
})

watch(statusFilter, () => { page.value = 1 })

const { data, isLoading, isError, refetch } = useInsurances({
  page,
  search: debouncedSearch,
  status: statusFilter,
})

function formatDate(dateStr: string): string {
  return new Date(`${dateStr}T00:00:00`).toLocaleDateString('es-EC', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' }).format(value)
}

const statusOptions = [
  { value: '', label: 'Todos' },
  { value: 'quoted', label: 'Cotizados' },
  { value: 'contracted', label: 'Contratados' },
]
</script>

<template>
  <div class="space-y-4">
    <!-- Filtros -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
      <div class="flex-1">
        <label for="search-input" class="block text-sm font-medium text-gray-700 mb-1">
          Buscar
        </label>
        <div class="relative">
          <input
            id="search-input"
            v-model="search"
            type="search"
            placeholder="Nombre, identificación, destino..."
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 pl-9 text-sm shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-label="Buscar contrataciones"
          />
          <svg
            class="absolute left-2.5 top-2.5 size-4 text-gray-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      <div>
        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
        <select
          id="status-filter"
          v-model="statusFilter"
          class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>
    </div>

    <!-- Estado de carga / error -->
    <div v-if="isLoading" class="py-12 text-center" aria-live="polite" aria-label="Cargando contrataciones">
      <svg class="mx-auto size-8 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      <p class="mt-2 text-sm text-gray-500">Cargando...</p>
    </div>

    <div v-else-if="isError" role="alert" class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700 flex items-center gap-3">
      <span>No se pudieron cargar las contrataciones.</span>
      <AppButton variant="ghost" size="sm" @click="() => refetch()">Reintentar</AppButton>
    </div>

    <template v-else-if="data">
      <!-- Tabla desktop -->
      <div class="hidden overflow-hidden rounded-xl border border-gray-200 sm:block">
        <table class="min-w-full divide-y divide-gray-200" aria-label="Lista de contrataciones de seguro">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Identificación</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Destino</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Salida</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Regreso</th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Valor</th>
              <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
              <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Creado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr
              v-for="item in data.data"
              :key="item.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ item.full_name }}</div>
                <div class="text-xs text-gray-500">{{ item.email }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ item.identification_number }}</td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-900">{{ item.destination_country }}</div>
                <div class="text-xs text-gray-500">{{ item.destination_region }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(item.departure_date) }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(item.return_date) }}</td>
              <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                {{ formatCurrency(item.total_amount) }}
              </td>
              <td class="px-4 py-3 text-center">
                <StatusBadge :status="item.status" />
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">
                {{ formatDate(item.created_at.split('T')[0]) }}
              </td>
            </tr>
            <tr v-if="data.data.length === 0">
              <td colspan="8" class="py-12 text-center text-sm text-gray-400">
                No se encontraron contrataciones.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tarjetas mobile -->
      <div class="space-y-3 sm:hidden" aria-label="Lista de contrataciones de seguro">
        <div
          v-for="item in (data.data as Insurance[])"
          :key="item.id"
          class="rounded-xl border border-gray-200 bg-white p-4 space-y-2"
        >
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ item.full_name }}</p>
              <p class="text-xs text-gray-500">{{ item.identification_number }}</p>
            </div>
            <StatusBadge :status="item.status" />
          </div>
          <div class="text-sm text-gray-700">
            {{ item.destination_country }} · {{ item.days_count }} días
          </div>
          <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500">{{ formatDate(item.departure_date) }} — {{ formatDate(item.return_date) }}</span>
            <span class="text-sm font-bold text-blue-700">{{ formatCurrency(item.total_amount) }}</span>
          </div>
        </div>
        <p v-if="data.data.length === 0" class="py-8 text-center text-sm text-gray-400">
          No se encontraron contrataciones.
        </p>
      </div>

      <!-- Paginación -->
      <div
        v-if="data.meta.last_page > 1"
        class="flex items-center justify-between"
        role="navigation"
        aria-label="Paginación"
      >
        <p class="text-sm text-gray-600">
          Mostrando {{ data.meta.from }}–{{ data.meta.to }} de {{ data.meta.total }} registros
        </p>
        <div class="flex gap-2">
          <AppButton
            variant="secondary"
            size="sm"
            :disabled="page === 1"
            :aria-label="`Página anterior (página ${page - 1})`"
            @click="page--"
          >
            ← Anterior
          </AppButton>
          <span class="inline-flex items-center px-3 text-sm text-gray-600" aria-current="page">
            {{ page }} / {{ data.meta.last_page }}
          </span>
          <AppButton
            variant="secondary"
            size="sm"
            :disabled="page >= data.meta.last_page"
            :aria-label="`Página siguiente (página ${page + 1})`"
            @click="page++"
          >
            Siguiente →
          </AppButton>
        </div>
      </div>
    </template>
  </div>
</template>
