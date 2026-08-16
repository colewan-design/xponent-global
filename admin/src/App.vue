<script setup>
import { useRoute } from 'vue-router'
import AdminLayout from './layouts/AdminLayout.vue'
import ToastStack from './components/ToastStack.vue'

const route = useRoute()
</script>

<template>
  <ToastStack />
  <AdminLayout v-if="!route.meta.public">
    <router-view />
  </AdminLayout>
  <router-view v-else />
</template>
