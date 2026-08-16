<script setup>
defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
})
</script>

<template>
  <div class="flex items-start justify-between gap-4 mb-6">
    <div>
      <h1 class="text-xl font-semibold text-neutral-900">{{ title }}</h1>
      <p v-if="description" class="text-sm text-neutral-500 mt-1">{{ description }}</p>
    </div>
    <div class="shrink-0 flex items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
