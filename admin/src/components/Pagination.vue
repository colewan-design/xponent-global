<script setup>
const props = defineProps({
  meta: { type: Object, default: null },
})

const emit = defineEmits(['change'])

function goTo(page) {
  if (page < 1 || page > props.meta.last_page || page === props.meta.current_page) return
  emit('change', page)
}
</script>

<template>
  <div v-if="meta && meta.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-neutral-500">
    <p>
      Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
    </p>
    <div class="flex items-center gap-2">
      <button
        class="rounded-md border border-neutral-300 px-3 py-1.5 disabled:opacity-40"
        :disabled="meta.current_page === 1"
        @click="goTo(meta.current_page - 1)"
      >
        Previous
      </button>
      <span class="px-2">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
      <button
        class="rounded-md border border-neutral-300 px-3 py-1.5 disabled:opacity-40"
        :disabled="meta.current_page === meta.last_page"
        @click="goTo(meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
