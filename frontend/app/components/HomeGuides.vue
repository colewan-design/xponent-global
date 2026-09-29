<script setup>
/**
 * Editorial row — the latest case studies and bulletins, whichever exist.
 *
 * Case studies lead because they are the higher-intent read for a buyer; news
 * fills whatever slots are left. The heading and its link are static, so they
 * stand whether or not anything is published — an empty shelf under a heading
 * that still points at the archive beats a missing section.
 *
 * Three cards, not four: these are the page's best trust-building surface, and
 * at four across a 1440px measure each one got a 16:9 sliver of artwork above a
 * 0.95rem title and three clamped lines of 0.82rem excerpt. At three the plate
 * carries, and the category and title are what the eye lands on.
 */
const props = defineProps({
  caseStudies: { type: Array, default: () => [] },
  news: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const CARD_COUNT = 3
const fallbackCovers = [
  '/cms/seed/gallery-img-08.jpg',
  '/cms/seed/gallery-img-06.jpg',
  '/cms/seed/gallery-img-16.jpg',
]

const cards = computed(() =>
  [
    ...props.caseStudies.map((post) => ({ ...post, eyebrow: 'Case study' })),
    ...props.news.map((post) => ({ ...post, eyebrow: 'Bulletin' })),
  ].slice(0, CARD_COUNT),
)
</script>

<template>
  <section class="bg-smoke py-10 lg:py-11" aria-label="Case studies and news">
    <div class="container-retail">
      <HomeSectionHead
        eyebrow="Insights"
        title="Case studies &amp; bulletins"
        description=""
        link-label="View all resources"
        link-to="/media/resources"
      />

      <div v-if="pending" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
        <div v-for="n in CARD_COUNT" :key="n" class="flex flex-col bg-white">
          <div class="skeleton aspect-3/2 w-full"></div>
          <div class="flex flex-1 flex-col p-5">
            <div class="skeleton h-3 w-24"></div>
            <div class="skeleton mt-4 h-5 w-full"></div>
            <div class="skeleton mt-2 h-5 w-3/4"></div>
          </div>
        </div>
      </div>

      <div v-else class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <NuxtLink
          v-for="(card, index) in cards"
          :key="card.id"
          :to="`/news/${card.slug}`"
          class="group flex flex-col bg-white transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
        >
          <div class="aspect-[3/1] overflow-hidden bg-smoke">
            <CmsImage
              :src="card.cover_image || fallbackCovers[index]"
              alt=""
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>

          <div class="flex flex-1 flex-col p-4">
            <p class="text-[0.58rem] font-bold uppercase tracking-[0.14em] text-gold-dark">
              {{ card.eyebrow }}
            </p>
            <h3
              class="mt-2 text-[0.95rem] font-bold leading-snug text-ink transition-colors group-hover:text-gold-dark"
            >
              {{ card.title }}
            </h3>
            <p v-if="card.excerpt" class="mt-2 line-clamp-1 text-[0.7rem] leading-relaxed text-ink/65">
              {{ card.excerpt }}
            </p>

            <span class="mt-auto pt-4 inline-flex items-center gap-2 text-[0.7rem] font-bold text-ink">
              Read more
              <svg
                width="17"
                height="17"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
                class="transition-transform duration-200 group-hover:translate-x-1"
              >
                <path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
          </div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
