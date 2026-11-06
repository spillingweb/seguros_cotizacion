<script setup lang="ts">
import type { InsuranceStatus } from '@/types'

interface Props {
  status: InsuranceStatus
}

defineProps<Props>()

const labels: Record<InsuranceStatus, string> = {
  quoted: 'Cotizado',
  contracted: 'Contratado',
}

const styles: Record<InsuranceStatus, string> = {
  quoted: 'bg-amber-100 text-amber-800',
  contracted: 'bg-green-100 text-green-800',
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
    :class="styles[status]"
  >
    {{ labels[status] }}
  </span>
</template>
