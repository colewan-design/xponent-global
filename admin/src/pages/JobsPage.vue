<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseTextarea from '../components/BaseTextarea.vue'
import BaseSelect from '../components/BaseSelect.vue'
import FormField from '../components/FormField.vue'
import StatusBadge from '../components/StatusBadge.vue'
import Pagination from '../components/Pagination.vue'
import SearchInput from '../components/SearchInput.vue'

const { items, loading, meta, list, create, update, destroy } = useResource('job-openings')

const employmentOptions = [
  { value: 'full_time', label: 'Full-time' },
  { value: 'part_time', label: 'Part-time' },
  { value: 'contract', label: 'Contract' },
]
const statusOptions = [
  { value: 'open', label: 'Open' },
  { value: 'closed', label: 'Closed' },
]

const search = ref('')
let searchTimer = null
watch(search, (value) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => list({ search: value }), 300)
})

function goToPage(page) {
  list({ search: search.value, page })
}

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())

function emptyForm() {
  return {
    title: '',
    department: '',
    location: '',
    employment_type: 'full_time',
    summary: '',
    description: '',
    requirements: '',
    status: 'open',
  }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  showModal.value = true
}

function openEdit(job) {
  editingId.value = job.id
  Object.assign(form, {
    title: job.title,
    department: job.department ?? '',
    location: job.location ?? '',
    employment_type: job.employment_type,
    summary: job.summary ?? '',
    description: job.description ?? '',
    requirements: job.requirements ?? '',
    status: job.status,
  })
  showModal.value = true
}

async function handleSubmit() {
  if (editingId.value) {
    await update(editingId.value, { ...form })
  } else {
    await create({ ...form })
  }
  showModal.value = false
  await list({ search: search.value })
}

async function removeJob(id) {
  if (!confirm('Delete this job opening? Any applications will also be removed.')) return
  await destroy(id)
}
</script>

<template>
  <PageHeader title="Job Openings" description="Careers page listings.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search job openings…" />
      <BaseButton @click="openCreate">New Job Opening</BaseButton>
    </template>
  </PageHeader>

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Location</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Applications</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="job in items" :key="job.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3 font-medium text-neutral-900">{{ job.title }}</td>
          <td class="px-4 py-3">{{ job.location }}</td>
          <td class="px-4 py-3"><StatusBadge :status="job.status" /></td>
          <td class="px-4 py-3">
            <RouterLink :to="{ name: 'job-applications', query: { job_opening_id: job.id } }" class="underline">
              {{ job.applications_count ?? 0 }}
            </RouterLink>
          </td>
          <td class="px-4 py-3 text-right">
            <button class="text-brand-charcoal underline mr-3" @click="openEdit(job)">Edit</button>
            <button class="text-red-600 underline" @click="removeJob(job.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="5" class="px-4 py-8 text-center text-neutral-400">No job openings found.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal v-model="showModal" :title="editingId ? 'Edit job opening' : 'New job opening'" wide>
    <form @submit.prevent="handleSubmit">
      <FormField label="Title" required>
        <BaseInput v-model="form.title" required />
      </FormField>
      <div class="grid grid-cols-2 gap-4">
        <FormField label="Department">
          <BaseInput v-model="form.department" />
        </FormField>
        <FormField label="Location">
          <BaseInput v-model="form.location" />
        </FormField>
        <FormField label="Employment type" required>
          <BaseSelect v-model="form.employment_type" :options="employmentOptions" />
        </FormField>
        <FormField label="Status" required>
          <BaseSelect v-model="form.status" :options="statusOptions" />
        </FormField>
      </div>
      <FormField label="Summary" hint="Short summary shown in the listing card.">
        <BaseTextarea v-model="form.summary" :rows="2" />
      </FormField>
      <FormField label="Description">
        <BaseTextarea v-model="form.description" :rows="4" />
      </FormField>
      <FormField label="Requirements" hint="One requirement per line.">
        <BaseTextarea v-model="form.requirements" :rows="4" />
      </FormField>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
</template>
