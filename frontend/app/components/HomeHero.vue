<script setup>
/**
 * Landing masthead: the brand promise over the company's own site footage.
 *
 * The retail language opens on a flat dark statement band. Xponent has a hero
 * video and no catalogue to merchandise, so the band carries the film behind the
 * type — run through the same duotone plate as every still on the site, so it
 * doesn't read as a separate palette.
 */
</script>

<template>
  <section aria-label="Xponent Global">
    <div class="border-b border-white/10 bg-steel-900">
      <p class="container-retail-wide py-2.5 text-center text-[0.82rem] text-white/85">
        Supplying the
        <span class="font-semibold text-white">mining, drilling, oil &amp; gas, construction and energy</span>
        sectors across five regions
      </p>
    </div>

    <div class="relative overflow-hidden bg-steel-950">
      <div class="absolute inset-0">
        <div class="plate plate-hero h-full w-full">
          <video
            class="h-full w-full object-cover"
            autoplay
            loop
            muted
            playsinline
            poster="/images/page-hero-bg.jpg"
          >
            <source src="/videos/hero-video-1080p.mp4" type="video/mp4" />
          </video>
        </div>
      </div>
      <div class="u-grid pointer-events-none absolute inset-0 opacity-50"></div>

      <div class="container-retail-wide relative py-16 text-center sm:py-24 lg:py-28">
        <h1 class="mx-auto max-w-4xl text-[clamp(1.5rem,4vw,3rem)] font-extrabold leading-tight tracking-tight text-white">
          Supplying confidence. Delivering certainty.
        </h1>
        <p class="mx-auto mt-5 max-w-2xl text-[0.92rem] leading-relaxed text-white/70 sm:text-[0.98rem]">
          An international total solutions provider, delivering quality around the globe.
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
          <NuxtLink
            to="/contact"
            class="bg-gold px-7 py-3.5 text-[0.86rem] font-semibold text-steel-950 transition-colors hover:bg-gold-light"
          >
            Get in Touch
          </NuxtLink>
          <NuxtLink
            to="/solutions"
            class="border border-white/40 px-7 py-3.5 text-[0.86rem] font-semibold text-white transition-colors hover:border-gold hover:text-gold"
          >
            Browse Solutions
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>
