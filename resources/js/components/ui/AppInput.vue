<script setup lang="ts">
import Field from "./field/Field.vue";
import FieldDescription from "./field/FieldDescription.vue";
import FieldError from "./field/FieldError.vue";
import FieldLabel from "./field/FieldLabel.vue";
import Input from "./input/Input.vue";

interface Props {
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
}

defineProps<Props>();

const model = defineModel<string>({ required: true });
const id = `input-${Math.random().toString(36).slice(2, 9)}`;
</script>

<template>
    <Field class="relative">
        <Input
            :id="id"
            v-model="model"
            v-bind="$attrs"
            :aria-invalid="!!error"
            :aria-describedby="
                error ? `${id}-error` : hint ? `${id}-hint` : undefined
            "
            class="peer placeholder-transparent"
            placeholder=" "
        />
        <FieldLabel
            :for="id"
            class="absolute w-fit! px-1 top-3 text-base left-3 text-muted-foreground peer-focus:-top-2 peer-focus:left-2 peer-focus:bg-white peer-focus:text-xs peer-not-placeholder-shown:-top-2 peer-not-placeholder-shown:left-2 peer-not-placeholder-shown:bg-white peer-not-placeholder-shown:text-xs transition-all duration-200"
        >
            {{ label }}
            <span v-if="required" aria-label="campo requerido">*</span>
        </FieldLabel>

        <FieldError v-if="error" :id="`${id}-error`">{{ error }}</FieldError>
        <FieldDescription v-else-if="hint" :id="`${id}-hint`">{{
            hint
        }}</FieldDescription>
    </Field>
</template>
