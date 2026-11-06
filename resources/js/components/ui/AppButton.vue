<script setup lang="ts">
import type { ButtonHTMLAttributes } from 'vue'
import Button from './button/Button.vue';
import type { ButtonVariants } from './button';

interface Props {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  loading?: boolean
  type?: ButtonHTMLAttributes['type']
}

const { variant = 'default', size = 'lg', loading = false, type = 'button' } = defineProps<Props>()
</script>

<template>
  <Button
    :type="type"
    :disabled="loading"
    :aria-busy="loading"
    :variant="variant"
    :size="size"
  >
    <svg
      v-if="loading"
      class="size-4 animate-spin"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
      />
    </svg>
    <slot />
  </Button>
</template>
