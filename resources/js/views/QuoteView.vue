<script setup lang="ts">
import { ref, computed } from 'vue'
import { useInsuranceStore } from '@/stores/insurance'
import QuoteForm from '@/components/QuoteForm.vue'
import QuoteSummary from '@/components/QuoteSummary.vue'

const store = useInsuranceStore()
const showForm = ref(!store.currentQuote)

const insurance = computed(() => store.currentQuote)

function onQuoted() {
  showForm.value = false
}

function onNewQuote() {
  showForm.value = true
}
</script>

<template>
  <div class="flex-1 overflow-auto mx-auto max-w-3xl px-4 py-8 sm:px-6">
    <div class="my-8 text-center">
      <h1 class="text-2xl font-bold text-gray-900">Cotizar Seguro de Viaje</h1>
      <p class="mt-1 text-sm text-gray-600">
        Ingresa tus datos para obtener una cotización personalizada en segundos.
      </p>
    </div>

    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
      mode="out-in"
    >
      <QuoteForm v-if="showForm" @quoted="onQuoted" />
      <QuoteSummary
        v-else-if="insurance"
        :insurance="insurance"
        @new-quote="onNewQuote"
        @contracted="() => {}"
      />
    </Transition>
  </div>
</template>
