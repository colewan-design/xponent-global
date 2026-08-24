<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../lib/api'
import PageHeader from '../components/PageHeader.vue'
import AppIcon from '../components/AppIcon.vue'

const stats = ref(null)

// `feature` grounds one tile in the brand tint — one per view, per the design
// system's KPI rule.
const tiles = [
  { key: 'open_orders', label: 'Open Orders', icon: 'shopping-cart', feature: true },
  { key: 'open_order_value', label: 'Open Order Value', icon: 'package', money: true },
  { key: 'orders_awaiting_payment', label: 'Awaiting Payment', icon: 'tag' },
  { key: 'low_stock_items', label: 'Low Stock Lines', icon: 'alert-triangle' },
  { key: 'active_products', label: 'Active Products', icon: 'boxes' },
  { key: 'new_enquiries', label: 'New Enquiries', icon: 'inbox' },
  { key: 'total_enquiries', label: 'Total Enquiries', icon: 'mail' },
  { key: 'subscribers', label: 'Newsletter Subscribers', icon: 'users' },
  { key: 'open_jobs', label: 'Open Job Postings', icon: 'briefcase' },
  { key: 'new_applications', label: 'New Applications', icon: 'user-check' },
  { key: 'published_posts', label: 'Published Posts', icon: 'file-text' },
  { key: 'resources', label: 'Resources', icon: 'download' },
]

function display(tile) {
  if (!stats.value) return '—'

  // Orders carry a per-order currency, so the pipeline figure has no single one
  // to name — it is shown as a plain rounded amount rather than claiming a
  // symbol the underlying orders may not share.
  return tile.money
    ? new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(stats.value[tile.key])
    : stats.value[tile.key]
}

onMounted(async () => {
  const { data } = await api.get('/admin/dashboard')
  stats.value = data
})
</script>

<template>
  <div class="page-frame">
    <PageHeader title="Dashboard" section="Overview" icon="grid" description="Site activity at a glance." />

    <div class="kpi-grid">
      <div
        v-for="tile in tiles"
        :key="tile.key"
        class="kpi-card"
        :class="{ 'kpi-card--feature': tile.feature }"
      >
        <div class="stat-icon-box">
          <AppIcon :name="tile.icon" :size="19" />
        </div>
        <div>
          <p class="stat-label">{{ tile.label }}</p>
          <p class="stat-value">{{ display(tile) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
