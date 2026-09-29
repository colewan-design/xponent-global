<script setup>
defineProps({
  section: { type: Object, default: null },
  pending: { type: Boolean, default: false },
})

const capabilities = [
  { title: 'What we supply', icon: '◆', items: ['Equipment and consumables', 'Camp and site facilities', 'Steel wire, mesh and fencing', 'Maintenance and industrial supplies'] },
  { title: 'How we support', icon: '⚙', items: ['Product sourcing and procurement', 'Logistics and international shipping', 'Technical product support', 'Project-based supply solutions'] },
  { title: 'Where we operate', icon: '●', items: ['Americas', 'Africa', 'Asia–Pacific', 'Global project support'] },
]
</script>

<template>
  <section class="section-tight border-t border-line bg-white" aria-label="Our capabilities">
    <div class="container-retail">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="flex items-center gap-2 text-[0.62rem] font-bold uppercase tracking-[0.15em] text-ink/55"><span class="h-0.5 w-5 bg-gold"></span>Our capabilities<span class="h-0.5 w-5 bg-gold"></span></p>
          <h2 class="mt-2 max-w-[620px] text-[clamp(1.55rem,2.8vw,2.2rem)] font-extrabold leading-[1.05] tracking-[-0.035em]">Your supply partner for mining,<br />drilling, construction and energy</h2>
        </div>
        <NuxtLink to="/solutions" class="text-[0.72rem] font-bold">View capabilities&nbsp; <span class="text-gold">→</span></NuxtLink>
      </div>

      <div class="mt-7 grid gap-7 md:grid-cols-3 md:gap-0">
        <article v-for="(capability, index) in capabilities" :key="capability.title" class="flex gap-4 md:px-8 md:first:pl-0 md:last:pr-0" :class="index ? 'md:border-l md:border-line' : ''">
          <span class="text-xl text-ink" aria-hidden="true">{{ capability.icon }}</span>
          <div>
            <h3 class="text-[0.78rem] font-bold">{{ capability.title }}</h3>
            <ul class="mt-2 space-y-1 text-[0.66rem] leading-relaxed text-ink/70">
              <li v-for="item in capability.items" :key="item">• &nbsp;{{ item }}</li>
            </ul>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
