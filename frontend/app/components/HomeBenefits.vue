<script setup>
/**
 * Five service promises on one dark band, split by hairlines.
 *
 * The counts are read off live CMS records rather than asserted in markup, so the
 * band can't claim more offices or ranges than the site actually publishes.
 */
const props = defineProps({
  officeCount: { type: Number, default: 0 },
  rangeCount: { type: Number, default: 0 },
})

const benefits = computed(() => [
  {
    title: 'Total solutions provider',
    detail: `${props.rangeCount || 'Multiple'} supply ranges across exploration, production, camp and construction.`,
    href: '/solutions',
    cta: 'Browse Solutions',
  },
  {
    title: 'Global footprint',
    detail: `${props.officeCount || 'Offices'} offices and warehouses supporting five regions.`,
    href: '/about#where-we-operate',
    cta: 'Where We Operate',
  },
  {
    title: 'Specialist support',
    detail: 'A team who know drilling and mining programs, not a call centre.',
    href: '/contact',
    cta: 'Talk To Us',
  },
  {
    title: 'Trusted brand partners',
    detail: 'Genuine consumables and technical backing from the people who build the tools.',
    href: '/solutions/our-brand-partners',
    cta: 'Our Partners',
  },
  {
    title: 'Safety first',
    detail: 'Rated protective equipment supplied alongside the tooling it works with.',
    href: '/solutions/personal-protection-equipment',
    cta: 'View PPE',
  },
])
</script>

<template>
  <section class="container-retail pb-8 sm:pb-10" aria-label="Why work with Xponent Global">
    <div class="rule-grid rule-dark grid bg-steel-950 sm:grid-cols-2 lg:grid-cols-5">
      <div v-for="benefit in benefits" :key="benefit.title" class="p-5 lg:p-6">
        <h3 class="text-[0.98rem] font-bold text-white">{{ benefit.title }}</h3>
        <p class="mt-1.5 text-[0.82rem] leading-relaxed text-white/70">{{ benefit.detail }}</p>
        <NuxtLink
          :to="benefit.href"
          class="mt-2 inline-block text-[0.78rem] font-medium text-gold underline underline-offset-4 hover:text-white"
        >
          {{ benefit.cta }}
        </NuxtLink>
      </div>
    </div>
  </section>
</template>
