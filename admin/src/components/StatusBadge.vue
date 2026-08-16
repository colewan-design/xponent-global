<script setup>
const props = defineProps({
  status: { type: String, required: true },
})

const palette = {
  new: 'bg-blue-100 text-blue-700',
  contacted: 'bg-amber-100 text-amber-700',
  closed: 'bg-neutral-200 text-neutral-600',
  open: 'bg-green-100 text-green-700',
  subscribed: 'bg-green-100 text-green-700',
  unsubscribed: 'bg-neutral-200 text-neutral-600',
  reviewed: 'bg-amber-100 text-amber-700',
  rejected: 'bg-red-100 text-red-700',
  hired: 'bg-green-100 text-green-700',
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
    :class="palette[props.status] ?? 'bg-neutral-200 text-neutral-600'"
  >
    {{ props.status }}
  </span>
</template>
