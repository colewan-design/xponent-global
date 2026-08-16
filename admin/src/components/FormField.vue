<script setup>
defineProps({
  label: { type: String, required: true },
  required: { type: Boolean, default: false },
  hint: { type: String, default: '' },
})
</script>

<template>
  <label class="block mb-4">
    <span class="block text-sm font-medium text-neutral-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </span>
    <slot />
    <span v-if="hint" class="block text-xs text-neutral-400 mt-1">{{ hint }}</span>
  </label>
</template>
