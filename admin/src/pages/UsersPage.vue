<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useResource } from '../composables/useResource'
import { useAuthStore } from '../stores/auth'
import PageHeader from '../components/PageHeader.vue'
import Modal from '../components/Modal.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import BaseSelect from '../components/BaseSelect.vue'
import FormField from '../components/FormField.vue'

const { items, loading, list, create, update, destroy } = useResource('users')
const auth = useAuthStore()

const roleOptions = [
  { value: 'admin', label: 'Admin' },
  { value: 'editor', label: 'Editor' },
]

const showModal = ref(false)
const editingId = ref(null)
const form = reactive(emptyForm())

function emptyForm() {
  return { name: '', email: '', password: '', role: 'editor' }
}

onMounted(() => list())

function openCreate() {
  editingId.value = null
  Object.assign(form, emptyForm())
  showModal.value = true
}

function openEdit(user) {
  editingId.value = user.id
  Object.assign(form, { name: user.name, email: user.email, password: '', role: user.role })
  showModal.value = true
}

async function handleSubmit() {
  const payload = { ...form }
  if (editingId.value && !payload.password) delete payload.password

  if (editingId.value) {
    await update(editingId.value, payload)
  } else {
    await create(payload)
  }
  showModal.value = false
  await list()
}

async function removeUser(id) {
  if (!confirm('Delete this admin user?')) return
  await destroy(id)
}
</script>

<template>
  <PageHeader title="Admin Users" description="People who can sign in to this admin console.">
    <template #actions>
      <BaseButton @click="openCreate">New User</BaseButton>
    </template>
  </PageHeader>

  <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white">
    <table class="w-full text-sm">
      <thead class="bg-neutral-50 text-left text-xs uppercase tracking-wide text-neutral-500">
        <tr>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">Email</th>
          <th class="px-4 py-3">Role</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-neutral-100">
        <tr v-for="user in items" :key="user.id" class="hover:bg-neutral-50">
          <td class="px-4 py-3 font-medium text-neutral-900">{{ user.name }}</td>
          <td class="px-4 py-3">{{ user.email }}</td>
          <td class="px-4 py-3 capitalize">{{ user.role }}</td>
          <td class="px-4 py-3 text-right">
            <button class="text-brand-charcoal underline mr-3" @click="openEdit(user)">Edit</button>
            <button
              v-if="user.id !== auth.user?.id"
              class="text-red-600 underline"
              @click="removeUser(user.id)"
            >
              Delete
            </button>
          </td>
        </tr>
        <tr v-if="!loading && items.length === 0">
          <td colspan="4" class="px-4 py-8 text-center text-neutral-400">No users yet.</td>
        </tr>
      </tbody>
    </table>
  </div>

  <Modal v-model="showModal" :title="editingId ? 'Edit user' : 'New user'">
    <form @submit.prevent="handleSubmit">
      <FormField label="Name" required>
        <BaseInput v-model="form.name" required />
      </FormField>
      <FormField label="Email" required>
        <BaseInput v-model="form.email" type="email" required />
      </FormField>
      <FormField label="Password" :required="!editingId" :hint="editingId ? 'Leave blank to keep the current password.' : ''">
        <BaseInput v-model="form.password" type="password" />
      </FormField>
      <FormField label="Role" required>
        <BaseSelect v-model="form.role" :options="roleOptions" />
      </FormField>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="showModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
</template>
