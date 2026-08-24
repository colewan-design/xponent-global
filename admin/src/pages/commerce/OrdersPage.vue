<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { api } from '../../lib/api'
import { useResource } from '../../composables/useResource'
import { formatDate, formatMoney, formatQuantity } from '../../lib/format'
import { useToastStore } from '../../stores/toast'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import AppIcon from '../../components/AppIcon.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import BaseSelect from '../../components/BaseSelect.vue'
import FormField from '../../components/FormField.vue'
import Pagination from '../../components/Pagination.vue'
import SearchInput from '../../components/SearchInput.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const { items, loading, meta, list, create, update, destroy } = useResource('orders')
const toast = useToastStore()

const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']
const paymentStatuses = ['unpaid', 'partial', 'paid', 'refunded']

const statusOptions = statuses.map((value) => ({ value, label: label(value) }))
const paymentStatusOptions = paymentStatuses.map((value) => ({ value, label: label(value) }))
const statusFilterOptions = [{ value: '', label: 'All statuses' }, ...statusOptions]
const paymentFilterOptions = [{ value: '', label: 'All payment states' }, ...paymentStatusOptions]

function label(value) {
  return value.charAt(0).toUpperCase() + value.slice(1)
}

const warehouses = ref([])
const products = ref([])

const warehouseOptions = computed(() => [
  { value: '', label: 'No warehouse yet' },
  ...warehouses.value
    .filter((warehouse) => warehouse.is_active)
    .map((warehouse) => ({ value: warehouse.id, label: `${warehouse.name} (${warehouse.code})` })),
])
const productOptions = computed(() => [
  { value: '', label: 'Custom line (no product)' },
  ...products.value.map((product) => ({ value: product.id, label: `${product.sku} — ${product.name}` })),
])

/* ---------- listing ---------- */

const search = ref('')
const statusFilter = ref('')
const paymentFilter = ref('')
const page = ref(1)

function load() {
  return list({
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    payment_status: paymentFilter.value || undefined,
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

watch([statusFilter, paymentFilter], () => {
  page.value = 1
  load()
})

function goToPage(newPage) {
  page.value = newPage
  load()
}

/**
 * Quick status change from the table.
 *
 * The server may refuse — moving to a stock-moving status without a warehouse,
 * or shipping more than is on the shelf — so the row is reloaded from the
 * response rather than assuming the change stuck.
 */
async function changeStatus(order, field, value) {
  try {
    const { data } = await api.patch(`/admin/orders/${order.id}/status`, { [field]: value })
    Object.assign(order, data.data)
    toast.success(`${order.order_number} updated.`)
  } catch (error) {
    const errors = error.response?.data?.errors
    toast.error(errors ? Object.values(errors).flat().join(' ') : 'Could not update this order.')
    await load()
  }
}

/* ---------- viewing ---------- */

const viewing = ref(null)

async function openView(order) {
  const { data } = await api.get(`/admin/orders/${order.id}`)
  viewing.value = data.data
}

/* ---------- create / edit ---------- */

const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const form = reactive(emptyForm())
const lines = ref([])

function emptyForm() {
  return {
    warehouse_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_company: '',
    shipping_address: '',
    billing_address: '',
    status: 'pending',
    payment_status: 'unpaid',
    currency: 'USD',
    discount_total: 0,
    tax_rate: 0,
    shipping_total: 0,
    notes: '',
    placed_at: '',
  }
}

function emptyLine() {
  return { product_id: '', sku: '', name: '', unit: 'kg', unit_price: 0, quantity: 1 }
}

// Mirrors Order::recalculateTotals() on the server. This figure is a preview
// only — what gets saved is whatever the server recomputes from the lines.
const preview = computed(() => {
  const subtotal = lines.value.reduce(
    (sum, line) => sum + Number(line.unit_price || 0) * Number(line.quantity || 0),
    0,
  )
  const discount = Math.min(Number(form.discount_total || 0), subtotal)
  const taxable = subtotal - discount
  const tax = taxable * (Number(form.tax_rate || 0) / 100)

  return {
    subtotal,
    discount,
    tax,
    total: taxable + tax + Number(form.shipping_total || 0),
  }
})

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  lines.value = [emptyLine()]
  showModal.value = true
}

async function openEdit(order) {
  const { data } = await api.get(`/admin/orders/${order.id}`)
  const full = data.data

  editingId.value = full.id
  Object.assign(form, {
    warehouse_id: full.warehouse_id ?? '',
    customer_name: full.customer_name,
    customer_email: full.customer_email ?? '',
    customer_phone: full.customer_phone ?? '',
    customer_company: full.customer_company ?? '',
    shipping_address: full.shipping_address ?? '',
    billing_address: full.billing_address ?? '',
    status: full.status,
    payment_status: full.payment_status,
    currency: full.currency,
    discount_total: full.discount_total,
    tax_rate: full.tax_rate,
    shipping_total: full.shipping_total,
    notes: full.notes ?? '',
    placed_at: full.placed_at ? full.placed_at.substring(0, 10) : '',
  })
  lines.value = full.items.map((item) => ({
    product_id: item.product_id ?? '',
    sku: item.sku,
    name: item.name,
    unit: item.unit,
    unit_price: item.unit_price,
    quantity: item.quantity,
  }))

  if (lines.value.length === 0) lines.value = [emptyLine()]

  showModal.value = true
}

/**
 * Picking a product fills the line from the catalogue. The price stays
 * editable afterwards — a negotiated rate is normal, and the order keeps what
 * was actually agreed rather than today's list price.
 */
function onProductChosen(line) {
  const product = products.value.find((candidate) => candidate.id === Number(line.product_id))
  if (!product) return

  line.sku = product.sku
  line.name = product.name
  line.unit = product.unit
  line.unit_price = product.unit_price
}

function addLine() {
  lines.value.push(emptyLine())
}

function removeLine(index) {
  lines.value.splice(index, 1)
  if (lines.value.length === 0) lines.value = [emptyLine()]
}

async function handleSubmit() {
  const payload = {
    ...form,
    warehouse_id: form.warehouse_id || null,
    placed_at: form.placed_at || null,
    items: lines.value.map((line) => ({
      product_id: line.product_id || null,
      sku: line.sku || null,
      name: line.name || null,
      unit: line.unit || null,
      unit_price: Number(line.unit_price || 0),
      quantity: Number(line.quantity || 0),
    })),
  }

  saving.value = true
  try {
    if (editingId.value) {
      await update(editingId.value, payload)
    } else {
      await create(payload)
    }
    showModal.value = false
    await load()
  } catch {
    // useResource has already surfaced the message; the modal stays open with
    // the operator's work in it so they can fix what the server objected to.
  } finally {
    saving.value = false
  }
}

async function removeOrder(order) {
  if (!confirm(`Delete ${order.order_number}? Any stock it holds is returned to the warehouse.`)) return

  await destroy(order.id)
}

onMounted(async () => {
  const [warehouseResponse, productResponse] = await Promise.all([
    api.get('/admin/warehouses'),
    api.get('/admin/products', { params: { status: 'active', per_page: 200 } }),
  ])
  warehouses.value = warehouseResponse.data.data
  products.value = productResponse.data.data
  await load()
})
</script>

<template>
  <div class="page-frame">
    <PageHeader
      title="Orders"
      section="Commerce"
      icon="shopping-cart"
      description="Customer orders. Confirming one reserves stock; shipping it takes the stock out."
    >
      <template #actions>
        <SearchInput v-model="search" placeholder="Search order no., customer…" />
        <BaseSelect v-model="statusFilter" :options="statusFilterOptions" />
        <BaseSelect v-model="paymentFilter" :options="paymentFilterOptions" />
        <BaseButton @click="openCreate">New Order</BaseButton>
      </template>
    </PageHeader>

    <div class="ui-table-wrap ui-table-scroll">
      <table class="ui-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Warehouse</th>
            <th>Lines</th>
            <th>Total</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Placed</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in items" :key="order.id">
            <td class="mono">{{ order.order_number }}</td>
            <td>
              <p class="cell-strong">{{ order.customer_name }}</p>
              <p v-if="order.customer_company" class="ui-text-2">{{ order.customer_company }}</p>
            </td>
            <td class="ui-text-2">{{ order.warehouse_name || '—' }}</td>
            <td>{{ order.items_count ?? 0 }}</td>
            <td class="cell-strong">{{ formatMoney(order.total, order.currency) }}</td>
            <td>
              <select
                class="ui-input"
                style="height: 30px; padding: 0 8px; font-size: 12px"
                :value="order.status"
                @change="changeStatus(order, 'status', $event.target.value)"
              >
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </td>
            <td>
              <select
                class="ui-input"
                style="height: 30px; padding: 0 8px; font-size: 12px"
                :value="order.payment_status"
                @change="changeStatus(order, 'payment_status', $event.target.value)"
              >
                <option v-for="option in paymentStatusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </td>
            <td class="ui-text-2">{{ formatDate(order.placed_at) }}</td>
            <td class="cell-actions">
              <button class="ui-link-btn" @click="openView(order)">View</button>
              <button class="ui-link-btn" @click="openEdit(order)">Edit</button>
              <button class="ui-link-btn ui-link-btn--danger" @click="removeOrder(order)">Delete</button>
            </td>
          </tr>
          <tr v-if="!loading && items.length === 0">
            <td colspan="9" class="ui-table-empty">No orders match these filters.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :meta="meta" @change="goToPage" />

    <!-- Read-only detail -->
    <Modal
      :model-value="!!viewing"
      :title="viewing ? `Order ${viewing.order_number}` : 'Order'"
      wide
      @update:model-value="viewing = null"
    >
      <template v-if="viewing">
        <div class="grid grid-cols-2 gap-4" style="margin-bottom: 18px">
          <div>
            <p class="eyebrow">Customer</p>
            <p class="cell-strong">{{ viewing.customer_name }}</p>
            <p v-if="viewing.customer_company" class="ui-text-2">{{ viewing.customer_company }}</p>
            <p v-if="viewing.customer_email" class="ui-text-2">{{ viewing.customer_email }}</p>
            <p v-if="viewing.customer_phone" class="ui-text-2">{{ viewing.customer_phone }}</p>
          </div>
          <div>
            <p class="eyebrow">Fulfilment</p>
            <p class="ui-text-2">{{ viewing.warehouse_name || 'No warehouse assigned' }}</p>
            <p class="ui-text-2" style="white-space: pre-wrap">{{ viewing.shipping_address || '—' }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2" style="margin-bottom: 18px">
          <StatusBadge :status="viewing.status" />
          <StatusBadge :status="viewing.payment_status" />
          <span class="caption-muted">Placed {{ formatDate(viewing.placed_at) }}</span>
        </div>

        <div class="ui-table-wrap ui-table-scroll" style="margin-bottom: 18px">
          <table class="ui-table">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit price</th>
                <th>Line total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in viewing.items" :key="item.id">
                <td class="mono">{{ item.sku }}</td>
                <td>{{ item.name }}</td>
                <td>{{ formatQuantity(item.quantity, item.unit) }}</td>
                <td>{{ formatMoney(item.unit_price, viewing.currency) }}</td>
                <td class="cell-strong">{{ formatMoney(item.line_total, viewing.currency) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <dl class="inner-card" style="padding: 14px; font-size: 13px">
          <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ formatMoney(viewing.subtotal, viewing.currency) }}</dd></div>
          <div v-if="viewing.discount_total" class="flex justify-between">
            <dt>Discount</dt><dd>−{{ formatMoney(viewing.discount_total, viewing.currency) }}</dd>
          </div>
          <div class="flex justify-between"><dt>Tax ({{ viewing.tax_rate }}%)</dt><dd>{{ formatMoney(viewing.tax_total, viewing.currency) }}</dd></div>
          <div class="flex justify-between"><dt>Shipping</dt><dd>{{ formatMoney(viewing.shipping_total, viewing.currency) }}</dd></div>
          <div class="flex justify-between cell-strong" style="margin-top: 6px">
            <dt>Total</dt><dd>{{ formatMoney(viewing.total, viewing.currency) }}</dd>
          </div>
        </dl>

        <p class="caption-muted" style="margin-top: 12px">
          Stock:
          <template v-if="viewing.stock_deducted_at">deducted {{ formatDate(viewing.stock_deducted_at) }}</template>
          <template v-else-if="viewing.stock_reserved_at">reserved {{ formatDate(viewing.stock_reserved_at) }}</template>
          <template v-else>not yet committed</template>
        </p>

        <p v-if="viewing.notes" class="ui-surface-2" style="margin-top: 12px; padding: 12px; border-radius: 10px; white-space: pre-wrap">
          {{ viewing.notes }}
        </p>
      </template>
    </Modal>

    <!-- Create / edit -->
    <Modal v-model="showModal" :title="editingId ? 'Edit order' : 'New order'" wide>
      <form @submit.prevent="handleSubmit">
        <p class="eyebrow">Customer</p>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Name" required>
            <BaseInput v-model="form.customer_name" required />
          </FormField>
          <FormField label="Company">
            <BaseInput v-model="form.customer_company" />
          </FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Email">
            <BaseInput v-model="form.customer_email" type="email" />
          </FormField>
          <FormField label="Phone">
            <BaseInput v-model="form.customer_phone" />
          </FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Shipping address">
            <BaseTextarea v-model="form.shipping_address" :rows="3" />
          </FormField>
          <FormField label="Billing address">
            <BaseTextarea v-model="form.billing_address" :rows="3" />
          </FormField>
        </div>

        <p class="eyebrow" style="margin-top: 8px">Fulfilment</p>
        <div class="grid grid-cols-2 gap-4">
          <FormField
            label="Warehouse"
            hint="Required once the order is confirmed — it decides where stock comes from."
          >
            <BaseSelect v-model="form.warehouse_id" :options="warehouseOptions" />
          </FormField>
          <FormField label="Date placed">
            <BaseInput v-model="form.placed_at" type="date" />
          </FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <FormField label="Status" required>
            <BaseSelect v-model="form.status" :options="statusOptions" />
          </FormField>
          <FormField label="Payment" required>
            <BaseSelect v-model="form.payment_status" :options="paymentStatusOptions" />
          </FormField>
        </div>

        <p class="eyebrow" style="margin-top: 8px">Lines</p>
        <div class="ui-table-wrap ui-table-scroll" style="margin-bottom: 12px">
          <table class="ui-table">
            <thead>
              <tr>
                <th style="min-width: 220px">Product</th>
                <th style="min-width: 160px">Description</th>
                <th style="width: 110px">Qty</th>
                <th style="width: 120px">Unit price</th>
                <th style="width: 110px">Line total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(line, index) in lines" :key="index">
                <td>
                  <BaseSelect
                    v-model="line.product_id"
                    :options="productOptions"
                    @update:model-value="onProductChosen(line)"
                  />
                </td>
                <td>
                  <BaseInput v-model="line.name" placeholder="Line description" />
                </td>
                <td><BaseInput v-model="line.quantity" type="number" step="0.001" required /></td>
                <td><BaseInput v-model="line.unit_price" type="number" step="0.01" required /></td>
                <td class="cell-strong">
                  {{ formatMoney(Number(line.unit_price || 0) * Number(line.quantity || 0), form.currency) }}
                </td>
                <td class="cell-actions">
                  <button type="button" class="ui-link-btn ui-link-btn--danger" title="Remove line" @click="removeLine(index)">
                    <AppIcon name="trash" :size="15" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <BaseButton type="button" variant="secondary" @click="addLine">
          <AppIcon name="plus" :size="14" /> Add line
        </BaseButton>

        <p class="eyebrow" style="margin-top: 18px">Charges</p>
        <div class="grid grid-cols-4 gap-4">
          <FormField label="Currency">
            <BaseInput v-model="form.currency" />
          </FormField>
          <FormField label="Discount">
            <BaseInput v-model="form.discount_total" type="number" step="0.01" />
          </FormField>
          <FormField label="Tax rate (%)">
            <BaseInput v-model="form.tax_rate" type="number" step="0.01" />
          </FormField>
          <FormField label="Shipping">
            <BaseInput v-model="form.shipping_total" type="number" step="0.01" />
          </FormField>
        </div>

        <dl class="inner-card" style="padding: 14px; font-size: 13px; margin-bottom: 16px">
          <div class="flex justify-between"><dt>Subtotal</dt><dd>{{ formatMoney(preview.subtotal, form.currency) }}</dd></div>
          <div v-if="preview.discount" class="flex justify-between">
            <dt>Discount</dt><dd>−{{ formatMoney(preview.discount, form.currency) }}</dd>
          </div>
          <div class="flex justify-between"><dt>Tax</dt><dd>{{ formatMoney(preview.tax, form.currency) }}</dd></div>
          <div class="flex justify-between"><dt>Shipping</dt><dd>{{ formatMoney(form.shipping_total, form.currency) }}</dd></div>
          <div class="flex justify-between cell-strong" style="margin-top: 6px">
            <dt>Total</dt><dd>{{ formatMoney(preview.total, form.currency) }}</dd>
          </div>
        </dl>

        <FormField label="Internal notes">
          <BaseTextarea v-model="form.notes" :rows="2" />
        </FormField>

        <div class="flex justify-end gap-2">
          <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
          <BaseButton type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save order' }}</BaseButton>
        </div>
      </form>
    </Modal>
  </div>
</template>
