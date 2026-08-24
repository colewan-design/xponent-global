<script setup>
defineProps({
  label: { type: String, required: true },
  required: { type: Boolean, default: false },
  hint: { type: String, default: '' },
})
</script>

<template>
  <label class="ui-field">
    <span class="ui-field-label">
      {{ label }}
      <span v-if="required" class="ui-field-required">*</span>
    </span>
    <slot />
    <span v-if="hint" class="ui-field-hint">{{ hint }}</span>
  </label>
</template>
