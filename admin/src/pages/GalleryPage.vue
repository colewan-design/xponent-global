<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import FormField from '../components/FormField.vue'
import FileInput from '../components/FileInput.vue'
import Pagination from '../components/Pagination.vue'
import SearchInput from '../components/SearchInput.vue'

const { items, loading, meta, list, create, update, destroy } = useResource('gallery-images')

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
const form = reactive({ caption: '', sort_order: 0 })
const image = ref(null)

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, { caption: '', sort_order: items.value.length + 1 })
  image.value = null
  showModal.value = true
}

function openEdit(item) {
  editingId.value = item.id
  Object.assign(form, { caption: item.caption ?? '', sort_order: item.sort_order })
  image.value = null
  showModal.value = true
}

async function handleSubmit() {
  const payload = { ...form }
  if (image.value) payload.image = image.value

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }
  showModal.value = false
  await list({ search: search.value })
}

async function removeImage(id) {
  if (!confirm('Delete this image?')) return
  await destroy(id)
}

async function move(index, direction) {
  const target = index + direction
  if (target < 0 || target >= items.value.length) return

  const current = items.value[index]
  const swapWith = items.value[target]

  await Promise.all([
    update(current.id, { caption: current.caption ?? '', sort_order: swapWith.sort_order }),
    update(swapWith.id, { caption: swapWith.caption ?? '', sort_order: current.sort_order }),
  ])
  await list({ search: search.value })
}
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Gallery" description="Photos shown on the public Gallery page.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search captions…" />
      <BaseButton @click="openCreate">Upload Image</BaseButton>
    </template>
  </PageHeader>

  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
    <div
      v-for="(item, index) in items"
      :key="item.id"
      class="group relative overflow-hidden apple-card"
    >
      <img :src="item.image" :alt="item.caption ?? ''" class="h-36 w-full object-cover" />
      <div class="absolute inset-0 flex flex-col justify-between bg-black/0 p-2 opacity-0 transition-opacity group-hover:bg-black/40 group-hover:opacity-100">
        <div class="flex justify-end gap-1">
          <button
            class="rounded bg-white/90 px-1.5 py-0.5 text-xs font-medium disabled:opacity-40"
            :disabled="index === 0"
            title="Move earlier"
            @click="move(index, -1)"
          >
            ↑
          </button>
          <button
            class="rounded bg-white/90 px-1.5 py-0.5 text-xs font-medium disabled:opacity-40"
            :disabled="index === items.length - 1"
            title="Move later"
            @click="move(index, 1)"
          >
            ↓
          </button>
        </div>
        <div class="flex items-end justify-between">
          <button class="rounded bg-white/90 px-2 py-1 text-xs font-medium" @click="openEdit(item)">Edit</button>
          <button class="rounded bg-red-600/90 px-2 py-1 text-xs font-medium text-white" @click="removeImage(item.id)">Delete</button>
        </div>
      </div>
    </div>
    <p v-if="!loading && items.length === 0" class="col-span-full py-8 text-center ui-muted">
      No images found.
    </p>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal v-model="showModal" :title="editingId ? 'Edit image' : 'Upload image'">
    <form @submit.prevent="handleSubmit">
      <FormField label="Image" :required="!editingId">
        <FileInput @change="(file) => (image = file)" />
      </FormField>
      <FormField label="Caption">
        <BaseInput v-model="form.caption" />
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
