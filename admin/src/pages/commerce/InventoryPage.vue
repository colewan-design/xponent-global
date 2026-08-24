<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { api } from '../../lib/api'
import { formatDate, formatQuantity, humanise } from '../../lib/format'
import { useToastStore } from '../../stores/toast'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import FormField from '../../components/FormField.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const toast = useToastStore()

const tab = ref('levels') // levels | movements

const warehouses = ref([])
const products = ref([])

const warehouseOptions = computed(() => [
  { value: '', label: 'All warehouses' },
  ...warehouses.value.map((warehouse) => ({ value: warehouse.id, label: warehouse.name })),
])
const warehousePickerOptions = computed(() =>
  warehouses.value
    .filter((warehouse) => warehouse.is_active)
    .map((warehouse) => ({ value: warehouse.id, label: `${warehouse.name} (${warehouse.code})` })),
)
const productPickerOptions = computed(() =>
  products.value.map((product) => ({ value: product.id, label: `${product.sku} — ${product.name}` })),
)

const typeFilterOptions = [
  { value: '', label: 'All movement types' },
  { value: 'in', label: 'Stock in' },
  { value: 'out', label: 'Stock out' },
  { value: 'adjustment', label: 'Correction' },
]

/* ---------- stock levels ---------- */

const levels = ref([])
const levelsMeta = ref(null)
const levelsLoading = ref(false)
const search = ref('')
const warehouseFilter = ref('')
const lowStockOnly = ref(false)
const levelsPage = ref(1)

async function loadLevels() {
  levelsLoading.value = true
  try {
    const { data } = await api.get('/admin/inventory', {
      params: {
        search: search.value || undefined,
        warehouse_id: warehouseFilter.value || undefined,
        low_stock: lowStockOnly.value ? 1 : undefined,
        page: levelsPage.value,
      },
    })
    levels.value = data.data
    levelsMeta.value = data.meta ?? null
  } finally {
    levelsLoading.value = false
  }
}

let searchTimer = null
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    levelsPage.value = 1
    loadLevels()
  }, 300)
})

watch([warehouseFilter, lowStockOnly], () => {
  levelsPage.value = 1
  loadLevels()
})

/* ---------- movement ledger ---------- */

const movements = ref([])
const movementsMeta = ref(null)
const movementsLoading = ref(false)
const movementType = ref('')
const movementsPage = ref(1)

async function loadMovements() {
  movementsLoading.value = true
  try {
    const { data } = await api.get('/admin/stock-movements', {
      params: {
        warehouse_id: warehouseFilter.value || undefined,
        type: movementType.value || undefined,
        page: movementsPage.value,
      },
    })
    movements.value = data.data
    movementsMeta.value = data.meta ?? null
  } finally {
    movementsLoading.value = false
  }
}

watch([movementType, warehouseFilter], () => {
  movementsPage.value = 1
  if (tab.value === 'movements') loadMovements()
})

watch(tab, (value) => {
  if (value === 'movements' && movements.value.length === 0) loadMovements()
})

/* ---------- posting a movement ---------- */

const adjustModal = ref(false)
const adjustForm = reactive(emptyAdjustForm())

const typeOptions = [
  { value: 'in', label: 'Stock in — goods received' },
  { value: 'out', label: 'Stock out — goods issued' },
  { value: 'adjustment', label: 'Correction — signed, e.g. -40 after a stock take' },
]

const reasonOptions = [
  { value: '', label: 'No reason given' },
  { value: 'purchase', label: 'Purchase' },
  { value: 'sale', label: 'Sale' },
  { value: 'return', label: 'Return' },
  { value: 'damage', label: 'Damage / write-off' },
  { value: 'stock_take', label: 'Stock take' },
  { value: 'correction', label: 'Correction' },
  { value: 'transfer', label: 'Transfer' },
  { value: 'initial', label: 'Opening balance' },
]

function emptyAdjustForm() {
  return {
    product_id: '',
    warehouse_id: '',
    type: 'in',
    quantity: '',
    reason: '',
    reference: '',
    note: '',
  }
}

function openAdjust(row = null) {
  Object.assign(adjustForm, emptyAdjustForm(), {
    // Pre-filled from the row when opened off the table, so "top this line up"
    // does not mean re-picking the product out of the whole catalogue.
    // `||` rather than `??` on purpose: the warehouse filter's "all" value is
    // an empty string, which `??` would treat as a real choice and leave the
    // picker on a value none of its options carry.
    product_id: row?.product_id || productPickerOptions.value[0]?.value || '',
    warehouse_id: row?.warehouse_id || warehouseFilter.value || warehousePickerOptions.value[0]?.value || '',
  })
  adjustModal.value = true
}

async function submitAdjust() {
  try {
    await api.post('/admin/inventory/adjust', {
      ...adjustForm,
      reason: adjustForm.reason || null,
      reference: adjustForm.reference || null,
      note: adjustForm.note || null,
    })
    toast.success('Stock movement posted.')
    adjustModal.value = false
    await Promise.all([loadLevels(), tab.value === 'movements' ? loadMovements() : Promise.resolve()])
  } catch (error) {
    const errors = error.response?.data?.errors
    toast.error(errors ? Object.values(errors).flat().join(' ') : 'Could not post the movement.')
  }
}

/* ---------- row settings ---------- */

const settingsModal = ref(false)
const settingsRow = ref(null)
const settingsForm = reactive({ reorder_level: 0, bin_location: '' })

function openSettings(row) {
  settingsRow.value = row
  Object.assign(settingsForm, {
    reorder_level: row.reorder_level,
    bin_location: row.bin_location ?? '',
  })
  settingsModal.value = true
}

async function submitSettings() {
  try {
    await api.put(`/admin/inventory/${settingsRow.value.id}`, { ...settingsForm })
    toast.success('Saved successfully.')
    settingsModal.value = false
    await loadLevels()
  } catch {
    toast.error('Could not save this stock line.')
  }
}

onMounted(async () => {
  const [warehouseResponse, productResponse] = await Promise.all([
    api.get('/admin/warehouses'),
    api.get('/admin/products', { params: { status: 'active', per_page: 200 } }),
  ])
  warehouses.value = warehouseResponse.data.data
  products.value = productResponse.data.data
  await loadLevels()
})
</script>

<template>
  <div class="page-frame">
    <PageHeader
      title="Inventory"
      section="Commerce"
      icon="boxes"
      description="Stock on hand per warehouse, and the ledger of every movement behind it."
    >
      <template #actions>
        <BaseSelect v-model="warehouseFilter" :options="warehouseOptions" />
        <BaseButton @click="openAdjust()">Post Movement</BaseButton>
      </template>
    </PageHeader>

    <div class="ui-tabs">
      <button class="ui-tab" :class="{ active: tab === 'levels' }" @click="tab = 'levels'">Stock levels</button>
      <button class="ui-tab" :class="{ active: tab === 'movements' }" @click="tab = 'movements'">Movements</button>
    </div>

    <template v-if="tab === 'levels'">
      <div class="page-header-actions" style="margin-bottom: 14px">
        <SearchInput v-model="search" placeholder="Search SKU or product…" />
        <label class="ui-checkbox-row" style="margin: 0">
          <input v-model="lowStockOnly" type="checkbox" />
          Low stock only
        </label>
      </div>

      <div class="ui-table-wrap ui-table-scroll">
        <table class="ui-table">
          <thead>
            <tr>
              <th>SKU</th>
              <th>Product</th>
              <th>Warehouse</th>
              <th>On hand</th>
              <th>Reserved</th>
              <th>Available</th>
              <th>Reorder at</th>
              <th>Bin</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in levels" :key="row.id">
              <td class="mono">{{ row.product_sku }}</td>
              <td class="cell-strong">{{ row.product_name }}</td>
              <td class="ui-text-2">{{ row.warehouse_name }}</td>
              <td>{{ formatQuantity(row.quantity, row.unit) }}</td>
              <td>{{ formatQuantity(row.reserved_quantity) }}</td>
              <td class="cell-strong">{{ formatQuantity(row.available) }}</td>
              <td class="ui-text-2">{{ row.reorder_level || '—' }}</td>
              <td class="ui-text-2">{{ row.bin_location || '—' }}</td>
              <td><StatusBadge :status="row.stock_status" /></td>
              <td class="cell-actions">
                <button class="ui-link-btn" @click="openAdjust(row)">Move</button>
                <button class="ui-link-btn" @click="openSettings(row)">Settings</button>
              </td>
            </tr>
            <tr v-if="!levelsLoading && levels.length === 0">
              <td colspan="10" class="ui-table-empty">
                No stock lines yet — post a movement to bring stock into a warehouse.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        :meta="levelsMeta"
        @change="
          (page) => {
            levelsPage = page
            loadLevels()
          }
        "
      />
    </template>

    <template v-else>
      <div class="page-header-actions" style="margin-bottom: 14px">
        <BaseSelect v-model="movementType" :options="typeFilterOptions" />
      </div>

      <div class="ui-table-wrap ui-table-scroll">
        <table class="ui-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>SKU</th>
              <th>Product</th>
              <th>Warehouse</th>
              <th>Change</th>
              <th>Balance after</th>
              <th>Reason</th>
              <th>Reference</th>
              <th>By</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="movement in movements" :key="movement.id">
              <td class="ui-text-2">{{ formatDate(movement.created_at) }}</td>
              <td class="mono">{{ movement.product_sku }}</td>
              <td>{{ movement.product_name }}</td>
              <td class="ui-text-2">{{ movement.warehouse_name }}</td>
              <td class="cell-strong">
                {{ movement.quantity > 0 ? '+' : '' }}{{ formatQuantity(movement.quantity) }}
              </td>
              <td>{{ formatQuantity(movement.balance_after) }}</td>
              <td class="ui-text-2">{{ humanise(movement.reason) }}</td>
              <td class="ui-text-2">{{ movement.reference || '—' }}</td>
              <td class="ui-text-2">{{ movement.user_name || 'System' }}</td>
            </tr>
            <tr v-if="!movementsLoading && movements.length === 0">
              <td colspan="9" class="ui-table-empty">No stock movements recorded.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        :meta="movementsMeta"
        @change="
          (page) => {
            movementsPage = page
            loadMovements()
          }
        "
      />
    </template>

    <Modal v-model="adjustModal" title="Post stock movement">
      <form @submit.prevent="submitAdjust">
        <FormField label="Product" required>
          <BaseSelect v-model="adjustForm.product_id" :options="productPickerOptions" required />
        </FormField>
        <FormField label="Warehouse" required>
          <BaseSelect v-model="adjustForm.warehouse_id" :options="warehousePickerOptions" required />
        </FormField>

        <div class="grid grid-cols-2 gap-4">
          <FormField label="Type" required>
            <BaseSelect v-model="adjustForm.type" :options="typeOptions" />
          </FormField>
          <FormField
            label="Quantity"
            required
            :hint="adjustForm.type === 'adjustment' ? 'Signed — negative writes stock off.' : 'Positive figure.'"
          >
            <BaseInput v-model="adjustForm.quantity" type="number" step="0.001" required />
          </FormField>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <FormField label="Reason">
            <BaseSelect v-model="adjustForm.reason" :options="reasonOptions" />
          </FormField>
          <FormField label="Reference" hint="PO number, delivery note, order number.">
            <BaseInput v-model="adjustForm.reference" />
          </FormField>
        </div>

        <FormField label="Note">
          <BaseTextarea v-model="adjustForm.note" :rows="2" />
        </FormField>

        <div class="flex justify-end gap-2">
          <BaseButton type="button" variant="secondary" @click="adjustModal = false">Cancel</BaseButton>
          <BaseButton type="submit">Post movement</BaseButton>
        </div>
      </form>
    </Modal>

    <Modal v-model="settingsModal" title="Stock line settings">
      <form @submit.prevent="submitSettings">
        <p v-if="settingsRow" class="ui-text-2" style="margin-bottom: 14px">
          {{ settingsRow.product_sku }} at {{ settingsRow.warehouse_name }}. Quantities are only changed by posting a
          movement.
        </p>
        <FormField label="Reorder level" hint="Flags the line as low once available stock falls to this figure.">
          <BaseInput v-model="settingsForm.reorder_level" type="number" required />
        </FormField>
        <FormField label="Bin location" hint="Where it sits in the warehouse, e.g. B12-03.">
          <BaseInput v-model="settingsForm.bin_location" />
        </FormField>
        <div class="flex justify-end gap-2">
          <BaseButton type="button" variant="secondary" @click="settingsModal = false">Cancel</BaseButton>
          <BaseButton type="submit">Save</BaseButton>
        </div>
      </form>
    </Modal>
  </div>
</template>
