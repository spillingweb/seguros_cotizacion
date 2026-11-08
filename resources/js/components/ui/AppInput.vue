<script setup lang="ts">
import Field from "./field/Field.vue";
import FieldDescription from "./field/FieldDescription.vue";
import FieldError from "./field/FieldError.vue";
import FieldLabel from "./field/FieldLabel.vue";
import Input from "./input/Input.vue";

type AppInputField = {
    name: string;
    state: {
        value: string | undefined;
        meta: {
            isTouched: boolean;
            isValid: boolean;
            errors: Array<string | { message: string | undefined } | undefined>;
        };
    };
    handleChange: (
        value: string | ((currentValue: string) => string),
    ) => void;
    handleBlur: () => void;
};

interface Props {
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
    field: AppInputField;
}

defineProps<Props>();

function isInvalid(field: AppInputField) {
    return field.state.meta.isTouched && !field.state.meta.isValid;
}
</script>

<template #default="{ field }">
    <Field class="relative" :data-invalid="isInvalid(field)">
        <Input
            :id="field.name"
            :name="field.name"
            :model-value="field.state.value"
            v-bind="$attrs"
            :aria-invalid="isInvalid(field)"
            :aria-describedby="
                isInvalid(field)
                    ? `${field.name}-error`
                    : hint
                      ? `${field.name}-hint`
                      : undefined
            "
            class="peer placeholder-transparent"
            placeholder=" "
            @blur="field.handleBlur"
            @input="(event: Event) => field.handleChange((event.target as HTMLInputElement).value)"
        />
        <FieldLabel
            :for="field.name"
            class="absolute w-fit! px-1 top-2 text-base left-3 text-muted-foreground peer-focus:-top-2 peer-focus:left-2 peer-focus:bg-white peer-focus:text-xs peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:left-2 peer-not-placeholder-shown:bg-white peer-not-placeholder-shown:text-xs transition-all duration-200"
        >
            {{ label }}
            <span v-if="required" aria-label="campo requerido">*</span>
        </FieldLabel>

        <FieldError
            v-if="isInvalid(field)"
            :id="`${field.name}-error`"
            :errors="field.state.meta.errors"
        />
        <FieldDescription v-else-if="hint" :id="`${field.name}-hint`">{{
            hint
        }}</FieldDescription>
    </Field>
</template>
