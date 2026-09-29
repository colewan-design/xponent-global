<script setup>
/**
 * Full search results for `?q=`.
 *
 * The header panel is the fast path; this is the one you can bookmark, share or
 * land on from a search engine. Both read the same API and the same
 * result → URL map, so a result never leads somewhere different depending on
 * where it was rendered.
 *
 * `useApiFetch` rather than the panel's `$fetch`, because this page renders on
 * the server: the results have to be in the HTML, not fetched after hydration.
 */
const route = useRoute()
const router = useRouter()

const term = computed(() => String(route.query.q ?? '').trim())

const { data: response, status } = await useApiFetch('/search', {
  key: 'site-search',
  // `query`, not `params`: re-runs the request when the visitor searches again
  // from this page rather than serving the first term's results forever.
  query: { q: term },
})

const results = computed(() => response.value?.data ?? { total: 0, groups: [] })

// Noindex: a results page is thin, infinitely variable and exactly what search
// engines ask you not to submit. The page is for visitors, not crawlers.
useSeoMeta({
  title: () => (term.value ? `Search: ${term.value}` : 'Search'),
  description: 'Search products, solutions, articles and resources across Xponent Global.',
  robots: 'noindex, follow',
})

const input = ref(term.value)

// Keeps the box in step when the visitor arrives via back/forward or a link.
watch(term, (value) => {
  input.value = value
})

function submit() {
  const next = input.value.trim()
  if (next === term.value) return
  router.push(next ? { path: '/search', query: { q: next } } : { path: '/search' })
}
</script>

<template>
  <div>
    <PageHero
      eyebrow="Search"
      :title="term ? `Results for “${term}”` : 'Search'"
      subtitle="Products, solution ranges, articles and downloadable resources."
    />

    <section class="container-retail py-8 sm:py-10">
      <form class="flex max-w-2xl items-center gap-3 border border-line px-4" @submit.prevent="submit">
        <svg class="h-4 w-4 shrink-0 text-ink/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" />
        </svg>
        <input
          v-model="input"
          type="search"
          class="h-12 w-full bg-transparent text-[0.9rem] text-ink outline-none placeholder:text-ink/40"
          placeholder="Search products, solutions, articles…"
          aria-label="Search query"
        />
        <button type="submit" class="shrink-0 text-[0.8rem] font-bold text-gold-dark hover:text-ink">Search</button>
      </form>

      <p v-if="term" class="mt-4 text-[0.85rem] text-ink/55" aria-live="polite">
        <template v-if="status === 'pending'">Searching…</template>
        <template v-else>
          {{ results.total }} {{ results.total === 1 ? 'result' : 'results' }} for “{{ term }}”
        </template>
      </p>
    </section>

    <section v-if="term && !results.total && status !== 'pending'" class="container-retail pb-12">
      <p class="max-w-2xl text-[0.9rem] leading-relaxed text-ink/70">
        Nothing matched that. Try a part number, a material or a range name — or
        <NuxtLink to="/contact" class="text-gold-dark underline underline-offset-4">ask our team directly</NuxtLink>,
        since a good deal of what we supply is made to a customer's own specification and never
        appears in a catalogue.
      </p>
    </section>

    <section
      v-for="group in results.groups"
      :key="group.type"
      class="border-t border-line"
      :aria-label="group.label"
    >
      <div class="container-retail py-8">
        <h2 class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-ink/45">
          {{ group.label }}
        </h2>

        <!-- Capped rather than full-bleed: at container width the context label
             on the right ends up an inch of empty space away from the title it
             belongs to. -->
        <ul class="mt-4 max-w-5xl divide-y divide-line border-y border-line">
          <li v-for="(result, index) in group.results" :key="`${group.type}-${index}`">
            <NuxtLink
              :to="searchResultLink(group.type, result)"
              class="group block py-4 transition-colors hover:bg-smoke"
            >
              <div class="flex items-baseline justify-between gap-4">
                <h3 class="text-[0.95rem] font-bold text-ink group-hover:text-gold-dark">
                  {{ result.title }}
                </h3>
                <span v-if="result.context" class="shrink-0 font-mono text-[0.65rem] uppercase tracking-code text-ink/40">
                  {{ result.context }}
                </span>
              </div>
              <p v-if="result.snippet" class="mt-1.5 max-w-3xl text-[0.84rem] leading-relaxed text-ink/65">
                {{ result.snippet }}
              </p>
            </NuxtLink>
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>
