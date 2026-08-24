<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Search…' },
})
defineEmits(['update:modelValue'])
</script>

<template>
  <input
    class="ui-search"
    :value="modelValue"
    type="search"
    :placeholder="placeholder"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
