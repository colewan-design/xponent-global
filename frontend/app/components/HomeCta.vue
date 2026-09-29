<template>
  <section class="home-cta relative overflow-hidden bg-steel-950 text-white" aria-label="Contact Xponent Global">
    <img src="/images/page-hero-bg.jpg" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20" aria-hidden="true" />
    <div class="absolute inset-0 bg-steel-950/72" aria-hidden="true"></div>
    <div class="container-retail relative grid gap-7 py-9 lg:grid-cols-[1.25fr_0.75fr] lg:items-center lg:gap-20 lg:py-10">
      <div>
        <p class="flex items-center gap-2 text-[0.62rem] font-bold uppercase tracking-[0.15em] text-gold"><span class="h-0.5 w-5 bg-gold"></span>Let's work together<span class="h-0.5 w-5 bg-gold"></span></p>
        <h2 class="mt-3 text-[clamp(1.8rem,3.3vw,2.75rem)] font-extrabold leading-[1.04] tracking-[-0.04em]">Tell us what the program needs.</h2>
        <p class="mt-4 max-w-[650px] text-[0.83rem] leading-relaxed text-white/72">Our team is ready to help with product selection, availability and logistics support. Get in touch and we’ll respond within one business day.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <NuxtLink to="/contact" class="btn btn-primary">Talk to Supply&nbsp; →</NuxtLink>
          <NuxtLink to="/contact" class="btn btn-secondary-dark">Request a Quote</NuxtLink>
        </div>
        <p class="mt-3 text-[0.65rem] text-white/55">▣ &nbsp;Responses within one business day</p>
      </div>

      <ul class="border-l border-white/18 pl-8 text-[0.76rem] font-semibold text-white/85">
        <li v-for="item in ['Reliable supply chain', 'Global reach', 'Responsive support', 'Long-term partnerships']" :key="item" class="flex items-center gap-3 py-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-full border border-gold text-[0.65rem] text-gold">✓</span>{{ item }}
        </li>
      </ul>
    </div>
  </section>
</template>
