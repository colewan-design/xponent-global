<script setup>
defineProps({
  eyebrow: { type: String, default: '' },
  title: { type: String, required: true },
  description: { type: String, default: '' },
  linkLabel: { type: String, default: '' },
  linkTo: { type: String, default: '' },
  tone: { type: String, default: 'light' },
})
</script>

<template>
  <div class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_minmax(220px,360px)] sm:items-end sm:gap-10">
    <div>
      <p v-if="eyebrow" class="flex items-center gap-2 text-[0.62rem] font-bold uppercase tracking-[0.15em]" :class="tone === 'dark' ? 'text-gold' : 'text-ink/55'">
        <span class="h-0.5 w-5 bg-gold" aria-hidden="true"></span>{{ eyebrow }}<span class="h-0.5 w-5 bg-gold" aria-hidden="true"></span>
      </p>
      <h2 class="mt-2 text-[clamp(1.55rem,2.8vw,2.25rem)] font-extrabold leading-[1.06] tracking-[-0.035em] text-balance" :class="tone === 'dark' ? 'text-white' : 'text-ink'">{{ title }}</h2>
    </div>
    <div class="sm:pb-1">
      <p v-if="description" class="text-[0.82rem] leading-relaxed" :class="tone === 'dark' ? 'text-white/65' : 'text-ink/65'">{{ description }}</p>
      <NuxtLink v-if="linkLabel && linkTo" :to="linkTo" class="mt-3 inline-flex items-center gap-2 text-[0.72rem] font-bold transition-colors hover:text-gold-dark" :class="tone === 'dark' ? 'text-white' : 'text-ink'">
        {{ linkLabel }} <span class="text-gold">→</span>
      </NuxtLink>
    </div>
  </div>
</template>
