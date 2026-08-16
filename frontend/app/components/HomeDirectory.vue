<script setup>
/**
 * The full solutions directory — the page's main browse surface.
 *
 * One column per catalogue category, listing that category's items as links into
 * the category's own anchor on `/solutions`. Item thumbnails are `object-contain`
 * rather than `cover`: these are product shots with their own margin, and
 * cropping them clips the product.
 *
 * The list is capped per column so a category with thirty items can't run the
 * page off the screen — "View all" carries the rest.
 */
const props = defineProps({
  categories: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const PER_COLUMN = 7

const columns = computed(() =>
  props.categories.map((category) => ({
    ...category,
    visibleItems: (category.items ?? []).slice(0, PER_COLUMN),
    overflow: Math.max(0, (category.items?.length ?? 0) - PER_COLUMN),
  })),
)
</script>

<template>
  <section id="solutions" class="container-retail pb-10 sm:pb-12" aria-label="Solutions directory">
    <SectionHead title="Our solutions" link-label="See all solutions" link-to="/solutions" />

    <div
      v-if="pending"
      class="mt-6 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
      aria-hidden="true"
    >
      <div v-for="n in 4" :key="n">
        <div class="skeleton h-11 w-full"></div>
        <ul class="mt-3 space-y-1 px-1">
          <li v-for="row in 5" :key="row" class="flex items-center gap-3 py-1">
            <span class="skeleton h-9 w-9 shrink-0"></span>
            <span class="skeleton h-3 flex-1"></span>
          </li>
        </ul>
        <div class="skeleton mt-3 ml-1 h-3 w-24"></div>
      </div>
    </div>

    <div v-else class="mt-6 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="column in columns" :key="column.id">
        <h3 class="bg-smoke px-4 py-3 text-[0.92rem] font-bold text-ink">{{ column.title }}</h3>

        <ul class="mt-3 space-y-1 px-1">
          <li v-for="item in column.visibleItems" :key="item.id">
            <NuxtLink
              :to="`/solutions#${column.slug}`"
              class="group flex items-center gap-3 py-1 text-[0.84rem] leading-snug text-ink/80 transition-colors hover:text-gold-dark"
            >
              <span
                class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden border border-line bg-smoke transition-colors duration-200 group-hover:border-gold group-hover:bg-gold/10"
              >
                <CmsImage v-if="item.image" :src="item.image" alt="" loading="lazy" class="h-full w-full object-contain" />
              </span>
              {{ item.title }}
            </NuxtLink>
          </li>
        </ul>

        <NuxtLink
          :to="`/solutions#${column.slug}`"
          :aria-label="`View all ${column.title}`"
          class="mt-3 inline-block px-1 text-[0.84rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
        >
          {{ column.overflow ? `View all ${column.items.length}` : 'View Range' }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
