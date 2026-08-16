<script setup>
import { onMounted, ref } from 'vue'
import { api } from '../lib/api'
import PageHeader from '../components/PageHeader.vue'

const stats = ref(null)

const tiles = [
  { key: 'new_enquiries', label: 'New Enquiries' },
  { key: 'total_enquiries', label: 'Total Enquiries' },
  { key: 'subscribers', label: 'Newsletter Subscribers' },
  { key: 'open_jobs', label: 'Open Job Postings' },
  { key: 'new_applications', label: 'New Applications' },
  { key: 'published_posts', label: 'Published Posts' },
  { key: 'resources', label: 'Resources' },
]

onMounted(async () => {
  const { data } = await api.get('/admin/dashboard')
  stats.value = data
})
</script>

<template>
  <PageHeader title="Dashboard" description="Overview of site activity." />

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    <div
      v-for="tile in tiles"
      :key="tile.key"
      class="rounded-xl bg-white p-5 shadow-sm border border-neutral-200"
    >
      <p class="text-sm text-neutral-500">{{ tile.label }}</p>
      <p class="mt-2 text-2xl font-semibold text-neutral-900">
        {{ stats ? stats[tile.key] : '—' }}
      </p>
    </div>
  </div>
</template>
