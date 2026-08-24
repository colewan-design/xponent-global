<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useResource } from '../composables/useResource'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import FormField from '../components/FormField.vue'

const { items, loading, list, create, update, destroy } = useResource('office-locations')

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())

function emptyForm() {
  return { label: '', address: '', city: '', country: '', sort_order: 0 }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  showModal.value = true
}

function openEdit(location) {
  editingId.value = location.id
  Object.assign(form, {
    label: location.label,
    address: location.address,
    city: location.city,
    country: location.country,
    sort_order: location.sort_order,
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
  await list()
}

async function removeLocation(id) {
  if (!confirm('Delete this office location?')) return
  await destroy(id)
}
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Office Locations" description="Shown on About > Where We Operate and the Contact page.">
    <template #actions>
      <BaseButton @click="openCreate">Add Location</BaseButton>
    </template>
  </PageHeader>

  <div class="ui-table-wrap ui-table-scroll">
    <table class="ui-table">
      <thead>
        <tr>
          <th>Label</th>
          <th>Address</th>
          <th>Country</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="location in items" :key="location.id">
          <td class="cell-strong">{{ location.label }}</td>
          <td>{{ location.address }}, {{ location.city }}</td>
          <td>{{ location.country }}</td>
          <td class="cell-actions">
            <button class="ui-link-btn" @click="openEdit(location)">Edit</button>
            <button class="ui-link-btn ui-link-btn--danger" @click="removeLocation(location.id)">Delete</button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="ui-table-empty">No office locations yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Modal v-model="showModal" :title="editingId ? 'Edit location' : 'Add location'">
    <form @submit.prevent="handleSubmit">
      <FormField label="Label" required hint="e.g. Brisbane, Australia">
        <BaseInput v-model="form.label" required />
      </FormField>
      <FormField label="Address" required>
        <BaseInput v-model="form.address" required />
      </FormField>
      <div class="grid grid-cols-2 gap-4">
        <FormField label="City" required>
          <BaseInput v-model="form.city" required />
        </FormField>
        <FormField label="Country" required>
          <BaseInput v-model="form.country" required />
        </FormField>
      </div>
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
