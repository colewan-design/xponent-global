<script setup>
import { useToastStore } from '../stores/toast'

const toast = useToastStore()
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80">
    <div
      v-for="item in toast.items"
      :key="item.id"
      class="rounded-lg px-4 py-3 text-sm shadow-lg text-white"
      :class="item.type === 'error' ? 'bg-red-600' : 'bg-neutral-900'"
    >
      {{ item.message }}
    </div>
  </div>
</template>
