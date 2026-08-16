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
    <img
      v-if="currentUrl"
      :src="currentUrl"
      alt="Current file"
      class="mb-2 h-20 w-20 rounded-md border border-neutral-200 object-cover"
    />
    <input
      type="file"
      :accept="accept"
      class="block w-full text-sm text-neutral-600 file:mr-3 file:rounded-md file:border-0 file:bg-neutral-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-neutral-200"
      @change="handleChange"
    />
  </div>
</template>
