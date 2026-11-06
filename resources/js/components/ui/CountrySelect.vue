<script setup lang="ts">
import Field from "./field/Field.vue";
import FieldContent from "./field/FieldContent.vue";
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
}

const props = defineProps<Props>();
const model = defineModel<string | undefined>({ required: true });
const id = `select-${Math.random().toString(36).slice(2, 9)}`;

// Group options by region
const groupedOptions = computed(() => {
    const groups = new Map<string, Option[]>();
    
    for (const option of props.options) {
        if (!groups.has(option.region)) {
            groups.set(option.region, []);
        }
        groups.get(option.region)!.push(option);
    }
    
    // Sort regions alphabetically
    return Array.from(groups.entries()).sort(([a], [b]) => a.localeCompare(b));
});
</script>

<template>
    <Field class="relative">
        <Select
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :disabled="loading"
            :aria-invalid="!!error"
            :aria-describedby="error ? `${id}-error` : undefined"
            :aria-busy="loading"
        >
            <SelectTrigger
                class="w-full"
            >
                <SelectValue :placeholder="placeholder ? placeholder : 'Adónde viajas?'" />
            </SelectTrigger>
            <SelectContent>
                <template v-for="[region, regionOptions] in groupedOptions" :key="region">
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
        <Spinner v-if="loading" class="size-4" />
        <FieldLabel
            class="absolute w-fit! px-1 text-muted-foreground -top-1.5 left-2 bg-white text-xs"
        >
            {{ label }}
            <span v-if="required" aria-label="campo requerido">*</span>
        </FieldLabel>
        <FieldError v-if="error" :id="`${id}-error`">
            {{ error }}
        </FieldError>
    </Field>
</template>
