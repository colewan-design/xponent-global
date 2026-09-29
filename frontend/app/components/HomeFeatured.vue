<script setup>
/**
 * A sample of the catalogue — what Xponent actually ships, as product cards.
 *
 * Replaces `HomeDirectory`, which rendered every category as a column of up to
 * seven 0.84rem text rows beside a 36px thumbnail: four columns of dense list
 * copy, around thirty links, and the page's single hardest block to scan. The
 * categories themselves are already the cards above, so repeating them as text
 * lists added length without adding a way in.
 *
 * Items are taken round-robin across categories rather than in catalogue order,
 * so the row samples the whole range instead of showing eight variants of the
 * first category. Thumbnails are `object-contain` on a smoke ground — these are
 * product shots with their own margin, and cropping them clips the product.
 */
const props = defineProps({
  categories: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const CARD_COUNT = 8

const featured = computed(() => {
  // Only items that actually have artwork. Plenty of catalogue entries have none
  // yet, and this is a visual shelf — an entry with no image renders as an empty
  // grey box, which looks like a broken card rather than a product. Those items
  // are still reachable through their category.
  const lists = props.categories.map((category) =>
    (category.items ?? [])
      .filter((item) => item.image)
      .map((item) => ({ ...item, category: category.title, slug: category.slug })),
  )

  // Round-robin: one item from each category, then the next from each, until the
  // row is full or every list is spent.
  //
  // Skipping titles that share a leading run of characters, because the titles
  // are clamped to two lines: several catalogue entries are variants of one
  // product ("CIMC Modular 4-Bedroom Relocatable Accommodation — …"), and two of
  // those side by side truncate to exactly the same string and read as the card
  // having been rendered twice.
  const seen = new Set()
  const fingerprint = (title) => (title ?? '').toLowerCase().replace(/\s+/g, ' ').trim().slice(0, 40)

  const picked = []
  for (let depth = 0; picked.length < CARD_COUNT; depth += 1) {
    const before = picked.length
    for (const list of lists) {
      const item = list[depth]
      if (!item) continue
      const key = fingerprint(item.title)
      if (seen.has(key)) continue
      seen.add(key)
      picked.push(item)
      if (picked.length === CARD_COUNT) break
    }
    if (picked.length === before) break
  }
  return picked
})
</script>

<template>
  <section class="section bg-smoke" aria-label="Featured products">
    <div class="container-retail">
      <HomeSectionHead
        eyebrow="Featured products"
        title="Equipment and consumables, ready to ship"
        description="A sample of the range — tooling, consumables, ground support and camp supply, stocked and sourced against your program."
        link-label="View all inventory"
        link-to="/solutions"
      />

      <div v-if="pending" class="mt-6 grid gap-3 grid-cols-2 lg:grid-cols-4" aria-hidden="true">
        <div v-for="n in CARD_COUNT" :key="n" class="bg-white p-3">
          <div class="skeleton aspect-4/3 w-full"></div>
          <div class="skeleton mt-4 h-3 w-24"></div>
          <div class="skeleton mt-2.5 h-4 w-full"></div>
        </div>
      </div>

      <div v-else class="mt-6 grid gap-3 grid-cols-2 lg:grid-cols-4">
        <NuxtLink
          v-for="item in featured"
          :key="item.id"
          :to="`/solutions#${item.slug}`"
          class="group flex flex-col bg-white p-3 transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
        >
          <span class="flex aspect-[16/7] items-center justify-center overflow-hidden bg-smoke">
            <CmsImage
              v-if="item.image"
              :src="item.image"
              alt=""
              loading="lazy"
              class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-105"
            />
          </span>

          <!-- Both clamped: category names run from "Construction" to
               "Exploration and Geotechnical Products and Solutions", and letting
               them wrap freely gave every card in the row a different height. -->
          <span class="mt-3 line-clamp-1 text-[0.55rem] font-bold uppercase tracking-[0.12em] text-ink/45">
            {{ item.category }}
          </span>
          <h3
            class="mt-1 line-clamp-2 text-[0.78rem] font-bold leading-snug text-ink transition-colors group-hover:text-gold-dark"
          >
            {{ item.title }}
          </h3>
        </NuxtLink>
      </div>

      <div class="mt-5 flex flex-wrap justify-center gap-3">
        <NuxtLink to="/contact" class="btn btn-primary">Request availability&nbsp; →</NuxtLink>
        <NuxtLink to="/contact" class="btn btn-secondary">Ask for pricing</NuxtLink>
      </div>
    </div>
  </section>
</template>
