<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'
import AppIcon from './AppIcon.vue'

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
    <div v-if="modelValue" class="ui-dialog-backdrop">
      <div
        ref="dialogRef"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        class="ui-dialog"
        :class="{ 'ui-dialog--wide': wide }"
      >
        <div class="ui-dialog-header">
          <h2 :id="titleId" class="ui-dialog-title">{{ title }}</h2>
          <button class="ui-dialog-close" aria-label="Close" @click="$emit('update:modelValue', false)">
            <AppIcon name="x" :size="16" />
          </button>
        </div>
        <div class="ui-dialog-body">
          <slot />
        </div>
      </div>
    </div>
  </Teleport>
</template>
