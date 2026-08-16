<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <p class="text-sm font-semibold uppercase tracking-wide text-brand-gold">404</p>
    <h1 class="mt-2 text-xl font-semibold text-neutral-900">Page not found</h1>
    <p class="mt-2 text-sm text-neutral-500">The page you're looking for doesn't exist.</p>
    <RouterLink to="/" class="mt-6 rounded-md bg-brand-charcoal px-4 py-2 text-sm text-white">
      Back to Dashboard
    </RouterLink>
  </div>
</template>
