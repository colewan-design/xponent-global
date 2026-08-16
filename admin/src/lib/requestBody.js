function containsFile(data) {
  return Object.values(data).some((value) => value instanceof File)
}

export function toRequestBody(data) {
  if (!containsFile(data)) {
    return { body: data, isFormData: false }
  }

  const formData = new FormData()

  for (const [key, value] of Object.entries(data)) {
    if (value === null || value === undefined) {
      continue
    }
    if (typeof value === 'boolean') {
      formData.append(key, value ? '1' : '0')
    } else {
      formData.append(key, value)
    }
  }

  return { body: formData, isFormData: true }
}
