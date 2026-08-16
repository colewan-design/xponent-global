<script setup>
/**
 * Four photographic entry points, straight off the solutions catalogue.
 *
 * Below `sm` four tiles are most of a screenful of scrolling, so the same markup
 * becomes a snap carousel — one card at a time, dot pagination — and reverts to
 * the grid from `sm` up. The dots are a pointer affordance only: all four links
 * stay in the DOM and in the tab order, so nobody is forced to drive a slider to
 * reach a solution.
 */
const props = defineProps({
  categories: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const railRef = ref(null)
const active = ref(0)

function onScroll() {
  const rail = railRef.value
  if (!rail) return
  const width = rail.clientWidth
  if (width === 0) return
  active.value = Math.round(rail.scrollLeft / width)
}

function goTo(index) {
  const rail = railRef.value
  if (!rail) return
  rail.scrollTo({ left: index * rail.clientWidth, behavior: 'smooth' })
}

const tiles = computed(() => props.categories.slice(0, 4))
</script>

<template>
  <section class="container-retail py-8 sm:py-10" aria-label="Our solutions">
    <!-- Four placeholders, matching the grid the tiles will land in, so the
         sections below do not jump up the page when they arrive. -->
    <div
      v-if="pending"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
      aria-hidden="true"
    >
      <div v-for="n in 4" :key="n">
        <div class="skeleton aspect-15/8 w-full"></div>
        <div class="skeleton mt-2 h-3 w-11/12"></div>
        <div class="skeleton mt-1.5 h-3 w-2/3"></div>
      </div>
    </div>

    <div v-else class="relative">
      <div
        ref="railRef"
        class="retail-rail flex snap-x snap-mandatory overflow-x-auto sm:grid sm:grid-cols-2 sm:gap-4 sm:overflow-visible xl:grid-cols-4"
        @scroll.passive="onScroll"
      >
        <div
          v-for="tile in tiles"
          :key="tile.id"
          class="w-full shrink-0 snap-start px-0 sm:w-auto sm:shrink"
        >
          <NuxtLink
            :to="`/solutions#${tile.slug}`"
            class="group relative block aspect-15/8 overflow-hidden focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
          >
            <CmsImage
              v-if="tile.image"
              :src="tile.image"
              :alt="tile.title"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <span class="absolute inset-0 bg-linear-to-b from-steel-950/75 via-steel-950/30 to-steel-950/75"></span>

            <span class="absolute inset-0 flex flex-col justify-between p-5">
              <span class="max-w-56 text-[1.1rem] font-bold leading-snug text-white">{{ tile.title }}</span>
              <span class="text-[0.82rem] font-semibold text-white underline underline-offset-4 group-hover:text-gold-light">
                View Range
              </span>
            </span>
          </NuxtLink>

          <p v-if="tile.description" class="mt-2 line-clamp-2 px-0 text-[0.78rem] leading-snug text-ink/55">
            {{ tile.description }}
          </p>
        </div>
      </div>

      <!-- One dot bar for the whole rail, outside the scroller so it stays put
           while the cards move under it. It sits over the artwork by borrowing
           the card's own aspect ratio, so its bottom edge lands on the image's.

           Hidden from assistive tech and the tab order — the rail scrolls
           natively and every card's link is already reachable. -->
      <div
        class="pointer-events-none absolute inset-x-0 top-0 flex aspect-15/8 items-end justify-center pb-3 sm:hidden"
        aria-hidden="true"
      >
        <div class="pointer-events-auto flex items-center gap-2 rounded-full bg-steel-950/65 px-2.5 py-1.5 backdrop-blur-sm">
          <button
            v-for="(dot, index) in tiles"
            :key="dot.id"
            type="button"
            tabindex="-1"
            class="h-1.5 w-1.5 rounded-full transition-colors"
            :class="index === active ? 'bg-white' : 'bg-white/40'"
            @click="goTo(index)"
          ></button>
        </div>
      </div>
    </div>
  </section>
</template>
