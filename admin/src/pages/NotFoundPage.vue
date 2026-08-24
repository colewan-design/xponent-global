<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="flex flex-col items-center justify-center py-24 text-center">
    <p class="eyebrow">404</p>
    <h1 class="mt-2 text-xl font-semibold ui-text">Page not found</h1>
    <p class="mt-2 text-sm ui-text-2">The page you're looking for doesn't exist.</p>
    <RouterLink to="/" class="ui-btn ui-btn--primary" style="margin-top: 24px">
      Back to Dashboard
    </RouterLink>
  </div>
</template>
