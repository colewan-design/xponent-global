<script setup>
const { data: careers } = await useApiFetch('/page-content/careers')
const { data: jobs } = await useApiFetch('/jobs')

useSeoMeta({
  title: 'Careers',
  description: 'Join Xponent Global — current openings across mining, drilling, oil & gas, and construction.',
})

const sections = computed(() => careers.value?.data?.sections ?? [])
const [intro, whyWorkWithUs] = sections.value

/** "Why work with us" arrives as `Term: detail` blocks separated by blank lines. */
const reasons = computed(() =>
  (whyWorkWithUs?.body ?? '')
    .split('\n\n')
    .filter(Boolean)
    .map((block) => ({
      term: block.split(':')[0],
      detail: block.split(':').slice(1).join(':').trim(),
    })),
)

const openings = computed(() => jobs.value?.data ?? [])

const applyingTo = ref(null)
</script>

<template>
  <div>
    <PageHero
      eyebrow="Careers"
      title="Work With Us"
      subtitle="Join a team keeping essential industries moving — through dependable supply, practical support, and work done with care."
      :image="intro?.image ?? '/images/page-hero-bg.jpg'"
    />

    <!-- Invitation beside the openings count, one grey field. -->
    <section v-if="intro" class="container-retail py-8 sm:py-10" aria-label="Join us">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-7">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            {{ intro.heading }}
          </h2>
          <div class="mt-3 space-y-3 text-[0.9rem] leading-relaxed text-ink/70">
            <p v-for="(paragraph, i) in intro.body.split('\n\n')" :key="i">{{ paragraph }}</p>
          </div>
          <a href="#openings" class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink">
            {{ openings.length ? `See ${openings.length} open role${openings.length === 1 ? '' : 's'}` : 'See openings' }}
          </a>
        </div>

        <!-- Openings summary rather than a slice of the reasons below: splitting
             that list across two bands orphans whatever doesn't divide evenly. -->
        <div class="lg:col-span-5">
          <div class="bg-white p-5">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Open roles</p>
            <p class="mt-2 text-[2.2rem] font-extrabold leading-none tracking-tight text-ink">
              {{ openings.length }}
            </p>
            <p class="mt-2 text-[0.86rem] leading-relaxed text-ink/70">
              {{
                openings.length
                  ? 'Positions currently open across our offices and sites.'
                  : "No roles are posted right now — send us an introduction and we'll keep it on file."
              }}
            </p>
            <NuxtLink
              to="/contact"
              class="mt-3 inline-block text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
            >
              Ask about a role
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Openings. One row per role — a manifest reads better than a card grid for
         a short list, and roles arrive one or two at a time. -->
    <section id="openings" class="container-retail pb-8 sm:pb-10" aria-label="Current openings">
      <SectionHead title="Current openings" link-label="Ask about a role" link-to="/contact" />

      <div v-if="openings.length" v-reveal:group class="mt-6 grid gap-px bg-line">
        <article
          v-for="job in openings"
          :key="job.id"
          class="flex flex-wrap items-center justify-between gap-6 bg-white p-5 sm:p-6"
        >
          <div class="min-w-0 flex-1">
            <h3 class="text-[1rem] font-bold text-ink">{{ job.title }}</h3>
            <p class="mt-1 text-[0.8rem] text-ink/55">
              {{ job.location }} <span class="text-ink/30">/</span>
              {{ job.employment_type.replace('_', '-') }}
            </p>
            <p class="mt-2 max-w-2xl text-[0.86rem] leading-relaxed text-ink/70">{{ job.summary }}</p>
          </div>

          <button
            class="shrink-0 bg-steel-950 px-6 py-3 text-[0.84rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950"
            @click="applyingTo = job"
          >
            Apply Now
          </button>
        </article>
      </div>

      <div v-else class="mt-6 border border-line bg-smoke p-8 text-center sm:p-10">
        <h3 class="text-[1.05rem] font-bold text-ink">No open roles right now.</h3>
        <p class="mx-auto mt-2 max-w-md text-[0.88rem] leading-relaxed text-ink/70">
          Nothing is posted at the moment — check back soon, or send us an introduction and we'll keep
          it on file.
        </p>
        <NuxtLink
          to="/contact"
          class="mt-4 inline-block bg-steel-950 px-6 py-3 text-[0.84rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950"
        >
          Introduce Yourself
        </NuxtLink>
      </div>
    </section>

    <!-- One dark band per page: every reason to join, in one grid. -->
    <section v-if="reasons.length" class="container-retail pb-8 sm:pb-10" :aria-label="whyWorkWithUs.heading">
      <div class="bg-steel-950 p-6 sm:p-8">
        <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold tracking-tight text-white">
          {{ whyWorkWithUs.heading }}
        </h2>
        <!-- auto-fit rather than a fixed column count: the reason list is
             CMS-driven, and a fixed grid leaves the remainder stranded in a
             half-empty row. -->
        <div v-reveal:group class="mt-5 grid gap-4 sm:grid-cols-[repeat(auto-fit,minmax(15rem,1fr))]">
          <div v-for="reason in reasons" :key="reason.term" class="border border-white/15 p-5">
            <h3 class="text-[0.98rem] font-bold text-gold">{{ reason.term }}</h3>
            <p class="mt-1.5 text-[0.84rem] leading-relaxed text-white/70">{{ reason.detail }}</p>
          </div>
        </div>
      </div>
    </section>

    <ScheduleVisit />

    <JobApplyModal v-if="applyingTo" :job="applyingTo" @close="applyingTo = null" />
  </div>
</template>
