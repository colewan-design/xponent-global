<script setup>
const props = defineProps({
  clients: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})

const fallbackClients = [
  'Barrick', 'Newmont', 'AngloAmerican', 'Rio Tinto', 'Teck',
  'Kinross', 'FLSmidth', 'Epiroc', 'Sandvik', 'CAT',
].map((name, index) => ({ id: `fallback-${index}`, name, logo: `/cms/seed/cl-logo-${String(index + 1).padStart(2, '0')}.jpg` }))

const displayedClients = computed(() => props.clients.length ? props.clients.slice(0, 10) : fallbackClients)
</script>

<template>
  <section class="section container-retail" aria-label="Our clients">
    <HomeSectionHead
      eyebrow="Trusted partners"
      title="Trusted across mining, construction and industrial operations"
      description="We work with leading organizations around the world to deliver successful projects."
    />

    <ul class="mt-5 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-3 lg:grid-cols-5">
      <li v-for="client in displayedClients" :key="client.id" class="flex h-11 items-center justify-center px-3">
        <CmsImage :src="client.logo" :alt="client.name" loading="lazy" class="max-h-10 w-full object-contain grayscale mix-blend-multiply opacity-75 transition hover:grayscale-0 hover:opacity-100" />
      </li>
    </ul>
  </section>
</template>
