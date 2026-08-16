<script setup>
import { onMounted } from 'vue'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import StatusBadge from '../components/StatusBadge.vue'
import Pagination from '../components/Pagination.vue'

const { items, loading, meta, list, destroy } = useResource('newsletter-subscribers')

function goToPage(page) {
  list({ page })
}

onMounted(() => list())

async function removeSubscriber(id) {
  if (!confirm('Remove this subscriber?')) return
  await destroy(id)
}
</script>

<template>
  <PageHeader title="Newsletter Subscribers" description="Everyone who has signed up for updates." />

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Email</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Subscribed</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="subscriber in items" :key="subscriber.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3 font-medium text-neutral-900">{{ subscriber.email }}</td>
          <td class="px-4 py-3"><StatusBadge :status="subscriber.status" /></td>
          <td class="px-4 py-3 text-neutral-500">{{ new Date(subscriber.created_at).toLocaleDateString() }}</td>
          <td class="px-4 py-3 text-right">
            <button class="text-red-600 underline" @click="removeSubscriber(subscriber.id)">Remove</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="px-4 py-8 text-center text-neutral-400">No subscribers yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />
</template>
