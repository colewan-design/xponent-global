<script setup>
const { data: partners } = await useApiFetch('/partners', { params: { type: 'brand_partner' } })

useSeoMeta({
  title: 'Our Brand Partners',
  description:
    'Xponent Global has earned the trust of some of the most dependable brands in the mining, drilling, and exploration industry.',
})
</script>

<template>
  <div>
    <PageHero
      eyebrow="Solutions"
      title="Our Brand Partners"
      subtitle="The manufacturers and marques whose ranges we carry, stock and support."
    />

    <!-- Statement beside the standing claim, one grey field. -->
    <section class="container-retail py-8 sm:py-10" aria-label="Our standing">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-5">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            The international benchmark.
          </h2>
          <NuxtLink
            to="/contact"
            class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
          >
            Enquire about a brand
          </NuxtLink>
        </div>

        <p class="text-[0.9rem] leading-relaxed text-ink/70 lg:col-span-7">
          Uniglobal Industrial Trading Inc and Xponent Global Ltd aspire to set the international
          benchmark in the industry, becoming a globally recognized leader in mining, drilling, and
          exploration supplies. Our vision is to drive innovation, excellence, and reliability,
          ensuring we remain the preferred choice for companies worldwide. By fostering continuous
          innovation, we earned the trust of some of the most dependable brands in the industry.
        </p>
      </div>
    </section>

    <section class="container-retail pb-10 sm:pb-12" aria-label="Brand partners">
      <SectionHead title="The brands we carry" link-label="Browse solutions" link-to="/solutions" />

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article
          v-for="partner in partners?.data"
          :key="partner.id"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <!-- Marks are artwork on white grounds, so they sit on white cells with
               generous padding rather than being cropped to a tile. -->
          <div class="flex flex-1 items-center justify-center bg-white px-8 py-10">
            <CmsImage
              :src="partner.logo"
              :alt="partner.name"
              loading="lazy"
              class="h-14 w-auto max-w-full object-contain transition-transform duration-500 group-hover:scale-105"
            />
          </div>

          <div class="border-t border-line px-5 py-4">
            <h3 class="text-[0.9rem] font-bold leading-snug text-ink">{{ partner.name }}</h3>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
