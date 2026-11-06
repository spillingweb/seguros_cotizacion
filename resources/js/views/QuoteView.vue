<script setup lang="ts">
import { ref, computed } from "vue";
import { useInsuranceStore } from "@/stores/insurance";
import QuoteForm from "@/components/QuoteForm.vue";
import QuoteSummary from "@/components/QuoteSummary.vue";
import { Sparkles } from "@lucide/vue";

const store = useInsuranceStore();
const showForm = ref(!store.currentQuote);

const insurance = computed(() => store.currentQuote);

function onQuoted() {
    showForm.value = false;
}

function onNewQuote() {
    showForm.value = true;
}
</script>

<template>
    <div class="flex-1 overflow-auto mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <div class="my-8">
            <div class="inline-flex items-center gap-2 text-chart-4 text-xs uppercase tracking-wider">
                <Sparkles :size="14" />
                Cotiza en menos de 2 minutos
            </div>
            <h1 class="text-3xl font-bold tracking-wide">
                Tu viaje empieza aquí
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Cuéntanos sobre tu próxima aventura y encuentra la protección ideal.
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
