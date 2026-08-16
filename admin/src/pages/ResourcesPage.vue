<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseTextarea from '../components/BaseTextarea.vue'
import BaseSelect from '../components/BaseSelect.vue'
import FormField from '../components/FormField.vue'
import FileInput from '../components/FileInput.vue'
import Pagination from '../components/Pagination.vue'
import SearchInput from '../components/SearchInput.vue'

const { items, loading, meta, list, create, update, destroy } = useResource('resources')

const categoryOptions = [
  { value: 'technical_document', label: 'Technical Document' },
  { value: 'datasheet', label: 'Datasheet' },
  { value: 'safety_compliance', label: 'Safety & Compliance' },
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
const file = ref(null)

function emptyForm() {
  return { category: 'technical_document', title: '', description: '', published: true }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  file.value = null
  showModal.value = true
}

function openEdit(resource) {
  editingId.value = resource.id
  Object.assign(form, {
    category: resource.category,
    title: resource.title,
    description: resource.description ?? '',
    published: resource.published,
  })
  file.value = null
  showModal.value = true
}

async function handleSubmit() {
  const payload = { ...form }
  if (file.value) payload.file = file.value

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }
  showModal.value = false
  await list({ search: search.value })
}

async function removeResource(id) {
  if (!confirm('Delete this resource?')) return
  await destroy(id)
}
</script>

<template>
  <PageHeader title="Resources" description="Technical documents, datasheets, and safety & compliance files.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search resources…" />
      <BaseButton @click="openCreate">New Resource</BaseButton>
    </template>
  </PageHeader>

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Title</th>
          <th class="px-4 py-3">Category</th>
          <th class="px-4 py-3">Published</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="resource in items" :key="resource.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3">
            <a :href="resource.file" target="_blank" class="font-medium text-neutral-900 hover:underline">{{ resource.title }}</a>
          </td>
          <td class="px-4 py-3 capitalize">{{ resource.category.replace('_', ' ') }}</td>
          <td class="px-4 py-3">{{ resource.published ? 'Yes' : 'No' }}</td>
          <td class="px-4 py-3 text-right">
            <button class="text-brand-charcoal underline mr-3" @click="openEdit(resource)">Edit</button>
            <button class="text-red-600 underline" @click="removeResource(resource.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="px-4 py-8 text-center text-neutral-400">No resources found.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal v-model="showModal" :title="editingId ? 'Edit resource' : 'New resource'">
    <form @submit.prevent="handleSubmit">
      <FormField label="Category" required>
        <BaseSelect v-model="form.category" :options="categoryOptions" />
      </FormField>
      <FormField label="Title" required>
        <BaseInput v-model="form.title" required />
      </FormField>
      <FormField label="Description">
        <BaseTextarea v-model="form.description" :rows="3" />
      </FormField>
      <FormField label="File" :required="!editingId" hint="PDF, Word, or Excel document.">
        <FileInput accept=".pdf,.doc,.docx,.xls,.xlsx" @change="(f) => (file = f)" />
      </FormField>
      <label class="flex items-center gap-2 mb-4 text-sm text-neutral-700">
        <input v-model="form.published" type="checkbox" class="rounded border-neutral-300" />
        Published
      </label>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
</template>
