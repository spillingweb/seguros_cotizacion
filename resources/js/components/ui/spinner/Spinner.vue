<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

import { Loader2Icon } from '@lucide/vue'
import { cn } from 'cn'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <Loader2Icon role="status" aria-label="Loading" :strokeWidth="3" :class="cn('size-8 text-chart-2/80 animate-spin', props.class)" />
</template>
