<script setup>
/**
 * The recurring section heading: a title with an optional inline link beside it,
 * baseline-aligned. Used on every band so the page reads as one document rather
 * than a stack of differently-titled blocks.
 */
defineProps({
  title: { type: String, required: true },
  linkLabel: { type: String, default: '' },
  linkTo: { type: String, default: '' },
})
</script>

<template>
  <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
    <h2 class="text-[clamp(1.5rem,2.6vw,2rem)] font-bold tracking-tight text-ink">{{ title }}</h2>
    <NuxtLink
      v-if="linkLabel && linkTo"
      :to="linkTo"
      class="text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
    >
      {{ linkLabel }}
    </NuxtLink>
  </div>
</template>
