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

const emit = defineEmits<{ quoted: [] }>();

const store = useInsuranceStore();
const {
    data: countries,
    isLoading: loadingCountries,
    isError: countriesError,
} = useCountries();

const createQuote = useCreateQuote();

const form = useForm("post", "/api/quotes", {
    first_name: "",
    last_name: "",
    identification_number: "",
    email: "",
    date_of_birth: "",
    destination_country: "",
    destination_country_code: "",
    destination_region: "",
    departure_date: "",
    return_date: "",
} satisfies QuoteFormData);

const countryOptions = computed(() =>
    (countries.value ?? []).map((c: Country) => ({
        value: c.alpha2Code,
        label: c.translations.es,
        flag: c.flags.svg,
        region: c.region,
    })),
);

const selectedCountryCode = ref(form.destination_country_code);

watch(selectedCountryCode, (code) => {
    const country = countries.value?.find((c) => c.alpha2Code === code);
    if (!country) return;
    form.destination_country = country.translations.es;
    form.destination_country_code = code;
    form.destination_region = country.region;
    form.validate("destination_country");
});

const todayStr = computed(() => new Date().toISOString().split("T")[0]);

const minReturnDate = computed(() => form.departure_date || todayStr.value);

watch(
    () => form.departure_date,
    (newDate) => {
        if (form.return_date && form.return_date <= newDate) {
            form.return_date = "";
        }
    },
);

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
        class="space-y-10"
        novalidate
        aria-label="Formulario de cotización de seguro de viaje"
        @submit.prevent="submit"
    >
        <!-- Datos del asegurado -->
        <!-- <Card class="gap-8"> -->
        <FieldSet>
            <FieldLegend> Datos del Asegurado </FieldLegend>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <AppInput
                    v-model="form.first_name"
                    label="Nombres"
                    type="text"
                    autocomplete="given-name"
                    required
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
                    max="todayStr"
                    :error="form.errors.date_of_birth"
                    @change="form.validate('date_of_birth')"
                />
            </div>
        </FieldSet>
        <!-- </Card> -->

        <!-- Datos del viaje -->
        <!-- <Card> -->
        <FieldSet>
            <FieldLegend> Datos del Viaje </FieldLegend>
            <ErrorMessage :error="countriesError" />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <CountrySelect
                        v-model="selectedCountryCode"
                        label="País de destino"
                        :options="countryOptions"
                        :loading="loadingCountries"
                        required
                        :error="form.errors.destination_country"
                        @change="form.validate('destination_country')"
                    />
                </div>
                <AppInput
                    v-model="form.departure_date"
                    label="Fecha de salida"
                    type="date"
                    required
                    min="todayStr"
                    :error="form.errors.departure_date"
                    @change="form.validate('departure_date')"
                />
                <AppInput
                    v-model="form.return_date"
                    label="Fecha de regreso"
                    type="date"
                    required
                    min="minReturnDate"
                    :error="form.errors.return_date"
                    @change="form.validate('return_date')"
                />
            </div>
        </FieldSet>
        <!-- </Card> -->

        <ErrorMessage
            :error="
                createQuote.isError.value
                    ? 'Ocurrió un error al generar la cotización. Por favor, verifica los datos e intenta de nuevo.'
                    : null
            "
        />

        <div class="flex justify-end">
            <AppButton
                type="submit"
                size="lg"
                :loading="form.processing || createQuote.isPending.value"
            >
                Obtener cotización
            </AppButton>
        </div>
    </form>
</template>
