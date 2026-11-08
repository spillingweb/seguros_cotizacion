<script setup lang="ts">
import { ref, watch } from 'vue'
import { useInsurances } from '@/composables/useInsurances'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Table from '../ui/table/Table.vue'
import TableHead from '../ui/table/TableHead.vue'
import TableRow from '../ui/table/TableRow.vue'
import TableHeader from '../ui/table/TableHeader.vue'
import TableBody from '../ui/table/TableBody.vue'
import TableCell from '../ui/table/TableCell.vue'
import Spinner from '../ui/spinner/Spinner.vue'
import Field from '../ui/field/Field.vue'
import FieldLabel from '../ui/field/FieldLabel.vue'
import { Search, X, ChevronUp, ChevronDown, ChevronsUpDown } from '@lucide/vue'
import InputGroup from '../ui/input-group/InputGroup.vue'
import InputGroupInput from '../ui/input-group/InputGroupInput.vue'
import InputGroupAddon from '../ui/input-group/InputGroupAddon.vue'
import ErrorMessage from '../ui/ErrorMessage.vue'
import Card from '../ui/card/Card.vue'
import { formatCurrency, formatDate } from '@/lib/utils.ts'

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

const columns = [
  { id: 'first_name', label: 'Nombre' },
  { id: 'identification_number', label: 'No. de ID' },
  { id: 'destination_country', label: 'Destino' },
  { id: 'departure_date', label: 'Salida' },
  { id: 'return_date', label: 'Retorno' },
  { id: 'total_amount', label: 'Valor' },
  { id: 'status', label: 'Estado' },
  { id: 'created_at', label: 'Creado' }
]

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
</script>

<template>
  <Card class="hidden sm:block">
    <!-- Filtros -->
    <div class="flex gap-3 items-end mb-3">
      <!-- Búsqueda -->
      <Field class="flex-1 gap-1">
        <FieldLabel for="search-input-desktop" class="">
          Buscar
        </FieldLabel>
        <InputGroup>
          <InputGroupInput id="search-input-desktop" v-model="search" placeholder="Nombre, identificación, destino......"
            class="placeholder:text-sm" />
          <InputGroupAddon>
            <Search aria-label="Search" />
          </InputGroupAddon>
          <InputGroupAddon :align="'inline-end'">
            <AppButton variant="ghost" size="icon-sm" @click="search = ''" aria-label="Clear search">
              <X />
            </AppButton>
          </InputGroupAddon>
        </InputGroup>
      </Field>

      <!-- Filtros de estado -->
      <div class="grid grid-cols-3 gap-2">
        <AppButton :variant="statusFilter === '' ? 'default' : 'outline'" :aria-pressed="statusFilter === ''"
          @click="statusFilter = ''" aria-label="Filter all">
          Todos
        </AppButton>
        <AppButton :variant="statusFilter === 'quoted' ? 'default' : 'outline'"
          :aria-pressed="statusFilter === 'quoted'" @click="statusFilter = 'quoted'" aria-label="Filter quoted">
          Cotizados
        </AppButton>
        <AppButton :variant="statusFilter === 'contracted' ? 'default' : 'outline'"
          :aria-pressed="statusFilter === 'contracted'" @click="statusFilter = 'contracted'" aria-label="Filter contracted">
          Contratados
        </AppButton>
      </div>
    </div>

    <!-- Tabla desktop -->
    <div class="rounded-md overflow-hidden">
      <Table aria-label="Lista de contrataciones de seguro">
        <TableHeader class="bg-muted">
          <TableRow>
            <TableHead v-for="column in columns" :key="column.id">
              <button class="flex items-center gap-1 hover:text-foreground transition-colors"
                @click="toggleSort(column.id)">
                {{ column.label }}
                <component :is="getSortIcon(column.id)" class="size-3.5"
                  :class="sortColumn === column.id ? 'text-foreground' : 'text-muted-foreground/50'" />
              </button>
            </TableHead>
            <!--<TableHeadStyled v-for="column in columns" :key="column.id" :column="column.id" :label="column.label"
              v-model:sort-column="sortColumn" v-model:sort-direction="sortDirection" v-model:page="page" /> -->
          </TableRow>
        </TableHeader>

        <TableBody v-if="data">
          <TableRow v-for="item in data.data" :key="item.id">
            <!-- Cliente -->
            <TableCell>
              <div class="font-medium">{{ item.full_name }}</div>
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
            <TableCell colspan="8" class="py-12 text-center text-muted-foreground">
              No se encontraron contrataciones.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Paginación (desktop) -->
    <div v-if="data && data.meta.last_page > 1"
      class="flex gap-2 items-center justify-between" role="navigation"
      aria-label="Paginación">
      <p class="text-gray-600">
        Mostrando {{ data.meta.from }}–{{ data.meta.to }} de {{ data.meta.total }}
        registros
      </p>
      <div class="flex gap-2">
        <AppButton variant="outline" size="sm" :disabled="page === 1"
          :aria-label="`Página anterior (página ${page - 1})`" @click="page--">
          ← Anterior
        </AppButton>
        <span class="inline-flex items-center px-3 text-gray-600" aria-current="page">
          {{ page }} / {{ data.meta.last_page }}
        </span>
        <AppButton variant="outline" size="sm" :disabled="page >= data.meta.last_page"
          :aria-label="`Página siguiente (página ${page + 1})`" @click="page++">
          Siguiente →
        </AppButton>
      </div>
    </div>

    <!-- Estado de carga / error (desktop) -->
    <div v-if="isLoading" class="py-12 flex flex-col gap-2 items-center" aria-live="polite"
      aria-label="Cargando contrataciones">
      <Spinner />
      <p class="mt-2 text-muted-foreground">Cargando...</p>
    </div>

    <div v-if="isError" class="justify-self-center flex gap-2 items-center">
      <ErrorMessage error="No se pudieron cargar las contrataciones." />
      <AppButton variant="link" size="sm" @click="() => refetch()"> Reintentar</AppButton>
    </div>

  </Card>
</template>
