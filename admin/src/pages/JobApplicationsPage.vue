<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../lib/api'
import PageHeader from '../components/PageHeader.vue'
import BaseSelect from '../components/BaseSelect.vue'
import Pagination from '../components/Pagination.vue'
import { useToastStore } from '../stores/toast'

const route = useRoute()
const toast = useToastStore()

const items = ref([])
const loading = ref(false)
const statusFilter = ref('')
const meta = ref(null)
const page = ref(1)

const statusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'new', label: 'New' },
  { value: 'reviewed', label: 'Reviewed' },
  { value: 'rejected', label: 'Rejected' },
  { value: 'hired', label: 'Hired' },
]

async function load() {
  loading.value = true
  try {
    const params = { page: page.value }
    if (route.query.job_opening_id) params.job_opening_id = route.query.job_opening_id
    if (statusFilter.value) params.status = statusFilter.value

    const { data } = await api.get('/admin/job-applications', { params })
    items.value = data.data
    meta.value = data.meta ?? null
  } finally {
    loading.value = false
  }
}

function goToPage(newPage) {
  page.value = newPage
  load()
}

async function updateStatus(application, status) {
  try {
    await api.put(`/admin/job-applications/${application.id}`, { status })
    application.status = status
    toast.success('Application updated.')
  } catch {
    toast.error('Could not update application.')
  }
}

async function removeApplication(id) {
  if (!confirm('Delete this application?')) return
  await api.delete(`/admin/job-applications/${id}`)
  items.value = items.value.filter((item) => item.id !== id)
  toast.success('Application deleted.')
}

onMounted(load)
watch([() => route.query.job_opening_id, statusFilter], () => {
  page.value = 1
  load()
})
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Job Applications" description="Applicants who applied through the careers page.">
    <template #actions>
      <BaseSelect v-model="statusFilter" :options="statusOptions" />
    </template>
  </PageHeader>

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Job</th>
          <th>Resume</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="application in items" :key="application.id">
          <td>
            <p class="font-medium ui-text">{{ application.name }}</p>
            <p class="ui-text-2">{{ application.email }}</p>
          </td>
          <td>{{ application.job_title }}</td>
          <td>
            <a :href="application.resume" target="_blank" class="underline">Download</a>
          </td>
          <td>
            <select
              class="rounded-md border border-neutral-300 px-2 py-1 text-sm"
              :value="application.status"
              @change="updateStatus(application, $event.target.value)"
            >
              <option value="new">New</option>
              <option value="reviewed">Reviewed</option>
              <option value="rejected">Rejected</option>
              <option value="hired">Hired</option>
            </select>
          </td>
          <td class="cell-actions">
            <button class="ui-link-btn ui-link-btn--danger" @click="removeApplication(application.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="5" class="ui-table-empty">No applications yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />
  </div>
</template>
