import { computed, watch } from "vue";
import { revalidateLogic, useForm } from "@tanstack/vue-form";
import { isAxiosError } from "axios";
import { useCountries } from "@/composables/useCountries";
import { useCreateQuote } from "@/composables/useInsurances";
import { useInsuranceStore } from "@/stores/insurance";
import { getTodayIso, toDateOnly } from "@/lib/utils";
import { quoteFormSchema } from "@/schemas/quoteFormSchema";
import type { Country, QuoteFormData } from "@/types";

export function useQuoteForm(emit: (event: "quoted") => void) {
    const store = useInsuranceStore();
    const createQuote = useCreateQuote();
    const {
        data: countries,
        isLoading: loadingCountries,
        isError: countriesError,
    } = useCountries();

    const todayStr = computed(() => getTodayIso());

    const defaultValues = {
        first_name: "",
        last_name: "",
        identification_number: "",
        email: "",
        date_of_birth: "1993-01-01",
        destination_country: "",
        destination_country_code: "ES",
        destination_region: "",
        departure_date: todayStr.value,
        return_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
            .toISOString()
            .slice(0, 10),
    } satisfies QuoteFormData;

    const quoteFormFieldNames = Object.keys(defaultValues) as Array<
        keyof QuoteFormData
    >;

    const isQuoteFormField = (field: string): field is keyof QuoteFormData =>
        quoteFormFieldNames.includes(field as keyof QuoteFormData);

    const setServerFieldError = (
        field: keyof QuoteFormData,
        message: string,
    ) => {
        form.setFieldMeta(field, (previous) => {
            const meta = previous ?? {
                isTouched: false,
                isBlurred: false,
                isDirty: false,
                isValidating: false,
                errorMap: {},
                errorSourceMap: {},
                _pendingValidationsCount: 0,
                _arrayVersion: 0,
            };

            return {
                ...meta,
                isTouched: true,
                errorMap: {
                    ...(meta.errorMap ?? {}),
                    onServer: message,
                },
                errorSourceMap: {
                    ...(meta.errorSourceMap ?? {}),
                    onServer: "form",
                },
            };
        });
    };

    const clearServerFieldErrors = () => {
        quoteFormFieldNames.forEach((field) => {
            form.setFieldMeta(field, (previous) => {
                const meta = previous ?? {
                    isTouched: false,
                    isBlurred: false,
                    isDirty: false,
                    isValidating: false,
                    errorMap: {},
                    errorSourceMap: {},
                    _pendingValidationsCount: 0,
                    _arrayVersion: 0,
                };

                return {
                    ...meta,
                    errorMap: {
                        ...(meta.errorMap ?? {}),
                        onServer: undefined,
                    },
                    errorSourceMap: {
                        ...(meta.errorSourceMap ?? {}),
                        onServer: undefined,
                    },
                };
            });
        });
    };

    const form = useForm({
        defaultValues,
        validators: {
            onDynamic: quoteFormSchema,
        },
        validationLogic: revalidateLogic({
            mode: "submit",
            modeAfterSubmission: "change"
        }),
        onSubmit: async ({ value }) => {
            const payload = {
                ...value,
                destination_country: destinationCountryName.value,
                destination_region: destinationRegion.value,
            };
            clearServerFieldErrors();

            try {
                const insurance = await createQuote.mutateAsync({ ...payload });
                store.setCurrentQuote(insurance);
                emit("quoted");
            } catch (err: unknown) {
                if (
                    isAxiosError<{
                        message?: string;
                        errors?: Record<string, string[]>;
                    }>(err) &&
                    err.response?.status === 422
                ) {
                    const backendErrors = err.response.data?.errors ?? {};

                    Object.entries(backendErrors).forEach(([field, messages]) => {
                        if (!isQuoteFormField(field)) return;

                        const message =
                            Array.isArray(messages) && messages.length > 0
                                ? messages[0]
                                : undefined;

                        if (!message) return;
                        setServerFieldError(field, message);
                    });
                    return;
                }

                console.error("Error inesperado al cotizar:", err);
            }
        },
    });

    const countryOptions = computed(() =>
        (countries.value ?? []).map((country: Country) => ({
            value: country.code,
            label: country.name,
            flag: country.flag ?? "",
            region: country.region,
        })),
    );

    const selectedCountryCode = form.useSelector(
        (state) => state.values.destination_country_code,
    );

    const selectedCountry = computed(() =>
        countries.value?.find((country) => country.code === selectedCountryCode.value),
    );

    const destinationCountryName = computed(
        () => selectedCountry.value?.name ?? "",
    );

    const destinationRegion = computed(
        () => selectedCountry.value?.region ?? "",
    );

    const selectedSurchargePercentage = computed(
        () => selectedCountry.value?.surcharge_percentage ?? 0,
    );

    const countriesErrorMessage = computed(() =>
        countriesError.value
            ? "No se pudieron cargar los países. Inténtalo de nuevo en unos minutos."
            : null,
    );

    const protectionType = computed(() =>
        selectedCountryCode.value === "EC" ? "Nacional" : "Internacional",
    );

    const departureDate = form.useSelector((state) => state.values.departure_date);
    const returnDate = form.useSelector((state) => state.values.return_date);
    const minReturnDate = computed(() => departureDate.value || todayStr.value);

    watch(
        departureDate,
        (newDate) => {
            if (!newDate) return;
            if (
                returnDate.value &&
                toDateOnly(returnDate.value) <= toDateOnly(newDate)
            ) {
                const nextDay = new Date(toDateOnly(newDate));
                nextDay.setDate(nextDay.getDate() + 1);
                form.setFieldValue("return_date", nextDay.toISOString().slice(0, 10));
            }
        },
    );

    const duration = computed(() => {
        if (!departureDate.value || !returnDate.value) return "";

        const departure = toDateOnly(departureDate.value);
        const arrival = toDateOnly(returnDate.value);
        const diffDays = Math.ceil(
            (arrival.getTime() - departure.getTime()) / (1000 * 60 * 60 * 24),
        );

        if (diffDays < 1) return null;
        return `${diffDays} día${diffDays > 1 ? "s" : ""}`;
    });

    return {
        form,
        countries,
        loadingCountries,
        countriesErrorMessage,
        countryOptions,
        selectedCountryCode,
        selectedCountry,
        destinationCountryName,
        destinationRegion,
        selectedSurchargePercentage,
        todayStr,
        minReturnDate,
        departureDate,
        returnDate,
        duration,
        protectionType,
        createQuote,
    };
}

export type QuoteFormApi = ReturnType<typeof useQuoteForm>["form"];
