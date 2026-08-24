<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useResource } from '../../composables/useResource'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import FormField from '../../components/FormField.vue'

const { items, loading, list, create, update, destroy } = useResource('product-categories')

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())

function emptyForm() {
  return { name: '', description: '', sort_order: 0 }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm(), { sort_order: items.value.length + 1 })
  showModal.value = true
}

function openEdit(category) {
  editingId.value = category.id
  Object.assign(form, {
    name: category.name,
    description: category.description ?? '',
    sort_order: category.sort_order,
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

async function removeCategory(category) {
  const warning = category.products_count
    ? `Delete "${category.name}"? Its ${category.products_count} product(s) will become uncategorised.`
    : `Delete "${category.name}"?`

  if (!confirm(warning)) return

  await destroy(category.id)
}
</script>

<template>
  <div class="page-frame">
    <PageHeader
      title="Product Categories"
      section="Commerce"
      icon="tag"
      description="How the stocked catalogue is grouped. Separate from the marketing Solutions catalogue."
    >
      <template #actions>
        <BaseButton @click="openCreate">New Category</BaseButton>
      </template>
    </PageHeader>

    <div class="ui-table-wrap ui-table-scroll">
      <table class="ui-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Products</th>
            <th>Order</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="category in items" :key="category.id">
            <td class="cell-strong">{{ category.name }}</td>
            <td class="ui-text-2">{{ category.description || '—' }}</td>
            <td>{{ category.products_count ?? 0 }}</td>
            <td>{{ category.sort_order }}</td>
            <td class="cell-actions">
              <button class="ui-link-btn" @click="openEdit(category)">Edit</button>
              <button class="ui-link-btn ui-link-btn--danger" @click="removeCategory(category)">Delete</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length === 0">
            <td colspan="5" class="ui-table-empty">No product categories yet.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal v-model="showModal" :title="editingId ? 'Edit category' : 'New category'">
      <form @submit.prevent="handleSubmit">
        <FormField label="Name" required>
          <BaseInput v-model="form.name" required />
        </FormField>
        <FormField label="Description">
          <BaseTextarea v-model="form.description" :rows="3" />
        </FormField>
        <FormField label="Sort order" hint="Lower numbers appear first in pickers and lists.">
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
