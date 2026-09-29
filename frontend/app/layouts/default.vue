<script setup>
/**
 * A page that carries its own top-of-screen navigation opts out of the global
 * header with `definePageMeta({ hideSiteHeader: true })`. The about page's
 * scrollytelling scene is the one doing so today — the footer is unaffected.
 */
const route = useRoute()
</script>

<template>
  <div class="flex min-h-screen flex-col bg-white">
    <SiteHeader v-if="!route.meta.hideSiteHeader" />
    <main class="flex-1">
      <slot />
    </main>
    <SiteFooter />
  </div>
</template>
