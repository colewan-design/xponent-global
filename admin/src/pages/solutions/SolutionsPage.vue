<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useResource } from '../../composables/useResource'
import PageHeader from '../../components/PageHeader.vue'
import Modal from '../../components/Modal.vue'
import BaseButton from '../../components/BaseButton.vue'
import BaseInput from '../../components/BaseInput.vue'
import BaseTextarea from '../../components/BaseTextarea.vue'
import FormField from '../../components/FormField.vue'
import FileInput from '../../components/FileInput.vue'

const categories = useResource('solution-categories')
const solutionItems = useResource('solution-items')

const expanded = ref(null)

const categoryModal = ref(false)
const editingCategoryId = ref(null)
const categoryForm = reactive({ title: '', description: '', sort_order: 0 })
const categoryImage = ref(null)

const itemModal = ref(false)
const editingItemId = ref(null)
const itemForm = reactive({ solution_category_id: null, title: '', description: '', sort_order: 0 })
const itemImage = ref(null)

onMounted(() => categories.list())

function toggleExpand(id) {
  expanded.value = expanded.value === id ? null : id
}

function openCreateCategory() {
  editingCategoryId.value = null
  Object.assign(categoryForm, { title: '', description: '', sort_order: categories.items.value.length + 1 })
  categoryImage.value = null
  categoryModal.value = true
}

function openEditCategory(category) {
  editingCategoryId.value = category.id
  Object.assign(categoryForm, {
    title: category.title,
    description: category.description ?? '',
    sort_order: category.sort_order,
  })
  categoryImage.value = null
  categoryModal.value = true
}

async function submitCategory() {
  const payload = { ...categoryForm }
  if (categoryImage.value) payload.image = categoryImage.value

  if (editingCategoryId.value) {
    await categories.update(editingCategoryId.value, payload)
  } else {
    await categories.create(payload)
  }
  categoryModal.value = false
  await categories.list()
}

async function removeCategory(id) {
  if (!confirm('Delete this category and all its items?')) return
  await categories.destroy(id)
}

function openCreateItem(categoryId) {
  editingItemId.value = null
  Object.assign(itemForm, { solution_category_id: categoryId, title: '', description: '', sort_order: 0 })
  itemImage.value = null
  itemModal.value = true
}

function openEditItem(item) {
  editingItemId.value = item.id
  Object.assign(itemForm, {
    solution_category_id: item.solution_category_id,
    title: item.title,
    description: item.description ?? '',
    sort_order: item.sort_order,
  })
  itemImage.value = null
  itemModal.value = true
}

async function submitItem() {
  const payload = { ...itemForm }
  if (itemImage.value) payload.image = itemImage.value

  if (editingItemId.value) {
    await solutionItems.update(editingItemId.value, payload)
  } else {
    await solutionItems.create(payload)
  }
  itemModal.value = false
  await categories.list()
}

async function removeItem(id) {
  if (!confirm('Delete this item?')) return
  await solutionItems.destroy(id)
  await categories.list()
}

async function moveCategory(index, direction) {
  const list = categories.items.value
  const target = index + direction
  if (target < 0 || target >= list.length) return

  const current = list[index]
  const swapWith = list[target]

  await Promise.all([
    categories.update(current.id, { title: current.title, sort_order: swapWith.sort_order }),
    categories.update(swapWith.id, { title: swapWith.title, sort_order: current.sort_order }),
  ])
  await categories.list()
}

async function moveItem(category, index, direction) {
  const target = index + direction
  if (target < 0 || target >= category.items.length) return

  const current = category.items[index]
  const swapWith = category.items[target]

  await Promise.all([
    solutionItems.update(current.id, {
      solution_category_id: current.solution_category_id,
      title: current.title,
      sort_order: swapWith.sort_order,
    }),
    solutionItems.update(swapWith.id, {
      solution_category_id: swapWith.solution_category_id,
      title: swapWith.title,
      sort_order: current.sort_order,
    }),
  ])
  await categories.list()
}
</script>

<template>
  <div class="page-frame">
  <PageHeader title="Solutions Catalogue" description="Product categories and items shown on the Solutions page.">
    <template #actions>
      <BaseButton @click="openCreateCategory">New Category</BaseButton>
    </template>
  </PageHeader>

  <div class="space-y-3">
    <div v-for="(category, categoryIndex) in categories.items.value" :key="category.id" class="apple-card">
      <div class="flex items-center justify-between px-4 py-3">
        <button class="flex items-center gap-3 text-left" @click="toggleExpand(category.id)">
          <img v-if="category.image" :src="category.image" class="h-10 w-10 rounded object-cover" alt="" />
          <div>
            <p class="font-medium ui-text">{{ category.title }}</p>
            <p class="text-xs ui-text-2">{{ category.items?.length ?? 0 }} items</p>
          </div>
        </button>
        <div class="flex items-center gap-3 text-sm">
          <div class="flex gap-1 text-xs">
            <button
              class="disabled:opacity-30"
              :disabled="categoryIndex === 0"
              title="Move earlier"
              @click="moveCategory(categoryIndex, -1)"
            >
              ↑
            </button>
            <button
              class="disabled:opacity-30"
              :disabled="categoryIndex === categories.items.value.length - 1"
              title="Move later"
              @click="moveCategory(categoryIndex, 1)"
            >
              ↓
            </button>
          </div>
          <button class="underline" @click="openCreateItem(category.id)">Add item</button>
          <button class="ui-link-btn" @click="openEditCategory(category)">Edit</button>
          <button class="ui-link-btn ui-link-btn--danger" @click="removeCategory(category.id)">Delete</button>
        </div>
      </div>

      <div v-if="expanded === category.id" class="border-t border-neutral-100 px-4 py-3">
        <div v-if="!category.items?.length" class="text-sm ui-muted py-2">No items in this category yet.</div>
        <div v-for="(item, itemIndex) in category.items" :key="item.id" class="flex items-center justify-between border-b border-neutral-100 py-2 last:border-0">
          <div class="flex items-center gap-3">
            <img v-if="item.image" :src="item.image" class="h-8 w-8 rounded object-cover" alt="" />
            <p class="text-sm ui-text">{{ item.title }}</p>
          </div>
          <div class="flex items-center gap-3 text-xs">
            <div class="flex gap-1">
              <button
                class="disabled:opacity-30"
                :disabled="itemIndex === 0"
                title="Move earlier"
                @click="moveItem(category, itemIndex, -1)"
              >
                ↑
              </button>
              <button
                class="disabled:opacity-30"
                :disabled="itemIndex === category.items.length - 1"
                title="Move later"
                @click="moveItem(category, itemIndex, 1)"
              >
                ↓
              </button>
            </div>
            <button class="ui-link-btn" @click="openEditItem(item)">Edit</button>
            <button class="ui-link-btn ui-link-btn--danger" @click="removeItem(item.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="!categories.loading.value && categories.items.value.length === 0" class="py-8 text-center ui-muted">
      No solution categories yet.
    </p>
  </div>

  <Modal v-model="categoryModal" :title="editingCategoryId ? 'Edit category' : 'New category'">
    <form @submit.prevent="submitCategory">
      <FormField label="Title" required>
        <BaseInput v-model="categoryForm.title" required />
      </FormField>
      <FormField label="Description">
        <BaseTextarea v-model="categoryForm.description" :rows="3" />
      </FormField>
      <FormField label="Image">
        <FileInput @change="(f) => (categoryImage = f)" />
      </FormField>
      <FormField label="Sort order">
        <BaseInput v-model="categoryForm.sort_order" type="number" />
      </FormField>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="categoryModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>

  <Modal v-model="itemModal" :title="editingItemId ? 'Edit item' : 'New item'">
    <form @submit.prevent="submitItem">
      <FormField label="Title" required>
        <BaseInput v-model="itemForm.title" required />
      </FormField>
      <FormField label="Description">
        <BaseTextarea v-model="itemForm.description" :rows="3" />
      </FormField>
      <FormField label="Image">
        <FileInput @change="(f) => (itemImage = f)" />
      </FormField>
      <FormField label="Sort order">
        <BaseInput v-model="itemForm.sort_order" type="number" />
      </FormField>
      <div class="flex justify-end gap-2">
        <BaseButton type="button" variant="secondary" @click="itemModal = false">Cancel</BaseButton>
        <BaseButton type="submit">Save</BaseButton>
      </div>
    </form>
  </Modal>
  </div>
</template>
