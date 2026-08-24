<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import AdminLayout from './layouts/AdminLayout.vue'
import ToastStack from './components/ToastStack.vue'
import { useUiStore } from './stores/ui'

const route = useRoute()
const ui = useUiStore()

// Applied before first paint of the shell so the stored theme does not flash
// light on reload.
onMounted(() => ui.loadTheme())
</script>

<template>
  <ToastStack />
  <AdminLayout v-if="!route.meta.public">
    <router-view />
  </AdminLayout>
  <router-view v-else />
</template>
