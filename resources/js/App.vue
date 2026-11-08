<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppHeader from './components/header/AppHeader.vue';
import AppFooter from './components/footer/AppFooter.vue';
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
