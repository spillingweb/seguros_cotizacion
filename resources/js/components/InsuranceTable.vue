<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useIntersectionObserver } from '@vueuse/core'
import { useInsurances, useInsurancesInfinite, useInsurancesStats } from '@/composables/useInsurances'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import type { Insurance } from '@/types'
import Table from './ui/table/Table.vue'
import TableHead from './ui/table/TableHead.vue'
import TableRow from './ui/table/TableRow.vue'
import TableHeader from './ui/table/TableHeader.vue'
import TableBody from './ui/table/TableBody.vue'
import TableCell from './ui/table/TableCell.vue'
import Spinner from './ui/spinner/Spinner.vue'
import Field from './ui/field/Field.vue'
import FieldLabel from './ui/field/FieldLabel.vue'
import { Search, Files, FileClock, FileCheck2, Wallet, X, ChevronUp, ChevronDown, ChevronsUpDown } from '@lucide/vue'
import InputGroup from './ui/input-group/InputGroup.vue'
import InputGroupInput from './ui/input-group/InputGroupInput.vue'
import InputGroupAddon from './ui/input-group/InputGroupAddon.vue'
import ErrorMessage from './ui/ErrorMessage.vue'
import Card from './ui/card/Card.vue'

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

const sortColumn = ref('')
const sortDirection = ref('')

// Desktop — regular paginated query
const { data, isLoading, isError, refetch } = useInsurances({
  page,
  search: debouncedSearch,
  status: statusFilter,
  sort: sortColumn,
  direction: sortDirection,
})

// Mobile — infinite query
const {
  data: infiniteData,
  isFetchingNextPage,
  hasNextPage,
  fetchNextPage,
  isLoading: isLoadingMobile,
  isError: isErrorMobile,
  refetch: refetchMobile,
} = useInsurancesInfinite({
  search: debouncedSearch,
  status: statusFilter,
  sort: sortColumn,
  direction: sortDirection,
})

const { data: statsData } = useInsurancesStats()

const allItems = computed<Insurance[]>(() =>
  infiniteData.value?.pages.flatMap((p) => p.data) ?? []
)
const totalQuoted = computed(() => statsData.value?.total_quoted ?? 0)
const totalContracted = computed(() => statsData.value?.total_contracted ?? 0)
const totalAmount = computed(() => statsData.value?.total_amount ?? 0)

// Sentinel element for infinite scroll trigger
const sentinel = ref<HTMLElement | null>(null)
useIntersectionObserver(sentinel, ([entry]) => {
  if (entry.isIntersecting && hasNextPage.value && !isFetchingNextPage.value) {
    fetchNextPage()
  }
})

function formatDate(dateStr: string): string {
  return new Date(`${dateStr}T00:00:00`).toLocaleDateString('es-EC', {
    day: '2-digit', month: '2-digit', year: 'numeric',
  })
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-EC', { style: 'currency', currency: 'USD' }).format(value)
}

function toggleSort(column: string) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : ''
    if (!sortDirection.value) sortColumn.value = ''
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  page.value = 1
}

function getSortIcon(column: string) {
  if (sortColumn.value !== column) return ChevronsUpDown
  return sortDirection.value === 'asc' ? ChevronUp : ChevronDown
}

const statusOptions = [
  { value: '', label: 'Todos' },
  { value: 'quoted', label: 'Cotizados' },
  { value: 'contracted', label: 'Contratados' },
]
</script>

<template>
  <!-- Estadísticas -->
  <div class="grid grid-cols-1 gap-4 mb-4 sm:grid-cols-3">

    <Card class=" flex-row gap-2 items-center">
      <span class="rounded-md bg-amber-500/15 p-2 text-amber-700">
        <FileClock class="size-6" />
      </span>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs tracking-wider text-amber-700">Cotizados</p>
          <p class="text-xl font-bold text-amber-950">{{ totalQuoted }}</p>
        </div>

      </div>
    </Card>

    <Card class=" flex-row gap-2 items-center">
      <span class="rounded-md bg-emerald-600/15 p-2 text-emerald-700">
        <FileCheck2 class="size-6" />
      </span>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs uppercase tracking-wide text-emerald-700">Contratados</p>
          <p class="text-xl font-bold text-emerald-950">{{ totalContracted }}</p>
        </div>

      </div>
    </Card>

    <Card class=" flex-row gap-2 items-center">
      <span class="rounded-md bg-fuchsia-600/15 p-2 text-fuchsia-700">
        <Wallet class="size-6" />
      </span>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs uppercase tracking-wide text-fuchsia-700">Monto total</p>
          <p class="text-xl font-bold text-fuchsia-950">{{ formatCurrency(totalAmount) }}</p>
        </div>

      </div>
    </Card>
  </div>

  <!-- Table Section -->
  <Card>
    <!-- Filtros -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
      <!-- Búsqueda -->
      <Field class="flex-1 gap-1">
        <FieldLabel for="search-input" class="">
          Buscar
        </FieldLabel>
        <InputGroup>
          <InputGroupInput id="search-input" v-model="search" placeholder="Nombre, identificación, destino......"
            class="placeholder:text-sm" />
          <InputGroupAddon>
            <Search />
          </InputGroupAddon>
          <InputGroupAddon :align="'inline-end'">
            <AppButton variant="ghost" size="icon-sm" @click="search = ''"><X /></AppButton>
          </InputGroupAddon>
        </InputGroup>
      </Field>

      <!-- Filtros de estado -->
      <div class="grid grid-cols-3 gap-2">
        <AppButton :variant="statusFilter === '' ? 'default' : 'outline'" :aria-pressed="statusFilter === ''"
          @click="statusFilter = ''">
          Todos
        </AppButton>
        <AppButton :variant="statusFilter === 'quoted' ? 'default' : 'outline'"
          :aria-pressed="statusFilter === 'quoted'" @click="statusFilter = 'quoted'">
          Cotizados
        </AppButton>
        <AppButton :variant="statusFilter === 'contracted' ? 'default' : 'outline'"
          :aria-pressed="statusFilter === 'contracted'" @click="statusFilter = 'contracted'">
          Contratados
        </AppButton>
      </div>
    </div>

    <!-- Tabla desktop -->
    <div class="hidden rounded-md overflow-hidden sm:block">
      <Table aria-label="Lista de contrataciones de seguro">
        <TableHeader class="bg-muted">
          <TableRow>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('first_name')">
                Cliente
                <component :is="getSortIcon('first_name')" class="size-3.5" :class="sortColumn === 'first_name' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('identification_number')">
                Identificación
                <component :is="getSortIcon('identification_number')" class="size-3.5" :class="sortColumn === 'identification_number' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('destination_country')">
                Destino
                <component :is="getSortIcon('destination_country')" class="size-3.5" :class="sortColumn === 'destination_country' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('departure_date')">
                Salida
                <component :is="getSortIcon('departure_date')" class="size-3.5" :class="sortColumn === 'departure_date' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('return_date')">
                Regreso
                <component :is="getSortIcon('return_date')" class="size-3.5" :class="sortColumn === 'return_date' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('total_amount')">
                Valor
                <component :is="getSortIcon('total_amount')" class="size-3.5" :class="sortColumn === 'total_amount' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('status')">
                Estado
                <component :is="getSortIcon('status')" class="size-3.5" :class="sortColumn === 'status' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <TableHead>
              <button class="flex items-center gap-1 hover:text-foreground transition-colors" @click="toggleSort('created_at')">
                Creado
                <component :is="getSortIcon('created_at')" class="size-3.5" :class="sortColumn === 'created_at' ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
          </TableRow>
        </TableHeader>

        <TableBody v-if="data">
          <TableRow v-for="item in data.data" :key="item.id">
            <!-- Cliente -->
            <TableCell>
              <div class="text-sm font-medium">{{ item.full_name }}</div>
              <div class="text-xs text-muted-foreground">{{ item.email }}</div>
            </TableCell>

            <!-- Identificación -->
            <TableCell>{{ item.identification_number }}</TableCell>

            <!-- Destino -->
            <TableCell>
              <div class="text-sm">{{ item.destination_country }}</div>
              <div class="text-xs text-muted-foreground">{{ item.destination_region }}</div>
            </TableCell>

            <!-- Salida -->
            <TableCell>{{ formatDate(item.departure_date) }}</TableCell>

            <!-- Regreso -->
            <TableCell>{{ formatDate(item.return_date) }}</TableCell>

            <!-- Valor -->
            <TableCell class="text-right font-semibold">
              {{ formatCurrency(item.total_amount) }}
            </TableCell>

            <!-- Estado -->
            <TableCell class="text-center">
              <StatusBadge :status="item.status" />
            </TableCell>

            <!-- Creado -->
            <TableCell>{{ formatDate(item.created_at.split('T')[0]) }}</TableCell>
          </TableRow>

          <!-- No se encontraron registros -->
          <TableRow v-if="data.data.length === 0">
            <TableCell colspan="8" class="py-12 text-center text-sm text-muted-foreground">
              No se encontraron contrataciones.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Paginación (desktop) -->
    <div v-if="data && data.meta.last_page > 1"
      class="hidden sm:flex flex-col gap-2 sm:flex-row items-center justify-between" role="navigation"
      aria-label="Paginación">
      <p class="text-sm text-gray-600">
        <span class="hidden sm:inline">Mostrando</span> {{ data.meta.from }}–{{ data.meta.to }} de {{ data.meta.total }}
        registros
      </p>
      <div class="flex gap-2">
        <AppButton variant="outline" size="sm" :disabled="page === 1"
          :aria-label="`Página anterior (página ${page - 1})`" @click="page--">
          ← Anterior
        </AppButton>
        <span class="inline-flex items-center px-3 text-sm text-gray-600" aria-current="page">
          {{ page }} / {{ data.meta.last_page }}
        </span>
        <AppButton variant="outline" size="sm" :disabled="page >= data.meta.last_page"
          :aria-label="`Página siguiente (página ${page + 1})`" @click="page++">
          Siguiente →
        </AppButton>
      </div>
    </div>


    <!-- Estado de carga / error (desktop) -->
    <div v-if="isLoading" class="py-12 hidden sm:flex flex-col gap-2 items-center" aria-live="polite"
      aria-label="Cargando contrataciones">
      <Spinner />
      <p class="mt-2 text-sm text-muted-foreground">Cargando...</p>
    </div>

    <!-- Estado de carga / error (mobile) -->
    <div v-if="isLoadingMobile" class="py-12 flex flex-col gap-2 items-center sm:hidden" aria-live="polite"
      aria-label="Cargando contrataciones">
      <Spinner />
      <p class="mt-2 text-sm text-muted-foreground">Cargando...</p>
    </div>

    <div v-if="isError" class="justify-self-center flex gap-2 items-center">
      <ErrorMessage error="No se pudieron cargar las contrataciones." />
      <AppButton variant="link" size="sm" @click="() => refetch()"> Reintentar</AppButton>
    </div>

    <div v-if="isErrorMobile" class="justify-self-center flex gap-2 items-center">
      <ErrorMessage error="No se pudieron cargar las contrataciones." />
      <AppButton variant="link" size="sm" @click="() => refetchMobile()">Reintentar</AppButton>
    </div>

    <!-- Tarjetas mobile — infinite scroll -->
    <div class="space-y-3 sm:hidden" aria-label="Lista de contrataciones de seguro" aria-live="polite">
      <Card v-for="item in allItems" :key="item.id">
        <div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ item.full_name }}</p>
            <p class="text-xs text-muted-foreground">{{ item.identification_number }}</p>
          </div>
          <StatusBadge :status="item.status" />
        </div>
        <div class="text-sm text-gray-700">
          {{ item.destination_country }} · {{ item.days_count }} días
        </div>
        <div class="flex items-center justify-between">
          <span class="text-xs text-muted-foreground">{{ formatDate(item.departure_date) }} — {{
            formatDate(item.return_date)
          }}</span>
          <span class="text-sm font-bold text-blue-700">{{ formatCurrency(item.total_amount) }}</span>
        </div>
      </Card>

      <p v-if="allItems.length === 0 && !isLoadingMobile" class="py-8 text-center text-sm text-gray-400">
        No se encontraron contrataciones.
      </p>

      <!-- Sentinel: triggers fetchNextPage when scrolled into view -->
      <div ref="sentinel" class="h-1" aria-hidden="true" />

      <div v-if="isFetchingNextPage" class="flex flex-col gap-2 items-center">
        <Spinner class="text-center self-center" />
      </div>

      <p v-else-if="!hasNextPage && allItems.length > 0" class="py-4 text-center text-xs text-gray-400">
        Has llegado al final de la lista.
      </p>
    </div>
  </Card>
</template>
