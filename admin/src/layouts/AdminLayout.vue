<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const baseNav = [
  { to: '/', label: 'Dashboard' },
  { to: '/enquiries', label: 'Enquiries' },
  { to: '/subscribers', label: 'Newsletter Subscribers' },
  { to: '/posts', label: 'Posts & Case Studies' },
  { to: '/resources', label: 'Resources' },
  { to: '/jobs', label: 'Job Openings' },
  { to: '/job-applications', label: 'Job Applications' },
  { to: '/gallery', label: 'Gallery' },
  { to: '/partners', label: 'Clients & Partners' },
  { to: '/office-locations', label: 'Office Locations' },
  { to: '/solutions', label: 'Solutions Catalogue' },
  { to: '/page-content', label: 'Page Content' },
]

// Settings and Admin Users management require the "admin" role — the backend
// enforces this too, but hiding the links avoids editors hitting a 403.
const adminOnlyNav = [
  { to: '/settings', label: 'Settings' },
  { to: '/users', label: 'Admin Users' },
]

const nav = computed(() => (auth.user?.role === 'admin' ? [...baseNav, ...adminOnlyNav] : baseNav))

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen flex">
    <aside class="w-64 shrink-0 bg-brand-charcoal text-neutral-200 flex flex-col">
      <div class="px-5 py-5 border-b border-white/10">
        <p class="text-white font-semibold tracking-wide">Xponent Global</p>
        <p class="text-xs text-neutral-400">Admin Console</p>
      </div>
      <nav class="flex-1 overflow-y-auto py-3">
        <router-link
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="block px-5 py-2 text-sm hover:bg-white/5 hover:text-white transition-colors"
          active-class="bg-brand-gold/10 text-brand-gold border-r-2 border-brand-gold"
        >
          {{ item.label }}
        </router-link>
      </nav>
      <div class="px-5 py-4 border-t border-white/10 text-xs text-neutral-400">
        <p class="truncate">{{ auth.user?.name }}</p>
        <p class="truncate text-neutral-500">{{ auth.user?.email }}</p>
        <button class="mt-2 text-neutral-300 hover:text-white underline" @click="handleLogout">
          Log out
        </button>
      </div>
    </aside>
    <main class="flex-1 bg-neutral-100 min-h-screen overflow-y-auto">
      <div class="max-w-6xl mx-auto px-6 py-8">
        <slot />
      </div>
    </main>
  </div>
</template>
