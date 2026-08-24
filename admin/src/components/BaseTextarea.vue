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
    class="ui-input"
    :value="modelValue"
    :rows="rows"
    :required="required"
    @input="$emit('update:modelValue', $event.target.value)"
  ></textarea>
</template>
