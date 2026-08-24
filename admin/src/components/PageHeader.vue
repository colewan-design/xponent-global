<script setup>
/**
 * Page header — eyebrow, subtitle and actions only.
 *
 * The <h1> moved to the topbar (AdminNavbar.vue) when it grew to 68px to carry
 * the page title, so rendering it here too would show it twice. The `title`
 * prop is kept because every call site passes it and it documents each view's
 * identity there; the topbar derives its own title from the route via
 * utils/navigation.js.
 */
import AppIcon from './AppIcon.vue'

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  section: { type: String, default: '' },
  icon: { type: String, default: 'grid' },
})
</script>

<template>
  <div class="page-header">
    <div>
      <div v-if="section" class="page-header-eyebrow">
        <AppIcon :name="icon" :size="15" />
        <span>{{ section }}</span>
      </div>
      <p v-if="description" class="page-header-subtitle">{{ description }}</p>
    </div>
    <div class="page-header-actions">
      <slot name="actions" />
    </div>
  </div>
</template>
