<script setup>
/**
 * Admin shell: fixed sidebar + topbar + routed content.
 *
 * The sidebar is always expanded at --sidebar-width, so nav targets never move
 * and the content column never reflows. Below 720px the same element becomes an
 * off-canvas drawer (see theme.css) with the bottom tab bar as the primary
 * navigation.
 */
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useUiStore } from '../stores/ui'
import { navGroups as allNavGroups } from '../utils/navigation'
import AdminNavbar from '../components/AdminNavbar.vue'
import AppIcon from '../components/AppIcon.vue'

const auth = useAuthStore()
const ui = useUiStore()
const route = useRoute()

function isActive(to) {
  return to === '/' ? route.path === '/' : route.path === to || route.path.startsWith(`${to}/`)
}

// Settings and Admin Users require the "admin" role. The backend enforces this
// too, but hiding the links avoids editors hitting a 403.
const navGroups = computed(() =>
  allNavGroups
    .map((group) => ({
      ...group,
      items: group.items.filter((item) => !item.adminOnly || auth.user?.role === 'admin'),
    }))
    .filter((group) => group.items.length),
)

const mobileNavItems = computed(() =>
  [
    { title: 'Home', icon: 'grid', to: '/' },
    { title: 'Enquiries', icon: 'inbox', to: '/enquiries' },
    { title: 'Posts', icon: 'file-text', to: '/posts' },
    { title: 'Jobs', icon: 'briefcase', to: '/jobs' },
    { title: 'Settings', icon: 'settings', to: '/settings', adminOnly: true },
  ].filter((item) => !item.adminOnly || auth.user?.role === 'admin'),
)

const initials = computed(() => {
  const name = auth.user?.name || ''
  return name.split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase()
})
</script>

<template>
  <aside class="sidebar" :class="{ 'is-mobile-open': ui.mobileDrawerOpen }">
    <div class="sidebar-brand">
      <span class="sidebar-brand-box">XG</span>
      <div class="sidebar-brand-text">
        <div class="sidebar-brand-name">Xponent Global</div>
        <div class="sidebar-brand-subtext">Admin Console</div>
      </div>
    </div>

    <div class="sidebar-nav">
      <template v-for="(group, index) in navGroups" :key="group.label">
        <div v-if="index === navGroups.length - 1 && navGroups.length > 1" class="nav-group-divider" />
        <div class="nav-section-label">{{ group.label }}</div>
        <router-link
          v-for="item in group.items"
          :key="item.to"
          :to="item.to"
          class="nav-item"
          :class="{ active: isActive(item.to) }"
          @click="ui.closeMobileDrawer()"
        >
          <AppIcon class="nav-icon" :name="item.icon" :size="18" />
          <span class="nav-label">{{ item.title }}</span>
        </router-link>
      </template>
    </div>

    <div class="sidebar-user">
      <span class="navbar-avatar" style="width: 32px; height: 32px; font-size: 11px">{{ initials }}</span>
      <div style="min-width: 0; flex: 1">
        <div class="sidebar-user-name">{{ auth.user?.name }}</div>
        <div class="sidebar-user-email">{{ auth.user?.email }}</div>
      </div>
    </div>
  </aside>

  <div v-show="ui.mobileDrawerOpen" class="sidebar-backdrop" @click="ui.closeMobileDrawer()" />

  <div class="admin-content-shell">
    <AdminNavbar />
    <main class="admin-page">
      <slot />
    </main>
  </div>

  <nav class="admin-mobile-tabs" aria-label="Primary navigation">
    <router-link
      v-for="item in mobileNavItems"
      :key="item.to"
      :to="item.to"
      class="admin-mobile-tab"
      :class="{ active: isActive(item.to) }"
    >
      <AppIcon :name="item.icon" :size="20" />
      <span>{{ item.title }}</span>
    </router-link>
  </nav>
</template>
