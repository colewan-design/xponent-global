<script setup>
/**
 * Topbar. Carries the page title — PageHeader.vue no longer renders its own
 * <h1>, so the title appears exactly once per page. The title is derived from
 * the route via utils/navigation.js, the same map the sidebar reads.
 */
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'
import { pageTitleForPath } from '../utils/navigation'
import AppIcon from './AppIcon.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

const pageTitle = computed(() => pageTitleForPath(route.path))

const initials = computed(() => {
  const name = auth.user?.name || ''
  return name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase()
})

const menuOpen = ref(false)
const menuRoot = ref(null)

function handleDocumentClick(event) {
  if (menuRoot.value && !menuRoot.value.contains(event.target)) {
    menuOpen.value = false
  }
}

function handleEscape(event) {
  if (event.key === 'Escape') menuOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  document.removeEventListener('keydown', handleEscape)
})

async function handleLogout() {
  menuOpen.value = false
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <header class="admin-navbar">
    <button class="navbar-icon-btn navbar-menu-btn" aria-label="Open menu" @click="ui.toggleMobileDrawer()">
      <AppIcon name="menu" :size="16" />
    </button>

    <div class="admin-navbar-title">{{ pageTitle }}</div>
    <div class="admin-navbar-mobile-title">{{ pageTitle }}</div>

    <div class="admin-navbar-spacer" />

    <button
      class="navbar-icon-btn"
      :aria-label="ui.theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'"
      @click="ui.toggleTheme()"
    >
      <AppIcon :name="ui.theme === 'dark' ? 'sun' : 'moon'" :size="16" />
    </button>

    <div ref="menuRoot" style="position: relative">
      <button class="navbar-user-btn" aria-haspopup="menu" :aria-expanded="menuOpen" @click="menuOpen = !menuOpen">
        <span class="navbar-avatar">{{ initials }}</span>
        <span class="navbar-user-meta">
          <span class="navbar-user-name">{{ auth.user?.name }}</span>
          <span class="navbar-user-role">{{ auth.user?.role }}</span>
        </span>
        <AppIcon class="navbar-user-chevron" name="chevron-down" :size="16" />
      </button>

      <div v-if="menuOpen" class="navbar-menu-card" role="menu">
        <div class="navbar-menu-header">
          <div class="navbar-menu-name">{{ auth.user?.name }}</div>
          <div class="navbar-menu-email">{{ auth.user?.email }}</div>
        </div>
        <div class="navbar-menu-divider" />
        <div class="navbar-menu-section">
          <RouterLink
            v-if="auth.user?.role === 'admin'"
            to="/settings"
            class="navbar-menu-item"
            role="menuitem"
            @click="menuOpen = false"
          >
            <AppIcon name="settings" :size="14" />
            Settings
          </RouterLink>
          <RouterLink
            v-if="auth.user?.role === 'admin'"
            to="/users"
            class="navbar-menu-item"
            role="menuitem"
            @click="menuOpen = false"
          >
            <AppIcon name="key" :size="14" />
            Change password
          </RouterLink>
          <button class="navbar-menu-item navbar-menu-item-danger" role="menuitem" @click="handleLogout">
            <AppIcon name="log-out" :size="14" />
            Sign out
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
