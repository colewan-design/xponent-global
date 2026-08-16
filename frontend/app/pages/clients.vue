<script setup>
const { data: clients } = await useApiFetch('/partners', { params: { type: 'client' } })

useSeoMeta({
  title: 'Our Clients',
  description:
    'From multinational drilling and mining corporations to local exploration and water-well drilling companies.',
})

const clientList = computed(() => clients.value?.data ?? [])
</script>

<template>
  <div>
    <PageHero
      eyebrow="Our Clients"
      title="Who We Supply"
      subtitle="From multinational drilling and mining corporations to local exploration and water-well drilling companies."
    />

    <section class="container-retail py-8 sm:py-10" aria-label="Our client base">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-5">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            Partnerships built to last.
          </h2>
          <NuxtLink
            to="/contact"
            class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
          >
            Start a conversation
          </NuxtLink>
        </div>

        <div class="space-y-3 text-[0.9rem] leading-relaxed text-ink/70 lg:col-span-7">
          <p>
            The client base of Xponent Global ranges from multinational drilling and mining
            corporations to local exploration and water-well drilling companies.
          </p>
          <p>
            Our long-term partnership with our clients is based on unwavering commitment, support,
            enthusiasm, and innovation.
          </p>
        </div>
      </div>
    </section>

    <section class="container-retail pb-10 sm:pb-12" aria-label="Client register">
      <SectionHead :title="`${clientList.length} operators and counting`" link-label="Browse solutions" link-to="/solutions" />

      <!--
        Logos are supplied as artwork on white grounds, so each sits on a white
        cell with `mix-blend-multiply` to drop the ground. Cells that link out
        carry an arrow; the rest are plain.
      -->
      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <component
          :is="client.website_url ? 'a' : 'div'"
          v-for="client in clientList"
          :key="client.id"
          :href="client.website_url || undefined"
          :target="client.website_url ? '_blank' : undefined"
          :rel="client.website_url ? 'noopener noreferrer' : undefined"
          class="group flex flex-col border border-line transition-colors"
          :class="client.website_url ? 'hover:border-gold' : ''"
        >
          <div class="flex flex-1 items-center justify-center bg-white px-6 py-9">
            <CmsImage
              :src="client.logo"
              :alt="client.name"
              loading="lazy"
              class="max-h-12 w-full object-contain mix-blend-multiply"
            />
          </div>

          <div class="flex items-center justify-between gap-3 border-t border-line px-4 py-3">
            <p class="text-[0.82rem] font-semibold leading-snug text-ink/80">{{ client.name }}</p>
            <svg
              v-if="client.website_url"
              width="12"
              height="12"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              aria-hidden="true"
              class="shrink-0 text-ink/30 transition-all duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-gold-dark"
            >
              <path d="M7 17L17 7M9 7h8v8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
        </component>
      </div>
    </section>

    <ScheduleVisit />
  </div>
</template>
