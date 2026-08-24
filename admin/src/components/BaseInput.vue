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
    class="ui-input"
    :type="type"
    :value="modelValue"
    :placeholder="placeholder"
    :required="required"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
