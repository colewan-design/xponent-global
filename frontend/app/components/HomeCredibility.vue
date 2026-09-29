<script setup>
const props = defineProps({
  section: { type: Object, default: null },
  pending: { type: Boolean, default: false },
  officeCount: { type: Number, default: 0 },
  rangeCount: { type: Number, default: 0 },
  clientCount: { type: Number, default: 0 },
  mission: { type: Object, default: null },
  vision: { type: Object, default: null },
  briefsPending: { type: Boolean, default: false },
})

const paragraph = computed(() => (props.section?.body ?? '').split('\n\n').filter(Boolean)[0] ?? '')
const metrics = computed(() => [
  { value: '30+', label: 'Years combined experience' },
  { value: props.rangeCount || 6, label: 'Industry sectors' },
  { value: props.officeCount || 4, label: 'Operating regions' },
  { value: props.clientCount || 12, label: 'Strategic supply partners' },
])
const briefBody = (section) => (section?.body ?? '').split('\n\n').filter(Boolean)[0] ?? ''
</script>

<template>
  <section class="section bg-steel-950 text-white" aria-label="Why work with Xponent Global">
    <div class="container-retail">
      <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-16">
        <div>
          <p class="flex items-center gap-2 text-[0.62rem] font-bold uppercase tracking-[0.15em] text-gold"><span class="h-0.5 w-5 bg-gold"></span>Why Xponent Global<span class="h-0.5 w-5 bg-gold"></span></p>
          <h2 class="mt-3 text-[clamp(1.8rem,3.2vw,2.75rem)] font-extrabold leading-[1.02] tracking-[-0.04em]">
            Driving innovation.<br />Powering <span class="text-gold">progress.</span>
          </h2>
          <p class="mt-5 max-w-[570px] text-[0.86rem] leading-relaxed text-white/70">
            {{ paragraph || 'For over 30 years, we have delivered dependable supply and project support to demanding operations around the world.' }}
          </p>
        </div>

        <dl class="grid grid-cols-2">
          <div v-for="metric in metrics" :key="metric.label" class="border-b border-white/15 px-6 py-6 odd:border-r">
            <dd class="text-[2.1rem] font-extrabold leading-none text-gold">{{ metric.value }}</dd>
            <dt class="mt-2 text-[0.7rem] leading-snug text-white/70">{{ metric.label }}</dt>
          </div>
        </dl>
      </div>

      <div class="mt-9 grid gap-6 border-t border-white/15 pt-7 md:grid-cols-2 md:gap-0">
        <article class="flex gap-5 md:border-r md:border-white/15 md:pr-9">
          <svg class="h-9 w-9 shrink-0 text-gold" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="16" cy="16" r="12" /><path d="m16 8 2.5 5.5L24 16l-5.5 2.5L16 24l-2.5-5.5L8 16l5.5-2.5z" /></svg>
          <div><h3 class="text-[0.85rem] font-bold uppercase">Our mission</h3><p class="mt-1 text-[0.75rem] leading-relaxed text-white/66">{{ briefBody(mission) || 'To be a trusted global supply partner, delivering solutions that keep critical industries moving.' }}</p></div>
        </article>
        <article class="flex gap-5 md:pl-9">
          <svg class="h-9 w-9 shrink-0 text-gold" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 24V12l5-5 5 5v12M13 24V9l6-5 5 5v15M24 24V14l3-3 2 3v10M2 24h28" /></svg>
          <div><h3 class="text-[0.85rem] font-bold uppercase">Our vision</h3><p class="mt-1 text-[0.75rem] leading-relaxed text-white/66">{{ briefBody(vision) || 'To power a more resilient and sustainable future through reliable supply, innovation and partnership.' }}</p></div>
        </article>
      </div>
    </div>
  </section>
</template>
