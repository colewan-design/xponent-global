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

const { items, loading, meta, list, create, update, destroy } = useResource('partners')

const typeTabs = [
  { value: 'client', label: 'Clients' },
  { value: 'brand_partner', label: 'Brand Partners' },
  { value: 'affiliation', label: 'Affiliations' },
]
const activeType = ref('client')
const search = ref('')

function load(params = {}) {
  return list({ type: activeType.value, search: search.value, ...params })
}

let searchTimer = null
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(), 300)
})

watch(activeType, () => load())

function goToPage(page) {
  load({ page })
}

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())
const logo = ref(null)

function emptyForm() {
  return { type: activeType.value, name: '', website_url: '', description: '', sort_order: 0 }
}

onMounted(() => load())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  logo.value = null
  showModal.value = true
}

function openEdit(partner) {
  editingId.value = partner.id
  Object.assign(form, {
    type: partner.type,
    name: partner.name,
    website_url: partner.website_url ?? '',
    description: partner.description ?? '',
    sort_order: partner.sort_order,
  })
  logo.value = null
  showModal.value = true
}

async function handleSubmit() {
  const payload = { ...form }
  if (logo.value) payload.logo = logo.value

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }
  showModal.value = false
  await load()
}

async function removePartner(id) {
  if (!confirm('Delete this entry?')) return
  await destroy(id)
}

async function move(index, direction) {
  const target = index + direction
  if (target < 0 || target >= items.value.length) return

  const current = items.value[index]
  const swapWith = items.value[target]

  await Promise.all([
    update(current.id, { type: current.type, name: current.name, sort_order: swapWith.sort_order }),
    update(swapWith.id, { type: swapWith.type, name: swapWith.name, sort_order: current.sort_order }),
  ])
  await load()
}
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Clients & Partners" description="Logos shown on Our Clients, Our Brand Partners, and About > Affiliations.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search by name…" />
      <BaseButton @click="openCreate">Add Entry</BaseButton>
    </template>
  </PageHeader>

  <div class="ui-tabs">
    <button
      v-for="tab in typeTabs"
      :key="tab.value"
      class="ui-tab"
      :class="{ active: activeType === tab.value }"
      @click="activeType = tab.value"
    >
      {{ tab.label }}
    </button>
  </div>

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
    <div v-for="(item, index) in items" :key="item.id" class="inner-card" style="padding: 12px">
      <img :src="item.logo" :alt="item.name" class="mb-2 h-16 w-full object-contain" />
      <p class="truncate text-sm font-medium ui-text">{{ item.name }}</p>
      <div class="mt-2 flex items-center justify-between text-xs">
        <div class="flex gap-1">
          <button class="disabled:opacity-30" :disabled="index === 0" title="Move earlier" @click="move(index, -1)">↑</button>
          <button class="disabled:opacity-30" :disabled="index === items.length - 1" title="Move later" @click="move(index, 1)">↓</button>
        </div>
        <button class="ui-link-btn" @click="openEdit(item)">Edit</button>
        <button class="ui-link-btn ui-link-btn--danger" @click="removePartner(item.id)">Delete</button>
      </div>
    </div>
    <p v-if="!loading && items.length === 0" class="col-span-full py-8 text-center ui-muted">
      Nothing found.
    </p>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal v-model="showModal" :title="editingId ? 'Edit entry' : 'Add entry'">
    <form @submit.prevent="handleSubmit">
      <FormField label="Type" required>
        <BaseSelect v-model="form.type" :options="typeTabs.map((t) => ({ value: t.value, label: t.label }))" />
      </FormField>
      <FormField label="Name" required>
        <BaseInput v-model="form.name" required />
      </FormField>
      <FormField label="Logo" :required="!editingId">
        <FileInput @change="(file) => (logo = file)" />
      </FormField>
      <FormField label="Website URL">
        <BaseInput v-model="form.website_url" type="url" placeholder="https://" />
      </FormField>
      <FormField label="Description">
        <BaseTextarea v-model="form.description" :rows="3" />
      </FormField>
      <FormField label="Sort order">
        <BaseInput v-model="form.sort_order" type="number" />
      </FormField>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
  </div>
</template>
