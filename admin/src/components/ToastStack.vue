<script setup>
import { useToastStore } from '../stores/toast'

const toast = useToastStore()
</script>

<template>
  <div class="ui-toast-stack">
    <div
      v-for="item in toast.items"
      :key="item.id"
      class="ui-toast"
      :class="item.type === 'error' ? 'ui-toast--error' : 'ui-toast--success'"
    >
      {{ item.message }}
    </div>
  </div>
</template>
