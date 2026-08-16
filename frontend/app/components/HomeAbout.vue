<script setup>
/**
 * Provenance claim on the left, proof on the right, one grey field.
 *
 * Copy comes from the `home` PageContent record's first block so it stays
 * admin-editable; the three commitment cards restate what the company says it
 * stands for, in the register a buyer reads before a supplier is trusted.
 *
 * The commitment cards and the About Us link are static, so they do not wait on
 * the CMS — only the heading and body paragraphs are conditional. A missing
 * `home` record costs those two elements, not the whole section.
 */
const props = defineProps({
  section: { type: Object, default: null },
  pending: { type: Boolean, default: false },
})

const paragraphs = computed(() =>
  (props.section?.body ?? '').split('\n\n').filter(Boolean).slice(0, 2),
)

const commitments = [
  {
    title: 'Quality without compromise',
    detail: 'Protected at every step — from sourcing and storage through to accurate, on-time delivery.',
  },
  {
    title: 'Committed partnership',
    detail: 'We work as an extension of your team, responding quickly and following through on every commitment.',
  },
  {
    title: 'Dependable value',
    detail: 'Reliable products made accessible through practical pricing, ready stock, and efficient service.',
  },
]
</script>

<template>
  <section class="container-retail pb-8 sm:pb-10" aria-label="About Xponent Global">
    <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
      <div class="lg:col-span-4">
        <template v-if="pending">
          <div class="skeleton h-8 w-4/5"></div>
          <div class="mt-3 max-w-md space-y-2">
            <div class="skeleton h-4 w-full"></div>
            <div class="skeleton h-4 w-11/12"></div>
            <div class="skeleton h-4 w-3/4"></div>
          </div>
        </template>
        <template v-else>
          <h2
            v-if="section?.heading"
            class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink"
          >
            {{ section.heading }}
          </h2>
          <div v-if="paragraphs.length" class="mt-3 max-w-md space-y-3 text-[0.9rem] leading-relaxed text-ink/70">
            <p v-for="(paragraph, i) in paragraphs" :key="i">{{ paragraph }}</p>
          </div>
        </template>
        <NuxtLink
          to="/about"
          class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
        >
          About Us
        </NuxtLink>
      </div>

      <div class="grid gap-4 lg:col-span-8 lg:grid-cols-3">
        <div v-for="commitment in commitments" :key="commitment.title" class="bg-white p-5">
          <h3 class="text-[0.95rem] font-bold text-ink">{{ commitment.title }}</h3>
          <p class="mt-2 text-[0.84rem] leading-relaxed text-ink/70">{{ commitment.detail }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
