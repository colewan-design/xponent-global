<script setup>
/**
 * Solutions — retail layout: one section per catalogue category, each a heading
 * row and a grid of flat bordered item cards.
 *
 * Section ids are the category slugs, because the header's Solutions sheet
 * deep-links to them (`/solutions#construction`, …).
 */
const { data: solutions } = await useApiFetch('/solutions')

useSeoMeta({
  title: 'Solutions',
  description:
    'Exploration and geotechnical products, mining and production consumables, mining camp facilities, and construction solutions.',
})

const categories = computed(
  () => solutions.value?.data?.filter((category) => category.slug !== 'personal-protection-equipment') ?? [],
)

const expanded = ref(new Set())

function toggle(id) {
  const next = new Set(expanded.value)
  next.has(id) ? next.delete(id) : next.add(id)
  expanded.value = next
}

// Cards clamp to three lines, so multi-paragraph copy (Bits and Reamers, Discoverer
// Core Trays) is unreachable without a toggle.
function isLong(description) {
  return Boolean(description) && (description.includes('\n') || description.length > 160)
}
</script>

<template>
  <div>
    <PageHero
      eyebrow="Solutions"
      title="Products & Solutions"
      subtitle="Underground and surface tooling, drilling consumables, coring systems, camp facilities and construction supply — sourced, stocked and supported."
      :image="categories[0]?.image ?? '/images/page-hero-bg.jpg'"
    />

    <!-- Jump bar: a long page of ranges needs a way in from the top. -->
    <nav v-if="categories.length" class="border-b border-line bg-smoke" aria-label="Solution ranges">
      <div class="container-retail retail-rail flex items-center gap-1 overflow-x-auto">
        <a
          v-for="category in categories"
          :key="category.id"
          :href="`#${category.slug}`"
          class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark"
        >
          {{ category.title }}
        </a>
        <NuxtLink
          to="/solutions/personal-protection-equipment"
          class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark"
        >
          Personal Protection Equipment
        </NuxtLink>
      </div>
    </nav>

    <section
      v-for="category in categories"
      :id="category.slug"
      :key="category.id"
      class="container-retail py-8 sm:py-10"
      :aria-label="category.title"
    >
      <SectionHead :title="category.title" link-label="Enquire about this range" link-to="/contact" />
      <p v-if="category.description" class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">
        {{ category.description }}
      </p>

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <article
          v-for="item in category.items"
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

          <div class="flex flex-1 flex-col p-4">
            <h3 class="text-[0.95rem] font-bold leading-snug text-ink">{{ item.title }}</h3>
            <p
              class="mt-2 text-[0.82rem] leading-relaxed text-ink/65"
              :class="expanded.has(item.id) ? 'whitespace-pre-line' : 'line-clamp-3'"
            >
              {{ item.description }}
            </p>
            <button
              v-if="isLong(item.description)"
              type="button"
              class="mt-2 w-fit text-[0.8rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              :aria-expanded="expanded.has(item.id)"
              @click="toggle(item.id)"
            >
              {{ expanded.has(item.id) ? 'Show less' : 'Read more' }}
            </button>
          </div>
        </article>
      </div>
    </section>

    <!-- Closing band: the two ranges that live on their own pages. -->
    <section class="container-retail pb-10 sm:pb-12" aria-label="See also">
      <div class="grid gap-px bg-white/15 md:grid-cols-2">
        <NuxtLink
          v-for="link in [
            {
              to: '/solutions/personal-protection-equipment',
              title: 'Personal Protection Equipment',
              blurb: 'PPE ranges supplied to keep crews safe across mining, drilling and construction sites.',
            },
            {
              to: '/solutions/our-brand-partners',
              title: 'Our Brand Partners',
              blurb: 'The manufacturers and marques whose ranges we carry and support.',
            },
          ]"
          :key="link.to"
          :to="link.to"
          class="group bg-steel-950 p-6 lg:p-8"
        >
          <h2 class="text-[1.05rem] font-bold text-white group-hover:text-gold">{{ link.title }}</h2>
          <p class="mt-1.5 max-w-md text-[0.84rem] leading-relaxed text-white/70">{{ link.blurb }}</p>
          <span class="mt-2 inline-block text-[0.8rem] font-medium text-gold underline underline-offset-4">
            View Range
          </span>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
