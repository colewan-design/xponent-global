<script setup>
defineProps({
  accept: { type: String, default: 'image/*' },
  currentUrl: { type: String, default: '' },
})

const emit = defineEmits(['change'])

function handleChange(event) {
  const file = event.target.files[0]
  emit('change', file ?? null)
}
</script>

<template>
  <div>
    <img v-if="currentUrl" :src="currentUrl" alt="Current file" class="ui-file-preview" />
    <input class="ui-file-input" type="file" :accept="accept" @change="handleChange" />
  </div>
</template>
