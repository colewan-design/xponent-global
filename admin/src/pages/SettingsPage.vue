<script setup>
import { onMounted, reactive, ref } from 'vue'
import { api } from '../lib/api'
import PageHeader from '../components/PageHeader.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseTextarea from '../components/BaseTextarea.vue'
import { useToastStore } from '../stores/toast'

const toast = useToastStore()
const saving = ref(false)

const fields = [
  { key: 'company_name', label: 'Company name' },
  { key: 'company_legal_name', label: 'Legal entity name' },
  { key: 'company_tagline', label: 'Tagline' },
  { key: 'contact_email', label: 'Contact email' },
  { key: 'contact_email_alt', label: 'Contact email (secondary)' },
  { key: 'contact_phone', label: 'Contact phone' },
  { key: 'contact_phone_alt', label: 'Contact phone (secondary)' },
  { key: 'hours_weekdays', label: 'Opening hours — Mon to Fri' },
  { key: 'hours_saturday', label: 'Opening hours — Saturday' },
  { key: 'hours_sunday', label: 'Opening hours — Sunday' },
  { key: 'footer_about', label: 'Footer about text', textarea: true },
  { key: 'facebook_url', label: 'Facebook URL' },
  { key: 'instagram_url', label: 'Instagram URL' },
  { key: 'twitter_url', label: 'X / Twitter URL' },
  { key: 'youtube_url', label: 'YouTube URL' },
]

const values = reactive({})

async function load() {
  const { data } = await api.get('/admin/settings')
  for (const field of fields) {
    values[field.key] = data[field.key] ?? ''
  }
}

async function save() {
  saving.value = true
  try {
    await api.put('/admin/settings', { settings: { ...values } })
    toast.success('Settings saved.')
  } catch {
    toast.error('Could not save settings.')
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Settings" description="Site-wide contact details and social links." />

  <form class="apple-card apple-card-body" style="max-width: 640px" @submit.prevent="save">
    <label v-for="field in fields" :key="field.key" class="block">
      <span class="block text-sm font-medium ui-text mb-1">{{ field.label }}</span>
      <BaseTextarea v-if="field.textarea" v-model="values[field.key]" :rows="3" />
      <BaseInput v-else v-model="values[field.key]" />
    </label>

    <BaseButton type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save settings' }}</BaseButton>
  </form>
  </div>
</template>
