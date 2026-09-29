<script setup>
/**
 * The search panel the header's magnifier opens.
 *
 * Until this existed the magnifier was a link to /media/resources — the same
 * destination as the "Resources" nav item two places to its left, and not a
 * search by any reading. It now opens a real query box with live results across
 * solutions, products, articles and resources.
 *
 * Results appear under the input as you type; Enter goes to /search for the full
 * list. The panel is the fast path, the page is the linkable one.
 */
const open = defineModel('open', { type: Boolean, default: false })

const query = ref('')
const results = ref({ total: 0, groups: [] })
const searching = ref(false)
const inputRef = ref(null)

let controller = null
let debounce = null

const hasQuery = computed(() => query.value.trim().length >= 2)

/**
 * Each keystroke cancels the request the previous one started.
 *
 * Without the abort, responses can land out of order and the panel settles on
 * the results for a prefix of what the visitor has typed.
 */
async function search() {
  if (!hasQuery.value) {
    results.value = { total: 0, groups: [] }
    searching.value = false
    return
  }

  controller?.abort()
  controller = new AbortController()
  searching.value = true

  try {
    results.value = await runSearch(query.value, { signal: controller.signal })
  } catch {
    // Aborted by the next keystroke; that request's handler owns the state now.
    return
  } finally {
    // Only the newest request clears the spinner — an aborted one returning
    // late must not.
    if (!controller.signal.aborted) searching.value = false
  }
}

watch(query, () => {
  clearTimeout(debounce)
  // Long enough that a fast typist sends one request per word rather than per
  // letter; short enough to feel live.
  debounce = setTimeout(search, 180)
})

// Same treatment the mobile menu gets: the page behind a full-screen overlay
// must not scroll under it.
watch(open, async (isOpen) => {
  document.body.style.overflow = isOpen ? 'hidden' : ''
  if (!isOpen) return
  await nextTick()
  inputRef.value?.focus()
})

function close() {
  open.value = false
  query.value = ''
  results.value = { total: 0, groups: [] }
  controller?.abort()
  clearTimeout(debounce)
}

// Enter hands off to the full results page, which is bookmarkable and
// shareable in a way the panel is not.
const router = useRouter()

function submit() {
  if (!hasQuery.value) return
  const term = query.value.trim()
  close()
  router.push({ path: '/search', query: { q: term } })
}

function go(link) {
  close()
  router.push(link)
}

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  controller?.abort()
  clearTimeout(debounce)
})
</script>

<template>
  <!--
    Escape is bound on the wrapper rather than on window: the panel only exists
    while it is open, so the listener cannot outlive it or fire for a different
    overlay.
  -->
  <div
    v-if="open"
    class="fixed inset-0 z-[60] flex justify-center bg-steel-950/60 px-4 pt-[15vh] backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-label="Search this site"
    @keydown.esc="close"
  >
    <!-- Backdrop click closes; the panel itself stops the event so a click
         inside it does not. -->
    <div class="absolute inset-0" @click="close"></div>

    <div class="relative w-full max-w-2xl">
      <form class="flex items-center gap-3 border border-line bg-white px-4" @submit.prevent="submit">
        <svg class="h-4 w-4 shrink-0 text-ink/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" />
        </svg>
        <input
          ref="inputRef"
          v-model="query"
          type="search"
          class="h-14 w-full bg-transparent text-[0.95rem] text-ink outline-none placeholder:text-ink/40"
          placeholder="Search products, solutions, articles…"
          aria-label="Search query"
          autocomplete="off"
        />
        <button type="button" class="shrink-0 text-[0.8rem] font-medium text-ink/50 hover:text-ink" @click="close">
          Esc
        </button>
      </form>

      <div
        v-if="hasQuery"
        class="mt-px max-h-[55vh] overflow-y-auto border border-line bg-white"
        aria-live="polite"
      >
        <p v-if="searching && !results.total" class="px-4 py-5 text-[0.85rem] text-ink/55">Searching…</p>

        <p v-else-if="!results.total" class="px-4 py-5 text-[0.85rem] text-ink/55">
          No matches for “{{ query.trim() }}”.
          <NuxtLink to="/contact" class="text-gold-dark underline underline-offset-4" @click="close">
            Ask our team
          </NuxtLink>
          instead.
        </p>

        <template v-else>
          <section v-for="group in results.groups" :key="group.type" :aria-label="group.label">
            <h2 class="border-b border-line bg-smoke px-4 py-2 font-mono text-[0.65rem] font-bold uppercase tracking-code text-ink/50">
              {{ group.label }}
            </h2>
            <button
              v-for="(result, index) in group.results"
              :key="`${group.type}-${index}`"
              type="button"
              class="block w-full border-b border-line px-4 py-3 text-left last:border-0 hover:bg-smoke"
              @click="go(searchResultLink(group.type, result))"
            >
              <span class="flex items-baseline justify-between gap-3">
                <span class="text-[0.88rem] font-semibold text-ink">{{ result.title }}</span>
                <span v-if="result.context" class="shrink-0 font-mono text-[0.65rem] uppercase tracking-code text-ink/40">
                  {{ result.context }}
                </span>
              </span>
              <span v-if="result.snippet" class="mt-1 line-clamp-2 block text-[0.8rem] leading-relaxed text-ink/60">
                {{ result.snippet }}
              </span>
            </button>
          </section>

          <button
            type="button"
            class="block w-full bg-smoke px-4 py-3 text-left text-[0.82rem] font-medium text-gold-dark hover:text-ink"
            @click="submit"
          >
            See all {{ results.total }} results →
          </button>
        </template>
      </div>
    </div>
  </div>
</template>
