<script setup>
defineProps({
  variant: { type: String, default: 'primary' }, // primary, secondary, danger, ghost
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <button :type="type" :disabled="disabled" class="ui-btn" :class="`ui-btn--${variant}`">
    <slot />
  </button>
</template>
