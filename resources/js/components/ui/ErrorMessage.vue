<script setup lang="ts">
interface Props {
    error?: string | null;
}

const { error } = defineProps<Props>();
</script>

<!-- Make this a generic error message component -->
<template>
    <div
        v-if="error"
        role="alert"
        class="rounded-lg bg-destructive/5 border border-red-200 p-4 text-xs text-destructive"
    >
        {{ error }}
    </div>
</template>
