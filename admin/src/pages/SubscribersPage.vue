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
  <div class="page-frame">
  <PageHeader title="Newsletter Subscribers" description="Everyone who has signed up for updates." />

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Email</th>
          <th>Status</th>
          <th>Subscribed</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="subscriber in items" :key="subscriber.id">
          <td class="cell-strong">{{ subscriber.email }}</td>
          <td><StatusBadge :status="subscriber.status" /></td>
          <td>{{ new Date(subscriber.created_at).toLocaleDateString() }}</td>
          <td class="cell-actions">
            <button class="ui-link-btn ui-link-btn--danger" @click="removeSubscriber(subscriber.id)">Remove</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="ui-table-empty">No subscribers yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />
  </div>
</template>
