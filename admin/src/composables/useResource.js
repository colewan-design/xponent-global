import { ref } from 'vue'
import { api } from '../lib/api'
import { toRequestBody } from '../lib/requestBody'
import { useToastStore } from '../stores/toast'

export function useResource(endpoint) {
  const items = ref([])
  const loading = ref(false)
  const meta = ref(null)
  const toast = useToastStore()

  async function list(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get(`/admin/${endpoint}`, { params })
      items.value = data.data
      meta.value = data.meta ?? null
      return data
    } finally {
      loading.value = false
    }
  }

  async function create(payload) {
    const { body, isFormData } = toRequestBody(payload)
    try {
      const { data } = await api.post(`/admin/${endpoint}`, body, {
        headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : undefined,
      })
      toast.success('Created successfully.')
      return data.data
    } catch (error) {
      toast.error(extractError(error))
      throw error
    }
  }

  async function update(id, payload) {
    const { body, isFormData } = toRequestBody(payload)
    try {
      let data
      if (isFormData) {
        body.append('_method', 'PUT')
        ;({ data } = await api.post(`/admin/${endpoint}/${id}`, body, {
          headers: { 'Content-Type': 'multipart/form-data' },
        }))
      } else {
        ;({ data } = await api.put(`/admin/${endpoint}/${id}`, body))
      }
      toast.success('Saved successfully.')
      return data.data
    } catch (error) {
      toast.error(extractError(error))
      throw error
    }
  }

  async function destroy(id) {
    try {
      await api.delete(`/admin/${endpoint}/${id}`)
      items.value = items.value.filter((item) => item.id !== id)
      toast.success('Deleted successfully.')
    } catch (error) {
      toast.error(extractError(error))
      throw error
    }
  }

  return { items, loading, meta, list, create, update, destroy }
}

function extractError(error) {
  const response = error.response?.data
  if (!response) return 'Something went wrong.'
  if (response.errors) {
    return Object.values(response.errors).flat().join(' ')
  }
  return response.message ?? 'Something went wrong.'
}
