<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  wide: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const dialogRef = ref(null)
const titleId = `modal-title-${Math.random().toString(36).slice(2, 8)}`

function focusableElements() {
  if (!dialogRef.value) return []
  return Array.from(
    dialogRef.value.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled])'),
  )
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    emit('update:modelValue', false)
    return
  }

  if (event.key !== 'Tab') return

  const elements = focusableElements()
  if (!elements.length) return

  const first = elements[0]
  const last = elements[elements.length - 1]

  if (event.shiftKey && document.activeElement === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && document.activeElement === last) {
    event.preventDefault()
    first.focus()
  }
}

watch(
  () => props.modelValue,
  async (open) => {
    if (open) {
      document.addEventListener('keydown', handleKeydown)
      await nextTick()
      focusableElements()[0]?.focus()
    } else {
      document.removeEventListener('keydown', handleKeydown)
    }
  },
)

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto bg-black/40 p-4 py-10">
      <div
        ref="dialogRef"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        class="w-full rounded-xl bg-white shadow-xl"
        :class="wide ? 'max-w-2xl' : 'max-w-lg'"
      >
        <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4">
          <h2 :id="titleId" class="text-base font-semibold text-neutral-900">{{ title }}</h2>
          <button
            class="text-neutral-400 hover:text-neutral-700"
            @click="$emit('update:modelValue', false)"
          >
            ✕
          </button>
        </div>
        <div class="px-5 py-5">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
