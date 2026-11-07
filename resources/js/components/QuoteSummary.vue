<script setup lang="ts">
import { ref } from "vue";
import { useContractInsurance } from "@/composables/useInsurances";
import { useInsuranceStore } from "@/stores/insurance";
import AppButton from "@/components/ui/AppButton.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import type { Insurance } from "@/types";
import Card from "./ui/card/Card.vue";
import {
    Calendar,
    Check,
    CreditCardCheck,
    Download,
    ExternalLink,
    MapPin,
    Undo2,
    User2,
} from "@lucide/vue";
import Dialog from "./ui/dialog/Dialog.vue";
import DialogTrigger from "./ui/dialog/DialogTrigger.vue";
import DialogClose from "./ui/dialog/DialogClose.vue";
import DialogContent from "./ui/dialog/DialogContent.vue";
import DialogHeader from "./ui/dialog/DialogHeader.vue";
import DialogDescription from "./ui/dialog/DialogDescription.vue";
import DialogFooter from "./ui/dialog/DialogFooter.vue";
import DialogTitle from "./ui/dialog/DialogTitle.vue";
import ErrorMessage from "./ui/ErrorMessage.vue";
import { cn } from "cn";

interface Props {
    insurance: Insurance;
}

defineProps<Props>();
const emit = defineEmits<{
    contracted: [insurance: Insurance];
    newQuote: [];
}>();

const store = useInsuranceStore();
const contractMutation = useContractInsurance();
const contractError = ref<string | null>(null);
const showConfirm = ref(false);

async function confirmContract(id: number) {
    contractError.value = null;
    try {
        const updated = await contractMutation.mutateAsync(id);
        store.setCurrentQuote(updated);
        showConfirm.value = false;
        emit("contracted", updated);
    } catch {
        contractError.value =
            "No se pudo contratar el seguro. Inténtalo de nuevo.";
    }
}

function handleNewQuote() {
    store.clearCurrentQuote();
    emit("newQuote");
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat("es-EC", {
        style: "currency",
        currency: "USD",
    }).format(value);
}

function formatDate(dateStr: string): string {
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString("es-EC", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}
</script>

<template>
    <Card role="region" aria-label="Resumen de cotización">
        <!-- Encabezado -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold">Cotización generada</h2>
                <p class="text-sm text-muted-foreground mt-0.5">
                    Referencia #{{ String(insurance.id).padStart(6, "0") }}
                </p>
            </div>
            <StatusBadge :status="insurance.status" />
        </div>

        <!-- Detalles en grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <template v-if="insurance.status === 'contracted'">
                <div
                    class="col-span-full flex items-center gap-2 text-green-800"
                >
                    <span class="rounded-full bg-green-100 p-1"
                        ><Check class="size-4" aria-hidden="true"
                    /></span>
                    <span class="text-base font-semibold"
                        >¡Seguro contratado exitosamente!</span
                    >
                </div>
            </template>

            <div class="rounded-md bg-muted border p-4 flex gap-4 items-start">
                <User2 class="size-10 text-primary" />
                <div class="flex flex-col justify-center">
                    <div
                        class="text-xs font-semibold uppercase tracking-wide text-primary mb-1"
                    >
                        Asegurado
                    </div>
                    <div class="text-lg font-semibold">
                        {{ insurance.full_name }}
                    </div>
                    <div class="text-sm text-muted-foreground">
                        ID: {{ insurance.identification_number }}
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ insurance.email }}
                    </div>
                </div>
            </div>
            <div class="rounded-md bg-muted border p-4 flex gap-4 items-start">
                <MapPin class="size-10 text-primary" />
                <div class="flex flex-col justify-center">
                    <div
                        class="text-xs font-semibold uppercase tracking-wide text-primary mb-1"
                    >
                        Destino
                    </div>
                    <div class="text-lg font-semibold">
                        <span class="text-base">{{
                            insurance.destination_country_code
                        }}</span>
                        {{ insurance.destination_country }}
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ insurance.destination_region }}
                    </div>
                </div>
            </div>
            <div
                class="rounded-md bg-muted border p-4 text-muted-foreground flex gap-4 items-start"
            >
                <Calendar class="size-10 text-primary" />
                <div class="flex flex-col justify-center">
                    <div
                        class="text-xs font-semibold uppercase tracking-wide text-primary mb-1"
                    >
                        Fechas del viaje
                    </div>
                    <div class="text-sm">
                        Salida:
                        <span class="font-medium">{{
                            formatDate(insurance.departure_date)
                        }}</span>
                    </div>
                    <div class="text-sm">
                        Regreso:
                        <span class="font-medium">{{
                            formatDate(insurance.return_date)
                        }}</span>
                    </div>
                    <div class="text-sm">
                        Duración:
                        <span class="font-medium"
                            >{{ insurance.days_count }} días</span
                        >
                    </div>
                </div>
            </div>
            <div
                class="rounded-md bg-primary text-white p-4 flex gap-4 items-start"
            >
                <CreditCardCheck class="size-10 text-white" />
                <div class="flex flex-col justify-center">
                    <div
                        class="text-xs font-semibold uppercase tracking-wide text-blue-100 mb-1"
                    >
                        Valor del seguro
                    </div>
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(insurance.total_amount) }}
                    </div>
                    <div class="text-sm text-blue-100 mt-1">
                        Base {{ formatCurrency(insurance.base_rate) }} + Recargo
                        {{ insurance.surcharge_percentage }}%
                    </div>
                </div>
            </div>
        </div>

        <!-- Error de contratación -->
        <ErrorMessage v-if="contractError" :error="contractError">
            {{ contractError }}
        </ErrorMessage>

        <!-- Acciones -->
        <div
            class="flex flex-wrap gap-3 items-center"
            role="group"
            aria-label="Acciones de la cotización"
        >
            <div class="flex-1">
                <AppButton variant="ghost" @click="handleNewQuote">
                    <Undo2 class="size-4" aria-hidden="true" />
                    Nueva cotización
                </AppButton>
            </div>

            <AppButton asChild variant="secondary">
                <a
                    :href="`/api/quotes/${insurance.id}/pdf`"
                    target="_blank"
                    rel="noopener"
                    :aria-label="`Descargar PDF de la cotización ${insurance.id}`"
                    class="flex items-center gap-2"
                >
                    <Download class="size-4 hidden sm:inline" aria-hidden="true" />
                    Descargar PDF
                </a>
            </AppButton>

            <template v-if="insurance.status === 'quoted'">
                <Dialog v-model:show="showConfirm">
                    <DialogTrigger asChild>
                        <AppButton>
                            <Check class="size-4" aria-hidden="true" />
                            Contratar seguro
                        </AppButton>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                ¿Estás seguro de que deseas contratar este
                                seguro?</DialogTitle
                            >
                            <DialogDescription>
                                Si confirmas, se procederá con la contratación
                                del seguro.
                            </DialogDescription>
                        </DialogHeader>
                        <div>
                            Total a pagar:
                            <strong>{{
                                formatCurrency(insurance.total_amount)
                            }}</strong>
                        </div>
                        <DialogFooter>
                            <DialogClose asChild>
                                <AppButton variant="secondary">
                                    Cancelar
                                </AppButton>
                            </DialogClose>
                            <AppButton
                                :loading="contractMutation.isPending.value"
                                @click="confirmContract(insurance.id)"
                            >
                                Confirmar
                            </AppButton>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </template>
        </div>
    </Card>
</template>
