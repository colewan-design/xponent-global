<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  rows: { type: Number, default: 4 },
  required: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <textarea
    :value="modelValue"
    :rows="rows"
    :required="required"
    class="w-full rounded-md border border-neutral-300 px-3 py-2 text-sm text-neutral-900 focus:border-brand-gold focus:outline-none focus:ring-1 focus:ring-brand-gold"
    @input="$emit('update:modelValue', $event.target.value)"
  ></textarea>
</template>
