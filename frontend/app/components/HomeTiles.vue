<script setup>
/**
 * The page's main browse surface: the solution catalogue as large photographic
 * cards.
 *
 * Previously four short 15:8 tiles with the category description in 0.78rem grey
 * text *underneath* the artwork, and a snap carousel below `sm` that showed one
 * card at a time. Both worked against the section: the tiles were too slight to
 * read as the primary entry point, and a carousel hides three of the four
 * categories on the screen size where scanning matters most.
 *
 * Now: up to six cards, tall enough to carry their own artwork, with the title
 * and description inside the card over a single bottom-weighted scrim, and a
 * plain responsive grid at every width so nothing is hidden behind a swipe.
 */
defineProps({
  categories: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const CARD_COUNT = 6
</script>

<template>
  <section id="solutions" class="section container-retail" aria-label="Our solutions">
    <HomeSectionHead
      eyebrow="Our solutions"
      title="Solutions for every stage of the program"
      description="From exploration and geotechnical tooling through production consumables, ground support, camp facilities and construction supply."
    />

    <!-- Placeholders match the real grid so the sections below do not jump up
         the page when the categories arrive. -->
    <div
      v-if="pending"
      class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
      aria-hidden="true"
    >
      <div v-for="n in CARD_COUNT" :key="n" class="skeleton min-h-64 w-full"></div>
    </div>

    <div v-else class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <NuxtLink
        v-for="tile in categories.slice(0, CARD_COUNT)"
        :key="tile.id"
        :to="`/solutions#${tile.slug}`"
        class="group relative flex min-h-64 flex-col justify-end overflow-hidden bg-steel-900 p-5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
      >
        <CmsImage
          v-if="tile.image"
          :src="tile.image"
          alt=""
          loading="lazy"
          class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
        />

        <!-- One bottom-weighted scrim rather than the previous dark wash at both
             edges: the artwork stays legible and the type still has a ground. -->
        <span
          class="absolute inset-0 bg-linear-to-t from-steel-950 via-steel-950/70 to-transparent"
          aria-hidden="true"
        ></span>

        <span class="relative">
          <h3 class="text-[1rem] font-bold leading-snug text-white">{{ tile.title }}</h3>
          <!-- No `block` here: `line-clamp-*` works by setting
               `display: -webkit-box`, and a `block` utility alongside it wins in
               the cascade and silently disables the clamp. The CMS descriptions
               run to six or seven lines, which overflowed the card and pushed
               "View range" out of sight. -->
          <span v-if="tile.description" class="mt-2 line-clamp-2 text-[0.72rem] leading-relaxed text-white/72">
            {{ tile.description }}
          </span>
          <span class="mt-3 inline-flex items-center gap-2 text-[0.72rem] font-bold text-gold">
            View category
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
        </span>
      </NuxtLink>
    </div>

  </section>
</template>
