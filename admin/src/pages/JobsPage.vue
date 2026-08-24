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
  <div class="page-frame">
  <PageHeader title="Job Openings" description="Careers page listings.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search job openings…" />
      <BaseButton @click="openCreate">New Job Opening</BaseButton>
    </template>
  </PageHeader>

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Location</th>
          <th>Status</th>
          <th>Applications</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="job in items" :key="job.id">
          <td class="cell-strong">{{ job.title }}</td>
          <td>{{ job.location }}</td>
          <td><StatusBadge :status="job.status" /></td>
          <td>
            <RouterLink :to="{ name: 'job-applications', query: { job_opening_id: job.id } }" class="underline">
              {{ job.applications_count ?? 0 }}
            </RouterLink>
          </td>
          <td class="cell-actions">
            <button class="ui-link-btn" @click="openEdit(job)">Edit</button>
            <button class="ui-link-btn ui-link-btn--danger" @click="removeJob(job.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="5" class="ui-table-empty">No job openings found.</td>
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
  </div>
</template>
