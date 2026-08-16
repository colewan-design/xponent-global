<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Search…' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <input
    :value="modelValue"
    type="search"
    :placeholder="placeholder"
    class="w-64 rounded-md border border-neutral-300 px-3 py-2 text-sm focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
