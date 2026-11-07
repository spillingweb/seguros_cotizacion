<script setup lang="ts">
import { computed } from "vue";
import { useQuoteForm } from "@/composables/useQuoteForm";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import Card from "@/components/ui/card/Card.vue";
import CountrySelect from "@/components/ui/CountrySelect.vue";
import FieldDescription from "@/components/ui/field/FieldDescription.vue";
import FieldLegend from "@/components/ui/field/FieldLegend.vue";
import FieldSet from "@/components/ui/field/FieldSet.vue";
import ErrorMessage from "@/components/ui/ErrorMessage.vue";
import Separator from "@/components/ui/separator/Separator.vue";
import {
    ArrowRight,
    Calendar,
    Check,
    FileText,
    Plane,
    ShieldCheck,
} from "@lucide/vue";

const emit = defineEmits<{ quoted: [] }>();
const {
    form,
    countryOptions,
    loadingCountries,
    countriesErrorMessage,
    selectedCountry,
    selectedCountryCode,
    destinationRegion,
    selectedSurchargePercentage,
    todayStr,
    minReturnDate,
    duration,
    protectionType,
    createQuote,
} = useQuoteForm(emit);

const submitLoading = computed(
    () => form.state.isValidating || createQuote.isPending.value,
);
</script>

<template>
    <form
        class="gap-6 grid md:grid-cols-[auto_20em]"
        aria-label="Formulario de cotización de seguro de viaje"
        novalidate
        @submit="
            (e) => {
                e.preventDefault();
                e.stopPropagation();
                form.handleSubmit();
            }
        "
    >
        <Card class="gap-8 @container">
            <!-- Datos del viajero -->
            <FieldSet>
                <div class="flex items-center gap-4">
                    <span
                        class="font-bold text-lg px-4.5 py-2 rounded-lg bg-primary text-primary-foreground shadow-md"
                        >1</span
                    >
                    <div>
                        <FieldLegend> Datos del Asegurado </FieldLegend>
                        <FieldDescription>
                            La persona que disfrutará del viaje.
                        </FieldDescription>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 @md:grid-cols-2">
                    <form.Field name="first_name">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Nombres"
                                type="text"
                                autocomplete="given-name"
                                required
                                autofocus
                            />
                        </template>
                    </form.Field>

                    <form.Field name="last_name">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Apellidos"
                                type="text"
                                autocomplete="family-name"
                                required
                            />
                        </template>
                    </form.Field>

                    <form.Field name="identification_number">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Número de identificación"
                                type="text"
                                autocomplete="off"
                                required
                            />
                        </template>
                    </form.Field>

                    <form.Field name="email">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Correo electrónico"
                                type="email"
                                autocomplete="email"
                                required
                            />
                        </template>
                    </form.Field>

                    <form.Field name="date_of_birth">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Fecha de nacimiento"
                                type="date"
                                required
                                :max="todayStr"
                            />
                        </template>
                    </form.Field>
                </div>
            </FieldSet>

            <Separator />

            <!-- Datos del viaje -->
            <FieldSet>
                <div class="flex items-center gap-4">
                    <span
                        class="font-bold text-lg px-4.5 py-2 rounded-lg bg-primary text-primary-foreground shadow-md"
                        >2</span
                    >
                    <div>
                        <FieldLegend> Datos del Viaje </FieldLegend>
                        <FieldDescription>
                            ¿Dónde y cuándo será tu próxima aventura?
                        </FieldDescription>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 @md:grid-cols-2">
                    <!-- País de destino -->
                    <form.Field name="destination_country_code">
                        <template v-slot="{ field }">
                            <CountrySelect
                                :field="field"
                                label="País de destino"
                                required
                                :options="countryOptions"
                                :loading="loadingCountries"
                            />
                        </template>
                    </form.Field>

                    <div class="flex flex-col text-right">
                        <p class="text-sm font-semibold text-gray-800">
                            Región y recargo adicional
                        </p>
                        <p
                            v-if="destinationRegion"
                            class="mt-1 text-sm text-gray-600"
                        >
                            {{ destinationRegion }} —
                            {{ selectedSurchargePercentage }}% de recargo
                        </p>
                        <p v-else class="mt-1 text-sm text-gray-500">
                            Selecciona un país para ver el recargo.
                        </p>
                    </div>

                    <!-- Fecha de salida -->
                    <form.Field name="departure_date">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Fecha de salida"
                                type="date"
                                required
                                :min="todayStr"
                            />
                        </template>
                    </form.Field>

                    <!-- Fecha de regreso -->
                    <form.Field name="return_date">
                        <template v-slot="{ field }">
                            <AppInput
                                :field="field"
                                label="Fecha de regreso"
                                type="date"
                                required
                                :min="minReturnDate"
                            />
                        </template>
                    </form.Field>
                </div>

                <ErrorMessage :error="countriesErrorMessage" />
            </FieldSet>
        </Card>

        <!-- Resumen del viaje -->
        <Card class="h-fit">
            <div
                class="uppercase text-muted-foreground tracking-widest font-semibold"
            >
                Resumen
            </div>
            <div class="flex gap-4 items-center">
                <span
                    class="p-4 rounded-lg bg-primary/20 text-primary shadow-md"
                    ><Plane class="size-6"
                /></span>
                <div class="flex flex-col">
                    <div class="text-[10px] text-muted-foreground mb-0.5">
                        Tu destino
                    </div>
                    <div class="font-semibold">
                        <span class="text-xs mr-1">{{
                            selectedCountryCode
                        }}</span>
                        {{ selectedCountry?.name }}
                    </div>
                    <div class="text-xs">{{ selectedCountry?.region }}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div
                    class="flex gap-2 items-center bg-chart-1/5 rounded-lg py-2 px-3 border"
                >
                    <Calendar class="size-6 text-muted-foreground" />
                    <div class="flex flex-col">
                        <div class="text-[10px] text-muted-foreground mb-0.5">
                            Duración
                        </div>
                        <div class="font-semibold text-xs">{{ duration }}</div>
                    </div>
                </div>
                <div
                    class="flex gap-2 items-center bg-chart-1/5 rounded-lg p-2 border"
                >
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

            <div class="rounded-lg py-2 px-3 flex gap-2">
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

            <div class="flex flex-col gap-2 p-3 rounded-lg border bg-muted">
                <div class="text-sm">Tu cobertura incluye</div>
                <ul class="text-xs text-muted-foreground space-y-1.5">
                    <li class="flex items-center gap-2">
                        <span class="rounded-full border p-1 bg-green-500/20">
                            <Check class="size-2 text-green-500" />
                        </span>
                        Asistencia médica 24/7
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="rounded-full border p-1 bg-green-500/20"
                            ><Check class="size-2 text-green-500"
                        /></span>
                        Pérdida de equipaje
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="rounded-full border p-1 bg-green-500/20"
                            ><Check class="size-2 text-green-500"
                        /></span>
                        Cancelación de viaje
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-1.5">
                <ErrorMessage
                    :error="
                        createQuote.isError.value
                            ? 'Ocurrió un error al generar la cotización. Por favor, verifica los datos e intenta de nuevo.'
                            : null
                    "
                />

                <AppButton
                    type="submit"
                    :loading="submitLoading"
                    class="group h-12"
                >
                    Obtener cotización
                    <ArrowRight
                        class="size-4 group-hover:translate-x-1 transition-transform"
                    />
                </AppButton>
                <div class="flex gap-1 text-muted-foreground self-center">
                    <ShieldCheck class="size-3" />
                    <p class="text-[10px]">
                        No se realizará ningún cobro al generar la cotización.
                    </p>
                </div>
            </div>
        </Card>
    </form>
</template>
