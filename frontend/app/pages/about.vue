<script setup>
/**
 * About page, reworked into larger "scenes" with route-specific GSAP timelines.
 * The ids remain stable because the header's About sheet links directly to them.
 */
const { data: about } = await useApiFetch('/page-content/about')
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })
const { data: affiliations } = await useApiFetch('/partners', { params: { type: 'affiliation' } })

useSeoMeta({
  title: 'About Us',
  description: 'Learn about Xponent Global - our vision, mission, core values, and where we operate.',
})

const sectors = ['Mining', 'Drilling', 'Oil & Gas', 'Construction', 'Energy']

const pageRef = ref(null)
const heroRef = ref(null)
const heroBackdropRef = ref(null)
const heroContentRef = ref(null)
const storySceneRef = ref(null)
const storyStageRef = ref(null)
const storyCopyRef = ref(null)
const storyRailRef = ref(null)
const valuesSectionRef = ref(null)
const valuesTrackRef = ref(null)
const footprintRef = ref(null)
const footprintMapRef = ref(null)
const affiliationRef = ref(null)

const heroMetricRefs = ref([])
const valueCardRefs = ref([])
const officeCardRefs = ref([])
const affiliationCardRefs = ref([])

let destroyScroll = () => {}

const sections = computed(() => about.value?.data?.sections ?? [])
const intro = computed(() => sections.value[0] ?? null)
const vision = computed(() => sections.value[1] ?? null)
const mission = computed(() => sections.value[2] ?? null)
const coreValues = computed(() => sections.value[3] ?? null)
const whereWeOperate = computed(() => sections.value[4] ?? null)
const affiliationsIntro = computed(() => sections.value[5] ?? null)

const splitParagraphs = (text) =>
  (text ?? '')
    .split('\n\n')
    .map((paragraph) => paragraph.trim())
    .filter(Boolean)

const introParagraphs = computed(() => splitParagraphs(intro.value?.body))
const affiliationsParagraphs = computed(() => splitParagraphs(affiliationsIntro.value?.body))
const footprintParagraphs = computed(() => splitParagraphs(whereWeOperate.value?.body))

const plottedLocations = computed(() =>
  (locations.value?.data ?? []).filter((location) => {
    const latitude = Number(location.latitude)
    const longitude = Number(location.longitude)

    return Number.isFinite(latitude) && Number.isFinite(longitude)
  }),
)

const values = computed(() =>
  splitParagraphs(coreValues.value?.body).map((block) => {
    const [term, ...detailParts] = block.split(':')

    return {
      term: term?.trim() ?? '',
      detail: detailParts.join(':').trim(),
    }
  }),
)

const footprintStats = computed(() => {
  const locationList = locations.value?.data ?? []
  const countries = new Set(locationList.map((location) => location.country).filter(Boolean))
  const cities = new Set(locationList.map((location) => location.city).filter(Boolean))

  return [
    {
      label: 'Office Network',
      value: String(locationList.length).padStart(2, '0'),
      detail: 'Operational hubs and warehouses',
    },
    {
      label: 'Countries',
      value: String(countries.size).padStart(2, '0'),
      detail: 'Markets currently served',
    },
    {
      label: 'Cities',
      value: String(cities.size).padStart(2, '0'),
      detail: 'Local teams and partner coverage',
    },
    {
      label: 'Affiliations',
      value: String((affiliations.value?.data ?? []).length).padStart(2, '0'),
      detail: 'Industry organizations and alliances',
    },
  ]
})

const setHeroMetricRef = (element) => {
  if (element) heroMetricRefs.value.push(element)
}

const setValueCardRef = (element) => {
  if (element) valueCardRefs.value.push(element)
}

const setOfficeCardRef = (element) => {
  if (element) officeCardRefs.value.push(element)
}

const setAffiliationCardRef = (element) => {
  if (element) affiliationCardRefs.value.push(element)
}

onBeforeUpdate(() => {
  heroMetricRefs.value = []
  valueCardRefs.value = []
  officeCardRefs.value = []
  affiliationCardRefs.value = []
})

const setupScrollScenes = async () => {
  if (!import.meta.client || !pageRef.value || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return
  }

  const [{ default: gsap }, scrollTriggerModule] = await Promise.all([import('gsap'), import('gsap/ScrollTrigger')])
  const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default

  gsap.registerPlugin(ScrollTrigger)

  const refreshTimers = []
  const ctx = gsap.context(() => {
    const fadeUp = {
      y: 42,
      autoAlpha: 0,
      duration: 0.9,
      ease: 'power3.out',
      stagger: 0.12,
    }

    if (heroContentRef.value) {
      gsap.from(heroContentRef.value.children, {
        ...fadeUp,
        clearProps: 'all',
      })
    }

    if (heroMetricRefs.value.length) {
      gsap.from(heroMetricRefs.value, {
        ...fadeUp,
        delay: 0.18,
        clearProps: 'all',
      })
    }

    if (heroBackdropRef.value) {
      gsap.fromTo(
        heroBackdropRef.value,
        { scale: 1.08, yPercent: -3 },
        {
          scale: 1.16,
          yPercent: 10,
          ease: 'none',
          scrollTrigger: {
            trigger: heroRef.value,
            start: 'top top',
            end: 'bottom top',
            scrub: true,
          },
        },
      )
    }

    const mm = gsap.matchMedia()

    mm.add('(min-width: 1024px)', () => {
      const storyTimeline = gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: {
          trigger: storySceneRef.value,
          start: 'top top+=104',
          end: '+=180%',
          scrub: 1,
          pin: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      })

      const introBlock = storyCopyRef.value?.querySelector('[data-story-block="intro"]')
      const missionCard = storyCopyRef.value?.querySelector('[data-story-card="mission"]')
      const visionCard = storyCopyRef.value?.querySelector('[data-story-card="vision"]')

      if (missionCard) gsap.set(missionCard, { autoAlpha: 0, y: 56 })
      if (visionCard) gsap.set(visionCard, { autoAlpha: 0, y: 56 })
      if (storyRailRef.value) gsap.set(storyRailRef.value, { scaleY: 0, transformOrigin: 'top center' })

      if (storyStageRef.value) {
        storyTimeline.fromTo(
          storyStageRef.value,
          { scale: 1.08, yPercent: 0 },
          { scale: 1, yPercent: -6 },
          0,
        )
      }

      if (storyRailRef.value) {
        storyTimeline.to(storyRailRef.value, { scaleY: 1 }, 0)
      }

      if (introBlock) {
        storyTimeline.to(
          introBlock,
          {
            y: -54,
            autoAlpha: 0.42,
          },
          0.15,
        )
      }

      if (missionCard) {
        storyTimeline.to(
          missionCard,
          {
            autoAlpha: 1,
            y: 0,
          },
          0.18,
        )

        storyTimeline.to(
          missionCard,
          {
            y: -34,
            autoAlpha: 0.26,
          },
          0.54,
        )
      }

      if (visionCard) {
        storyTimeline.to(
          visionCard,
          {
            autoAlpha: 1,
            y: 0,
          },
          0.58,
        )
      }
    })

    if (valuesTrackRef.value) {
      gsap.fromTo(
        valuesTrackRef.value,
        { scaleX: 0, transformOrigin: 'left center' },
        {
          scaleX: 1,
          ease: 'none',
          scrollTrigger: {
            trigger: valuesSectionRef.value,
            start: 'top 70%',
            end: 'bottom 45%',
            scrub: true,
          },
        },
      )
    }

    if (valueCardRefs.value.length) {
      gsap.from(valueCardRefs.value, {
        ...fadeUp,
        scrollTrigger: {
          trigger: valuesSectionRef.value,
          start: 'top 65%',
        },
        clearProps: 'all',
      })
    }

    if (footprintMapRef.value) {
      gsap.from(footprintMapRef.value, {
        y: 46,
        autoAlpha: 0,
        duration: 1,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: footprintRef.value,
          start: 'top 65%',
        },
        clearProps: 'all',
      })
    }

    if (officeCardRefs.value.length) {
      gsap.from(officeCardRefs.value, {
        ...fadeUp,
        scrollTrigger: {
          trigger: footprintRef.value,
          start: 'top 52%',
        },
        clearProps: 'all',
      })
    }

    if (affiliationCardRefs.value.length) {
      gsap.from(affiliationCardRefs.value, {
        ...fadeUp,
        scrollTrigger: {
          trigger: affiliationRef.value,
          start: 'top 65%',
        },
        clearProps: 'all',
      })
    }

    refreshTimers.push(window.setTimeout(() => ScrollTrigger.refresh(), 300))
    refreshTimers.push(window.setTimeout(() => ScrollTrigger.refresh(), 1200))

    destroyScroll = () => {
      refreshTimers.forEach((timer) => window.clearTimeout(timer))
      mm.revert()
      ctx.revert()
    }
  }, pageRef.value)
}

onMounted(async () => {
  await nextTick()
  await setupScrollScenes()
})

onBeforeUnmount(() => destroyScroll())
</script>

<template>
  <div ref="pageRef" class="about-page bg-white text-ink">
    <section ref="heroRef" class="about-hero u-grain relative isolate overflow-hidden bg-steel-950 text-white">
      <div ref="heroBackdropRef" class="absolute inset-0">
        <div class="absolute inset-0">
          <CmsImage
            :src="intro?.image ?? '/images/page-hero-bg.jpg'"
            alt=""
            fetchpriority="high"
            class="h-full w-full object-cover"
          />
        </div>
      </div>
      <div class="u-grid pointer-events-none absolute inset-0 opacity-55"></div>
      <div class="absolute inset-0 bg-linear-to-r from-steel-950 via-steel-950/62 to-steel-950/20"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(238,181,0,0.18),transparent_36%)]"></div>
      <div class="absolute inset-x-0 bottom-0 h-2/3 bg-linear-to-t from-steel-950 via-steel-950/72 to-transparent"></div>

      <div class="container-retail-wide relative z-10 py-12 sm:py-16 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.18fr)_22rem] lg:items-end">
          <div ref="heroContentRef" class="max-w-5xl">
            <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold">About Us</p>
            <h1
              id="about-xgl"
              class="display-tight mt-4 max-w-5xl text-[clamp(3.1rem,10vw,8rem)] leading-[0.9]"
            >
              Who We Are
            </h1>
            <p class="mt-6 max-w-3xl text-[0.98rem] leading-relaxed text-white/72 sm:text-[1.02rem]">
              An international total solutions provider to the mining, drilling, oil and gas, construction and
              energy sectors.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
              <span
                v-for="sector in sectors"
                :key="sector"
                class="border border-white/14 bg-white/6 px-3 py-2 font-mono text-[0.66rem] font-bold uppercase tracking-[0.18em] text-white/78 backdrop-blur-sm"
              >
                {{ sector }}
              </span>
            </div>
          </div>

          <div class="grid gap-3">
            <div
              v-for="stat in footprintStats.slice(0, 3)"
              :key="stat.label"
              :ref="setHeroMetricRef"
              class="about-hero__metric"
            >
              <p class="font-mono text-[0.66rem] font-bold uppercase tracking-code text-gold/95">
                {{ stat.label }}
              </p>
              <div class="mt-3 flex items-end justify-between gap-4">
                <p class="text-[2.55rem] font-bold leading-none text-white">{{ stat.value }}</p>
                <p class="max-w-[10rem] text-right text-[0.78rem] leading-relaxed text-white/58">{{ stat.detail }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="absolute bottom-5 right-4 z-10 hidden items-center gap-3 text-white/56 lg:flex">
        <span class="font-mono text-[0.6rem] font-bold uppercase tracking-code">Scroll</span>
        <span class="h-px w-14 bg-white/22"></span>
      </div>
    </section>

    <section ref="storySceneRef" class="container-retail-wide py-6 sm:py-8 lg:py-10">
      <div class="overflow-hidden border border-line bg-white">
        <div class="grid lg:min-h-[46rem] lg:grid-cols-[minmax(0,1.02fr)_minmax(24rem,0.98fr)]">
          <div class="relative isolate min-h-[24rem] overflow-hidden bg-steel-950 text-white sm:min-h-[28rem] lg:min-h-full">
            <div ref="storyStageRef" class="absolute inset-0">
              <CmsImage
                :src="intro?.image ?? '/images/page-hero-bg.jpg'"
                alt=""
                class="h-full w-full object-cover opacity-70"
              />
            </div>
            <div class="u-grid pointer-events-none absolute inset-0 opacity-45"></div>
            <div class="absolute inset-0 bg-linear-to-t from-steel-950 via-steel-950/45 to-transparent"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(238,181,0,0.16),transparent_34%)]"></div>

            <div class="relative z-10 flex h-full items-end p-6 sm:p-8 lg:p-10">
              <div class="max-w-xl">
                <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold">Inside Xponent Global</p>
                <h2 class="display-tight mt-4 max-w-lg text-[clamp(2.2rem,5.6vw,4.7rem)] text-white">
                  {{ intro?.heading ?? 'Built for demanding operational environments' }}
                </h2>
                <p class="mt-5 max-w-md text-[0.9rem] leading-relaxed text-white/68">
                  We combine regional reach, technical discipline, and responsive support for industries where timing and reliability matter.
                </p>
              </div>
            </div>
          </div>

          <div class="bg-smoke">
            <div class="grid h-full gap-6 p-6 sm:p-8 lg:grid-cols-[3rem_minmax(0,1fr)] lg:gap-8 lg:p-10">
              <div class="hidden lg:flex justify-center">
                <div class="about-story__rail">
                  <span ref="storyRailRef" class="about-story__rail-fill"></span>
                </div>
              </div>

              <div ref="storyCopyRef" class="relative space-y-6 lg:space-y-8">
                <div data-story-block="intro" class="space-y-3">
                  <p class="font-mono text-[0.64rem] font-bold uppercase tracking-code text-ink/40">Our story</p>
                  <p
                    v-for="(paragraph, index) in introParagraphs"
                    :key="`intro-${index}`"
                    class="max-w-2xl text-[0.96rem] leading-relaxed text-ink/72"
                  >
                    {{ paragraph }}
                  </p>
                </div>

                <article
                  v-if="mission"
                  id="our-mission"
                  data-story-card="mission"
                  class="story-panel border border-line bg-white p-5 sm:p-6"
                >
                  <p class="font-mono text-[0.64rem] font-bold uppercase tracking-code text-gold-dark">
                    {{ mission.heading }}
                  </p>
                  <p class="mt-3 text-[0.92rem] leading-relaxed text-ink/76">{{ mission.body }}</p>
                </article>

                <article
                  v-if="vision"
                  id="our-vision"
                  data-story-card="vision"
                  class="story-panel border border-line bg-white p-5 sm:p-6"
                >
                  <p class="font-mono text-[0.64rem] font-bold uppercase tracking-code text-gold-dark">
                    {{ vision.heading }}
                  </p>
                  <p class="mt-3 text-[0.92rem] leading-relaxed text-ink/76">{{ vision.body }}</p>
                </article>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section
      v-if="values.length"
      id="our-core-values"
      ref="valuesSectionRef"
      class="about-values bg-steel-950 py-10 text-white sm:py-12 lg:py-14"
      :aria-label="coreValues?.heading"
    >
      <div class="container-retail-wide">
        <div class="grid gap-8 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
          <div class="lg:sticky lg:top-[11rem]">
            <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold">Operating principles</p>
            <h2 class="mt-4 text-[clamp(1.8rem,4vw,3.3rem)] font-bold uppercase tracking-[-0.04em] text-white">
              {{ coreValues?.heading }}
            </h2>
            <p class="mt-4 max-w-sm text-[0.92rem] leading-relaxed text-white/64">
              The standards behind our work are consistent across markets, teams, and projects, shaping how we
              partner, respond, and deliver.
            </p>

            <div class="mt-8">
              <div class="h-px w-full bg-white/12">
                <div ref="valuesTrackRef" class="h-full w-full bg-gold"></div>
              </div>
              <p class="mt-3 font-mono text-[0.62rem] font-bold uppercase tracking-code text-white/44">
                Operating rhythm
              </p>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <article
              v-for="value in values"
              :key="value.term"
              :ref="setValueCardRef"
              class="about-values__card"
            >
              <p class="font-mono text-[0.62rem] font-bold uppercase tracking-code text-gold/92">
                {{ value.term }}
              </p>
              <p class="mt-4 text-[0.92rem] leading-relaxed text-white/70">{{ value.detail }}</p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section id="where-we-operate" ref="footprintRef" class="container-retail-wide py-10 sm:py-12 lg:py-14">
      <div class="grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
        <div class="space-y-5">
          <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold-dark">Global footprint</p>
          <h2 class="text-[clamp(1.9rem,4vw,3.4rem)] font-bold uppercase tracking-[-0.04em] text-ink">
            {{ whereWeOperate?.heading ?? 'Where we operate' }}
          </h2>

          <div class="space-y-3 text-[0.94rem] leading-relaxed text-ink/68">
            <p v-for="(paragraph, index) in footprintParagraphs" :key="`footprint-${index}`">
              {{ paragraph }}
            </p>
            <p v-if="!footprintParagraphs.length">
              Our delivery model is grounded in local presence, regional coordination, and practical support near the
              projects we serve.
            </p>
          </div>

          <NuxtLink
            to="/contact"
            class="inline-flex items-center gap-3 border border-line px-4 py-3 font-mono text-[0.66rem] font-bold uppercase tracking-code text-ink transition-colors hover:border-ink hover:text-gold-dark"
          >
            Contact an office
            <span aria-hidden="true">-&gt;</span>
          </NuxtLink>

          <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
            <div v-for="stat in footprintStats" :key="stat.label" class="border border-line bg-smoke px-4 py-4">
              <p class="font-mono text-[0.62rem] font-bold uppercase tracking-code text-ink/42">{{ stat.label }}</p>
              <div class="mt-2 flex items-end justify-between gap-4">
                <p class="text-[2rem] font-bold leading-none text-ink">{{ stat.value }}</p>
                <p class="max-w-[11rem] text-right text-[0.78rem] leading-relaxed text-ink/58">{{ stat.detail }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div ref="footprintMapRef" class="about-map-frame">
            <div class="u-grid pointer-events-none absolute inset-0 opacity-35"></div>
            <div class="absolute left-4 top-4 z-10 border border-white/14 bg-steel-950/82 px-3 py-2 font-mono text-[0.58rem] font-bold uppercase tracking-code text-gold backdrop-blur-sm">
              Live footprint
            </div>

            <ClientOnly v-if="plottedLocations.length">
              <OfficeMap :locations="plottedLocations" />
              <template #fallback>
                <div class="h-80 w-full bg-smoke sm:h-105 lg:h-120" aria-hidden="true" />
              </template>
            </ClientOnly>

            <div v-else class="bg-smoke p-6 sm:p-10">
              <CmsImage
                v-if="whereWeOperate?.image"
                :src="whereWeOperate.image"
                alt="Map of Xponent Global office and warehouse locations"
                loading="lazy"
                class="mx-auto w-full max-w-4xl object-contain"
              />
              <div v-else class="h-80 w-full bg-smoke sm:h-105 lg:h-120" aria-hidden="true" />
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article
              v-for="location in locations?.data"
              :key="location.id"
              :ref="setOfficeCardRef"
              class="border border-line bg-white p-5"
            >
              <p class="font-mono text-[0.58rem] font-bold uppercase tracking-code text-ink/38">
                {{ String(location.id).padStart(2, '0') }}
              </p>
              <h3 class="mt-3 text-[0.96rem] font-bold text-ink">{{ location.label }}</h3>
              <address class="mt-2 text-[0.84rem] not-italic leading-relaxed text-ink/64">
                {{ [location.address, location.city, location.country].filter(Boolean).join(', ') }}
              </address>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section
      v-if="affiliationsIntro"
      id="our-affiliations"
      ref="affiliationRef"
      class="container-retail-wide pb-10 sm:pb-12 lg:pb-14"
      aria-label="Our affiliations"
    >
      <div class="grid gap-6 border border-line bg-smoke p-6 sm:p-8 lg:grid-cols-[22rem_minmax(0,1fr)] lg:gap-8 lg:p-10">
        <div class="space-y-4">
          <p class="font-mono text-[0.68rem] font-bold uppercase tracking-code text-gold-dark">Trusted network</p>
          <h2 class="text-[clamp(1.8rem,4vw,3.3rem)] font-bold uppercase tracking-[-0.04em] text-ink">
            {{ affiliationsIntro.heading }}
          </h2>
          <div class="space-y-3 text-[0.94rem] leading-relaxed text-ink/68">
            <p v-for="(paragraph, index) in affiliationsParagraphs" :key="`affiliation-${index}`">
              {{ paragraph }}
            </p>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <article
            v-for="affiliation in affiliations?.data"
            :key="affiliation.id"
            :ref="setAffiliationCardRef"
            class="border border-line bg-white p-5 sm:p-6"
          >
            <CmsImage
              :src="affiliation.logo"
              :alt="affiliation.name"
              loading="lazy"
              class="h-12 w-auto max-w-48 object-contain"
            />
            <p class="mt-5 text-[0.86rem] leading-relaxed text-ink/70">{{ affiliation.description }}</p>
          </article>
        </div>
      </div>
    </section>

    <ScheduleVisit />
  </div>
</template>

<style scoped>
.about-page {
  overflow-x: clip;
}

.about-hero {
  min-height: min(96svh, 56rem);
  display: flex;
  align-items: end;
}

.about-hero__metric {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.04));
  padding: 1rem;
  backdrop-filter: blur(12px);
}

.about-story__rail {
  width: 1px;
  height: 100%;
  background: rgba(17, 17, 17, 0.12);
  position: relative;
}

.about-story__rail-fill {
  position: absolute;
  inset: 0;
  background: var(--color-gold);
}

.story-panel {
  box-shadow: 0 24px 64px rgba(17, 17, 17, 0.08);
}

.about-values {
  position: relative;
  overflow: hidden;
}

.about-values::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    radial-gradient(circle at top right, rgba(238, 181, 0, 0.1), transparent 28%),
    linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
  background-size:
    auto,
    72px 72px,
    72px 72px;
}

.about-values__card {
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.03));
  padding: 1.25rem;
  min-height: 13rem;
}

.about-values__card::after {
  content: '';
  position: absolute;
  inset-inline: 1.25rem;
  top: 1rem;
  height: 1px;
  background: rgba(238, 181, 0, 0.35);
}

.about-map-frame {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--color-line);
  background: var(--color-smoke);
}

@media (max-width: 1023px) {
  .about-hero {
    min-height: 42rem;
  }

  .about-values__card {
    min-height: auto;
  }

  .story-panel {
    box-shadow: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .about-page {
    scroll-behavior: auto;
  }
}
</style>
