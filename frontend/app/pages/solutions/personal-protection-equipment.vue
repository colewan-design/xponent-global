<script setup>
const { data: response } = await useApiFetch('/solutions/personal-protection-equipment')
const category = computed(() => response.value?.data)

useSeoMeta({
  title: 'Personal Protection Equipment',
  description: 'PPE ranges supplied to keep crews safe across mining, drilling, and construction sites.',
})
</script>

<template>
  <div>
    <PageHero
      eyebrow="Solutions"
      title="Personal Protection Equipment"
      :subtitle="category?.description"
      :image="category?.image ?? '/images/page-hero-bg.jpg'"
    />

    <section class="container-retail py-8 sm:py-10" aria-label="PPE range">
      <SectionHead title="Crew safety" link-label="Enquire about PPE" link-to="/contact" />
      <p class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">
        Rated protective equipment held in stock and supplied alongside the tooling it works with.
      </p>

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="item in category?.items"
          :key="item.id"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <div v-if="item.image" class="aspect-16/10 overflow-hidden bg-smoke">
            <CmsImage
              :src="item.image"
              :alt="item.title"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>

          <div class="flex flex-1 flex-col p-5">
            <h3 class="text-[0.98rem] font-bold leading-snug text-ink">{{ item.title }}</h3>
            <p class="mt-2 text-[0.84rem] leading-relaxed text-ink/65">{{ item.description }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="container-retail pb-10 sm:pb-12" aria-label="Enquire">
      <div class="bg-steel-950 p-6 sm:p-8">
        <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold tracking-tight text-white">
          Need a PPE package for your crew?
        </h2>
        <p class="mt-2 max-w-2xl text-[0.88rem] leading-relaxed text-white/70">
          Tell us the headcount, the site conditions, and the standards you work to — we'll come back
          with options and lead times.
        </p>
        <NuxtLink
          to="/contact"
          class="mt-4 inline-block bg-gold px-6 py-3 text-[0.84rem] font-semibold text-steel-950 transition-colors hover:bg-gold-light"
        >
          Send an Enquiry
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
