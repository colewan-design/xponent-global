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
  <div class="page-frame">
  <PageHeader title="Enquiries" description="Contact form submissions from the public site." />

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Enquiry</th>
          <th>Region / Country</th>
          <th>Status</th>
          <th>Received</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="enquiry in items" :key="enquiry.id">
          <td>
            <p class="font-medium ui-text">{{ enquiry.name }}</p>
            <p class="ui-text-2">{{ enquiry.email }}</p>
          </td>
          <td>{{ enquiry.enquiry_type }}</td>
          <td>{{ enquiry.region }} / {{ enquiry.country }}</td>
          <td><StatusBadge :status="enquiry.status" /></td>
          <td>{{ new Date(enquiry.created_at).toLocaleDateString() }}</td>
          <td class="cell-actions">
            <button class="ui-link-btn" @click="openEnquiry(enquiry)">View</button>
            <button class="ui-link-btn ui-link-btn--danger" @click="removeEnquiry(enquiry.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="6" class="ui-table-empty">No enquiries yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Pagination :meta="meta" @change="goToPage" />

  <Modal :model-value="!!selected" title="Enquiry details" @update:model-value="selected = null">
    <template v-if="selected">
      <dl class="space-y-2 text-sm mb-4">
        <div><dt class="ui-text-2 inline">Name:</dt> <dd class="inline">{{ selected.name }}</dd></div>
        <div><dt class="ui-text-2 inline">Email:</dt> <dd class="inline">{{ selected.email }}</dd></div>
        <div v-if="selected.company"><dt class="ui-text-2 inline">Company:</dt> <dd class="inline">{{ selected.company }}</dd></div>
        <div v-if="selected.phone"><dt class="ui-text-2 inline">Phone:</dt> <dd class="inline">{{ selected.phone }}</dd></div>
        <div><dt class="ui-text-2 inline">Enquiring about:</dt> <dd class="inline">{{ selected.enquiry_type }}</dd></div>
        <div><dt class="ui-text-2 inline">Region / Country:</dt> <dd class="inline">{{ selected.region }} / {{ selected.country }}</dd></div>
        <div>
          <dt class="ui-text-2 mb-1">Message:</dt>
          <dd class="whitespace-pre-wrap rounded-md ui-surface-2 p-3">{{ selected.message }}</dd>
        </div>
      </dl>

      <label class="block mb-4">
        <span class="block text-sm font-medium ui-text mb-1">Status</span>
        <BaseSelect v-model="selected.status" :options="statusOptions" />
      </label>

      <BaseButton @click="saveStatus">Save status</BaseButton>
    </template>
  </Modal>
  </div>
</template>
