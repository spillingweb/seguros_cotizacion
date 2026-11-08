<script setup lang="ts">
import type { ButtonHTMLAttributes } from 'vue'
import Button from './button/Button.vue';
import type { ButtonVariants } from './button';
import Spinner from './spinner/Spinner.vue';

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
    <Spinner v-if="loading" class="size-4" />
    <slot />
  </Button>
</template>
