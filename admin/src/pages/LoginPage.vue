<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue'

const email = ref('admin@xponent-global.com')
const password = ref('')
const error = ref('')
const loading = ref(false)

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push(route.query.redirect?.toString() ?? { name: 'dashboard' })
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Unable to log in.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-brand-charcoal px-4">
    <form class="w-full max-w-sm rounded-xl bg-white p-8 shadow-xl" @submit.prevent="handleSubmit">
      <h1 class="text-lg font-semibold text-neutral-900">Xponent Global Admin</h1>
      <p class="text-sm text-neutral-500 mt-1 mb-6">Sign in to manage the site.</p>

      <label class="block mb-4">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Email</span>
        <BaseInput v-model="email" type="email" required />
      </label>
      <label class="block mb-6">
        <span class="block text-sm font-medium text-neutral-700 mb-1">Password</span>
        <BaseInput v-model="password" type="password" required />
      </label>

      <p v-if="error" class="mb-4 text-sm text-red-600">{{ error }}</p>

      <BaseButton type="submit" class="w-full justify-center" :disabled="loading">
        {{ loading ? 'Signing in…' : 'Sign in' }}
      </BaseButton>
    </form>
  </div>
</template>
