<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <input
    :type="type"
    :value="modelValue"
    :placeholder="placeholder"
    :required="required"
    class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
