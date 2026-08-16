<script setup>
/**
 * About — retail layout: the shared dark PageHero, then flat square panels on
 * hairline borders over white and smoke fields, with one dark band (core values).
 *
 * The section ids are load-bearing: the header's About sheet links straight to
 * them (`/about#our-vision`, …), so they must not be renamed without updating
 * SiteHeader's nav.
 *
 * Copy comes from the `about` PageContent record as an ordered list of blocks;
 * their order in the CMS is the order destructured here.
 */
const { data: about } = await useApiFetch('/page-content/about')
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })
const { data: affiliations } = await useApiFetch('/partners', { params: { type: 'affiliation' } })

useSeoMeta({
  title: 'About Us',
  description: 'Learn about Xponent Global — our vision, mission, core values, and where we operate.',
})

const sections = computed(() => about.value?.data?.sections ?? [])
const [intro, vision, mission, coreValues, whereWeOperate, affiliationsIntro] = sections.value

/** Core values arrive as `Term: detail` blocks separated by blank lines. */
const values = computed(() =>
  (coreValues?.body ?? '')
    .split('\n\n')
    .filter(Boolean)
    .map((block) => ({
      term: block.split(':')[0],
      detail: block.split(':').slice(1).join(':').trim(),
    })),
)
</script>

<template>
  <div>
    <PageHero
      eyebrow="About Us"
      title="Who We Are"
      subtitle="An international total solutions provider to the mining, drilling, oil and gas, construction and energy sectors."
      :image="intro?.image ?? '/images/page-hero-bg.jpg'"
    />

    <!-- Story beside mission and vision, one grey field. -->
    <section v-if="intro" id="about-xgl" class="container-retail py-8 sm:py-10" aria-label="Our story">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-7">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            {{ intro.heading }}
          </h2>
          <div class="mt-3 space-y-3 text-[0.9rem] leading-relaxed text-ink/70">
            <p v-for="(paragraph, i) in intro.body.split('\n\n')" :key="i">{{ paragraph }}</p>
          </div>
        </div>

        <div class="grid content-start gap-4 lg:col-span-5">
          <div v-if="mission" id="our-mission" class="bg-white p-5">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">{{ mission.heading }}</p>
            <p class="mt-2 text-[0.86rem] leading-relaxed text-ink/80">{{ mission.body }}</p>
          </div>

          <div v-if="vision" id="our-vision" class="bg-white p-5">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">{{ vision.heading }}</p>
            <p class="mt-2 text-[0.86rem] leading-relaxed text-ink/80">{{ vision.body }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- One dark band per page: the core values. -->
    <section
      v-if="values.length"
      id="our-core-values"
      class="container-retail pb-8 sm:pb-10"
      :aria-label="coreValues.heading"
    >
      <div class="bg-steel-950 p-6 sm:p-8">
        <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold tracking-tight text-white">
          {{ coreValues.heading }}
        </h2>

        <div v-reveal:group class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="value in values" :key="value.term" class="border border-white/15 p-5">
            <h3 class="text-[0.98rem] font-bold text-gold">{{ value.term }}</h3>
            <p class="mt-1.5 text-[0.84rem] leading-relaxed text-white/70">{{ value.detail }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Where we operate: the map, then the offices as bordered cells. -->
    <section id="where-we-operate" class="container-retail pb-8 sm:pb-10" aria-label="Where we operate">
      <SectionHead :title="whereWeOperate?.heading ?? 'Where we operate'" link-label="Contact an office" link-to="/contact" />

      <div v-if="whereWeOperate?.image" class="mt-6 border border-line bg-smoke p-6 sm:p-10">
        <CmsImage
          :src="whereWeOperate.image"
          alt="Map of Xponent Global office and warehouse locations"
          loading="lazy"
          class="mx-auto w-full max-w-4xl object-contain"
        />
      </div>

      <!-- Bordered cells rather than a hairline grid: the office count is
           CMS-driven and rarely fills the last row. -->
      <div v-reveal:group class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="location in locations?.data" :key="location.id" class="border border-line p-5">
          <h3 class="text-[0.95rem] font-bold text-ink">{{ location.label }}</h3>
          <address class="mt-2 text-[0.84rem] not-italic leading-relaxed text-ink/65">
            {{ [location.address, location.city, location.country].filter(Boolean).join(', ') }}
          </address>
        </div>
      </div>
    </section>

    <!-- Affiliations. -->
    <section v-if="affiliationsIntro" id="our-affiliations" class="container-retail pb-10 sm:pb-12" aria-label="Our affiliations">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-4">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            {{ affiliationsIntro.heading }}
          </h2>
          <div class="mt-3 space-y-3 text-[0.9rem] leading-relaxed text-ink/70">
            <p v-for="(paragraph, i) in affiliationsIntro.body.split('\n\n')" :key="i">{{ paragraph }}</p>
          </div>
        </div>

        <div v-reveal:group class="grid gap-4 lg:col-span-8 lg:grid-cols-2">
          <article v-for="affiliation in affiliations?.data" :key="affiliation.id" class="bg-white p-5">
            <CmsImage
              :src="affiliation.logo"
              :alt="affiliation.name"
              loading="lazy"
              class="h-12 w-auto max-w-48 object-contain"
            />
            <p class="mt-4 text-[0.84rem] leading-relaxed text-ink/70">{{ affiliation.description }}</p>
          </article>
        </div>
      </div>
    </section>

    <ScheduleVisit />
  </div>
</template>
