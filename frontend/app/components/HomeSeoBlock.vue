<script setup>
/**
 * Long-tail copy at the foot of the page.
 *
 * The text is always in the DOM — only its height is clamped — so crawlers and
 * screen readers get the whole thing regardless of the toggle state.
 *
 * Body paragraphs come from the `home` PageContent record so the copy stays
 * admin-editable, with the closing paragraph assembled from the live contact
 * settings rather than hard-coded.
 */
const props = defineProps({
  section: { type: Object, default: null },
  pending: { type: Boolean, default: false },
})

// Lazy so this section is not held back by a request that only decides whether
// one closing sentence carries a mailto link.
const { data: settings } = useApiFetch('/settings', { key: 'site-settings', lazy: true })

const expanded = ref(false)

const paragraphs = computed(() => (props.section?.body ?? '').split('\n\n').filter(Boolean))
</script>

<template>
  <section class="border-t border-line bg-white py-10 sm:py-12" aria-label="About Xponent Global">
    <div class="container-retail">
      <h2 class="text-[clamp(1.4rem,2.6vw,1.95rem)] font-bold tracking-tight text-ink">
        Your supply partner for mining, drilling, construction and energy
      </h2>

      <div
        id="home-seo-copy"
        class="mt-4 max-w-5xl space-y-4 text-[0.9rem] leading-relaxed text-ink/70"
        :class="expanded ? '' : 'retail-clamp'"
      >
        <template v-if="pending">
          <div v-for="n in 3" :key="n" class="skeleton h-4" :class="n === 3 ? 'w-2/3' : 'w-full'"></div>
        </template>
        <p v-for="(paragraph, i) in paragraphs" v-else :key="i">{{ paragraph }}</p>
        <p>
          Our catalogue spans the full program — underground and surface tooling, drilling
          consumables, coring systems, bits and reamers, core trays and handling, rock bolts and
          ground support, grinding media, mine rails and cars, steel mesh, modular camp
          accommodation, and the personal protective equipment that keeps crews safe on site.
        </p>
        <p>
          If you cannot find a part in the range, send us the specification — sourcing hard-to-find
          equipment is a large part of what we do.
          <template v-if="settings?.contact_email">
            Send an enquiry through the
            <NuxtLink to="/contact" class="text-gold-dark underline underline-offset-4 hover:text-ink">
              contact page </NuxtLink
            >, or email
            <a :href="`mailto:${settings.contact_email}`" class="text-gold-dark underline underline-offset-4 hover:text-ink">
              {{ settings.contact_email }}</a
            >.
          </template>
        </p>
      </div>

      <button
        type="button"
        class="mt-4 flex items-center gap-1.5 text-[0.86rem] font-medium text-gold-dark hover:text-ink"
        :aria-expanded="expanded"
        aria-controls="home-seo-copy"
        @click="expanded = !expanded"
      >
        {{ expanded ? 'See Less' : 'See More' }}
        <svg
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.2"
          aria-hidden="true"
          class="transition-transform duration-300"
          :class="expanded ? 'rotate-180' : ''"
        >
          <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
  </section>
</template>
