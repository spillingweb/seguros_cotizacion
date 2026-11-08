<script setup lang="ts">
import { useInsurancesStats } from '@/composables/useInsurances';
import { formatCurrency } from '@/lib/utils';
import { computed } from 'vue';
import { FileClock, FileCheck2, Wallet } from '@lucide/vue';
import Card from '../ui/card/Card.vue';

const { data: statsData } = useInsurancesStats()

const totalQuoted = computed(() => statsData.value?.total_quoted ?? 0)
const totalContracted = computed(() => statsData.value?.total_contracted ?? 0)
const totalAmount = computed(() => statsData.value?.total_amount ?? 0)
</script>

<template>
    <div class="hidden sm:grid grid-cols-1 gap-4 mb-4 sm:grid-cols-3">
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
</template>