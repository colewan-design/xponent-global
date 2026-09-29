<script setup>
const industries = [
  { label: 'Mining', href: '/solutions#mining-and-production-consumables', icon: 'mine' },
  { label: 'Exploration', href: '/solutions#exploration-and-geotechnical', icon: 'explore' },
  { label: 'Logistics', href: '/solutions', icon: 'truck' },
  { label: 'Construction', href: '/solutions#construction', icon: 'build' },
  { label: 'Procurement', href: '/solutions', icon: 'box' },
]
</script>

<template>
  <section class="home-hero relative isolate overflow-hidden bg-steel-950 text-white" aria-label="Xponent Global">
    <video
      class="absolute inset-0 h-full w-full object-cover"
      autoplay
      loop
      muted
      playsinline
      poster="/images/page-hero-bg.jpg"
    >
      <source src="/videos/hero-video-720p.mp4" type="video/mp4" />
    </video>
    <div class="home-hero__scrim absolute inset-0" aria-hidden="true"></div>

    <div class="container-retail relative z-10 flex min-h-full items-center py-10 lg:py-10">
      <div class="grid w-full items-center gap-10 lg:grid-cols-[1fr_300px] lg:gap-20">
        <div class="max-w-[650px]">
          <p class="text-[0.68rem] font-bold uppercase tracking-[0.18em] text-white/75">
            Equipment&nbsp; / &nbsp;Consumables&nbsp; / &nbsp;Project support
          </p>
          <h1 class="mt-5 text-[clamp(2.7rem,5vw,4.7rem)] font-extrabold leading-[0.98] tracking-[-0.045em]">
            Supplying<br />confidence.<br /><span class="text-gold">Delivering certainty.</span>
          </h1>
          <p class="mt-6 max-w-[610px] text-[1rem] leading-relaxed text-white/82">
            Xponent Global supplies equipment, consumables and project support for mining, drilling,
            construction and energy operations around the world.
          </p>
          <div class="mt-7 flex flex-wrap gap-3">
            <NuxtLink to="/contact" class="btn btn-primary">Request a Quote</NuxtLink>
            <NuxtLink to="/solutions" class="btn btn-secondary-dark">Explore Solutions</NuxtLink>
          </div>
        </div>

        <nav class="border border-white/15 bg-steel-950/88 backdrop-blur-sm" aria-label="Industries">
          <NuxtLink
            v-for="industry in industries"
            :key="industry.label"
            :to="industry.href"
            class="group flex items-center gap-4 border-b border-white/12 px-5 py-3 last:border-b-0 hover:bg-white/8"
          >
            <svg class="h-7 w-7 shrink-0 text-gold" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
              <template v-if="industry.icon === 'truck'">
                <path d="M3.5 9h16v12H3.5zM19.5 13h5l4 4v4h-9z" /><circle cx="9" cy="23" r="2.5" /><circle cx="24" cy="23" r="2.5" />
              </template>
              <template v-else-if="industry.icon === 'box'">
                <path d="m6 10 10-5 10 5-10 5zM6 10v12l10 5 10-5V10M16 15v12" />
              </template>
              <template v-else-if="industry.icon === 'build'">
                <path d="M5 27V10l7-4v21M12 13h14v14M8 14h2M8 19h2M8 24h2M16 17h3M22 17h2M16 22h3M22 22h2" />
              </template>
              <template v-else>
                <path d="M5 24h22l-3-9-6-6h-5l-5 6zM10 15h14M8 20h18" />
                <path v-if="industry.icon === 'explore'" d="m16 4 3 5h-6z" />
              </template>
            </svg>
            <span class="text-[0.92rem] font-semibold">{{ industry.label }}</span>
            <svg class="ml-auto h-4 w-4 text-white/55 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="m9 5 7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </NuxtLink>
        </nav>
      </div>
    </div>

    <p class="absolute bottom-7 right-7 hidden border-l-2 border-gold pl-4 text-[0.7rem] font-bold uppercase leading-relaxed tracking-[0.14em] text-white/80 lg:block">
      People.<br />Supply.<br />Progress.
    </p>
  </section>
</template>

<style scoped>
.home-hero { min-height: 445px; }
.home-hero__scrim {
  background:
    linear-gradient(90deg, rgba(8, 10, 11, 0.94) 0%, rgba(8, 10, 11, 0.68) 43%, rgba(8, 10, 11, 0.16) 75%),
    linear-gradient(0deg, rgba(8, 10, 11, 0.42), rgba(8, 10, 11, 0.08));
}
@media (max-width: 1023px) {
  .home-hero { min-height: auto; }
  .home-hero__scrim { background: rgba(8, 10, 11, 0.72); }
}
</style>
