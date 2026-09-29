<script setup>
/**
 * Industries — the sectors served, each routed to the ranges it buys from.
 *
 * The copy is CMS-editable (`/page-content/industries`, seeded by
 * IndustriesPageContentSeeder) but the range links are not: which solution
 * categories a sector draws on is site structure, not editorial, and putting it
 * in the CMS would mean a heading could link at a category that no longer
 * exists. So the map lives here and is keyed on the heading.
 *
 * A heading with no entry in the map renders as copy with no chips rather than
 * breaking — renaming "Mining" in the admin costs its links, not the page.
 */
const { data: content } = await useApiFetch('/page-content/industries', { key: 'page-industries' })

useSeoMeta({
  title: 'Industries',
  description:
    'The sectors Xponent Global supplies — mining, exploration and geotechnical, oil, gas and energy, construction and infrastructure, and agriculture and land management.',
})

/**
 * Heading → the ranges that sector buys from.
 *
 * Every `to` here is a real destination: `/solutions#<slug>` anchors are the
 * category slugs the Solutions page renders its section ids from, and
 * `/products#<slug>` the same for the catalogue. Both are checked in the smoke
 * test for this page; if a slug is renamed in the admin, the anchor silently
 * lands at the top of the page rather than 404ing.
 */
const industryRanges = {
  'Mining': [
    { label: 'Mining and Production Consumables', to: '/solutions#mining-and-production-consumables' },
    { label: 'Mining Camp Facilities', to: '/solutions#mining-camp-facilities' },
    { label: 'Personal Protection Equipment', to: '/solutions/personal-protection-equipment' },
  ],
  'Exploration and Geotechnical': [
    { label: 'Exploration and Geotechnical', to: '/solutions#exploration-and-geotechnical' },
    { label: 'Mining Camp Facilities', to: '/solutions#mining-camp-facilities' },
  ],
  'Oil, Gas and Energy': [
    { label: 'Exploration and Geotechnical', to: '/solutions#exploration-and-geotechnical' },
    { label: 'Personal Protection Equipment', to: '/solutions/personal-protection-equipment' },
  ],
  'Construction and Infrastructure': [
    { label: 'Construction', to: '/solutions#construction' },
    { label: 'Steel Wire Products', to: '/products#steel-wire-products' },
    { label: 'Wire Mesh, Gabions and Fencing', to: '/products#wire-mesh-gabions-and-fencing' },
  ],
  'Agriculture and Land Management': [
    { label: 'Steel Wire Products', to: '/products#steel-wire-products' },
    { label: 'Wire Mesh, Gabions and Fencing', to: '/products#wire-mesh-gabions-and-fencing' },
    { label: 'Fencing Accessories', to: '/products#fencing-accessories' },
  ],
}

const sections = computed(() => content.value?.data?.sections ?? [])

// A leading section with no heading is the page's standfirst, the same
// convention the Sustainability page uses.
const intro = computed(() => (sections.value[0]?.heading ? null : sections.value[0]))
const industries = computed(() =>
  sections.value.filter((section) => Boolean(section.heading)).map((section) => ({
    ...section,
    id: slugify(section.heading),
    ranges: industryRanges[section.heading] ?? [],
  })),
)

function slugify(heading) {
  return String(heading)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
}
</script>

<template>
  <div>
    <PageHero
      eyebrow="Industries"
      title="Sectors We Supply"
      subtitle="One catalogue, one supply chain, specified for the conditions each sector works in — from underground production to civil works and land repair."
    />

    <nav v-if="industries.length" class="border-b border-line bg-smoke" aria-label="Industries">
      <div class="container-retail retail-rail flex items-center gap-1 overflow-x-auto">
        <a
          v-for="industry in industries"
          :key="industry.id"
          :href="`#${industry.id}`"
          class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark"
        >
          {{ industry.heading }}
        </a>
      </div>
    </nav>

    <section v-if="intro?.body" class="container-retail py-8 sm:py-10" aria-label="Overview">
      <p class="max-w-3xl whitespace-pre-line text-[0.95rem] leading-relaxed text-ink/75">
        {{ intro.body }}
      </p>
    </section>

    <section
      v-for="industry in industries"
      :id="industry.id"
      :key="industry.id"
      class="border-t border-line"
      :aria-label="industry.heading"
    >
      <div v-reveal class="container-retail grid gap-6 py-8 sm:py-10 lg:grid-cols-[1fr_1.35fr] lg:gap-12">
        <!--
          The heading tracks its own body on the way down rather than scrolling
          off and leaving an empty left column — these sections are tall, and
          `top-28` clears the two stacked bars of the sticky header.
        -->
        <div class="lg:sticky lg:top-28 lg:self-start">
          <h2 class="text-[clamp(1.5rem,2.6vw,2rem)] font-bold tracking-tight text-ink">
            {{ industry.heading }}
          </h2>
          <CmsImage
            v-if="industry.image"
            :src="industry.image"
            :alt="industry.heading"
            loading="lazy"
            class="mt-4 aspect-16/10 w-full object-cover"
          />
        </div>

        <div>
          <p class="max-w-2xl whitespace-pre-line text-[0.9rem] leading-relaxed text-ink/70">
            {{ industry.body }}
          </p>

          <template v-if="industry.ranges.length">
            <p class="mt-6 font-mono text-[0.68rem] font-bold uppercase tracking-code text-ink/45">
              Ranges supplied
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
              <NuxtLink
                v-for="range in industry.ranges"
                :key="range.to"
                :to="range.to"
                class="border border-line px-3 py-2 text-[0.8rem] text-ink/85 transition-colors hover:border-gold hover:text-gold-dark"
              >
                {{ range.label }}
              </NuxtLink>
            </div>
          </template>

          <NuxtLink
            :to="{ path: '/contact', query: { enquiry: `Enquiry — ${industry.heading}` } }"
            class="mt-6 inline-block text-[0.85rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
          >
            Talk to us about {{ industry.heading.toLowerCase() }}
          </NuxtLink>
        </div>
      </div>
    </section>

    <section v-if="!industries.length" class="container-retail py-16">
      <p class="text-[0.9rem] leading-relaxed text-ink/70">
        Sector information is being updated.
        <NuxtLink to="/contact" class="text-gold-dark underline underline-offset-4">Contact our team</NuxtLink>
        to discuss supply into your operation.
      </p>
    </section>
  </div>
</template>
