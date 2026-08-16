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
  <PageHeader title="Job Applications" description="Applicants who applied through the careers page.">
    <template #actions>
      <BaseSelect v-model="statusFilter" :options="statusOptions" />
    </template>
  </PageHeader>

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Applicant</th>
          <th class="px-4 py-3">Job</th>
          <th class="px-4 py-3">Resume</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="application in items" :key="application.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3">
            <p class="font-medium text-neutral-900">{{ application.name }}</p>
            <p class="text-neutral-500">{{ application.email }}</p>
          </td>
          <td class="px-4 py-3">{{ application.job_title }}</td>
          <td class="px-4 py-3">
            <a :href="application.resume" target="_blank" class="underline">Download</a>
          </td>
          <td class="px-4 py-3">
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
          <td class="px-4 py-3 text-right">
            <button class="text-red-600 underline" @click="removeApplication(application.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="5" class="px-4 py-8 text-center text-neutral-400">No applications yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />
</template>
