import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Insurance } from '@/types'

export const useInsuranceStore = defineStore('insurance', () => {
  const currentQuote = ref<Insurance | null>(null)

  function setCurrentQuote(quote: Insurance): void {
    currentQuote.value = quote
  }

  function clearCurrentQuote(): void {
    currentQuote.value = null
  }

  return { currentQuote, setCurrentQuote, clearCurrentQuote }
})
