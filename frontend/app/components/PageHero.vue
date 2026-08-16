<script setup>
/**
 * The shared inner-page masthead: a full-bleed duotone plate under an eyebrow and
 * display headline. The one dark, photographic element on an otherwise white
 * page — it sits on the narrower `container-page` measure than the body below it,
 * deliberately.
 *
 * `image` falls back to the packaged backdrop because several pages (Clients,
 * Brand Partners, Resources) have no single representative photo in the CMS —
 * the plate treatment makes one generic site photo work across all of them.
 */
defineProps({
  title: { type: String, required: true },
  eyebrow: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  image: { type: String, default: '/images/page-hero-bg.jpg' },
})
</script>

<template>
  <section class="u-grain relative flex min-h-80 items-end overflow-hidden bg-steel-950 text-white sm:min-h-[26rem]">
    <!--
      `.plate` sets its own `position`, so the full-bleed positioning lives on this
      wrapper rather than on the plate itself (see main.css).
    -->
    <div class="absolute inset-0">
      <div class="plate plate-hero h-full w-full">
        <CmsImage :src="image" alt="" class="h-full w-full object-cover" fetchpriority="high" />
      </div>
    </div>
    <div class="u-grid pointer-events-none absolute inset-0 opacity-60"></div>
    <div class="absolute inset-0 bg-linear-to-r from-steel-950 via-steel-950/55 to-transparent"></div>
    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-linear-to-t from-steel-950/80 to-transparent"></div>

    <div class="container-page relative z-10 w-full py-12 sm:py-16">
      <p v-if="eyebrow" class="font-mono text-[0.62rem] font-bold uppercase tracking-code text-gold">
        {{ eyebrow }}
      </p>
      <h1
        class="display-tight max-w-4xl text-[clamp(1.9rem,5vw,3.8rem)]"
        :class="eyebrow ? 'mt-4' : ''"
      >
        {{ title }}
      </h1>
      <p v-if="subtitle" class="mt-5 max-w-2xl text-[0.92rem] leading-relaxed text-white/70">
        {{ subtitle }}
      </p>
    </div>
  </section>
</template>
