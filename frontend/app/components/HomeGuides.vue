<script setup>
/**
 * Editorial row — the latest case studies and bulletins, whichever exist.
 *
 * Case studies lead because they are the higher-intent read for a buyer; news
 * fills whatever slots are left. The heading and its "All resources" link are
 * static, so they stand whether or not anything is published — an empty shelf
 * under a heading that still points at the archive beats a missing section.
 */
const props = defineProps({
  caseStudies: { type: Array, default: () => [] },
  news: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const cards = computed(() =>
  [
    ...props.caseStudies.map((post) => ({ ...post, eyebrow: 'Case study' })),
    ...props.news.map((post) => ({ ...post, eyebrow: 'Bulletin' })),
  ].slice(0, 4),
)
</script>

<template>
  <section class="container-retail pb-10 sm:pb-12" aria-label="Case studies and news">
    <SectionHead title="Case studies &amp; bulletins" link-label="All resources" link-to="/media/resources" />

    <div v-if="pending" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-hidden="true">
      <div v-for="n in 4" :key="n" class="flex flex-col border border-line">
        <div class="skeleton aspect-video w-full"></div>
        <div class="flex flex-1 flex-col p-4">
          <div class="skeleton h-2.5 w-20"></div>
          <div class="skeleton mt-2 h-4 w-full"></div>
          <div class="skeleton mt-1.5 h-4 w-3/4"></div>
          <div class="skeleton mt-3 h-3 w-full"></div>
          <div class="skeleton mt-1.5 h-3 w-5/6"></div>
        </div>
      </div>
    </div>

    <div v-else v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <NuxtLink
        v-for="card in cards"
        :key="card.id"
        :to="`/news/${card.slug}`"
        class="group flex flex-col border border-line transition-colors hover:border-gold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
      >
        <div class="aspect-video overflow-hidden bg-smoke">
          <CmsImage
            v-if="card.cover_image"
            :src="card.cover_image"
            alt=""
            loading="lazy"
            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
          />
        </div>

        <div class="flex flex-1 flex-col p-4">
          <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">{{ card.eyebrow }}</p>
          <h3 class="mt-2 text-[0.95rem] font-bold leading-snug text-ink group-hover:text-gold-dark">
            {{ card.title }}
          </h3>
          <p v-if="card.excerpt" class="mt-2 line-clamp-3 text-[0.82rem] leading-relaxed text-ink/65">
            {{ card.excerpt }}
          </p>
        </div>
      </NuxtLink>
    </div>
  </section>
</template>
