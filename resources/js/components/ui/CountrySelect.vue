<script setup lang="ts">
import Field from "./field/Field.vue";
import FieldError from "./field/FieldError.vue";
import FieldLabel from "./field/FieldLabel.vue";
import Select from "./select/Select.vue";
import SelectContent from "./select/SelectContent.vue";
import SelectGroup from "./select/SelectGroup.vue";
import SelectItem from "./select/SelectItem.vue";
import SelectLabel from "./select/SelectLabel.vue";
import SelectTrigger from "./select/SelectTrigger.vue";
import SelectValue from "./select/SelectValue.vue";
import Spinner from "./spinner/Spinner.vue";
import { computed } from "vue";

type CountrySelectField = {
    name: string;
    state: {
        value: string | undefined;
        meta: {
            isTouched: boolean;
            isValid: boolean;
            errors: Array<string | { message: string | undefined } | undefined>;
        };
    };
    handleChange: (value: string | ((currentValue: string) => string)) => void;
    handleBlur: () => void;
};

interface Option {
    value: string;
    label: string;
    flag: string;
    region: string;
}

interface Props {
    label: string;
    options: Option[];
    placeholder?: string;
    error?: string;
    required?: boolean;
    loading?: boolean;
    field: CountrySelectField;
}

const props = defineProps<Props>();
const id = `select-${Math.random().toString(36).slice(2, 9)}`;

const groupedOptions = computed(() => {
    const groups = new Map<string, Option[]>();

    for (const option of props.options) {
        if (!groups.has(option.region)) {
            groups.set(option.region, []);
        }
        groups.get(option.region)!.push(option);
    }

    return Array.from(groups.entries()).sort(([a], [b]) => a.localeCompare(b));
});

const isInvalid = computed(
    () => props.field.state.meta.isTouched && !props.field.state.meta.isValid,
);

const selectedLabel = computed(() => {
    const value = props.field.state.value;
    if (!value) return "";
    return (
        props.options.find((option) => option.value === value)?.label ?? value
    );
});
</script>

<template>
    <Field class="relative" :data-invalid="isInvalid">
        <Select
            :id="id"
            :model-value="field.state.value"
            @update:model-value="
                (value: unknown) => field.handleChange(String(value ?? ''))
            "
            @blur="field.handleBlur"
            v-bind="$attrs"
            :aria-invalid="isInvalid"
            :aria-describedby="isInvalid ? `${field.name}-error` : undefined"
            :aria-busy="loading"
        >
            <SelectTrigger class="w-full">
                <SelectValue :placeholder="placeholder ?? 'Seleccione un país'">
                    <template v-if="selectedLabel">
                        {{ selectedLabel }}
                    </template>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                <template
                    v-for="[region, regionOptions] in groupedOptions"
                    :key="region"
                >
                    <SelectGroup>
                        <SelectLabel>{{ region }}</SelectLabel>
                        <SelectItem
                            v-for="opt in regionOptions"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            <img
                                :src="opt.flag"
                                :alt="'Bandera de ' + opt.label"
                                class="inline-block w-4 h-4 mr-2"
                            />
                            {{ opt.label }}
                        </SelectItem>
                    </SelectGroup>
                </template>
            </SelectContent>
        </Select>
        <FieldLabel
            class="absolute w-fit! px-1 text-muted-foreground -top-1.5 left-2 bg-white text-xs"
        >
            {{ label }}
            <span v-if="required" aria-label="campo requerido">*</span>
        </FieldLabel>
        <FieldError
            v-if="isInvalid"
            :id="`${field.name}-error`"
            :errors="field.state.meta.errors"
        />
    </Field>
</template>
