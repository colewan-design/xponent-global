<script setup>
/**
 * About page rebuilt around a pinned scrollytelling command scene.
 * Anchor ids remain stable because the header links directly to them.
 */
const { data: about } = await useApiFetch('/page-content/about')
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })
const { data: affiliations } = await useApiFetch('/partners', { params: { type: 'affiliation' } })

useSeoMeta({
  title: 'About Us',
  description: 'Learn about Xponent Global - our vision, mission, core values, and where we operate.',
})

const pageRef = ref(null)
const commandSceneRef = ref(null)
const commandBackdropRef = ref(null)
const commandProgressRef = ref(null)
const valuesSectionRef = ref(null)
const valuesTrackRef = ref(null)
const footprintRef = ref(null)
const footprintMapRef = ref(null)
const affiliationRef = ref(null)

const commandPanelRefs = ref([])
const commandHotspotRefs = ref([])
const commandLabelRefs = ref([])
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

const commandStates = computed(() => {
  const introParagraphs = splitParagraphs(intro.value?.body)
  const missionParagraphs = splitParagraphs(mission.value?.body)
  const visionParagraphs = splitParagraphs(vision.value?.body)

  return [
    {
      key: 'intro',
      anchor: 'about-xgl',
      heading: intro.value?.heading ?? 'One connected support grid',
      paragraphs: introParagraphs.length ? introParagraphs : ['Integrated field support for demanding project environments.'],
      badge: 'SUPPORT GRID',
      tag: 'Operations relay',
      tone: 'gold',
      x: 58,
      y: 56,
      size: 15,
      labelDx: 4,
      labelDy: -1,
    },
    {
      key: 'mission',
      anchor: 'our-mission',
      heading: mission.value?.heading ?? 'Our mission',
      paragraphs: missionParagraphs.length ? missionParagraphs : ['Responsive, field-ready service with disciplined execution.'],
      badge: 'HOTSPOT',
      tag: 'Mission target',
      tone: 'coral',
      x: 53,
      y: 63,
      size: 20,
      labelDx: 4,
      labelDy: 0,
    },
    {
      key: 'vision',
      anchor: 'our-vision',
      heading: vision.value?.heading ?? 'Our vision',
      paragraphs: visionParagraphs.length ? visionParagraphs : ['A global support network with reliable local reach.'],
      badge: 'FIELD SPECIALIST',
      tag: 'Coverage node',
      tone: 'lime',
      x: 75,
      y: 39,
      size: 8,
      labelDx: 4,
      labelDy: -1,
    },
  ]
})

const commandFrames = [
  { left: '14%', top: '12%', width: '58%', height: '64%' },
  { left: '37%', top: '4%', width: '16%', height: '16%' },
  { left: '67%', top: '10%', width: '18%', height: '40%' },
  { left: '27%', top: '55%', width: '26%', height: '28%' },
  { left: '62%', top: '66%', width: '22%', height: '18%' },
]

const commandDots = [
  { left: '4%', top: '6%', tone: 'muted', size: '0.32rem' },
  { left: '11%', top: '44%', tone: 'muted', size: '0.28rem' },
  { left: '33%', top: '18%', tone: 'coral', size: '0.42rem' },
  { left: '37%', top: '62%', tone: 'coral', size: '0.42rem' },
  { left: '42%', top: '24%', tone: 'lime', size: '0.42rem' },
  { left: '48%', top: '51%', tone: 'lime', size: '0.38rem' },
  { left: '66%', top: '18%', tone: 'muted', size: '0.36rem' },
  { left: '79%', top: '27%', tone: 'coral', size: '0.42rem' },
  { left: '89%', top: '60%', tone: 'muted', size: '0.32rem' },
]

const commandHotspotStyle = (state) => ({
  left: `${state.x}%`,
  top: `${state.y}%`,
  '--spot-size': `${state.size}rem`,
})

const commandLabelStyle = (state) => ({
  left: `${state.x + state.labelDx}%`,
  top: `${state.y + state.labelDy}%`,
})

const setCommandPanelRef = (element) => {
  if (element) commandPanelRefs.value.push(element)
}

const setCommandHotspotRef = (element) => {
  if (element) commandHotspotRefs.value.push(element)
}

const setCommandLabelRef = (element) => {
  if (element) commandLabelRefs.value.push(element)
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
  commandPanelRefs.value = []
  commandHotspotRefs.value = []
  commandLabelRefs.value = []
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

    if (commandBackdropRef.value) {
      gsap.fromTo(
        commandBackdropRef.value,
        { scale: 1.04, xPercent: -1, yPercent: -2 },
        {
          scale: 1.11,
          xPercent: 2,
          yPercent: 6,
          ease: 'none',
          scrollTrigger: {
            trigger: commandSceneRef.value,
            start: 'top top',
            end: 'bottom top',
            scrub: true,
          },
        },
      )
    }

    gsap.from('.about-command__field', {
      autoAlpha: 0,
      scale: 0.94,
      duration: 1.1,
      ease: 'power3.out',
      clearProps: 'all',
    })

    gsap.from('.about-command__copy-shell', {
      x: -42,
      autoAlpha: 0,
      duration: 0.9,
      delay: 0.12,
      ease: 'power3.out',
      clearProps: 'all',
    })

    gsap.from('.about-command__frame', {
      autoAlpha: 0,
      scaleX: 0.92,
      duration: 1,
      stagger: 0.08,
      ease: 'power2.out',
      clearProps: 'all',
    })

    gsap.from('.about-command__scroll-orbit', {
      rotate: -140,
      autoAlpha: 0,
      duration: 1.2,
      delay: 0.18,
      ease: 'power2.out',
      clearProps: 'all',
    })

    if (commandDots.length) {
      gsap.to('.about-command__dot--pulse', {
        scale: 1.35,
        repeat: -1,
        yoyo: true,
        duration: 1.8,
        stagger: 0.2,
        ease: 'sine.inOut',
      })
    }

    const mm = gsap.matchMedia()

    mm.add('(min-width: 1024px)', () => {
      const panels = commandPanelRefs.value
      const hotspots = commandHotspotRefs.value
      const labels = commandLabelRefs.value

      if (!panels.length) return

      gsap.set(panels, { autoAlpha: 0, y: 40 })
      gsap.set(hotspots, { autoAlpha: 0.28, scale: 0.62 })
      gsap.set(labels, { autoAlpha: 0.2, y: 20 })
      gsap.set(panels[0], { autoAlpha: 1, y: 0 })
      gsap.set(hotspots[0], { autoAlpha: 1, scale: 1 })
      gsap.set(labels[0], { autoAlpha: 1, y: 0 })
      if (commandProgressRef.value) gsap.set(commandProgressRef.value, { scaleY: 0, transformOrigin: 'top center' })

      const timeline = gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: {
          trigger: commandSceneRef.value,
          start: 'top top+=104',
          end: `+=${Math.max(commandStates.value.length * 120, 260)}%`,
          scrub: 1,
          pin: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      })

      if (commandProgressRef.value) {
        timeline.to(commandProgressRef.value, { scaleY: 1 }, 0)
      }

      commandStates.value.forEach((state, index) => {
        const previousIndex = index - 1

        if (previousIndex >= 0) {
          timeline.to(
            panels[previousIndex],
            {
              y: -48,
              autoAlpha: 0,
            },
            index - 0.08,
          )

          timeline.to(
            hotspots[previousIndex],
            {
              autoAlpha: 0.18,
              scale: 0.5,
            },
            index - 0.08,
          )

          timeline.to(
            labels[previousIndex],
            {
              autoAlpha: 0,
              y: -14,
            },
            index - 0.08,
          )
        }

        if (index > 0) {
          timeline.to(
            panels[index],
            {
              y: 0,
              autoAlpha: 1,
            },
            index + 0.04,
          )

          timeline.to(
            hotspots[index],
            {
              autoAlpha: 1,
              scale: 1,
            },
            index + 0.04,
          )

          timeline.to(
            labels[index],
            {
              autoAlpha: 1,
              y: 0,
            },
            index + 0.04,
          )
        }
      })
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
            start: 'top 72%',
            end: 'bottom 46%',
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
          start: 'top 68%',
        },
        clearProps: 'all',
      })
    }

    if (footprintMapRef.value) {
      gsap.from(footprintMapRef.value, {
        y: 42,
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

    refreshTimers.push(window.setTimeout(() => ScrollTrigger.refresh(), 350))
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
    <section
      id="about-xgl"
      ref="commandSceneRef"
      class="about-command u-grain relative isolate overflow-hidden bg-steel-950 text-white"
    >
      <div class="container-retail-wide relative z-10 min-h-[100svh] py-8 sm:py-10 lg:py-14">
        <div class="grid min-h-[calc(100svh-4rem)] gap-8 lg:grid-cols-[24rem_minmax(0,1fr)_3.5rem] lg:items-end">
          <div class="flex items-end lg:min-h-[38rem]">
            <div class="about-command__copy-shell">
              <h1 class="sr-only">Who We Are</h1>
              <p class="font-mono text-[0.62rem] font-bold uppercase tracking-code text-white/38">About Us</p>
              <div class="about-command__copy">
              <div
                v-for="state in commandStates"
                :id="state.anchor"
                :key="state.key"
                :ref="setCommandPanelRef"
                class="about-command__panel"
              >
                <p class="font-mono text-[0.64rem] font-bold uppercase tracking-code text-gold">
                  {{ state.badge }}
                </p>
                <h2 class="mt-5 max-w-[18ch] font-mono text-[clamp(1.8rem,3vw,2.6rem)] font-bold uppercase tracking-[-0.02em] text-[#e8ff52]">
                  {{ state.heading }}
                </h2>
                <div class="mt-5 max-w-sm space-y-4 text-[1.02rem] leading-[1.35] text-white/88 sm:text-[1.1rem]">
                  <p v-for="(paragraph, index) in state.paragraphs" :key="`${state.key}-${index}`">
                    {{ paragraph }}
                  </p>
                </div>
              </div>
            </div>
            </div>
          </div>

          <div class="relative min-h-[31rem] lg:min-h-[42rem]">
            <div class="about-command__field">
              <div ref="commandBackdropRef" class="about-command__backdrop">
                <CmsImage
                  :src="intro?.image ?? '/images/page-hero-bg.jpg'"
                  alt=""
                  fetchpriority="high"
                  class="h-full w-full object-cover"
                />
              </div>

              <div class="about-command__surface">
                <div class="about-command__mesh"></div>
                <div
                  v-for="(frame, index) in commandFrames"
                  :key="`frame-${index}`"
                  class="about-command__frame"
                  :style="frame"
                ></div>
                <div
                  v-for="(dot, index) in commandDots"
                  :key="`dot-${index}`"
                  class="about-command__dot"
                  :class="[`about-command__dot--${dot.tone}`, dot.tone === 'muted' ? '' : 'about-command__dot--pulse']"
                  :style="{ left: dot.left, top: dot.top, width: dot.size, height: dot.size }"
                ></div>
              </div>

              <div class="about-command__visual">
                <div class="about-command__scroll-mark">
                  <span class="font-mono text-[0.6rem] font-bold uppercase tracking-code text-white/80">Scroll</span>
                  <span class="about-command__scroll-orbit"></span>
                </div>

                <div
                  v-for="state in commandStates"
                  :key="`spot-${state.key}`"
                  :style="commandHotspotStyle(state)"
                  :ref="setCommandHotspotRef"
                  class="about-command__hotspot"
                  :class="`about-command__hotspot--${state.tone}`"
                ></div>
                <div
                  v-for="state in commandStates"
                  :key="`label-${state.key}`"
                  :style="commandLabelStyle(state)"
                  :ref="setCommandLabelRef"
                  class="about-command__label"
                  :class="`about-command__label--${state.tone}`"
                >
                  {{ state.tag }}
                </div>
              </div>
            </div>
          </div>

          <div class="hidden lg:flex lg:justify-end">
            <div class="about-command__rail">
              <span ref="commandProgressRef" class="about-command__rail-fill"></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="border-y border-line bg-steel-950 px-4 py-5 text-white sm:px-6 lg:px-8">
      <div class="container-retail-wide grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div v-for="stat in footprintStats" :key="stat.label" class="about-stat">
          <p class="font-mono text-[0.62rem] font-bold uppercase tracking-code text-gold">{{ stat.label }}</p>
          <div class="mt-3 flex items-end justify-between gap-4">
            <p class="text-[2.4rem] font-bold leading-none text-white">{{ stat.value }}</p>
            <p class="max-w-[10rem] text-right text-[0.8rem] leading-relaxed text-white/58">{{ stat.detail }}</p>
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
            <p v-for="(paragraph, index) in splitParagraphs(whereWeOperate?.body)" :key="`footprint-${index}`">
              {{ paragraph }}
            </p>
            <p v-if="!splitParagraphs(whereWeOperate?.body).length">
              Our delivery model is grounded in local presence, regional coordination, and practical support near the
              projects we serve.
            </p>
          </div>

          <NuxtLink
            to="/contact"
            class="inline-flex items-center gap-3 border border-line px-4 py-3 font-mono text-[0.66rem] font-bold uppercase tracking-code text-ink transition-colors hover:border-ink hover:text-gold-dark"
          >
            Contact an office
            <span aria-hidden="true">-></span>
          </NuxtLink>
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
            <p v-for="(paragraph, index) in splitParagraphs(affiliationsIntro?.body)" :key="`affiliation-${index}`">
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

.about-command {
  min-height: 100svh;
  background:
    radial-gradient(circle at 82% 18%, rgba(238, 181, 0, 0.14), transparent 26%),
    radial-gradient(circle at 18% 78%, rgba(255, 97, 68, 0.08), transparent 24%),
    #0a0c0d;
}

.about-command__backdrop {
  position: absolute;
  inset: 0;
  opacity: 0.36;
  filter: grayscale(1) contrast(1.06) brightness(0.44);
}

.about-command__field {
  position: absolute;
  inset: 5% 2% 2% 0;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.02);
  clip-path: polygon(
    7% 0%,
    30% 0%,
    30% 3%,
    38% 3%,
    38% 0%,
    63% 0%,
    63% 3%,
    75% 3%,
    75% 0%,
    95% 0%,
    95% 4%,
    100% 4%,
    100% 25%,
    98% 25%,
    98% 42%,
    100% 42%,
    100% 72%,
    98% 72%,
    98% 91%,
    90% 91%,
    90% 100%,
    79% 100%,
    79% 96%,
    62% 96%,
    62% 100%,
    47% 100%,
    47% 94%,
    29% 94%,
    29% 100%,
    12% 100%,
    12% 94%,
    5% 94%,
    5% 90%,
    0% 90%,
    0% 55%,
    2% 55%,
    2% 37%,
    0% 37%,
    0% 14%,
    2% 14%,
    2% 6%,
    7% 6%
  );
}

.about-command__surface,
.about-command__visual {
  position: absolute;
  inset: 0;
}

.about-command__mesh {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(to right, rgba(255, 255, 255, 0.045) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
  background-size: 72px 72px;
  mask-image: radial-gradient(circle at center, black, transparent 96%);
}

.about-command__frame {
  position: absolute;
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.about-command__copy-shell {
  position: relative;
  z-index: 2;
}

.about-command__copy {
  position: relative;
  min-height: 22rem;
  max-width: 23rem;
}

.about-command__panel {
  position: absolute;
  inset: auto 0 auto 0;
}

.about-command__hotspot {
  position: absolute;
  width: var(--spot-size);
  height: var(--spot-size);
  border-radius: 999px;
  transform: translate(-50%, -50%);
  mix-blend-mode: screen;
}

.about-command__hotspot::after {
  content: '';
  position: absolute;
  inset: 50%;
  width: 0.8rem;
  height: 0.8rem;
  border-radius: 999px;
  transform: translate(-50%, -50%);
}

.about-command__hotspot--gold {
  background: rgba(232, 255, 82, 0.16);
  box-shadow: 0 0 0 1px rgba(232, 255, 82, 0.1);
}

.about-command__hotspot--gold::after {
  background: #e8ff52;
}

.about-command__hotspot--coral {
  background: rgba(255, 102, 73, 0.28);
  box-shadow: 0 0 0 1px rgba(255, 102, 73, 0.14);
}

.about-command__hotspot--coral::after {
  background: #ff6649;
}

.about-command__hotspot--lime {
  background: rgba(226, 255, 94, 0.2);
  box-shadow: 0 0 0 1px rgba(226, 255, 94, 0.12);
}

.about-command__hotspot--lime::after {
  background: #e2ff5e;
}

.about-command__label {
  position: absolute;
  transform: translateY(-50%);
  padding: 0.45rem 0.75rem;
  font-family: var(--font-mono);
  font-size: 0.78rem;
  font-weight: 700;
  line-height: 1;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  white-space: nowrap;
}

.about-command__label--gold {
  background: #e8ff52;
  color: #121212;
}

.about-command__label--coral {
  background: #ff6649;
  color: #ffffff;
}

.about-command__label--lime {
  background: #e8ff52;
  color: #121212;
}

.about-command__dot {
  position: absolute;
  border-radius: 999px;
  transform: translate(-50%, -50%);
}

.about-command__dot--muted {
  background: rgba(255, 255, 255, 0.18);
}

.about-command__dot--coral {
  background: #ff6a4d;
}

.about-command__dot--lime {
  background: #e8ff52;
}

.about-command__rail {
  position: relative;
  width: 1px;
  height: 14rem;
  background: rgba(255, 255, 255, 0.14);
  margin-top: auto;
  margin-bottom: 4rem;
}

.about-command__rail-fill {
  position: absolute;
  inset: 0;
  background: #e8ff52;
}

.about-stat {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.04);
  padding: 1rem;
}

.about-command__scroll-mark {
  position: absolute;
  left: 32%;
  top: 10%;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.about-command__scroll-orbit {
  width: 6.8rem;
  height: 6.8rem;
  border-radius: 999px;
  border: 4px solid #e8ff52;
  border-left-color: transparent;
  border-bottom-color: transparent;
  display: block;
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
  .about-command__field {
    position: relative;
    inset: auto;
    min-height: 28rem;
    margin-top: 1rem;
  }

  .about-command__copy {
    min-height: auto;
    max-width: none;
  }

  .about-command__panel {
    position: static;
    inset: auto;
    margin-bottom: 2rem;
  }

  .about-command__visual {
    min-height: 28rem;
  }

  .about-command__hotspot,
  .about-command__label {
    opacity: 1 !important;
  }

  .about-values__card {
    min-height: auto;
  }
}

@media (max-width: 767px) {
  .about-command__field {
    min-height: 23rem;
    clip-path: polygon(4% 0%, 92% 0%, 92% 3%, 100% 3%, 100% 94%, 90% 94%, 90% 100%, 12% 100%, 12% 96%, 4% 96%, 4% 90%, 0% 90%, 0% 8%, 4% 8%);
  }

  .about-command__visual {
    position: relative;
    min-height: 24rem;
  }

  .about-command__frame:nth-child(n + 3) {
    display: none;
  }

  .about-command__label {
    font-size: 0.68rem;
    padding: 0.38rem 0.58rem;
  }
}
</style>
