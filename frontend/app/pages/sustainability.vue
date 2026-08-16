<script setup>
/**
 * Sustainability — an intro grey field, then one bordered pillar card per
 * remaining PageContent section, and a closing dark band.
 */
const { data: sustainability } = await useApiFetch('/page-content/sustainability')

useSeoMeta({
  title: 'Sustainability',
  description: 'Our commitment to sustainability across safety, environment, community, people, and innovation.',
})

const sections = computed(() => sustainability.value?.data?.sections ?? [])
const intro = computed(() => sections.value[0])
const pillars = computed(() => sections.value.slice(1))
</script>

<template>
  <div>
    <PageHero
      eyebrow="Sustainability"
      title="How We Operate"
      subtitle="Safety, environment, community, people and innovation — the commitments behind every shipment."
      :image="intro?.image ?? '/images/page-hero-bg.jpg'"
    />

    <section v-if="intro" class="container-retail py-8 sm:py-10" aria-label="Our commitment">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-5">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            {{ intro.heading || 'Operating responsibly.' }}
          </h2>
          <NuxtLink
            to="/about"
            class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
          >
            About Us
          </NuxtLink>
        </div>

        <div class="space-y-3 text-[0.9rem] leading-relaxed text-ink/70 lg:col-span-7">
          <p v-for="(paragraph, i) in intro.body.split('\n\n')" :key="i">{{ paragraph }}</p>
        </div>
      </div>
    </section>

    <section v-if="pillars.length" class="container-retail pb-8 sm:pb-10" aria-label="Our pillars">
      <SectionHead title="Our commitments" link-label="Talk to us" link-to="/contact" />

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="(pillar, i) in pillars"
          :key="i"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <div v-if="pillar.image" class="aspect-16/10 overflow-hidden bg-smoke">
            <CmsImage
              :src="pillar.image"
              alt=""
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>

          <div class="flex flex-1 flex-col p-5">
            <h3 class="text-[0.98rem] font-bold leading-snug text-ink">{{ pillar.heading }}</h3>
            <p class="mt-2 text-[0.84rem] leading-relaxed text-ink/65">{{ pillar.body }}</p>
          </div>
        </article>
      </div>
    </section>

    <ScheduleVisit />
  </div>
</template>
