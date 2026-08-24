<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useResource } from '../../composables/useResource'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import FormField from '../../components/FormField.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const { items, loading, list, create, update, destroy } = useResource('warehouses')

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())

function emptyForm() {
  return { name: '', code: '', address: '', city: '', country: '', is_active: true }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  showModal.value = true
}

function openEdit(warehouse) {
  editingId.value = warehouse.id
  Object.assign(form, {
    name: warehouse.name,
    code: warehouse.code,
    address: warehouse.address ?? '',
    city: warehouse.city ?? '',
    country: warehouse.country ?? '',
    is_active: warehouse.is_active,
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

async function removeWarehouse(warehouse) {
  // The API refuses outright once a warehouse has stock history; this only
  // saves the round trip on the obvious cases.
  if (!confirm(`Delete "${warehouse.name}"? Warehouses with stock history cannot be deleted — mark them inactive instead.`)) return

  await destroy(warehouse.id)
}
</script>

<template>
  <div class="page-frame">
    <PageHeader
      title="Warehouses"
      section="Commerce"
      icon="warehouse"
      description="Stock-holding locations. Orders are fulfilled from one of these."
    >
      <template #actions>
        <BaseButton @click="openCreate">New Warehouse</BaseButton>
      </template>
    </PageHeader>

    <div class="ui-table-wrap ui-table-scroll">
      <table class="ui-table">
        <thead>
          <tr>
            <th>Warehouse</th>
            <th>Code</th>
            <th>Location</th>
            <th>Stocked lines</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="warehouse in items" :key="warehouse.id">
            <td class="cell-strong">{{ warehouse.name }}</td>
            <td class="mono">{{ warehouse.code }}</td>
            <td class="ui-text-2">
              {{ [warehouse.city, warehouse.country].filter(Boolean).join(', ') || '—' }}
            </td>
            <td>{{ warehouse.inventory_items_count ?? 0 }}</td>
            <td><StatusBadge :status="warehouse.is_active ? 'active' : 'inactive'" /></td>
            <td class="cell-actions">
              <button class="ui-link-btn" @click="openEdit(warehouse)">Edit</button>
              <button class="ui-link-btn ui-link-btn--danger" @click="removeWarehouse(warehouse)">Delete</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length === 0">
            <td colspan="6" class="ui-table-empty">No warehouses yet.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal v-model="showModal" :title="editingId ? 'Edit warehouse' : 'New warehouse'">
      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Name" required>
            <BaseInput v-model="form.name" required />
          </FormField>
          <FormField label="Code" required hint="Short prefix used on stock references, e.g. BNE.">
            <BaseInput v-model="form.code" required />
          </FormField>
        </div>
        <FormField label="Address">
          <BaseTextarea v-model="form.address" :rows="2" />
        </FormField>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="City">
            <BaseInput v-model="form.city" />
          </FormField>
          <FormField label="Country">
            <BaseInput v-model="form.country" />
          </FormField>
        </div>
        <label class="ui-checkbox-row">
          <input v-model="form.is_active" type="checkbox" />
          Active — inactive warehouses stay out of order and stock pickers
        </label>
        <div class="flex justify-end gap-2">
          <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
          <BaseButton type="submit">Save</BaseButton>
        </div>
      </form>
    </Modal>
  </div>
</template>
