<script setup>
import { onMounted, reactive, ref } from 'vue'
import { api } from '../lib/api'
import PageHeader from '../components/PageHeader.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseTextarea from '../components/BaseTextarea.vue'
import { useToastStore } from '../stores/toast'

const toast = useToastStore()

const pages = [
  { value: 'home', label: 'Home' },
  { value: 'about', label: 'About Us' },
  { value: 'sustainability', label: 'Sustainability' },
  { value: 'careers', label: 'Careers' },
  { value: 'resources', label: 'Resources' },
]

const activePage = ref('home')
const byPage = reactive({})
const saving = ref(false)

async function load() {
  const { data } = await api.get('/admin/page-content')
  for (const page of pages) {
    const record = data.data.find((item) => item.page === page.value)
    byPage[page.value] = record ? [...record.sections] : []
  }
}

function addSection() {
  byPage[activePage.value].push({ heading: '', body: '', image: '' })
}

function removeSection(index) {
  byPage[activePage.value].splice(index, 1)
}

async function save() {
  saving.value = true
  try {
    await api.put(`/admin/page-content/${activePage.value}`, { sections: byPage[activePage.value] })
    toast.success('Page content saved.')
  } catch {
    toast.error('Could not save page content.')
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <PageHeader title="Page Content" description="Editable copy blocks for About, Sustainability, Home, and Careers." />

  <div class="mb-4 flex gap-2">
    <button
      v-for="page in pages"
      :key="page.value"
      class="rounded-md px-3 py-1.5 text-sm font-medium"
      :class="activePage === page.value ? 'bg-brand-charcoal text-white' : 'bg-white border border-neutral-300 text-neutral-600'"
      @click="activePage = page.value"
    >
      {{ page.label }}
    </button>
  </div>

  <div v-if="byPage[activePage]" class="space-y-4">
    <div
      v-for="(section, index) in byPage[activePage]"
      :key="index"
      class="rounded-xl border border-neutral-200 bg-white p-4"
    >
      <div class="mb-3 flex items-center justify-between">
        <p class="text-sm font-medium text-neutral-500">Section {{ index + 1 }}</p>
        <button class="text-xs text-red-600 underline" @click="removeSection(index)">Remove</button>
      </div>
      <label class="block mb-3">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Heading</span>
        <BaseInput v-model="section.heading" />
      </label>
      <label class="block mb-3">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Body</span>
        <BaseTextarea v-model="section.body" :rows="4" />
      </label>
      <label class="block">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Image path</span>
        <BaseInput v-model="section.image" placeholder="seed/gallery-img-01.jpg" />
      </label>
    </div>

    <div class="flex items-center justify-between">
      <button class="text-sm underline" @click="addSection">+ Add section</button>
      <BaseButton :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save changes' }}</BaseButton>
    </div>
  </div>
</template>
