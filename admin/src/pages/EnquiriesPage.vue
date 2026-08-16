<script setup>
import { onMounted, ref } from 'vue'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseSelect from '../components/BaseSelect.vue'
import StatusBadge from '../components/StatusBadge.vue'
import Pagination from '../components/Pagination.vue'

const { items, loading, meta, list, update, destroy } = useResource('contact-enquiries')

const selected = ref(null)
const statusOptions = [
  { value: 'new', label: 'New' },
  { value: 'contacted', label: 'Contacted' },
  { value: 'closed', label: 'Closed' },
]

function goToPage(page) {
  list({ page })
}

onMounted(() => list())

function openEnquiry(enquiry) {
  selected.value = { ...enquiry }
}

async function saveStatus() {
  await update(selected.value.id, { status: selected.value.status })
  await list()
  selected.value = null
}

async function removeEnquiry(id) {
  if (!confirm('Delete this enquiry?')) return
  await destroy(id)
}
</script>

<template>
  <PageHeader title="Enquiries" description="Contact form submissions from the public site." />

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Enquiry</th>
          <th class="px-4 py-3">Region / Country</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3">Received</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="enquiry in items" :key="enquiry.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3">
            <p class="font-medium text-neutral-900">{{ enquiry.name }}</p>
            <p class="text-neutral-500">{{ enquiry.email }}</p>
          </td>
          <td class="px-4 py-3">{{ enquiry.enquiry_type }}</td>
          <td class="px-4 py-3">{{ enquiry.region }} / {{ enquiry.country }}</td>
          <td class="px-4 py-3"><StatusBadge :status="enquiry.status" /></td>
          <td class="px-4 py-3 text-neutral-500">{{ new Date(enquiry.created_at).toLocaleDateString() }}</td>
          <td class="px-4 py-3 text-right">
            <button class="text-brand-charcoal underline mr-3" @click="openEnquiry(enquiry)">View</button>
            <button class="text-red-600 underline" @click="removeEnquiry(enquiry.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="6" class="px-4 py-8 text-center text-neutral-400">No enquiries yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal :model-value="!!selected" title="Enquiry details" @update:model-value="selected = null">
    <template v-if="selected">
      <dl class="space-y-2 text-sm mb-4">
        <div><dt class="text-neutral-500 inline">Name:</dt> <dd class="inline">{{ selected.name }}</dd></div>
        <div><dt class="text-neutral-500 inline">Email:</dt> <dd class="inline">{{ selected.email }}</dd></div>
        <div v-if="selected.company"><dt class="text-neutral-500 inline">Company:</dt> <dd class="inline">{{ selected.company }}</dd></div>
        <div v-if="selected.phone"><dt class="text-neutral-500 inline">Phone:</dt> <dd class="inline">{{ selected.phone }}</dd></div>
        <div><dt class="text-neutral-500 inline">Enquiring about:</dt> <dd class="inline">{{ selected.enquiry_type }}</dd></div>
        <div><dt class="text-neutral-500 inline">Region / Country:</dt> <dd class="inline">{{ selected.region }} / {{ selected.country }}</dd></div>
        <div>
          <dt class="text-neutral-500 mb-1">Message:</dt>
          <dd class="whitespace-pre-wrap rounded-md bg-neutral-50 p-3">{{ selected.message }}</dd>
        </div>
      </dl>

      <label class="block mb-4">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Status</span>
        <BaseSelect v-model="selected.status" :options="statusOptions" />
      </label>

      <BaseButton @click="saveStatus">Save status</BaseButton>
    </template>
  </Modal>
</template>
