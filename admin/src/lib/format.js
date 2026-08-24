/**
 * Display formatting for the commerce views.
 *
 * Money and quantities are two different problems here. Prices are always two
 * decimals; quantities are not — wire is sold by weight, so a line reads
 * "1,250.5 kg", while gabion baskets read "120", and padding either to a fixed
 * width makes the column harder to scan rather than easier.
 */

export function formatMoney(value, currency = 'USD') {
  const amount = Number(value ?? 0)

  try {
    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency,
      minimumFractionDigits: 2,
    }).format(amount)
  } catch {
    // An unrecognised currency code (a typo in a product record) throws rather
    // than falling back, and a broken table cell is worse than a plain number.
    return `${currency} ${amount.toFixed(2)}`
  }
}

/** Trims trailing zeros: 1250.500 → "1,250.5", 120.000 → "120". */
export function formatQuantity(value, unit = '') {
  const amount = Number(value ?? 0)
  const text = new Intl.NumberFormat(undefined, { maximumFractionDigits: 3 }).format(amount)

  return unit ? `${text} ${unit}` : text
}

export function formatDate(value) {
  if (!value) return '—'

  return new Date(value).toLocaleDateString(undefined, {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

/** "order_shipment" → "Order shipment", for statuses and ledger reasons. */
export function humanise(value) {
  if (!value) return '—'

  const text = String(value).replace(/_/g, ' ')

  return text.charAt(0).toUpperCase() + text.slice(1)
}
