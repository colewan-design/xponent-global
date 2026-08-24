<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { api } from '../../lib/api'
import { useResource } from '../../composables/useResource'
import { formatMoney, formatQuantity } from '../../lib/format'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import FormField from '../../components/FormField.vue'
import FileInput from '../../components/FileInput.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const { items, loading, meta, list, create, update, destroy } = useResource('products')

const unitOptions = [
  { value: 'kg', label: 'Kilogram (kg)' },
  { value: 'tonne', label: 'Tonne' },
  { value: 'coil', label: 'Coil' },
  { value: 'roll', label: 'Roll' },
  { value: 'metre', label: 'Metre' },
  { value: 'piece', label: 'Piece' },
]

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'discontinued', label: 'Discontinued' },
]

const categories = ref([])
const categoryOptions = computed(() => [
  { value: '', label: 'Uncategorised' },
  ...categories.value.map((category) => ({ value: category.id, label: category.name })),
])
const categoryFilterOptions = computed(() => [
  { value: '', label: 'All categories' },
  ...categories.value.map((category) => ({ value: category.id, label: category.name })),
])
const statusFilterOptions = [{ value: '', label: 'All statuses' }, ...statusOptions]

const search = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')
const page = ref(1)

function load() {
  return list({
    search: search.value || undefined,
    product_category_id: categoryFilter.value || undefined,
    status: statusFilter.value || undefined,
    page: page.value,
  })
}

let searchTimer = null
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    load()
  }, 300)
})

watch([categoryFilter, statusFilter], () => {
  page.value = 1
  load()
})

function goToPage(newPage) {
  page.value = newPage
  load()
}

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())
const imageFile = ref(null)
const currentImage = ref('')

function emptyForm() {
  return {
    product_category_id: '',
    sku: '',
    name: '',
    description: '',
    specification: '',
    unit: 'kg',
    unit_price: 0,
    currency: 'USD',
    weight_kg: '',
    reorder_level: 0,
    status: 'active',
  }
}

onMounted(async () => {
  const { data } = await api.get('/admin/product-categories')
  categories.value = data.data
  await load()
})

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  imageFile.value = null
  currentImage.value = ''
  showModal.value = true
}

function openEdit(product) {
  editingId.value = product.id
  Object.assign(form, {
    product_category_id: product.product_category_id ?? '',
    sku: product.sku,
    name: product.name,
    description: product.description ?? '',
    specification: product.specification ?? '',
    unit: product.unit,
    unit_price: product.unit_price,
    currency: product.currency,
    weight_kg: product.weight_kg ?? '',
    reorder_level: product.reorder_level,
    status: product.status,
  })
  imageFile.value = null
  currentImage.value = product.image ?? ''
  showModal.value = true
}

async function handleSubmit() {
  const payload = {
    ...form,
    // The pickers use '' for "not set", which the API wants as null rather
    // than a zero-length string it would try to look up.
    product_category_id: form.product_category_id || null,
    weight_kg: form.weight_kg === '' ? null : form.weight_kg,
  }

  if (imageFile.value) {
    payload.image = imageFile.value
  }

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }

  showModal.value = false
  await load()
}

async function removeProduct(product) {
  if (
    !confirm(
      `Delete ${product.sku}? This also removes its stock balances and movement history. `
        + 'To retire a product while keeping its records, set its status to discontinued instead.',
    )
  ) {
    return
  }

  await destroy(product.id)
}
</script>

<template>
  <div class="page-frame">
    <PageHeader
      title="Products"
      section="Commerce"
      icon="package"
      description="The stocked, priced catalogue behind orders and inventory."
    >
      <template #actions>
        <SearchInput v-model="search" placeholder="Search SKU, name, spec…" />
        <BaseSelect v-model="categoryFilter" :options="categoryFilterOptions" />
        <BaseSelect v-model="statusFilter" :options="statusFilterOptions" />
        <BaseButton @click="openCreate">New Product</BaseButton>
      </template>
    </PageHeader>

    <div class="ui-table-wrap ui-table-scroll">
      <table class="ui-table">
        <thead>
          <tr>
            <th>SKU</th>
            <th>Product</th>
            <th>Category</th>
            <th>Unit price</th>
            <th>On hand</th>
            <th>Available</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in items" :key="product.id">
            <td class="mono">{{ product.sku }}</td>
            <td>
              <p class="cell-strong">{{ product.name }}</p>
              <p v-if="product.specification" class="ui-text-2">{{ product.specification }}</p>
            </td>
            <td class="ui-text-2">{{ product.category_name || '—' }}</td>
            <td>{{ formatMoney(product.unit_price, product.currency) }} / {{ product.unit }}</td>
            <td>{{ formatQuantity(product.stock_on_hand, product.unit) }}</td>
            <td>{{ formatQuantity(product.stock_available, product.unit) }}</td>
            <td><StatusBadge :status="product.status" /></td>
            <td class="cell-actions">
              <button class="ui-link-btn" @click="openEdit(product)">Edit</button>
              <button class="ui-link-btn ui-link-btn--danger" @click="removeProduct(product)">Delete</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length === 0">
            <td colspan="8" class="ui-table-empty">No products match these filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :meta="meta" @change="goToPage" />

    <Modal v-model="showModal" :title="editingId ? 'Edit product' : 'New product'" wide>
      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-2 gap-4">
          <FormField label="SKU" required hint="Unique. Quoted on orders and stock movements.">
            <BaseInput v-model="form.sku" required />
          </FormField>
          <FormField label="Category">
            <BaseSelect v-model="form.product_category_id" :options="categoryOptions" />
          </FormField>
        </div>

        <FormField label="Name" required>
          <BaseInput v-model="form.name" required />
        </FormField>

        <FormField label="Specification" hint="Diameter, coating, coil size — whatever identifies the grade.">
          <BaseTextarea v-model="form.specification" :rows="2" />
        </FormField>

        <FormField label="Description">
          <BaseTextarea v-model="form.description" :rows="3" />
        </FormField>

        <div class="grid grid-cols-3 gap-4">
          <FormField label="Unit" required>
            <BaseSelect v-model="form.unit" :options="unitOptions" />
          </FormField>
          <FormField label="Unit price" required>
            <BaseInput v-model="form.unit_price" type="number" step="0.01" required />
          </FormField>
          <FormField label="Currency">
            <BaseInput v-model="form.currency" />
          </FormField>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <FormField label="Weight (kg)" hint="Per unit, for freight.">
            <BaseInput v-model="form.weight_kg" type="number" step="0.001" />
          </FormField>
          <FormField label="Reorder level" hint="Low-stock threshold for new warehouse rows.">
            <BaseInput v-model="form.reorder_level" type="number" />
          </FormField>
          <FormField label="Status" required>
            <BaseSelect v-model="form.status" :options="statusOptions" />
          </FormField>
        </div>

        <FormField label="Image">
          <FileInput :current-url="currentImage" @change="(file) => (imageFile = file)" />
        </FormField>

        <div class="flex justify-end gap-2">
          <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
          <BaseButton type="submit">Save</BaseButton>
        </div>
      </form>
    </Modal>
  </div>
</template>
