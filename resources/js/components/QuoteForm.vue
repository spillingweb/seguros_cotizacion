<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useForm } from "laravel-precognition-vue";
import { useCountries } from "@/composables/useCountries";
import { useCreateQuote } from "@/composables/useInsurances";
import { useInsuranceStore } from "@/stores/insurance";
import AppInput from "@/components/ui/AppInput.vue";
import AppButton from "@/components/ui/AppButton.vue";
import type { Country, QuoteFormData } from "@/types";
import FieldSet from "./ui/field/FieldSet.vue";
import FieldLegend from "./ui/field/FieldLegend.vue";
import CountrySelect from "./ui/CountrySelect.vue";
import Separator from "./ui/separator/Separator.vue";
import Card from "./ui/card/Card.vue";
import FieldDescription from "./ui/field/FieldDescription.vue";
import ErrorMessage from "./ui/ErrorMessage.vue";
import {
    ArrowRight,
    Calendar,
    Check,
    FileText,
    Plane,
    ShieldCheck,
} from "@lucide/vue";

const emit = defineEmits<{ quoted: [] }>();

const store = useInsuranceStore();
const {
    data: countries,
    isLoading: loadingCountries,
    isError: countriesError,
} = useCountries();

const createQuote = useCreateQuote();

const today = new Date();

const form = useForm("post", "/api/quotes", {
    first_name: "",
    last_name: "",
    identification_number: "",
    email: "",
    date_of_birth: "1993-01-01",
    destination_country: "España",
    destination_country_code: "ES",
    destination_region: "Europa",
    departure_date: today.toISOString().split("T")[0],
    return_date: new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split("T")[0],
} satisfies QuoteFormData);

const countryOptions = computed(() =>
    (countries.value ?? []).map((c: Country) => ({
        value: c.code,
        label: c.name,
        flag: c.flag ?? "",
        region: c.region,
    })),
);

const selectedCountryCode = ref(form.destination_country_code);
const selectedCountry = computed(() =>
    countries.value?.find((c) => c.code === selectedCountryCode.value),
);
const selectedSurchargePercentage = computed(
    () => selectedCountry.value?.surcharge_percentage ?? 0,
);
const countriesErrorMessage = computed(() =>
    countriesError.value
        ? "No se pudieron cargar los países. Inténtalo de nuevo en unos minutos."
        : null,
);

watch(selectedCountryCode, (code) => {
    if (!selectedCountry.value || !code) {
        form.destination_country = "";
        form.destination_country_code = "";
        form.destination_region = "";
        return;
    }

    form.destination_country = selectedCountry.value.name;
    form.destination_country_code = code;
    form.destination_region = selectedCountry.value.region;
    form.validate("destination_country");
});

const todayStr = computed(() => new Date().toISOString().split("T")[0]);
const minReturnDate = computed(() => form.departure_date || todayStr.value);

watch(
    () => form.departure_date,
    (newDate) => {
        if (form.return_date && form.return_date <= newDate) {
            form.return_date = newDate;
        }
    },
);

let protectionType = "Internacional";

watch(
    () => form.destination_country_code,
    (newCode) => {
        if (newCode === "EC") {
            protectionType = "Nacional";
        } else {
            protectionType = "Internacional";
        }
    },
);

const duration = computed(() => {
    if (!form.departure_date || !form.return_date) return "";
    const departure = new Date(form.departure_date);
    const returnDate = new Date(form.return_date);
    const diffTime = Math.abs(returnDate.getTime() - departure.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (diffDays < 1) return null;
    return `${diffDays} día${diffDays > 1 ? "s" : ""}`;
});

async function submit() {
    try {
        const insurance = await createQuote.mutateAsync({
            ...form.data(),
        } as QuoteFormData);
        store.setCurrentQuote(insurance);
        emit("quoted");
    } catch (err: unknown) {
        // Los errores de validación los gestiona Precognition en form.errors
        if (
            (err as { response?: { status: number } })?.response?.status !== 422
        ) {
            console.error("Error inesperado al cotizar:", err);
        }
    }
}
</script>

<template>
    <form
        class="gap-6 grid md:grid-cols-[auto_20em]"
        novalidate
        aria-label="Formulario de cotización de seguro de viaje"
        @submit.prevent="submit"
    >
        <!-- Datos del asegurado -->
        <Card class="gap-8 @container">
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
                    <AppInput
                        v-model="form.first_name"
                        label="Nombres"
                        type="text"
                        autocomplete="given-name"
                        required
                        autofocus
                        :error="form.errors.first_name"
                        @change="form.validate('first_name')"
                    />
                    <AppInput
                        v-model="form.last_name"
                        label="Apellidos"
                        type="text"
                        autocomplete="family-name"
                        required
                        :error="form.errors.last_name"
                        @change="form.validate('last_name')"
                    />
                    <AppInput
                        v-model="form.identification_number"
                        label="Número de identificación"
                        type="text"
                        autocomplete="off"
                        required
                        :error="form.errors.identification_number"
                        @change="form.validate('identification_number')"
                    />
                    <AppInput
                        v-model="form.email"
                        label="Correo electrónico"
                        type="email"
                        autocomplete="email"
                        required
                        :error="form.errors.email"
                        @change="form.validate('email')"
                    />
                    <AppInput
                        v-model="form.date_of_birth"
                        label="Fecha de nacimiento"
                        required
                        type="date"
                        :max="todayStr"
                        :error="form.errors.date_of_birth"
                        @change="form.validate('date_of_birth')"
                    />
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
                <ErrorMessage :error="countriesErrorMessage" />

                <div class="grid grid-cols-1 gap-5 @md:grid-cols-2">
                    <CountrySelect
                        v-model="selectedCountryCode"
                        label="País de destino"
                        :options="countryOptions"
                        :loading="loadingCountries"
                        required
                        :error="form.errors.destination_country"
                        @change="form.validate('destination_country')"
                    />
                    <div class="flex flex-col text-right">
                        <p class="text-sm font-semibold text-gray-800">
                            Región y recargo adicional
                        </p>
                        <p
                            v-if="form.destination_region"
                            class="mt-1 text-sm text-gray-600"
                        >
                            {{ form.destination_region }} —
                            {{ selectedSurchargePercentage }}% de recargo
                        </p>
                        <p v-else class="mt-1 text-sm text-gray-500">
                            Selecciona un país para ver el recargo.
                        </p>
                    </div>
                    <AppInput
                        v-model="form.departure_date"
                        label="Fecha de salida"
                        type="date"
                        required
                        :min="todayStr"
                        :error="form.errors.departure_date"
                        @change="form.validate('departure_date')"
                    />
                    <AppInput
                        v-model="form.return_date"
                        label="Fecha de regreso"
                        type="date"
                        required
                        :min="minReturnDate"
                        :error="form.errors.return_date"
                        @change="form.validate('return_date')"
                    />
                </div>
            </FieldSet>
        </Card>

        <Card>
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
                            form.destination_country_code
                        }}</span
                        >{{ form.destination_country }}
                    </div>
                    <div class="text-xs">{{ form.destination_region }}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div
                    class="flex gap-2 items-center bg-chart-1/15 rounded-lg py-2 px-3 border"
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
                    class="flex gap-2 items-center bg-chart-1/15 rounded-lg p-2 border"
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

            <div class="flex-1 rounded-lg py-2 px-3 flex gap-2">
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

            <div
                class="flex flex-col gap-2 p-3 rounded-lg border bg-muted"
            >
                <div class="text-sm">
                    Tu cobertura incluye
                </div>
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
                    size="lg"
                    :loading="form.processing || createQuote.isPending.value"
                    class="group"
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
