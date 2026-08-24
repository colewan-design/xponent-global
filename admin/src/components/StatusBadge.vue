<script setup>
const props = defineProps({
  status: { type: String, required: true },
})

// Maps each domain status onto one of theme.css's semantic pill tones, so a
// palette change lands here once rather than per status.
const tones = {
  new: 'info',
  contacted: 'warning',
  closed: 'neutral',
  open: 'success',
  subscribed: 'success',
  unsubscribed: 'neutral',
  reviewed: 'warning',
  rejected: 'danger',
  hired: 'success',

  // Orders. "new" above already means an unworked enquiry, so an order's first
  // state is "pending" and shares the same info tone.
  pending: 'info',
  confirmed: 'brand',
  processing: 'warning',
  shipped: 'brand',
  delivered: 'success',
  cancelled: 'danger',

  // Payment.
  unpaid: 'danger',
  partial: 'warning',
  paid: 'success',
  refunded: 'neutral',

  // Products.
  active: 'success',
  inactive: 'neutral',
  discontinued: 'danger',

  // Stock. Amber rather than red for low: it is a reorder prompt, not a
  // failure — running out is the failure.
  in_stock: 'success',
  low_stock: 'warning',
  out_of_stock: 'danger',
}
</script>

<template>
  <span class="status-pill" :class="`status-pill--${tones[props.status] ?? 'neutral'}`">
    {{ props.status.replace(/_/g, ' ') }}
  </span>
</template>
