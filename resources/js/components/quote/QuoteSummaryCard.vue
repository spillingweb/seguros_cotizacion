<script setup lang="ts">
import { Plane, Calendar, ShieldCheck, FileText, Check } from '@lucide/vue';

interface QuoteSummaryCardProps {
    countryCode: string
    country: string
    region: string
    duration: string | null
    protectionType: string
}

defineProps<QuoteSummaryCardProps>()
</script>

<template>
    <div class="uppercase text-muted-foreground tracking-widest font-semibold">
        Resumen
    </div>
    <div class="flex gap-4 items-center">
        <span class="p-4 rounded-md bg-primary/20 text-primary shadow-md">
            <Plane class="size-6" />
        </span>
        <div class="flex flex-col">
            <div class="text-[10px] text-muted-foreground mb-0.5">
                Tu destino
            </div>
            <div class="font-semibold">
                <span class="text-xs mr-1">{{
                    countryCode
                    }}</span>
                {{ country }}
            </div>
            <div class="text-xs">{{ region }}</div>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div class="flex gap-2 items-center bg-chart-1/5 rounded-md py-2 px-3 border">
            <Calendar class="size-6 text-muted-foreground" />
            <div class="flex flex-col">
                <div class="text-[10px] text-muted-foreground mb-0.5">
                    Duración
                </div>
                <div class="font-semibold text-xs">{{ duration || 'N/A' }}</div>
            </div>
        </div>
        <div class="flex gap-2 items-center bg-chart-1/5 rounded-md p-2 border">
            <ShieldCheck class="size-6 text-muted-foreground" />
            <div class="flex flex-col">
                <div class="text-[10px] text-muted-foreground mb-0.5">
                    Protección
                </div>
                <div class="font-semibold text-xs">
                    {{ protectionType }}
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-md py-2 px-3 flex gap-2">
        <FileText class="size-10 text-muted-foreground" />
        <div class="flex flex-col gap-0.5">
            <div class="font-semibold">
                Tu precio aún no está calculado
            </div>
            <div class="text-muted-foreground text-xs">
                Completa el formulario y genera la cotización para
                conocer el valor final.
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-2 p-3 rounded-md border bg-muted">
        <div class="text-sm">Tu cobertura incluye</div>
        <ul class="text-xs text-muted-foreground space-y-1.5">
            <li class="flex items-center gap-2">
                <span class="rounded-full border p-1 bg-green-500/20">
                    <Check class="size-2 text-green-500" />
                </span>
                Asistencia médica 24/7
            </li>
            <li class="flex items-center gap-2">
                <span class="rounded-full border p-1 bg-green-500/20">
                    <Check class="size-2 text-green-500" />
                </span>
                Pérdida de equipaje
            </li>
            <li class="flex items-center gap-2">
                <span class="rounded-full border p-1 bg-green-500/20">
                    <Check class="size-2 text-green-500" />
                </span>
                Cancelación de viaje
            </li>
        </ul>
    </div>
</template>