<script setup lang="ts">
import { useInsurancesInfinite } from '@/composables/useInsurances';
import { ref, computed, watch } from 'vue';
import Field from '../ui/field/Field.vue'
import FieldLabel from '../ui/field/FieldLabel.vue'
import InputGroup from '../ui/input-group/InputGroup.vue'
import InputGroupInput from '../ui/input-group/InputGroupInput.vue'
import InputGroupAddon from '../ui/input-group/InputGroupAddon.vue'
import AppButton from '../ui/AppButton.vue'
import { Search, X } from '@lucide/vue'
import type { Insurance } from '@/types';
import Card from '../ui/card/Card.vue'
import StatusBadge from '../ui/StatusBadge.vue'
import Spinner from '../ui/spinner/Spinner.vue'
import { useIntersectionObserver } from '@vueuse/core'
import { formatCurrency, formatDate } from '@/lib/utils.ts';
import ErrorMessage from '../ui/ErrorMessage.vue';

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
})

const allItems = computed<Insurance[]>(() =>
    infiniteData.value?.pages.flatMap((p) => p.data) ?? []
)

// Sentinel element for infinite scroll trigger
const sentinel = ref<HTMLElement | null>(null)
useIntersectionObserver(sentinel, ([entry]) => {
    if (entry.isIntersecting && hasNextPage.value && !isFetchingNextPage.value) {
        fetchNextPage()
    }
})

</script>

<template>
    <!-- Filtros -->
    <div class="flex flex-col gap-3 sm:hidden mb-3">
        <!-- Búsqueda -->
        <Field class="flex-1 gap-1">
            <FieldLabel for="search-input-mobile" class="">
                Buscar
            </FieldLabel>
            <InputGroup class="bg-card">
                <InputGroupInput id="search-input-mobile" v-model="search" placeholder="Nombre, identificación, destino......"
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

    <!-- Tarjetas mobile — infinite scroll -->
    <div class="space-y-2 sm:hidden" aria-label="Lista de contrataciones de seguro" aria-live="polite">
        <Card v-for="item in allItems" :key="item.id" class="gap-0">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-base font-semibold">{{ item.full_name }}</p>
                    <p class="text-muted-foreground">ID: {{ item.identification_number }}</p>
                </div>
                <StatusBadge :status="item.status" />
            </div>
            <div class="text-muted-foreground">
                {{ item.destination_country }} · {{ item.days_count }} días
            </div>
            <div class="flex items-end justify-between">
                <span class="text-muted-foreground">{{ formatDate(item.departure_date) }} — {{
                    formatDate(item.return_date)
                    }}</span>
                <span class="text-xl font-bold text-primary">{{ formatCurrency(item.total_amount) }}</span>
            </div>
        </Card>

        <p v-if="allItems.length === 0 && !isLoadingMobile" class="py-8 text-center text-gray-400">
            No se encontraron contrataciones.
        </p>

        <!-- Sentinel: triggers fetchNextPage when scrolled into view -->
        <div ref="sentinel" class="h-1" aria-hidden="true" />

        <div v-if="isFetchingNextPage" class="flex flex-col gap-2 items-center">
            <Spinner class="text-center self-center" />
            <p class="text-muted-foreground">Cargando más contrataciones...</p>
        </div>

        <p v-else-if="!hasNextPage && allItems.length > 0" class="py-4 text-center text-xs text-gray-400">
            Has llegado al final de la lista.
        </p>
    </div>


    <!-- Estado de carga / error (mobile) -->
    <div v-if="isLoadingMobile" class="sm:hidden py-12 flex flex-col gap-2 items-center" aria-live="polite"
        aria-label="Cargando contrataciones">
        <Spinner />
        <p class="mt-2 text-muted-foreground">Cargando...</p>
    </div>

    <div v-if="isErrorMobile" class="sm:hidden justify-self-center flex gap-2 items-center">
        <ErrorMessage error="No se pudieron cargar las contrataciones." />
        <AppButton variant="link" size="sm" @click="() => refetchMobile()">Reintentar</AppButton>
    </div>
</template>