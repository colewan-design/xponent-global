<script setup>
/**
 * Products — the stocked SKU catalogue, one band per category.
 *
 * Deliberately not the same page as Solutions. A solution range describes a
 * capability ("Exploration and Geotechnical Products and Solutions"); a product
 * is a line item with a part number and a specification you can put on a
 * purchase order. Until this page existed the header offered both words and both
 * went to /solutions, which is why Products reads as a spec sheet here and
 * Solutions stays editorial.
 *
 * No prices. The API omits them by design — the site quotes rather than lists,
 * and every card ends at an enquiry carrying its SKU. See PublicProductResource.
 */
const { data: catalogue } = await useApiFetch('/products', { key: 'public-products' })

const categories = computed(() => catalogue.value?.data ?? [])
const productCount = computed(() =>
  categories.value.reduce((total, category) => total + (category.products?.length ?? 0), 0),
)

useSeoMeta({
  title: 'Products',
  description:
    'The Xponent Global product catalogue — steel wire, welded and woven mesh, gabions, fencing systems and fencing accessories, supplied to specification and quoted on request.',
})

/**
 * Sends the visitor to the contact form with the message already written.
 *
 * The form's `enquiry_type` is a fixed select that predates this catalogue and
 * has no option matching most SKUs, so the part number goes in the message
 * instead of being forced into a dropdown it does not fit.
 */
function quoteLink(product) {
  return {
    path: '/contact',
    query: { enquiry: `Request for quote: ${product.name} (${product.sku})` },
  }
}
</script>

<template>
  <div>
    <PageHero
      eyebrow="Products"
      title="Product Catalogue"
      subtitle="Steel wire, mesh, gabions and fencing systems manufactured to JIS, BS, AS/NZS, BS EN, ASTM and MS specifications — or to yours. Held in stock, quoted on request."
    />

    <!-- Jump bar, matching Solutions: a long catalogue needs a way in from the top. -->
    <nav v-if="categories.length" class="border-b border-line bg-smoke" aria-label="Product categories">
      <div class="container-retail retail-rail flex items-center gap-1 overflow-x-auto">
        <a
          v-for="category in categories"
          :key="category.id"
          :href="`#${category.slug}`"
          class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark"
        >
          {{ category.name }}
        </a>
      </div>
    </nav>

    <section
      v-for="category in categories"
      :id="category.slug"
      :key="category.id"
      class="container-retail py-8 sm:py-10"
      :aria-label="category.name"
    >
      <SectionHead :title="category.name" link-label="Enquire about this range" link-to="/contact" />
      <p v-if="category.description" class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">
        {{ category.description }}
      </p>

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <article
          v-for="product in category.products"
          :key="product.id"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <div v-if="product.image" class="aspect-16/10 overflow-hidden bg-smoke">
            <CmsImage
              :src="product.image"
              :alt="product.name"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>

          <div class="flex flex-1 flex-col p-4">
            <!--
              The part number leads. Someone arriving on this page from a search
              for "GALV-2.50" needs to confirm they have the right line before
              they read anything else.
            -->
            <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold-dark">
              {{ product.sku }}
            </p>
            <h3 class="mt-1.5 text-[0.95rem] font-bold leading-snug text-ink">{{ product.name }}</h3>

            <p v-if="product.specification" class="mt-2 text-[0.82rem] leading-relaxed text-ink/65">
              {{ product.specification }}
            </p>
            <p v-if="product.description" class="mt-2 text-[0.82rem] leading-relaxed text-ink/65">
              {{ product.description }}
            </p>

            <!-- mt-auto pins the footer row to the bottom so cards of unequal
                 copy length still line their CTAs up across the grid. -->
            <div class="mt-auto flex items-baseline justify-between gap-3 pt-4">
              <span class="font-mono text-[0.68rem] uppercase tracking-code text-ink/45">
                Per {{ product.unit }}
              </span>
              <NuxtLink
                :to="quoteLink(product)"
                class="text-[0.8rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              >
                Request a quote
              </NuxtLink>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- Nothing in the catalogue yet, or the API is unreachable. Better than a
         page that renders as a hero over blank space. -->
    <section v-if="!categories.length" class="container-retail py-16">
      <p class="text-[0.9rem] leading-relaxed text-ink/70">
        The online catalogue is being updated.
        <NuxtLink to="/contact" class="text-gold-dark underline underline-offset-4">Contact our team</NuxtLink>
        for current stock and specifications.
      </p>
    </section>

    <section v-else class="container-retail pb-10 sm:pb-12" aria-label="See also">
      <div class="grid gap-px bg-white/15 md:grid-cols-2">
        <NuxtLink to="/solutions" class="group bg-steel-950 p-6 lg:p-8">
          <h2 class="text-[1.05rem] font-bold text-white group-hover:text-gold">Solutions</h2>
          <p class="mt-1.5 max-w-md text-[0.84rem] leading-relaxed text-white/70">
            The wider ranges behind the catalogue — exploration and geotechnical tooling, mining
            consumables, camp facilities and construction supply.
          </p>
          <span class="mt-2 inline-block text-[0.8rem] font-medium text-gold underline underline-offset-4">
            View Solutions
          </span>
        </NuxtLink>
        <NuxtLink to="/contact" class="group bg-steel-950 p-6 lg:p-8">
          <h2 class="text-[1.05rem] font-bold text-white group-hover:text-gold">Request a quote</h2>
          <p class="mt-1.5 max-w-md text-[0.84rem] leading-relaxed text-white/70">
            {{ productCount }} lines are held as running stock across Brisbane, Manila and Hong Kong.
            Tell us the specification and volume and we will price it.
          </p>
          <span class="mt-2 inline-block text-[0.8rem] font-medium text-gold underline underline-offset-4">
            Contact Us
          </span>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
