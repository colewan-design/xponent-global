<script setup>
defineProps({
  variant: { type: String, default: 'primary' }, // primary, secondary, danger, ghost
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
})

const variants = {
  primary: 'bg-brand-charcoal text-white hover:bg-neutral-800',
  secondary: 'bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50',
  danger: 'bg-red-600 text-white hover:bg-red-700',
  ghost: 'text-neutral-600 hover:text-neutral-900',
}
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="inline-flex items-center gap-1.5 rounded-md px-3.5 py-2 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    :class="variants[variant]"
  >
    <slot />
  </button>
</template>
