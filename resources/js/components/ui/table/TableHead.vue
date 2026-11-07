<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from 'cn'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <th
    data-slot="table-head"
    :class="cn('text-muted-foreground text-xs font-semibold uppercase tracking-wide h-10 px-2 text-left align-middle whitespace-nowrap [&:has([role=checkbox])]:pr-0', props.class)"
  >
    <slot />
  </th>
</template>