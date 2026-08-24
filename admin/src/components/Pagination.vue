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
  <div v-if="meta && meta.last_page > 1" class="ui-pagination">
    <p>Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}</p>
    <div class="ui-pagination-controls">
      <button
        class="ui-btn ui-btn--secondary"
        :disabled="meta.current_page === 1"
        @click="goTo(meta.current_page - 1)"
      >
        Previous
      </button>
      <span style="padding: 0 4px">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
      <button
        class="ui-btn ui-btn--secondary"
        :disabled="meta.current_page === meta.last_page"
        @click="goTo(meta.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
