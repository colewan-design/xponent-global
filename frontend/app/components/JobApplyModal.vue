<script setup>
const props = defineProps({
  job: { type: Object, required: true },
})
const emit = defineEmits(['close'])

const name = ref('')
const email = ref('')
const phone = ref('')
const coverLetter = ref('')
const resume = ref(null)
const submitting = ref(false)
const success = ref(false)
const error = ref('')

const dialogRef = ref(null)
const titleId = `apply-modal-title-${Math.random().toString(36).slice(2, 8)}`

function handleFile(event) {
  resume.value = event.target.files[0] ?? null
}

async function submit() {
  error.value = ''
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('name', name.value)
    formData.append('email', email.value)
    formData.append('phone', phone.value)
    formData.append('cover_letter', coverLetter.value)
    if (resume.value) formData.append('resume', resume.value)

    await apiPost(`/jobs/${props.job.slug}/applications`, formData)
    success.value = true
  } catch (e) {
    error.value = extractApiErrorMessage(e)
  } finally {
    submitting.value = false
  }
}

function focusableElements() {
  if (!dialogRef.value) return []
  return Array.from(
    dialogRef.value.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled])'),
  )
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    emit('close')
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

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  focusableElements()[0]?.focus()
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-steel-950/70 p-4 py-10"
    @click.self="emit('close')"
  >
    <div
      ref="dialogRef"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
      class="w-full max-w-lg border border-line bg-white"
    >
      <div class="flex items-start justify-between gap-4 border-b border-line px-6 py-5">
        <div>
          <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Application</p>
          <h2 :id="titleId" class="mt-1 text-[1.05rem] font-bold leading-snug text-ink">{{ job.title }}</h2>
        </div>
        <button
          class="flex h-8 w-8 shrink-0 items-center justify-center border border-line text-ink/60 transition-colors hover:border-ink/40 hover:text-ink"
          aria-label="Close"
          @click="emit('close')"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <div v-if="success" class="px-6 py-10 text-center">
        <h3 class="text-[1rem] font-bold text-ink">Application received.</h3>
        <p class="mx-auto mt-2 max-w-sm text-[0.88rem] leading-relaxed text-ink/70">
          Thanks — we'll be in touch.
        </p>
        <button
          class="mt-5 bg-steel-950 px-6 py-3 text-[0.84rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950"
          @click="emit('close')"
        >
          Close
        </button>
      </div>

      <form v-else class="space-y-4 px-6 py-6" @submit.prevent="submit">
        <label class="block">
          <span class="field-label">Full name *</span>
          <input v-model="name" required type="text" class="field" />
        </label>
        <label class="block">
          <span class="field-label">Email *</span>
          <input v-model="email" required type="email" class="field" />
        </label>
        <label class="block">
          <span class="field-label">Phone</span>
          <input v-model="phone" type="text" class="field" />
        </label>
        <label class="block">
          <span class="field-label">Cover letter</span>
          <textarea v-model="coverLetter" rows="4" class="field"></textarea>
        </label>
        <label class="block">
          <span class="field-label">Resume (PDF or Word) *</span>
          <input
            required
            type="file"
            accept=".pdf,.doc,.docx"
            class="block w-full text-[0.84rem] text-ink/70 file:mr-4 file:border-0 file:bg-smoke file:px-4 file:py-2 file:text-[0.82rem] file:font-semibold file:text-ink hover:file:bg-line"
            @change="handleFile"
          />
        </label>

        <p v-if="error" class="border-l-2 border-red-600 bg-red-50 px-4 py-3 text-[0.86rem] text-red-700">
          {{ error }}
        </p>

        <div class="flex flex-wrap justify-end gap-3 pt-1">
          <button
            type="button"
            class="border border-line px-5 py-2.5 text-[0.84rem] font-semibold text-ink/75 transition-colors hover:border-ink/40 hover:text-ink"
            @click="emit('close')"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="bg-steel-950 px-5 py-2.5 text-[0.84rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950 disabled:opacity-50"
          >
            {{ submitting ? 'Submitting…' : 'Submit Application' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

