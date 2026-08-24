<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, required: true }, // [{ value, label }]
  required: { type: Boolean, default: false },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <select
    class="ui-input"
    :value="modelValue"
    :required="required"
    @change="$emit('update:modelValue', $event.target.value)"
  >
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>
