<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
</script>

<template>
  <div class="min-h-dvh flex flex-col">
    <AppHeader />
    <main class="flex-1 overflow-auto">
      <RouterView />
    </main>
    <AppFooter />
  </div>
</template>
