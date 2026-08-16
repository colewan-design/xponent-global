<script setup>
/**
 * The client register, as a plain logo wall on hairline-bordered cells.
 *
 * Logos are supplied through the admin as artwork on white grounds, so they sit
 * on white cells with `mix-blend-multiply` to drop the ground; greyscale at rest
 * keeps a wall of wildly different brand palettes reading as one block, and
 * colour returns on hover.
 */
defineProps({
  clients: { type: Array, default: () => [] },
  pending: { type: Boolean, default: false },
})
</script>

<template>
  <section class="container-retail pb-10 sm:pb-12" aria-label="Our clients">
    <SectionHead title="Trusted by operators" link-label="See our clients" link-to="/clients" />

    <p class="mt-2 max-w-2xl text-[0.9rem] leading-relaxed text-ink/70">
      From multinational drilling and mining corporations to local exploration and water-well drilling
      companies.
    </p>

    <div
      v-if="pending"
      class="rule-grid rule-light mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6"
      aria-hidden="true"
    >
      <div v-for="n in 12" :key="n" class="flex h-24 items-center justify-center bg-white px-5">
        <div class="skeleton h-11 w-full"></div>
      </div>
    </div>

    <ul v-else v-reveal:group class="rule-grid rule-light mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
      <li v-for="client in clients" :key="client.id" class="flex h-24 items-center justify-center bg-white px-5">
        <CmsImage
          :src="client.logo"
          :alt="client.name"
          loading="lazy"
          class="max-h-11 w-full object-contain grayscale mix-blend-multiply transition duration-300 hover:grayscale-0"
        />
      </li>
    </ul>
  </section>
</template>
