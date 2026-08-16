/**
 * Laravel validation failures return { message, errors: { field: [msg, ...] } }.
 * Surface the specific field messages instead of the generic top-level message.
 */
export function extractApiErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
  const data = error?.data
  if (data?.errors) {
    return Object.values(data.errors).flat().join(' ')
  }
  return data?.message ?? fallback
}
