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

const { items, loading, meta, list, create, update, destroy } = useResource('posts')

const typeOptions = [
  { value: 'news', label: 'News' },
  { value: 'case_study', label: 'Case Study' },
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
const coverImageFile = ref(null)

function emptyForm() {
  return {
    type: 'news',
    title: '',
    excerpt: '',
    body: '',
    published_at: '',
    published: true,
  }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  coverImageFile.value = null
  showModal.value = true
}

function openEdit(post) {
  editingId.value = post.id
  Object.assign(form, {
    type: post.type,
    title: post.title,
    excerpt: post.excerpt ?? '',
    body: post.body,
    published_at: post.published_at ? post.published_at.substring(0, 10) : '',
    published: post.published,
  })
  coverImageFile.value = null
  showModal.value = true
}

async function handleSubmit() {
  const payload = { ...form }
  if (coverImageFile.value) {
    payload.cover_image = coverImageFile.value
  }

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }
  showModal.value = false
  await list({ search: search.value })
}

async function removePost(id) {
  if (!confirm('Delete this post?')) return
  await destroy(id)
}
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Posts & Case Studies" description="News & Insights and Case Studies shown across the site.">
    <template #actions>
      <SearchInput v-model="search" placeholder="Search posts…" />
      <BaseButton @click="openCreate">New Post</BaseButton>
    </template>
  </PageHeader>

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Published</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="post in items" :key="post.id">
          <td class="cell-strong">{{ post.title }}</td>
          <td class="capitalize">{{ post.type.replace('_', ' ') }}</td>
          <td>{{ post.published ? 'Yes' : 'No' }}</td>
          <td class="cell-actions">
            <button class="ui-link-btn" @click="openEdit(post)">Edit</button>
            <button class="ui-link-btn ui-link-btn--danger" @click="removePost(post.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="ui-table-empty">No posts found.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal v-model="showModal" :title="editingId ? 'Edit post' : 'New post'" wide>
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-2 gap-4">
        <FormField label="Type" required>
          <BaseSelect v-model="form.type" :options="typeOptions" />
        </FormField>
        <FormField label="Published date">
          <BaseInput v-model="form.published_at" type="date" />
        </FormField>
      </div>
      <FormField label="Title" required>
        <BaseInput v-model="form.title" required />
      </FormField>
      <FormField label="Excerpt" hint="Short summary shown in listings.">
        <BaseTextarea v-model="form.excerpt" :rows="2" />
      </FormField>
      <FormField label="Body" required>
        <BaseTextarea v-model="form.body" :rows="6" required />
      </FormField>
      <FormField label="Cover image">
        <FileInput @change="(file) => (coverImageFile = file)" />
      </FormField>
      <label class="flex items-center gap-2 mb-4 text-sm ui-text">
        <input v-model="form.published" type="checkbox" class="rounded border-neutral-300" />
        Published
      </label>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
  </div>
</template>
