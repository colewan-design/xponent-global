<script setup>
/**
 * About page rebuilt around a pinned scrollytelling command scene.
 * Anchor ids remain stable because the header links directly to them.
 */
const { data: about } = await useApiFetch('/page-content/about')
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })
const { data: affiliations } = await useApiFetch('/partners', { params: { type: 'affiliation' } })
const aboutCommandBackdropImage = '/images/about-inversa.webp'
const aboutEditorialImage = '/cms/seed/gallery-img-02.jpg'
const aboutEditorialReverseImage = '/cms/seed/gallery-img-06.jpg'
const aboutEditorialMirrorImage = '/cms/seed/gallery-img-01.jpg'
const aboutHarvesterManagersPrimary = '/images/inversa/managers-1.svg'
const aboutHarvesterManagersSecondary = '/images/inversa/managers-2.svg'
const aboutHarvesterManagerLead = '/images/inversa/manager-lead-1.svg'
/* Drawn on the lead's own 256x816 canvas, so stacking it over him at the same
   geometry lands the badge on his chest without a single tuned offset. */
const aboutHarvesterManagerLeadCam = '/images/inversa/manager-lead-1-cam.svg'
/* Rendered turn, not a CSS one: the clip carries the unit around its own axis
   on a #13130f ground that matches the section, so it needs no blend to sit. */
const aboutHarvesterDeviceClip = '/videos/body-cam.mp4'
const aboutHarvesterSpeciesScan = '/images/inversa/data-sources-1.svg'

useSeoMeta({
  title: 'About Us',
  description: 'Learn about Xponent Global - our vision, mission, core values, and where we operate.',
})

const pageRef = ref(null)
const commandSceneRef = ref(null)
const commandBriefRef = ref(null)
const briefSectionRef = ref(null)
const commandStageRef = ref(null)
const commandGateRef = ref(null)
const commandFieldRef = ref(null)
const commandBackdropRef = ref(null)
const commandBootRef = ref(null)
const commandBootReadoutRef = ref(null)
const commandBootPhaseRef = ref(null)
const commandBootTagRef = ref(null)
const commandLandingRef = ref(null)
const commandSurfaceRef = ref(null)
const commandVisualRef = ref(null)
const commandProgressRef = ref(null)
const commandCursorRef = ref(null)
const commandCursorProgressRef = ref(null)
const harvesterRef = ref(null)
const valuesSectionRef = ref(null)
const valuesTrackRef = ref(null)
const footprintRef = ref(null)
const footprintMapRef = ref(null)
const affiliationRef = ref(null)

const commandHotspotRefs = ref([])
const commandLabelRefs = ref([])
const valueCardRefs = ref([])
const officeCardRefs = ref([])
const affiliationCardRefs = ref([])

let destroyScroll = () => {}

const sections = computed(() => about.value?.data?.sections ?? [])
const legacyAboutSectionCopy = {
  intro: {
    heading: 'About XGL â€” Who we are',
    body: "Xponent Global Limited\n\nXponent Global is an international total solutions provider in the mining, drilling, oil and gas, construction and energy sector. With combined professional experience of over 30 years, Xponent Global's broad scope of expertise includes effectively turning ideas into opportunities and opportunities into action, helping nations thrive and work towards a better world.",
  },
  vision: {
    heading: 'Our Vision',
    body: 'To drive innovation, excellence, and reliability, ensuring we remain the preferred choice for companies worldwide.',
  },
  mission: {
    heading: 'Our Mission',
    body: 'To provide the best possible product, service, technology and pricing on merchandise with the expertise to bring the deal together smoothly and quickly. We believe that to be able to run a great drilling & mining operation, a dependable and reliable supplier should be part of the team.',
  },
  coreValues: {
    heading: 'Our Core Values',
    body: "Quality: We prioritize client satisfaction by optimizing resources and ensuring rigorous quality control, maintaining accuracy from loading to shipment.\n\nCommitment: Upholding combined 30+ years of excellence, we seamlessly extend client operations with timely, organized delivery exceeding expectations.\n\nValue: We take pride in delivering the best deals, earning trust as the preferred global supplier in construction, mining, and exploration.",
  },
  whereWeOperate: {
    heading: 'Where We Operate',
    body: null,
  },
  affiliations: {
    heading: 'Our Affiliations',
    body: "We proudly support these organizations based on our mutual interests within the mining, construction and geotechnical industry.\n\nWe aim to develop and maintain a strong relationship with them to support their causes, extend our own knowledge, expertise and network of specialists.",
  },
}

const aboutSectionCopy = {
  intro: {
    heading: 'About XGL - Who we are',
    body: "Xponent Global Limited\n\nXponent Global is an international total solutions provider supporting mining, drilling, oil and gas, construction, energy, and industrial operations. With more than 30 years of combined experience, we connect specialist products, commercial insight, and dependable execution to help clients move from planning to delivery with confidence.",
  },
  vision: {
    heading: 'Our Vision',
    body: 'To be the preferred global solutions partner for industries that depend on safe, efficient, and reliable field operations.',
  },
  mission: {
    heading: 'Our Mission',
    body: "To deliver the right products, technical support, and commercial solutions at the right time and value.\n\nWe work as an extension of our clients' teams, helping keep drilling, mining, construction, and energy operations supplied, responsive, and ready to perform.",
  },
  coreValues: {
    heading: 'Our Core Values',
    body: "Quality: We maintain high standards across sourcing, coordination, and delivery so every order arrives accurate, compliant, and ready for use.\n\nCommitment: We respond with urgency, communicate clearly, and stay accountable from first enquiry to final delivery.\n\nValue: We combine technical understanding, trusted supply partnerships, and commercial discipline to deliver practical value in every project.",
  },
  whereWeOperate: {
    heading: 'Where We Operate',
    body: 'Our network spans key operating markets, with teams and partners positioned to support mobilisation, procurement, and delivery close to the projects we serve.',
  },
  affiliations: {
    heading: 'Our Affiliations',
    body: "Our industry affiliations keep us connected to the sectors we serve and strengthen the relationships, knowledge, and standards behind our work.\n\nThrough these networks, we stay engaged with industry developments, contribute to shared priorities, and expand the specialist support available to our clients.",
  },
}

const normalizeAboutSection = (section, legacy, replacement) => {
  if (!section) return { ...replacement, image: replacement.image ?? null }

  const heading = section.heading ?? null
  const body = section.body ?? null
  const hasMeaningfulCopy = Boolean((heading ?? '').trim() || (body ?? '').trim())
  const matchesLegacy = heading === legacy.heading && body === legacy.body
  const needsReplacement = !hasMeaningfulCopy || matchesLegacy || body === legacy.body

  return {
    ...section,
    ...(needsReplacement ? replacement : {}),
    image: section.image ?? replacement.image ?? null,
  }
}

const intro = computed(() => normalizeAboutSection(sections.value[0], legacyAboutSectionCopy.intro, aboutSectionCopy.intro))
const vision = computed(() => normalizeAboutSection(sections.value[1], legacyAboutSectionCopy.vision, aboutSectionCopy.vision))
const mission = computed(() => normalizeAboutSection(sections.value[2], legacyAboutSectionCopy.mission, aboutSectionCopy.mission))
const coreValues = computed(() => normalizeAboutSection(sections.value[3], legacyAboutSectionCopy.coreValues, aboutSectionCopy.coreValues))
const whereWeOperate = computed(() => normalizeAboutSection(sections.value[4], legacyAboutSectionCopy.whereWeOperate, aboutSectionCopy.whereWeOperate))
const affiliationsIntro = computed(() => normalizeAboutSection(sections.value[5], legacyAboutSectionCopy.affiliations, aboutSectionCopy.affiliations))

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

/*
 * Map telemetry, not copy. These three points are all the pinned scene narrates
 * now: it lights them in turn as it scrubs. The intro they used to caption scrolls
 * past the stage under its own steam, and the mission and vision briefs stand as
 * their own sections below, so nothing in here reads from the CMS any more.
 */
const commandStates = [
  {
    key: 'network',
    tag: 'Support network',
    tone: 'gold',
    x: 58,
    y: 56,
    size: 15,
    pulseDelay: 0.4,
    pulseDuration: 4.6,
    labelDx: 4,
    labelDy: -1,
  },
  {
    key: 'delivery',
    tag: 'Delivery brief',
    tone: 'coral',
    x: 53,
    y: 63,
    size: 20,
    pulseDelay: 0,
    pulseDuration: 3.8,
    labelDx: 4,
    labelDy: 0,
  },
  {
    key: 'reach',
    tag: 'Global reach',
    tone: 'lime',
    x: 75,
    y: 39,
    size: 8,
    pulseDelay: 0.8,
    pulseDuration: 4.1,
    labelDx: 4,
    labelDy: -1,
  },
]

const commandFrames = [
  { left: '14%', top: '12%', width: '58%', height: '64%' },
  { left: '37%', top: '4%', width: '16%', height: '16%' },
  { left: '67%', top: '10%', width: '18%', height: '40%' },
  { left: '27%', top: '55%', width: '26%', height: '28%' },
  { left: '62%', top: '66%', width: '22%', height: '18%' },
]

/*
 * `delay` staggers each pulse so the telemetry reads as scattered signal, not a
 * wave. The drift values steer a slow wander around the dot's anchor point:
 * signs, amplitudes and durations are all deliberately mismatched, and the
 * negative `driftDelay` drops each dot in at a different point on its path so
 * the field never falls into a visible pattern.
 */
const commandDots = [
  { left: '4%', top: '6%', tone: 'muted', size: '0.32rem', delay: 0.42, duration: 3.4, driftX: '1.6rem', driftY: '-1.1rem', driftDuration: 31, driftDelay: -4 },
  { left: '11%', top: '44%', tone: 'muted', size: '0.28rem', delay: 1.05, duration: 3.9, driftX: '-1.2rem', driftY: '1.5rem', driftDuration: 38, driftDelay: -19 },
  { left: '33%', top: '18%', tone: 'coral', size: '0.42rem', delay: 0, duration: 2.4, driftX: '2.1rem', driftY: '1.3rem', driftDuration: 27, driftDelay: -11 },
  { left: '37%', top: '62%', tone: 'coral', size: '0.42rem', delay: 0.68, duration: 2.9, driftX: '-1.8rem', driftY: '-1.4rem', driftDuration: 34, driftDelay: -22 },
  { left: '42%', top: '24%', tone: 'lime', size: '0.42rem', delay: 0.22, duration: 2.6, driftX: '1.3rem', driftY: '-1.9rem', driftDuration: 24, driftDelay: -7 },
  { left: '48%', top: '51%', tone: 'lime', size: '0.38rem', delay: 0.94, duration: 2.2, driftX: '-2.2rem', driftY: '0.9rem', driftDuration: 29, driftDelay: -15 },
  { left: '66%', top: '18%', tone: 'muted', size: '0.36rem', delay: 0.55, duration: 3.6, driftX: '1.1rem', driftY: '1.7rem', driftDuration: 36, driftDelay: -3 },
  { left: '79%', top: '27%', tone: 'coral', size: '0.42rem', delay: 0.36, duration: 2.75, driftX: '-1.5rem', driftY: '-1.2rem', driftDuration: 26, driftDelay: -18 },
  { left: '89%', top: '60%', tone: 'muted', size: '0.32rem', delay: 1.28, duration: 4.1, driftX: '1.9rem', driftY: '1.1rem', driftDuration: 33, driftDelay: -9 },
]

const commandDotStyle = (dot) => ({
  left: dot.left,
  top: dot.top,
  width: dot.size,
  height: dot.size,
  '--dot-delay': `${dot.delay}s`,
  '--dot-duration': `${dot.duration}s`,
  '--drift-x': dot.driftX,
  '--drift-y': dot.driftY,
  '--drift-duration': `${dot.driftDuration}s`,
  '--drift-delay': `${dot.driftDelay}s`,
})

const commandHotspotStyle = (state) => ({
  left: `${state.x}%`,
  top: `${state.y}%`,
  '--spot-size': `${state.size}rem`,
  '--spot-delay': `${state.pulseDelay}s`,
  '--spot-duration': `${state.pulseDuration}s`,
})

const commandLabelStyle = (state) => ({
  left: `${state.x + state.labelDx}%`,
  top: `${state.y + state.labelDy}%`,
})

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
  commandHotspotRefs.value = []
  commandLabelRefs.value = []
  valueCardRefs.value = []
  officeCardRefs.value = []
  affiliationCardRefs.value = []
})

/**
 * Dot and hotspot pulses are CSS-driven and gated by these classes so they only
 * run while the mask is locked into the command frame — no motion burned before
 * or after. The pulses free-run on their own clock; scroll only arms them, it
 * never scrubs them.
 */
const setCommandTelemetryLive = (isLive) => {
  const surface = commandSurfaceRef.value
  const visual = commandVisualRef.value
  if (surface) surface.classList.toggle('about-command__surface--live', isLive)
  if (visual) visual.classList.toggle('about-command__visual--live', isLive)
}

/**
 * One page-level bar replaces the chrome strip each section used to draw for
 * itself. It floats over the sections instead of sitting in flow, so the pinned
 * command scene still opens at a full viewport — which means the bar carries no
 * background of its own and has to borrow contrast from whatever is beneath it.
 * Every top-level section declares `data-about-tone`, and the bar flips ink as
 * that section passes under it.
 */
const aboutNavLinks = [
  { label: 'About Us', href: '/about' },
  { label: 'Solutions', href: '/solutions' },
  { label: 'Our Clients', href: '/clients' },
  { label: 'Sustainability', href: '/sustainability' },
  { label: 'Media', href: '/media/resources' },
  { label: 'Career', href: '/careers' },
  { label: 'Contact Us', href: '/contact' },
]

const isNavOpen = ref(false)
const navTone = ref('dark')

// The panel is always dark, so the bar sitting on top of it is always light.
const navToneClass = computed(() => `about-nav--${isNavOpen.value ? 'dark' : navTone.value}`)

let toneObserver = null

/**
 * Watches a 1px band just under the bar: whichever section straddles that line
 * is the one the bar is drawn on top of. Cheaper and steadier than measuring
 * section offsets on every scroll frame, and it stays correct while the command
 * scene's sticky stage is parked at the top of the viewport.
 */
const setupNavTone = () => {
  if (!import.meta.client || !pageRef.value || typeof IntersectionObserver === 'undefined') return

  const sections = pageRef.value.querySelectorAll('[data-about-tone]')
  if (!sections.length) return

  const band = 76
  toneObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) navTone.value = entry.target.dataset.aboutTone
      })
    },
    { rootMargin: `-${band}px 0px -${Math.max(0, window.innerHeight - band - 1)}px 0px` },
  )

  sections.forEach((section) => toneObserver.observe(section))
}

const teardownNavTone = () => {
  toneObserver?.disconnect()
  toneObserver = null
}

// rootMargin is baked in pixels, so a viewport height change invalidates it.
const rebuildNavTone = () => {
  teardownNavTone()
  setupNavTone()
}

const handleNavKeydown = (event) => {
  if (event.key === 'Escape') isNavOpen.value = false
}

watch(isNavOpen, (open) => {
  if (import.meta.client) document.body.style.overflow = open ? 'hidden' : ''
})

/*
 * The field is painted shut so the scene never flashes whole before GSAP is
 * imported, which means anything that skips the boot has to open it by hand.
 */
const openCommandField = () => {
  const stage = commandStageRef.value
  if (!stage) return
  for (const edge of ['--boot-t', '--boot-r', '--boot-b', '--boot-l']) {
    stage.style.setProperty(edge, '0%')
  }
  commandGateRef.value?.style.setProperty('--boot-bite', '0')
}

const setupScrollScenes = async () => {
  if (!import.meta.client || !pageRef.value) {
    return
  }

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    if (commandBootRef.value) commandBootRef.value.style.display = 'none'
    openCommandField()
    return
  }

  const [{ default: gsap }, scrollTriggerModule, lenisModule] = await Promise.all([
    import('gsap'),
    import('gsap/ScrollTrigger'),
    import('lenis'),
  ])
  const ScrollTrigger = scrollTriggerModule.ScrollTrigger || scrollTriggerModule.default
  const Lenis = lenisModule.default || lenisModule.Lenis

  gsap.registerPlugin(ScrollTrigger)

  const refreshTimers = []
  let destroyLenis = () => {}

  if (Lenis) {
    const lenis = new Lenis({
      lerp: 0.08,
      smoothWheel: true,
      syncTouch: false,
      wheelMultiplier: 0.9,
      touchMultiplier: 1,
    })
    const tickLenis = (time) => lenis.raf(time * 1000)

    lenis.on('scroll', ScrollTrigger.update)
    gsap.ticker.add(tickLenis)
    gsap.ticker.lagSmoothing(0)

    destroyLenis = () => {
      gsap.ticker.remove(tickLenis)
      lenis.destroy()
    }
  }

  const ctx = gsap.context(() => {
    const fadeUp = {
      y: 42,
      autoAlpha: 0,
      duration: 0.9,
      ease: 'power3.out',
      stagger: 0.12,
    }

    const commandBoot = commandBootRef.value
    const commandStage = commandStageRef.value
    const commandGate = commandGateRef.value
    const commandBootReadout = commandBootReadoutRef.value
    const commandBootPhase = commandBootPhaseRef.value
    const commandBootTag = commandBootTagRef.value
    const commandRail = commandProgressRef.value?.parentElement
    const introTimeline = gsap.timeline()

    gsap.set('.about-command__frame', { autoAlpha: 0, scaleX: 0.92, transformOrigin: 'center center' })
    gsap.set('.about-command__visual', { autoAlpha: 0 })
    if (commandSurfaceRef.value) gsap.set(commandSurfaceRef.value, { autoAlpha: 0 })
    if (commandLandingRef.value) gsap.set(commandLandingRef.value, { autoAlpha: 0, y: 32 })
    gsap.set('.about-hero__aside', { autoAlpha: 0, y: 24 })
    if (commandRail) gsap.set(commandRail, { autoAlpha: 0 })

    if (commandBoot && commandStage) {
      gsap.set(commandBoot, { autoAlpha: 1 })
      if (commandBootReadout) gsap.set(commandBootReadout, { x: 18, autoAlpha: 0 })
      if (commandBootTag) gsap.set(commandBootTag, { y: 12, autoAlpha: 0 })

      /*
       * Shut, the window is a zero-height slit sitting low in the frame. The clip
       * and every marker the boot draws read these same four insets, so the lines,
       * the readout and the tag ride the edges of the opening rather than running
       * beside it on their own clocks and drifting off it mid-reveal.
       */
      const stageEdge = (name, fallback) =>
        getComputedStyle(commandStage).getPropertyValue(name).trim() || fallback
      const shutX = stageEdge('--boot-shut-x', '29%')
      const wideX = stageEdge('--boot-wide-x', '18.5%')

      gsap.set(commandStage, { '--boot-t': '81%', '--boot-r': shutX, '--boot-b': '19%', '--boot-l': shutX })

      introTimeline
        .fromTo(
          '.about-command__boot-line',
          { scaleX: 0.56, autoAlpha: 0.4 },
          { scaleX: 1, autoAlpha: 1, duration: 0.84, stagger: 0.06, ease: 'power2.out' },
          0.02,
        )
        // 1 - the slit opens upward, the top edge doing all but a sliver of the travel.
        .to(commandStage, { '--boot-t': '9%', '--boot-b': '4.5%', duration: 0.95, ease: 'power3.inOut' }, 0.12)
        // 2 - held at that height, the sides widen.
        .to(commandStage, { '--boot-l': wideX, '--boot-r': wideX, duration: 0.5, ease: 'power2.inOut' }, 1.04)
        // 3 - all four edges run out to the frame together.
        .to(
          commandStage,
          {
            '--boot-t': '0%',
            '--boot-r': '0%',
            '--boot-b': '0%',
            '--boot-l': '0%',
            duration: 0.74,
            ease: 'power3.inOut',
          },
          1.48,
        )

      // Cut on the gate rather than the stage, so it rides the same beat from the
      // element the polygon is actually resolved against.
      if (commandGate) {
        introTimeline.to(commandGate, { '--boot-bite': 0, duration: 0.74, ease: 'power3.inOut' }, 1.48)
      }

      if (commandBootTag) {
        introTimeline.to(commandBootTag, { y: 0, autoAlpha: 1, duration: 0.4, ease: 'power2.out' }, 0.16)
      }

      if (commandBootPhase) {
        // Its own tween rather than the timeline's progress: the readout should
        // climb evenly across the three stages, not stall on each hand-off.
        const phase = { value: 0 }
        introTimeline.to(
          phase,
          {
            value: 100,
            duration: 2.1,
            ease: 'power1.inOut',
            onUpdate: () => {
              commandBootPhase.textContent = `${Math.round(phase.value)}%`
            },
          },
          0.12,
        )
      }

      if (commandBootReadout) {
        introTimeline
          .to(commandBootReadout, { x: 0, autoAlpha: 1, duration: 0.42, ease: 'power2.out' }, 0.08)
          .to(commandBootReadout, { autoAlpha: 0.28, duration: 0.24, ease: 'power1.out' }, 1.52)
      }

      introTimeline.to(commandBoot, { autoAlpha: 0, duration: 0.5, ease: 'power2.out' }, 1.86)
    } else {
      openCommandField()
    }

    if (commandLandingRef.value) {
      introTimeline.to(commandLandingRef.value, { y: 0, autoAlpha: 1, duration: 0.72, ease: 'power3.out' }, 1.72)
    }

    introTimeline.to('.about-hero__aside', { y: 0, autoAlpha: 1, duration: 0.66, ease: 'power3.out' }, 1.94)

    const commandCursor = commandCursorRef.value
    const commandCursorProgress = commandCursorProgressRef.value
    let removeCursorListeners = () => {}

    const cursorScenes = [commandSceneRef.value].filter(Boolean)

    if (commandCursor && cursorScenes.length && window.matchMedia('(pointer: fine)').matches) {
      gsap.set(commandCursor, { xPercent: -50, yPercent: -50, autoAlpha: 0, scale: 0.72 })
      if (commandCursorProgress) gsap.set(commandCursorProgress, { strokeDasharray: '0 1', strokeDashoffset: 0 })

      const moveCursorX = gsap.quickTo(commandCursor, 'x', { duration: 0.18, ease: 'power3.out' })
      const moveCursorY = gsap.quickTo(commandCursor, 'y', { duration: 0.18, ease: 'power3.out' })
      const handlePointerMove = (event) => {
        moveCursorX(event.clientX)
        moveCursorY(event.clientY)
      }
      const handlePointerEnter = (event) => {
        gsap.set(commandCursor, { x: event.clientX, y: event.clientY })
        gsap.to(commandCursor, { autoAlpha: 1, scale: 1, duration: 0.28, ease: 'power3.out' })
      }
      const handlePointerLeave = () => {
        gsap.to(commandCursor, { autoAlpha: 0, scale: 0.72, duration: 0.2, ease: 'power2.out' })
      }

      cursorScenes.forEach((scene) => {
        scene.addEventListener('pointermove', handlePointerMove)
        scene.addEventListener('pointerenter', handlePointerEnter)
        scene.addEventListener('pointerleave', handlePointerLeave)
      })

      removeCursorListeners = () => {
        cursorScenes.forEach((scene) => {
          scene.removeEventListener('pointermove', handlePointerMove)
          scene.removeEventListener('pointerenter', handlePointerEnter)
          scene.removeEventListener('pointerleave', handlePointerLeave)
        })
      }
    }

    if (commandCursorProgress) {
      ScrollTrigger.create({
        trigger: pageRef.value,
        start: 'top top',
        end: 'bottom bottom',
        onUpdate: (self) => {
          const progress = gsap.utils.clamp(0, 1, self.progress)
          gsap.set(commandCursorProgress, { strokeDasharray: `${progress} 1`, strokeDashoffset: 0 })
        },
      })
    }

    setCommandTelemetryLive(false)

    const mm = gsap.matchMedia()

    mm.add('(min-width: 1024px)', () => {
      const hotspots = commandHotspotRefs.value
      const labels = commandLabelRefs.value
      const field = commandFieldRef.value

      if (!hotspots.length || !field) return

      // Both polygons carry the same 68 points in the same order so GSAP can morph
      // between them: every notch in commandMask collapses onto the straight edge
      // of fullMask (its two risers share one point there). Four tabs per edge.
      const fullMask = 'polygon(0% 0%,11% 0%,11% 0%,22% 0%,22% 0%,33% 0%,33% 0%,44% 0%,44% 0%,56% 0%,56% 0%,67% 0%,67% 0%,78% 0%,78% 0%,89% 0%,89% 0%,100% 0%,100% 11%,100% 11%,100% 22%,100% 22%,100% 33%,100% 33%,100% 44%,100% 44%,100% 56%,100% 56%,100% 67%,100% 67%,100% 78%,100% 78%,100% 89%,100% 89%,100% 100%,89% 100%,89% 100%,78% 100%,78% 100%,67% 100%,67% 100%,56% 100%,56% 100%,44% 100%,44% 100%,33% 100%,33% 100%,22% 100%,22% 100%,11% 100%,11% 100%,0% 100%,0% 89%,0% 89%,0% 78%,0% 78%,0% 67%,0% 67%,0% 56%,0% 56%,0% 44%,0% 44%,0% 33%,0% 33%,0% 22%,0% 22%,0% 11%,0% 11%)'
      const commandMask = 'polygon(18% 8%,25% 8%,25% 4%,32% 4%,32% 8%,39% 8%,39% 11%,46% 11%,46% 8%,54% 8%,54% 3%,61% 3%,61% 8%,68% 8%,68% 5%,75% 5%,75% 10%,82% 10%,82% 19%,85% 19%,85% 28%,82% 28%,82% 36%,79% 36%,79% 45%,82% 45%,82% 55%,86% 55%,86% 64%,82% 64%,82% 72%,85% 72%,85% 81%,82% 81%,82% 90%,75% 90%,75% 95%,68% 95%,68% 90%,61% 90%,61% 87%,54% 87%,54% 90%,46% 90%,46% 94%,39% 94%,39% 90%,32% 90%,32% 95%,25% 95%,25% 90%,18% 90%,18% 81%,15% 81%,15% 72%,18% 72%,18% 63%,21% 63%,21% 54%,18% 54%,18% 44%,14% 44%,14% 35%,18% 35%,18% 26%,15% 26%,15% 17%,18% 17%)'

      gsap.set(hotspots, { autoAlpha: 0.28, scale: 0.62 })
      gsap.set(labels, { autoAlpha: 0.2, y: 20 })
      gsap.set(hotspots[0], { autoAlpha: 1, scale: 1 })
      gsap.set(labels[0], { autoAlpha: 1, y: 0 })
      gsap.set(field, { clipPath: fullMask })
      // Same filter functions in the same order at both ends so GSAP interpolates
      // the string rather than swapping it on the first frame.
      gsap.set(commandBackdropRef.value, { filter: 'grayscale(0) contrast(1)' })
      if (commandProgressRef.value) gsap.set(commandProgressRef.value, { scaleY: 0, transformOrigin: 'top center' })
      if (commandCursorProgress) gsap.set(commandCursorProgress, { strokeDasharray: '0 1', strokeDashoffset: 0 })

      // Timeline positions the dot pulse hangs off: it arms the instant the mask
      // finishes locking and disarms as the scene starts releasing.
      const maskLockStart = 1.36
      const maskLockDuration = 0.42
      const maskLockedAt = maskLockStart + maskLockDuration
      const sceneExitAt = 4.82

      const timeline = gsap.timeline({
        defaults: { ease: 'none' },
        // Fires on every playhead render, including the scrub's easing frames, so
        // the pulse still arms when the scroll stops just short of the lock.
        onUpdate: () => {
          const time = timeline.time()
          setCommandTelemetryLive(time >= maskLockedAt && time < sceneExitAt)
        },
        scrollTrigger: {
          trigger: commandSceneRef.value,
          start: 'top top',
          end: '+=420%',
          scrub: 1,
          invalidateOnRefresh: true,
          onUpdate: (self) => {
            if (commandProgressRef.value) gsap.set(commandProgressRef.value, { scaleY: self.progress })
          },
        },
      })

      timeline
        .to(commandBackdropRef.value, { yPercent: -10, duration: 1, ease: 'none' }, 0)
        .to(field, { clipPath: commandMask, duration: maskLockDuration }, maskLockStart)
        // The backdrop drains to black and white as the mask locks, so the read-out
        // layer is the only thing holding colour while the scene is armed.
        .to(
          commandBackdropRef.value,
          { filter: 'grayscale(1) contrast(1.08)', duration: maskLockDuration },
          maskLockStart,
        )
        .to(commandSurfaceRef.value, { autoAlpha: 1, duration: 0.24 }, 1.48)
        .to('.about-command__frame', { autoAlpha: 1, scaleX: 1, duration: 0.28, stagger: 0.03 }, 1.52)
        .to('.about-command__visual', { autoAlpha: 1, duration: 0.24 }, 1.58)

      if (commandRail) timeline.to(commandRail, { autoAlpha: 1, duration: 0.22 }, 1.62)

      commandStates.forEach((state, index) => {
        const previousIndex = index - 1
        const stateStart = index + 2.22

        if (previousIndex >= 0) {
          timeline.to(
            hotspots[previousIndex],
            {
              autoAlpha: 0.18,
              scale: 0.5,
            },
            stateStart - 0.16,
          )

          timeline.to(
            labels[previousIndex],
            {
              autoAlpha: 0,
              y: -14,
            },
            stateStart - 0.16,
          )
        }

        if (index > 0) {
          timeline.to(
            hotspots[index],
            {
              autoAlpha: 1,
              scale: 1,
            },
            stateStart,
          )

          timeline.to(
            labels[index],
            {
              autoAlpha: 1,
              y: 0,
            },
            stateStart,
          )
        }
      })

      timeline
        .to(['.about-command__surface', '.about-command__visual', commandRail], {
          autoAlpha: 0,
          duration: 0.22,
        }, sceneExitAt)
        .to(field, { clipPath: fullMask, duration: 0.42 }, 4.92)
        .to(commandBackdropRef.value, { yPercent: -16, duration: 0.42, ease: 'none' }, 4.92)
        .to(commandBackdropRef.value, { filter: 'grayscale(0) contrast(1)', duration: 0.42 }, 4.92)

      // Dropping below the breakpoint kills the timeline, so disarm the pulse too.
      return () => setCommandTelemetryLive(false)
    })

    /*
     * Two acts against one pinned stage. The crowd starts cropped by the fold and
     * grows until it stands centred; the moment it lands, the four behind it fade
     * off, the lead's body cam lights, and the read-out panels station themselves
     * either side of him. Below 1024px the section stays static — there is no room
     * to pin a scene that tall.
     */
    mm.add('(min-width: 1024px)', () => {
      const harvester = harvesterRef.value
      const figures = harvester?.querySelector('.about-harvester__figures')
      if (!harvester || !figures) return

      const harvesterCopy = harvester.querySelector('.about-harvester__content')
      const crowd = harvester.querySelectorAll('.about-harvester__figure--crowd')
      const cam = harvester.querySelector('.about-harvester__figure--cam')
      const hud = harvester.querySelectorAll('.about-harvester__hud-panel, .about-harvester__hud-copy')

      /*
       * Nothing here is measured. The figures are laid out at their settled size
       * in svh (see the stylesheet), so both ends of the growth are plain shares
       * of that box: 86% of it dropped until only heads and shoulders break the
       * fold, 100% of it standing on the floor. The drop is set by what it has to
       * clear — the summary above it — and it is deep on purpose; what keeps it
       * from reading as stuck is how little scroll it holds for, not how shallow
       * it is.
       * A resized window re-resolves the svh itself and the scene follows —
       * measuring innerHeight once, as this used to, left the crowd sized for
       * whatever the window had been when the scene was built.
       */
      const CROPPED_SCALE = 0.86
      const CROPPED_DROP = 70

      gsap.set(figures, { xPercent: -50, transformOrigin: 'center bottom' })

      const timeline = gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: {
          trigger: harvester,
          start: 'top top',
          end: 'bottom bottom',
          scrub: 0.6,
          invalidateOnRefresh: true,
        },
      })

      /*
       * The second act is GSAP's to hold, not the stylesheet's: CSS paints the
       * finished composition so a dead script still shows a complete scene, and
       * these sets are what re-open it for the scrub.
       */
      if (cam) gsap.set(cam, { autoAlpha: 0 })
      if (hud.length) gsap.set(hud, { autoAlpha: 0, y: 28 })

      timeline
        /*
         * Travel, not a fade. Over this slice of the scrub the copy covers its
         * own height in about the scroll it would have taken unpinned, so it
         * leaves the frame the way it would have if the stage never held it.
         */
        .to(harvesterCopy, { yPercent: -100, autoAlpha: 0, duration: 0.2 }, 0.02)
        /*
         * Lands at 0.18 — about a third of a screen of wheel — so the group is only
         * ever briefly cut. The long middle of the scrub is the full crowd standing,
         * which is the frame the scene is actually about.
         */
        .fromTo(
          figures,
          { yPercent: CROPPED_DROP, scale: CROPPED_SCALE },
          { yPercent: 0, scale: 1, duration: 0.16 },
          0.02,
        )
        /*
         * Handover, tight on the landing: the four go the moment the group is
         * centred, the badge lights on the lead they leave behind, and the panels
         * arrive last so the eye moves figure → chest → read-out. Everything is
         * seated by 0.54, leaving the back half of the scrub as the held frame.
         */
        .to(crowd, { autoAlpha: 0, duration: 0.12 }, 0.2)
        .to(cam, { autoAlpha: 1, duration: 0.08 }, 0.3)
        .to(hud, { autoAlpha: 1, y: 0, duration: 0.12, stagger: 0.06 }, 0.36)

      return () => gsap.set([figures, harvesterCopy, cam, ...crowd, ...hud], { clearProps: 'all' })
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

    if (commandBriefRef.value) {
      gsap.from(commandBriefRef.value, {
        ...fadeUp,
        scrollTrigger: {
          trigger: commandBriefRef.value,
          start: 'top 88%',
        },
        clearProps: 'all',
      })
    }

    if (briefSectionRef.value) {
      gsap.from('.about-briefs__item', {
        ...fadeUp,
        scrollTrigger: {
          trigger: briefSectionRef.value,
          start: 'top 72%',
        },
        clearProps: 'all',
      })
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
      setCommandTelemetryLive(false)
      removeCursorListeners()
      mm.revert()
      ctx.revert()
      destroyLenis()
    }
  }, pageRef.value)
}

onMounted(async () => {
  await nextTick()
  setupNavTone()
  window.addEventListener('keydown', handleNavKeydown)
  window.addEventListener('resize', rebuildNavTone)
  await setupScrollScenes()
})

onBeforeUnmount(() => {
  destroyScroll()
  teardownNavTone()
  window.removeEventListener('keydown', handleNavKeydown)
  window.removeEventListener('resize', rebuildNavTone)
  document.body.style.overflow = ''
})
</script>

<template>
  <div ref="pageRef" class="about-page bg-white text-ink">
    <div ref="commandCursorRef" class="about-command__cursor" aria-hidden="true">
      <svg class="about-command__cursor-ring" viewBox="0 0 120 120">
        <circle class="about-command__cursor-track" cx="60" cy="60" r="53"></circle>
        <circle
          ref="commandCursorProgressRef"
          class="about-command__cursor-progress"
          cx="60"
          cy="60"
          r="53"
          pathLength="1"
        ></circle>
      </svg>
      <span>Scroll</span>
    </div>

    <div class="about-nav" :class="navToneClass">
      <NuxtLink to="/" class="about-nav__logo">Xponent Global</NuxtLink>
      <button
        type="button"
        class="about-nav__toggle"
        aria-controls="about-nav-panel"
        :aria-expanded="isNavOpen ? 'true' : 'false'"
        @click="isNavOpen = !isNavOpen"
      >
        {{ isNavOpen ? 'Close' : 'Menu' }}<span aria-hidden="true">&nbsp; -:-</span>
      </button>
    </div>

    <nav
      id="about-nav-panel"
      class="about-nav__panel"
      :class="{ 'about-nav__panel--open': isNavOpen }"
      :inert="isNavOpen ? undefined : true"
      aria-label="Site"
    >
      <NuxtLink
        v-for="link in aboutNavLinks"
        :key="link.href"
        :to="link.href"
        @click="isNavOpen = false"
      >
        {{ link.label }}
      </NuxtLink>
    </nav>

    <section
      id="about-xgl"
      ref="commandSceneRef"
      data-about-tone="dark"
      class="about-command u-grain relative isolate bg-steel-950 text-white"
    >
      <div ref="commandStageRef" class="about-command__stage">
      <div class="about-command__ground" aria-hidden="true"></div>
      <div ref="commandGateRef" class="about-command__gate">
      <div ref="commandFieldRef" class="about-command__field">
        <div ref="commandBackdropRef" class="about-command__backdrop">
          <CmsImage
            :src="aboutCommandBackdropImage"
            alt="Aerial view of a river system and surrounding forest"
            fetchpriority="high"
            class="h-full w-full object-cover"
          />
        </div>

        <div ref="commandSurfaceRef" class="about-command__surface">
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
            :style="commandDotStyle(dot)"
          ></div>
        </div>

        <div ref="commandVisualRef" class="about-command__visual">
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

      <div class="about-command__rail">
        <span ref="commandProgressRef" class="about-command__rail-fill"></span>
      </div>

      <div ref="commandBootRef" class="about-command__boot" aria-hidden="true">
        <span class="about-command__boot-line about-command__boot-line--top"></span>
        <span class="about-command__boot-line about-command__boot-line--bottom"></span>
        <p ref="commandBootTagRef" class="about-command__boot-tag">Loading...</p>
        <div ref="commandBootReadoutRef" class="about-command__boot-readout">
          <span>PHASE</span>
          <span ref="commandBootPhaseRef">0%</span>
          <span>FREQ</span>
          <span>16HZ</span>
        </div>
      </div>
      </div>

      <div ref="commandBriefRef" class="about-command__brief">
        <p class="about-command__brief-tag">&bull;&nbsp; Who we are</p>
        <h2 class="sr-only">{{ intro?.heading ?? 'Who we are' }}</h2>
        <div class="about-command__brief-body">
          <p v-for="(paragraph, index) in splitParagraphs(intro?.body)" :key="`intro-${index}`">
            {{ paragraph }}
          </p>
          <p v-if="!splitParagraphs(intro?.body).length">
            Integrated field support for demanding project environments.
          </p>
        </div>
      </div>

      <div class="about-hero__content">
        <div ref="commandLandingRef" class="about-command__landing">
          <p class="about-command__landing-kicker">About Xponent Global</p>
          <h1>Global supply.<br />Local execution.</h1>
          <a href="#our-mission" class="about-command__landing-link">Explore our mission</a>
        </div>

        <div class="about-hero__aside">
          <p>TOTAL SOLUTIONS PARTNER</p>
          <span>Integrated procurement, logistics, and operational support for mining, drilling, construction, energy, and industrial projects.</span>
        </div>
      </div>

      <div class="about-command__outro">
        <p>&bull;&nbsp; READY FOR WHAT'S NEXT</p>
        <span>
          With the right supply network in place, teams regain momentum, reduce field risk, and keep critical
          operations moving from planning through delivery.
        </span>
      </div>

    </section>

    <section
      ref="briefSectionRef"
      data-about-tone="light"
      class="about-briefs"
      aria-label="Mission and vision"
    >
      <article id="our-mission" class="about-briefs__item">
        <p class="about-briefs__tag">&bull;&nbsp; Our mission</p>
        <h2 class="about-briefs__title">{{ mission?.heading ?? 'Our mission' }}</h2>
        <div class="about-briefs__body">
          <p v-for="(paragraph, index) in splitParagraphs(mission?.body)" :key="`mission-${index}`">
            {{ paragraph }}
          </p>
          <p v-if="!splitParagraphs(mission?.body).length">
            Responsive, field-ready service with disciplined execution.
          </p>
        </div>
      </article>

      <article id="our-vision" class="about-briefs__item">
        <p class="about-briefs__tag">&bull;&nbsp; Our vision</p>
        <h2 class="about-briefs__title">{{ vision?.heading ?? 'Our vision' }}</h2>
        <div class="about-briefs__body">
          <p v-for="(paragraph, index) in splitParagraphs(vision?.body)" :key="`vision-${index}`">
            {{ paragraph }}
          </p>
          <p v-if="!splitParagraphs(vision?.body).length">
            A global support network with reliable local reach.
          </p>
        </div>
      </article>
    </section>

    <section class="about-editorial" data-about-tone="light" aria-labelledby="about-editorial-title">
      <h2 id="about-editorial-title" class="about-editorial__title">
        Xponent is where<br />expertise, reach,<br />and execution meet.
      </h2>

      <div class="about-editorial__copy">
        <p>&bull;&nbsp; DELIVERY</p>
        <span>
          One connected partner reduces procurement friction, consolidates specialist supply, and keeps complex
          projects moving with confidence from planning through field execution.
        </span>
      </div>

      <div class="about-editorial__image">
        <CmsImage
          :src="aboutEditorialImage"
          alt="Xponent field team with drilling equipment"
          loading="lazy"
          class="h-full w-full object-cover"
        />
      </div>
    </section>

    <section class="about-editorial-reverse" data-about-tone="light" aria-label="Field reach">
      <div class="about-editorial-reverse__image">
        <CmsImage
          :src="aboutEditorialReverseImage"
          alt="Xponent field crew gathered at a remote drill site"
          loading="lazy"
          class="h-full w-full object-cover"
        />
      </div>

      <div class="about-editorial-reverse__copy">
        <p>&bull;&nbsp; FIELD REACH</p>
        <span>
          Xponent combines local teams, specialist supply, and regional knowledge to support complex operations
          wherever projects demand responsive execution.
        </span>
        <a href="#where-we-operate">Our footprint</a>
      </div>
    </section>

    <section
      class="about-editorial-reverse about-editorial-reverse--mirror"
      data-about-tone="light"
      aria-label="On the ground"
    >
      <div class="about-editorial-reverse__image">
        <CmsImage
          :src="aboutEditorialMirrorImage"
          alt="Xponent crew and vehicles on a mountain access road during a site mobilisation"
          loading="lazy"
          class="h-full w-full object-cover"
        />
      </div>

      <div class="about-editorial-reverse__copy">
        <p>&bull;&nbsp; ON THE GROUND</p>
        <span>
          Crews, vehicles, and equipment mobilise as one movement, so a remote site gets a working team on
          day one instead of a schedule of separate arrivals.
        </span>
        <a href="#our-core-values">How we work</a>
      </div>
    </section>

    <section
      id="about-harvester"
      ref="harvesterRef"
      class="about-harvester"
      data-about-tone="dark"
      aria-labelledby="about-harvester-title"
    >
      <div class="about-harvester__stage">
        <div class="about-harvester__content">
          <h2 id="about-harvester-title" class="about-harvester__title">
            Built for demanding field operations
          </h2>

          <p class="about-harvester__summary">
            Xponent helps crews mobilize with the equipment, logistics support, and operational visibility needed to keep projects moving safely and on schedule.
          </p>
        </div>

        <div class="about-harvester__figures" aria-hidden="true">
          <img
            :src="aboutHarvesterManagersPrimary"
            alt=""
            loading="lazy"
            class="about-harvester__figure about-harvester__figure--crowd"
          />
          <img
            :src="aboutHarvesterManagersSecondary"
            alt=""
            loading="lazy"
            class="about-harvester__figure about-harvester__figure--crowd"
          />
          <img
            :src="aboutHarvesterManagerLead"
            alt=""
            loading="lazy"
            class="about-harvester__figure about-harvester__figure--lead"
          />
          <img
            :src="aboutHarvesterManagerLeadCam"
            alt=""
            loading="lazy"
            class="about-harvester__figure about-harvester__figure--cam"
          />
        </div>

        <div class="about-harvester__hud" aria-hidden="true">
          <figure class="about-harvester__hud-panel about-harvester__hud-panel--device">
            <figcaption class="about-harvester__hud-tag">FIELD UNIT 01</figcaption>
            <video
              :src="aboutHarvesterDeviceClip"
              autoplay
              loop
              muted
              playsinline
              preload="metadata"
            ></video>
          </figure>

          <figure class="about-harvester__hud-panel about-harvester__hud-panel--scan">
            <figcaption class="about-harvester__hud-tag">
              DEPLOYMENT STATUS
              <em>LIVE FEED</em>
            </figcaption>
            <div class="about-harvester__hud-plate">
              <img :src="aboutHarvesterSpeciesScan" alt="" loading="lazy" />
            </div>
            <p class="about-harvester__hud-foot">// STATUS</p>
          </figure>

          <div class="about-harvester__hud-copy">
            <p class="about-harvester__hud-index">001 &middot; FIELD READOUT</p>
            <p>
              Live operational visibility helps teams coordinate equipment, movement, and site readiness with clearer communication and fewer delays.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section
      data-about-tone="dark"
      class="about-readouts"
      aria-label="Footprint at a glance"
    >
      <div class="container-retail-wide about-readouts__grid">
        <div v-for="(stat, index) in footprintStats" :key="stat.label" class="about-readout">
          <p class="about-readout__tag">
            {{ stat.label }}
            <em>{{ String(index + 1).padStart(3, '0') }}</em>
          </p>
          <p class="about-readout__value">{{ stat.value }}</p>
          <p class="about-readout__detail">{{ stat.detail }}</p>
        </div>
      </div>
    </section>

    <section
      v-if="values.length"
      id="our-core-values"
      ref="valuesSectionRef"
      data-about-tone="dark"
      class="about-values"
      :aria-label="coreValues?.heading"
    >
      <div class="container-retail-wide about-values__inner">
        <div class="about-values__aside">
          <p class="about-values__eyebrow">Operating principles</p>
          <h2 class="about-values__title">{{ coreValues?.heading }}</h2>
          <p class="about-values__lede">
            The standards behind our work are consistent across markets, teams, and projects, shaping how we
            partner, respond, and deliver.
          </p>

          <div class="about-values__rail">
            <div class="about-values__rail-track">
              <div ref="valuesTrackRef" class="about-values__rail-fill"></div>
            </div>
            <p class="about-values__rail-tag">
              Operating rhythm
              <em>{{ String(values.length).padStart(2, '0') }} principles</em>
            </p>
          </div>
        </div>

        <ol class="about-values__list">
          <li
            v-for="(value, index) in values"
            :key="value.term"
            :ref="setValueCardRef"
            class="about-values__row"
          >
            <p class="about-values__index">{{ String(index + 1).padStart(2, '0') }}</p>
            <h3 class="about-values__term">{{ value.term }}</h3>
            <p class="about-values__detail">{{ value.detail }}</p>
          </li>
        </ol>
      </div>
    </section>

    <section
      id="where-we-operate"
      ref="footprintRef"
      data-about-tone="light"
      class="container-retail-wide py-10 sm:py-12 lg:py-14"
    >
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
      data-about-tone="light"
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
@font-face {
  font-family: 'NB International Pro';
  src: url('/fonts/inversa/NBInternationalPro-Regular.woff2') format('woff2');
  font-style: normal;
  font-weight: 400;
  font-display: swap;
}

/*
 * The editorial frame: a step down across the top and a chamfered corner on the
 * bottom, handed left or right by which side of the section the photo sits on. The
 * two are one shape mirrored about x, point for point — kept as a pair of named
 * values rather than three polygons copied out, because the only thing holding the
 * device together is that every photo cuts the same way, and copies drift.
 */
.about-page {
  overflow-x: clip;
  --editorial-frame-left: polygon(50% 0, 100% 0, 100% 88%, 90% 100%, 0 100%, 0 9%, 37% 9%);
  --editorial-frame-right: polygon(50% 0, 0 0, 0 88%, 10% 100%, 100% 100%, 100% 9%, 63% 9%);
}

/**
 * Pinned above every section rather than in flow, so the command scene keeps a
 * full viewport to open into. It paints no background of its own — the sections
 * show straight through and `--tone` supplies the contrast, so the bar has to
 * stay pointer-transparent everywhere except on its own two controls or it
 * would swallow clicks aimed at the scene beneath.
 */
.about-nav {
  position: fixed;
  inset: 0 0 auto;
  z-index: 60;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.35rem 1.15rem;
  color: var(--about-nav-ink);
  pointer-events: none;
  transition: color 0.4s ease;
}

.about-nav--dark {
  --about-nav-ink: rgba(243, 239, 229, 0.96);
  --about-nav-shadow: 0 1px 14px rgba(0, 0, 0, 0.5);
}

.about-nav--light {
  --about-nav-ink: #10110d;
  --about-nav-shadow: none;
}

.about-nav > * {
  pointer-events: auto;
}

.about-nav__logo {
  display: inline-flex;
  min-height: 1.9rem;
  align-items: center;
  padding: 0.18rem 0.42rem 0.1rem;
  border: 1px solid currentColor;
  border-radius: 0.16rem;
  font-family: 'NB International Pro', var(--font-sans);
  font-size: 1.18rem;
  line-height: 1;
  letter-spacing: -0.045em;
  text-transform: uppercase;
  color: inherit;
  text-shadow: var(--about-nav-shadow);
  transition: opacity 0.2s ease;
}

.about-nav__toggle {
  display: inline-flex;
  align-items: center;
  border: 0;
  background: none;
  font-family: var(--font-mono);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: inherit;
  text-shadow: var(--about-nav-shadow);
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.about-nav__logo:hover,
.about-nav__toggle:hover {
  opacity: 0.66;
}

.about-nav__logo:focus-visible,
.about-nav__toggle:focus-visible {
  outline: 2px solid #e8ff52;
  outline-offset: 4px;
}

/* Sits under the bar so its Close control stays on top of the panel. */
.about-nav__panel {
  position: fixed;
  inset: 0;
  z-index: 55;
  display: grid;
  align-content: center;
  padding: 7rem 1.15rem 3rem;
  background: #0e1009;
  color: #f3efe5;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-1.5rem);
  transition:
    opacity 0.34s ease,
    transform 0.34s ease,
    visibility 0.34s;
}

.about-nav__panel--open {
  opacity: 1;
  visibility: visible;
  transform: none;
}

.about-nav__panel a {
  display: block;
  padding: 0.3rem 0;
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(2rem, 5.6vw, 3.6rem);
  line-height: 1.06;
  letter-spacing: -0.045em;
  text-transform: uppercase;
  color: inherit;
  transition:
    color 0.2s ease,
    transform 0.3s ease;
}

.about-nav__panel a:hover {
  color: #e8ff52;
  transform: translateX(0.6rem);
}

.about-hero__aside {
  position: absolute;
  z-index: 5;
  right: 6.5vw;
  bottom: 4.5vh;
  width: min(30rem, 29vw);
  text-shadow: 0 2px 22px rgba(0, 0, 0, 0.42);
}

.about-hero__aside p {
  margin-bottom: 1.2rem;
  color: #e8ff52;
  font-family: var(--font-mono);
  font-size: clamp(0.78rem, 1.2vw, 1.05rem);
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.about-hero__aside span {
  display: block;
  font-size: clamp(0.95rem, 1.3vw, 1.22rem);
  font-weight: 600;
  line-height: 1.28;
}

.about-command {
  position: relative;
  min-height: 650svh;
  background: #0e1009;
  overflow: clip;
}

/*
 * The boot window, as four insets off the stage's own edges. They live here rather
 * than on the field because the field is only one of the things that reads them.
 * CSS holds the shut state so the first paint is the loading screen and not a
 * flash of the whole scene, which is the one thing a reveal cannot come back from
 * — the cost is that a skipped boot has to open the window itself.
 */
.about-command__stage {
  position: sticky;
  top: 0;
  height: 100svh;
  overflow: hidden;
  --boot-shut-x: 29%;
  --boot-wide-x: 18.5%;
  --boot-t: 81%;
  --boot-r: var(--boot-shut-x);
  --boot-b: 19%;
  --boot-l: var(--boot-shut-x);
  --boot-inline: 3%;
}

.about-hero__content {
  position: absolute;
  inset: 0 0 auto;
  z-index: 5;
  height: 100svh;
  pointer-events: none;
}

.about-hero__content a {
  pointer-events: auto;
}

.about-command__outro {
  position: absolute;
  z-index: 6;
  top: 525svh;
  right: 6.5vw;
  width: min(31rem, 31vw);
  text-shadow: 0 2px 22px rgba(0, 0, 0, 0.42);
}

.about-command__outro p {
  margin-bottom: 1.35rem;
  color: #e8ff52;
  font-family: var(--font-mono);
  font-size: clamp(0.8rem, 1.2vw, 1.08rem);
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
}

.about-command__outro span {
  display: block;
  font-size: clamp(1rem, 1.35vw, 1.28rem);
  font-weight: 600;
  line-height: 1.3;
}

.about-editorial {
  position: relative;
  min-height: 112svh;
  overflow: hidden;
  background: #f1f0e6;
  color: #10110d;
}

.about-editorial__title {
  position: relative;
  z-index: 2;
  max-width: 16ch;
  padding: 4.2rem 1.15rem 0;
  font-size: clamp(3.8rem, 4.1vw, 5rem);
  font-weight: 500;
  line-height: 1.03;
  letter-spacing: -0.065em;
}

.about-editorial__copy {
  position: absolute;
  z-index: 2;
  left: 9.2vw;
  top: 72%;
  width: min(27rem, 27vw);
}

.about-editorial__copy p {
  margin-bottom: 1.55rem;
  font-family: var(--font-mono);
  font-size: 0.88rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.about-editorial__copy span {
  display: block;
  font-size: clamp(1rem, 1.25vw, 1.2rem);
  font-weight: 500;
  line-height: 1.3;
}

.about-editorial__image {
  position: absolute;
  right: 1%;
  top: 38%;
  width: 45%;
  height: 68%;
  overflow: hidden;
  clip-path: var(--editorial-frame-right);
}

.about-editorial__image :deep(picture),
.about-editorial__image :deep(img) {
  display: block;
  width: 100%;
  height: 100%;
}

.about-editorial-reverse {
  position: relative;
  min-height: 100svh;
  overflow: hidden;
  background: #f1f0e6;
  color: #10110d;
}

/*
 * Height, not padding, sets the gap under this: the frame is positioned in
 * percentages of the section's padding box, so padding-bottom would grow the box
 * and take the image down with it, still flush against the section below. Ten per
 * cent off the bottom is what leaves a band of the cream ground under the photo.
 */
.about-editorial-reverse__image {
  position: absolute;
  left: 1%;
  top: 10%;
  width: 49%;
  height: 80%;
  overflow: hidden;
  clip-path: var(--editorial-frame-left);
}

.about-editorial-reverse__image :deep(picture),
.about-editorial-reverse__image :deep(img) {
  display: block;
  width: 100%;
  height: 100%;
}

.about-editorial-reverse__copy {
  position: absolute;
  right: 10.5vw;
  top: 43%;
  width: min(29rem, 27vw);
}

/*
 * The same section handed the other way. Only the two edges and the frame flip —
 * the ground, the type, and the breakpoint rules stay in one place, so the pair
 * cannot drift apart.
 */
.about-editorial-reverse--mirror .about-editorial-reverse__image {
  left: auto;
  right: 1%;
  clip-path: var(--editorial-frame-right);
}

.about-editorial-reverse--mirror .about-editorial-reverse__copy {
  right: auto;
  left: 10.5vw;
}

.about-editorial-reverse__copy p {
  margin-bottom: 1.55rem;
  font-family: var(--font-mono);
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.about-editorial-reverse__copy span {
  display: block;
  font-size: clamp(1.05rem, 1.3vw, 1.28rem);
  font-weight: 500;
  line-height: 1.28;
}

.about-editorial-reverse__copy a {
  display: inline-flex;
  min-height: 3.35rem;
  align-items: center;
  margin-top: 1.8rem;
  padding: 0.75rem 1.5rem;
  background: #10110d;
  color: #ffffff;
  font-family: var(--font-mono);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  clip-path: polygon(0 0, 100% 0, 100% 72%, 88% 100%, 0 100%);
}

.about-harvester {
  position: relative;
  min-height: 112svh;
  /* clip, not hidden: hidden would make this the scroll container and kill the
     stage's sticky pin. */
  overflow: clip;
  background: #12120c;
  color: #f3efe5;
  isolation: isolate;
}

.about-harvester::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    radial-gradient(circle at 50% 10%, rgba(255, 255, 255, 0.075), transparent 32%),
    linear-gradient(to right, rgba(255, 255, 255, 0.045) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
  background-size:
    auto,
    92px 92px,
    92px 92px;
  opacity: 0.18;
  mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.9), transparent 76%);
}

.about-harvester__stage {
  position: relative;
  /* Fills the section so the figures still measure their drop from its floor. */
  min-height: inherit;
}

.about-harvester__content {
  position: relative;
  z-index: 2;
  width: min(118rem, calc(100% - 2.3rem));
  margin: 0 auto;
  padding: 10.25rem 0 22rem;
}

.about-harvester__title {
  max-width: 7.2em;
  margin-left: clamp(13rem, 29vw, 35rem);
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(4.2rem, 5.5vw, 7rem);
  font-weight: 400;
  line-height: 0.84;
  letter-spacing: -0.045em;
}

.about-harvester__summary {
  width: min(23.5rem, 25vw);
  margin-top: 1.9rem;
  margin-left: clamp(39rem, 50vw, 62rem);
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(1.18rem, 1.45vw, 1.62rem);
  font-weight: 400;
  line-height: 1.16;
  letter-spacing: -0.03em;
}

.about-harvester__figures {
  position: absolute;
  left: 50%;
  bottom: 0;
  z-index: 1;
  width: min(54rem, 46vw);
  aspect-ratio: 1765 / 1060;
  transform: translateX(-50%);
  pointer-events: none;
}

/*
 * The crowd is two SVGs sharing one 1765x1060 canvas — managers-1 draws the
 * inner pair, managers-2 the outer pair — so they only line up when both are
 * stretched over the same box. Neither holds the fifth figure: it is a separate
 * portrait asset that stands in the gap the pair leaves at centre, a touch
 * taller than the rest so it reads as the one in front.
 *
 * The two canvases are not a shared scale: a crowd figure is drawn the full 1060
 * of its canvas, so it stands the whole height of the box, while the lead is its
 * own 256x817 portrait sized against that same box. Anything under 100% here puts
 * him behind the crowd rather than in front of it — the height below is the
 * overshoot that makes him the tallest, and since width follows the intrinsic
 * ratio he gains breadth with it and reads as the nearest figure.
 */
.about-harvester__figure--crowd {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  max-width: none;
}

.about-harvester__figure--lead,
.about-harvester__figure--cam {
  position: absolute;
  left: 50%;
  bottom: 0;
  width: auto;
  height: 108%;
  max-width: none;
  transform: translateX(-50%);
}

/*
 * The badge is the same canvas as the lead, so it needs no offset of its own —
 * only the same box. Give it a different geometry and it drifts off his chest.
 */
.about-harvester__figure--cam {
  z-index: 1;
}

/*
 * Second act. The panels read as instrument surfaces rather than cards: a hairline
 * frame, one corner notched off, mono type at the top. They are positioned as
 * shares of the stage so they hold their station either side of the lead at any
 * window size, and they are the scene's only lime — the crowd behind them is all
 * hairline grey, so the accent lands on the reading, not the figure.
 */
.about-harvester__hud {
  position: absolute;
  inset: 0;
  z-index: 3;
  pointer-events: none;
}

/*
 * The frame is two clipped layers rather than a border, because clip-path cuts a
 * border off with everything else — which is what left the notch reading as an
 * open edge instead of a cut corner. The panel paints the hairline colour edge to
 * edge and its ::before lays the ground back over it a pixel in, so a line
 * survives every corner the polygon cuts. Both layers read the same custom
 * property, so a panel's shape is declared once, on the panel.
 */
.about-harvester__hud-panel {
  position: absolute;
  isolation: isolate;
  margin: 0;
  padding: 1.15rem 1.25rem 1.35rem;
  background: rgba(255, 255, 255, 0.19);
  clip-path: var(--panel-clip);
}

.about-harvester__hud-panel::before {
  content: '';
  position: absolute;
  inset: 1px;
  z-index: 0;
  background: #12120c;
  clip-path: var(--panel-clip);
}

/* Positioned children clear the ground layer without each needing a rule. */
.about-harvester__hud-panel > * {
  position: relative;
  z-index: 1;
}

.about-harvester__hud-panel--device {
  --panel-clip: polygon(0 0, 85% 0, 100% 13%, 100% 100%, 0 100%);

  left: 25%;
  top: 12%;
  width: min(11rem, 9.5vw);
}

/*
 * The turn is rendered into the clip, so there is no transform here to fight the
 * one GSAP puts on the panel — and no flat-asset edge-on pass, which is what a
 * CSS rotateY of the old still could never avoid. The frame already carries its
 * own margin around the unit, so it runs the full width of the plate.
 */
.about-harvester__hud-panel--device video {
  display: block;
  width: 100%;
  height: auto;
}

.about-harvester__hud-panel--scan {
  --panel-clip: polygon(0 0, 82% 0, 88% 8%, 100% 8%, 100% 100%, 14% 100%, 8% 92%, 0 92%);

  left: 62%;
  top: 25%;
  width: min(34rem, 29vw);
}

.about-harvester__hud-panel img {
  display: block;
  width: 100%;
  height: auto;
}

.about-harvester__hud-tag {
  /* Bullet and label travel together; only the certainty read-out is pushed to
     the far edge, so space-between across all three items is the wrong tool. */
  display: flex;
  align-items: baseline;
  margin-bottom: 0.9rem;
  font-family: var(--font-mono);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  text-transform: uppercase;
  color: rgba(243, 239, 229, 0.72);
}

/*
 * The square bullet the rest of the page's readouts carry, blinking here because
 * these two are live instruments. The keyframes hold each state and cross in 4%
 * of the cycle, so it snaps like a status lamp rather than breathing.
 */
.about-harvester__hud-tag::before {
  content: '';
  flex: none;
  width: 0.32rem;
  height: 0.32rem;
  margin-right: 0.55rem;
  background: #e8ff52;
  animation: about-harvester-signal 1.6s linear infinite;
}

/* Offset so the two panels read as instruments keeping their own time. */
.about-harvester__hud-panel--scan .about-harvester__hud-tag::before {
  animation-delay: -0.7s;
}

@keyframes about-harvester-signal {
  0%,
  48% {
    opacity: 1;
  }

  52%,
  100% {
    opacity: 0.16;
  }
}

.about-harvester__hud-tag em {
  margin-left: auto;
  padding-left: 1rem;
  font-style: normal;
  color: #e8ff52;
}

/* Rules above and below the scan sit on the plate, not the panel, so they track
   the illustration rather than the padding. */
.about-harvester__hud-plate {
  position: relative;
  padding: 0.85rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.16);
  border-bottom: 1px solid rgba(255, 255, 255, 0.16);
}

.about-harvester__hud-foot {
  margin-top: 0.9rem;
  font-family: var(--font-mono);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  color: rgba(243, 239, 229, 0.5);
}

.about-harvester__hud-copy {
  position: absolute;
  left: 9%;
  bottom: 15%;
  width: min(21rem, 22vw);
}

.about-harvester__hud-index {
  margin-bottom: 0.9rem;
  font-family: var(--font-mono);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  color: #e8ff52;
}

.about-harvester__hud-copy p + p {
  font-size: 0.95rem;
  line-height: 1.45;
  color: rgba(243, 239, 229, 0.74);
}

/* The HUD is a beat of the pinned scene; without the pin there is no frame to
   station it in, so below the breakpoint the section stays just the crowd. */
@media (max-width: 1023px), (prefers-reduced-motion: reduce) {
  .about-harvester__hud {
    display: none;
  }
}

/*
 * Desktop plays the section as a scene: the stage pins for the length of the
 * section while the crowd climbs out of the fold and grows to full height, then
 * the four background workers fade off and leave the lead standing alone. The
 * motion itself lives in GSAP — these rules only frame it, so reduced-motion and
 * no-JS still land on the composition described above.
 */
@media (min-width: 1024px) and (prefers-reduced-motion: no-preference) {
  /*
   * Three screens: one to stand the crowd up, one to hold it, one to collapse to
   * the lead. Every duration in the timeline is a share of this minus the pinned
   * screen, so lengthening the section stretches the entrance in absolute scroll —
   * at 430svh the crowd spent a whole screen of wheel cropped at the waist.
   */
  .about-harvester {
    min-height: 300svh;
  }

  .about-harvester__stage {
    position: sticky;
    top: 0;
    height: 100svh;
    /* The inherited min-height is the section's — it would unpin this. */
    min-height: 0;
    overflow: hidden;
  }

  /*
   * Pinned, the copy is a frame of the scene rather than the head of a section,
   * so it loses the standing lead-in and sits high. The block's floor and the
   * crowd's heads are the two things this scene has to keep apart: every rem left
   * here walks the summary down toward them.
   */
  .about-harvester__content {
    padding-top: 3.25rem;
    padding-bottom: 0;
  }

  /*
   * Laid out at the size the crowd settles into: boots a few svh clear of the floor
   * with the rest of the frame left as air over the heads, so the whole body reads
   * and nothing grazes the stage edge. The 56vw term (93vw across the 1765:1060
   * group) keeps a tall window from growing the group wider than the frame and
   * cutting the outer two off. Everything the scrub does is a share of this box, so
   * a resize re-resolves the svh and the scene comes with it. What CSS paints is
   * the settled composition, not the pre-scroll one — the crop the scene opens on
   * is GSAP's from-state and lands only once the timeline exists, so a stalled
   * scrub, a reduced-motion pass or a failed script all leave the whole crowd
   * standing rather than buried below the fold.
   */
  .about-harvester__figures {
    --crowd-height: min(78svh, 56vw);

    width: auto;
    height: var(--crowd-height);
    bottom: 5svh;
    transform: translateX(-50%);
    transform-origin: center bottom;
  }
}

.about-command__backdrop {
  position: absolute;
  inset: -26% -12%;
  will-change: transform, filter;
}

.about-command__backdrop :deep(picture),
.about-command__backdrop :deep(img) {
  display: block;
  width: 100%;
  height: 100%;
}

.about-command__boot {
  position: absolute;
  inset: 0;
  z-index: 10;
  overflow: hidden;
  pointer-events: none;
}

/*
 * The dark the reveal opens out of. It lies under the field instead of over it,
 * because the window grows on all four sides: a pair of sliding panels can only
 * part along one axis, and the four it would take to bound a box carry four copies
 * of this grid, each re-anchoring its dots to its own moving edge.
 */
.about-command__ground {
  position: absolute;
  inset: 0;
  background: #0e1009;
}

.about-command__ground::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle, rgba(255, 255, 255, 0.2) 1.5px, transparent 1.8px);
  background-size: 10.5rem 10.5rem;
  background-position: 1rem 1.25rem;
  opacity: 0.42;
}

.about-command__boot-line {
  position: absolute;
  left: var(--boot-inline);
  right: var(--boot-inline);
  height: 1px;
  background: rgba(255, 255, 255, 0.28);
  transform-origin: center center;
}

.about-command__boot-line--top {
  top: var(--boot-t);
}

.about-command__boot-line--bottom {
  top: calc(100% - var(--boot-b));
}

.about-command__boot-readout {
  position: absolute;
  right: 4%;
  top: calc(var(--boot-t) + 1rem);
  display: grid;
  grid-template-columns: auto auto;
  gap: 0.3rem 2.1rem;
  font-family: var(--font-mono);
  font-size: 0.7rem;
  font-weight: 700;
  line-height: 1.25;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(255, 255, 255, 0.88);
}

.about-command__boot-tag {
  position: absolute;
  left: var(--boot-inline);
  bottom: calc(var(--boot-b) + 0.85rem);
  padding: 0.62rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.45);
  font-family: var(--font-mono);
  font-size: 0.72rem;
  font-weight: 700;
  line-height: 1;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.88);
}

/*
 * The boot clip rides one layer above the field because the field's own clip-path
 * is already spoken for: the pinned scene sets it inline on the first frame and
 * morphs it through the notched HUD frame for the length of the scrub, which would
 * paint straight over anything the reveal wrote there.
 */
/*
 * The four insets give the window's corners; the blocks are cut off those edges to
 * the same recipe the HUD mask uses, four to an edge, so the reveal and the scene
 * it hands over to are carrying one shape language. Each alternates which side of
 * its edge it falls on — some bite in, some stand out — and no two share a run or
 * a depth. The irregularity is written out by hand rather than generated, because
 * a hero that deals itself a different silhouette on every load is a different
 * design each time and untestable besides.
 *
 * Every number here is relative, but to different things, and that is what lets one
 * shape survive three stages of movement. A block's two stops are shares of the
 * window, so it keeps its place while the edge is still travelling; pinned to the
 * stage they would sit outside the slit for the whole first stage and then arrive
 * all at once. Depth is per-axis: the side blocks measure against the stage's width
 * so they hold their size as the window grows past them, while the top and bottom
 * measure against the window's own height, which zeroes them for free while it is
 * still a slit — as a share of the stage they would stand off a zero-height line as
 * a row of loose blocks before the reveal had started.
 *
 * --boot-bite scales every depth at once and is the single thing the last stage
 * runs to nothing: all sixteen collapse onto their straight edges together, and the
 * hero ends square and full-bleed. It is unitless because it multiplies both a
 * width share and a height share, and calc cannot take a percentage of a percentage.
 */
.about-command__gate {
  position: absolute;
  inset: 0;
  --boot-tab: 2.4%;
  --boot-bite: 1;
  --boot-x1: var(--boot-l);
  --boot-x2: calc(100% - var(--boot-r));
  --boot-y1: var(--boot-t);
  --boot-y2: calc(100% - var(--boot-b));
  --boot-run: calc(var(--boot-x2) - var(--boot-x1));
  --boot-span: calc(var(--boot-y2) - var(--boot-y1));
  clip-path: polygon(
    var(--boot-x1) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.12) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.12) calc(var(--boot-y1) + var(--boot-span) * -0.055 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.22) calc(var(--boot-y1) + var(--boot-span) * -0.055 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.22) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.31) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.31) calc(var(--boot-y1) + var(--boot-span) * 0.042 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.4) calc(var(--boot-y1) + var(--boot-span) * 0.042 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.4) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.5) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.5) calc(var(--boot-y1) + var(--boot-span) * -0.03 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.62) calc(var(--boot-y1) + var(--boot-span) * -0.03 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.62) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.72) var(--boot-y1),
    calc(var(--boot-x1) + var(--boot-run) * 0.72) calc(var(--boot-y1) + var(--boot-span) * 0.058 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.86) calc(var(--boot-y1) + var(--boot-span) * 0.058 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.86) var(--boot-y1),
    var(--boot-x2) var(--boot-y1),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.08),
    calc(var(--boot-x2) - var(--boot-tab) * 1.25 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.08),
    calc(var(--boot-x2) - var(--boot-tab) * 1.25 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.19),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.19),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.28),
    calc(var(--boot-x2) - var(--boot-tab) * -0.65 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.28),
    calc(var(--boot-x2) - var(--boot-tab) * -0.65 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.36),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.36),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.47),
    calc(var(--boot-x2) - var(--boot-tab) * 0.85 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.47),
    calc(var(--boot-x2) - var(--boot-tab) * 0.85 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.61),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.61),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.72),
    calc(var(--boot-x2) - var(--boot-tab) * -1.35 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.72),
    calc(var(--boot-x2) - var(--boot-tab) * -1.35 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.83),
    var(--boot-x2) calc(var(--boot-y1) + var(--boot-span) * 0.83),
    var(--boot-x2) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.88) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.88) calc(var(--boot-y2) - var(--boot-span) * -0.028 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.75) calc(var(--boot-y2) - var(--boot-span) * -0.028 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.75) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.66) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.66) calc(var(--boot-y2) - var(--boot-span) * 0.062 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.52) calc(var(--boot-y2) - var(--boot-span) * 0.062 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.52) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.41) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.41) calc(var(--boot-y2) - var(--boot-span) * -0.034 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.33) calc(var(--boot-y2) - var(--boot-span) * -0.034 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.33) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.24) var(--boot-y2),
    calc(var(--boot-x1) + var(--boot-run) * 0.24) calc(var(--boot-y2) - var(--boot-span) * 0.048 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.1) calc(var(--boot-y2) - var(--boot-span) * 0.048 * var(--boot-bite)),
    calc(var(--boot-x1) + var(--boot-run) * 0.1) var(--boot-y2),
    var(--boot-x1) var(--boot-y2),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.87),
    calc(var(--boot-x1) + var(--boot-tab) * -0.6 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.87),
    calc(var(--boot-x1) + var(--boot-tab) * -0.6 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.74),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.74),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.63),
    calc(var(--boot-x1) + var(--boot-tab) * 1.45 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.63),
    calc(var(--boot-x1) + var(--boot-tab) * 1.45 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.49),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.49),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.38),
    calc(var(--boot-x1) + var(--boot-tab) * -1.15 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.38),
    calc(var(--boot-x1) + var(--boot-tab) * -1.15 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.3),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.3),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.21),
    calc(var(--boot-x1) + var(--boot-tab) * 0.75 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.21),
    calc(var(--boot-x1) + var(--boot-tab) * 0.75 * var(--boot-bite)) calc(var(--boot-y1) + var(--boot-span) * 0.1),
    var(--boot-x1) calc(var(--boot-y1) + var(--boot-span) * 0.1)
  );
  will-change: clip-path;
}

.about-command__field {
  position: absolute;
  inset: 0;
  overflow: hidden;
  background: #0e1009;
  will-change: clip-path;
}

.about-command__landing {
  position: absolute;
  z-index: 5;
  left: 1.15rem;
  bottom: 1.2rem;
  max-width: min(58rem, 72vw);
  text-shadow: 0 2px 26px rgba(0, 0, 0, 0.28);
}

.about-command__landing-kicker {
  margin-bottom: 0.8rem;
  font-family: var(--font-mono);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.about-command__landing h1 {
  font-size: clamp(3.8rem, 5.3vw, 6.8rem);
  font-weight: 500;
  line-height: 0.91;
  letter-spacing: -0.065em;
}

.about-command__landing-link {
  display: inline-flex;
  align-items: center;
  min-height: 3.25rem;
  margin-top: 1.5rem;
  padding: 0.7rem 1.45rem;
  background: #e8ff52;
  color: #11140c;
  font-family: var(--font-mono);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  text-shadow: none;
  clip-path: polygon(0 0, 100% 0, 100% 72%, 88% 100%, 0 100%);
}

.about-command__surface,
.about-command__visual {
  position: absolute;
  inset: 0;
  opacity: 0;
}

.about-command__mesh {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(to right, rgba(255, 255, 255, 0.14) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.14) 1px, transparent 1px),
    rgba(10, 12, 9, 0.46);
  background-size: 6.25vw 6.25vw, 6.25vw 6.25vw, auto;
}

.about-command__frame {
  position: absolute;
  border: 1px solid rgba(255, 255, 255, 0.18);
}

/*
 * Absolute against the section rather than the stage, the way the outro already
 * is. The stage is sticky, so anything inside it is parked for the whole pin —
 * out here the section's own scroll carries this up across the map and off, which
 * is the only way one block of copy can be read at reading distance and still
 * leave the scene behind it. The offset lands it just as the mask finishes
 * locking, so it arrives to a scene that has already composed itself.
 */
.about-command__brief {
  position: absolute;
  z-index: 6;
  top: 235svh;
  left: 5.1vw;
  width: min(30rem, 42vw);
}

.about-command__brief-tag {
  font-family: var(--font-mono);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #e8ff52;
}

.about-command__brief-body {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-size: clamp(1.05rem, 1.45vw, 1.42rem);
  font-weight: 600;
  line-height: 1.3;
  color: #fff;
  text-shadow: 0 2px 22px rgba(0, 0, 0, 0.42);
}

/*
 * The two briefs the command scene used to cycle in place. Side by side they read
 * as the pair they are, and each keeps the anchor the header and the hero link
 * point at.
 */
.about-briefs {
  display: grid;
  gap: 2.5rem 4vw;
  padding: 6rem 5.1vw;
  background: #f1f0e6;
  color: #10110d;
}

/*
 * The global anchor offset is cut for the site header's three stacked bars; this
 * page flies its own single-line nav, so the same number parks the heading a
 * screenful too low.
 */
.about-briefs__item {
  scroll-margin-top: 7.5rem;
}

.about-briefs__tag {
  font-family: var(--font-mono);
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--color-gold-dark);
}

.about-briefs__title {
  margin-top: 1rem;
  font-size: clamp(1.9rem, 3.2vw, 2.9rem);
  font-weight: 700;
  line-height: 1.02;
  letter-spacing: -0.04em;
  text-transform: uppercase;
}

.about-briefs__body {
  margin-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  max-width: 34rem;
  font-size: 1rem;
  line-height: 1.6;
  color: rgba(16, 17, 13, 0.72);
}

@media (min-width: 900px) {
  .about-briefs {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

/*
 * The element itself stays a bare positioning box: GSAP owns its transform for
 * the scroll-driven active state. The disc and core live on pseudo-elements so
 * their pulse free-runs without fighting those inline styles.
 */
.about-command__hotspot {
  --spot-fill: rgba(232, 255, 82, 0.16);
  --spot-edge: rgba(232, 255, 82, 0.1);
  --spot-core: #e8ff52;
  position: absolute;
  width: var(--spot-size);
  height: var(--spot-size);
  transform: translate(-50%, -50%);
  mix-blend-mode: screen;
}

.about-command__hotspot::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 999px;
  background: var(--spot-fill);
  box-shadow: 0 0 0 1px var(--spot-edge);
}

.about-command__hotspot::after {
  content: '';
  position: absolute;
  inset: 50%;
  width: 0.8rem;
  height: 0.8rem;
  border-radius: 999px;
  transform: translate(-50%, -50%);
  background: var(--spot-core);
}

.about-command__hotspot--gold {
  --spot-fill: rgba(232, 255, 82, 0.16);
  --spot-edge: rgba(232, 255, 82, 0.1);
  --spot-core: #e8ff52;
}

.about-command__hotspot--coral {
  --spot-fill: rgba(255, 102, 73, 0.28);
  --spot-edge: rgba(255, 102, 73, 0.14);
  --spot-core: #ff6649;
}

.about-command__hotspot--lime {
  --spot-fill: rgba(226, 255, 94, 0.2);
  --spot-edge: rgba(226, 255, 94, 0.12);
  --spot-core: #e2ff5e;
}

.about-command__visual--live .about-command__hotspot::before {
  will-change: transform, opacity;
  animation: about-spot-breathe var(--spot-duration, 4.2s) ease-in-out var(--spot-delay, 0s) infinite;
}

.about-command__visual--live .about-command__hotspot::after {
  animation: about-spot-core var(--spot-duration, 4.2s) ease-in-out var(--spot-delay, 0s) infinite;
}

@keyframes about-spot-breathe {
  0%,
  100% {
    opacity: 0.68;
    transform: scale(0.9);
  }

  50% {
    opacity: 1;
    transform: scale(1.14);
  }
}

@keyframes about-spot-core {
  0%,
  100% {
    transform: translate(-50%, -50%) scale(0.84);
  }

  50% {
    transform: translate(-50%, -50%) scale(1.22);
  }
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
  --dot-glow: rgba(255, 106, 77, 0.9);
  background: #ff6a4d;
}

.about-command__dot--lime {
  --dot-glow: rgba(232, 255, 82, 0.9);
  background: #e8ff52;
}

/* Dormant until `.about-command__surface--live` lands, i.e. the mask is locked. */
.about-command__dot--pulse {
  opacity: 0;
  transform: translate(-50%, -50%) scale(0.2);
}

.about-command__dot--pulse::after {
  content: '';
  position: absolute;
  inset: -0.14rem;
  border-radius: 999px;
  border: 1px solid var(--dot-glow, rgba(255, 255, 255, 0.6));
  opacity: 0;
}

/*
 * Drift rides the standalone `translate` property, which composes with — rather
 * than overwrites — the `transform` the pulse keyframes own. Each rule has to
 * name it explicitly: `animation` is a shorthand, so a more specific rule would
 * otherwise drop it.
 */
.about-command__surface--live .about-command__dot--pulse {
  will-change: transform, translate, opacity;
  animation:
    about-dot-land 0.52s cubic-bezier(0.22, 1, 0.36, 1) var(--dot-delay, 0s) both,
    about-dot-breathe var(--dot-duration, 2.4s) ease-in-out calc(var(--dot-delay, 0s) + 0.52s) infinite,
    about-dot-drift var(--drift-duration, 30s) ease-in-out var(--drift-delay, 0s) infinite;
}

.about-command__surface--live .about-command__dot--pulse::after {
  animation: about-dot-ripple var(--dot-duration, 2.4s) ease-out calc(var(--dot-delay, 0s) + 0.52s) infinite;
}

.about-command__surface--live .about-command__dot--muted {
  will-change: translate, opacity;
  animation:
    about-dot-flicker var(--dot-duration, 3.6s) ease-in-out var(--dot-delay, 0s) infinite,
    about-dot-drift var(--drift-duration, 30s) ease-in-out var(--drift-delay, 0s) infinite;
}

@keyframes about-dot-land {
  from {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.2);
  }

  to {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
}

@keyframes about-dot-breathe {
  0%,
  100% {
    transform: translate(-50%, -50%) scale(1);
  }

  50% {
    transform: translate(-50%, -50%) scale(1.42);
  }
}

@keyframes about-dot-ripple {
  0% {
    opacity: 0.85;
    transform: scale(1);
  }

  65% {
    opacity: 0;
    transform: scale(3.6);
  }

  100% {
    opacity: 0;
    transform: scale(3.6);
  }
}

@keyframes about-dot-flicker {
  0%,
  100% {
    opacity: 0.5;
  }

  50% {
    opacity: 1;
  }
}

/* Five uneven waypoints that close the loop, so it repeats without a seam. */
@keyframes about-dot-drift {
  0%,
  100% {
    translate: 0 0;
  }

  20% {
    translate: calc(var(--drift-x, 1.5rem) * 0.62) calc(var(--drift-y, 1.5rem) * -0.34);
  }

  40% {
    translate: calc(var(--drift-x, 1.5rem) * -0.28) calc(var(--drift-y, 1.5rem) * 0.86);
  }

  60% {
    translate: calc(var(--drift-x, 1.5rem) * -0.91) calc(var(--drift-y, 1.5rem) * 0.19);
  }

  80% {
    translate: calc(var(--drift-x, 1.5rem) * -0.37) calc(var(--drift-y, 1.5rem) * -0.73);
  }
}

@media (prefers-reduced-motion: reduce) {
  .about-command__surface--live .about-command__dot--pulse,
  .about-command__surface--live .about-command__dot--pulse::after,
  .about-command__surface--live .about-command__dot--muted,
  .about-command__visual--live .about-command__hotspot::before,
  .about-command__visual--live .about-command__hotspot::after {
    animation: none;
  }

  .about-command__dot--pulse {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }

  .about-nav,
  .about-nav__panel,
  .about-nav__panel a {
    transition-duration: 0.01ms;
  }

  .about-nav__panel {
    transform: none;
  }

  .about-nav__panel a:hover {
    transform: none;
  }
}

.about-command__rail {
  position: absolute;
  z-index: 6;
  right: 3.8vw;
  top: 37%;
  width: 1px;
  height: 15.5rem;
  background: rgba(255, 255, 255, 0.14);
  opacity: 0;
}

.about-command__rail-fill {
  position: absolute;
  inset: 0;
  background: #e8ff52;
}

/*
 * The band reads as the harvester scene's read-out strip rather than a new
 * section, so it takes that ground rather than the cool steel the rest of the
 * site uses for dark bands, and each figure is carried on the same instrument
 * surface as the HUD panels: hairline frame, one corner notched off, a mono tag
 * over a lime status square. Stacking the caption under the figure — the old
 * band set the two side by side — is what stops a two-word detail wrapping into
 * a right-aligned column barely wider than itself.
 */
.about-readouts {
  position: relative;
  padding: 2rem 1rem 2.1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: #12120c;
  color: #f3efe5;
}

.about-readouts::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    linear-gradient(to right, rgba(255, 255, 255, 0.045) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
  background-size: 92px 92px;
  opacity: 0.32;
}

.about-readouts__grid {
  position: relative;
  display: grid;
  gap: 0.9rem;
}

@media (min-width: 768px) {
  .about-readouts__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1280px) {
  .about-readouts__grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

/* Same two-layer frame as the HUD panels: the panel paints the hairline edge to
   edge and its ::before lays the ground back a pixel in, so the line survives
   the cut corner that a border would lose. The notch is set in rem rather than
   the panels' percentages because these four share a width and would otherwise
   each cut a different angle. */
.about-readout {
  --panel-clip: polygon(0 0, calc(100% - 0.9rem) 0, 100% 0.9rem, 100% 100%, 0 100%);

  position: relative;
  isolation: isolate;
  padding: 1.05rem 1.2rem 1.25rem;
  background: rgba(255, 255, 255, 0.19);
  clip-path: var(--panel-clip);
}

.about-readout::before {
  content: '';
  position: absolute;
  inset: 1px;
  z-index: 0;
  background: #12120c;
  clip-path: var(--panel-clip);
}

.about-readout > * {
  position: relative;
  z-index: 1;
}

.about-readout__tag {
  display: flex;
  align-items: baseline;
  font-family: var(--font-mono);
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  text-transform: uppercase;
  color: rgba(243, 239, 229, 0.72);
}

.about-readout__tag::before {
  content: '';
  flex: none;
  width: 0.3rem;
  height: 0.3rem;
  margin-right: 0.55rem;
  background: #e8ff52;
}

/* Channel number, pushed to the far edge — the label and its status square
   travel together, so space-between across all three is the wrong tool. */
.about-readout__tag em {
  margin-left: auto;
  padding-left: 0.9rem;
  font-style: normal;
  color: rgba(243, 239, 229, 0.3);
}

.about-readout__value {
  margin-top: 1.3rem;
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(3.1rem, 4.2vw, 4.4rem);
  font-weight: 400;
  line-height: 0.82;
  letter-spacing: -0.05em;
  color: #f3efe5;
}

.about-readout__detail {
  margin-top: 1.1rem;
  padding-top: 0.8rem;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.82rem;
  line-height: 1.45;
  color: rgba(243, 239, 229, 0.6);
}

.about-command__cursor {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 100;
  display: none;
  width: 7.5rem;
  height: 7.5rem;
  place-items: center;
  pointer-events: none;
  color: rgba(255, 255, 255, 0.92);
  font-family: var(--font-mono);
  font-size: 0.72rem;
  font-weight: 700;
  line-height: 1;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  will-change: transform, opacity;
}

.about-command__cursor-ring {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: visible;
  transform: rotate(-90deg);
}

.about-command__cursor-track,
.about-command__cursor-progress {
  fill: none;
  stroke-width: 4;
}

.about-command__cursor-track {
  stroke: rgba(232, 255, 82, 0.2);
}

.about-command__cursor-progress {
  stroke: #e8ff52;
  stroke-linecap: square;
  stroke-dasharray: 0 1;
  stroke-dashoffset: 0;
  will-change: stroke-dasharray;
}

.about-values {
  position: relative;
  overflow: hidden;
  padding: 4.75rem 0 5.25rem;
  background: #12120c;
  color: #f3efe5;
}

.about-values::before {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    radial-gradient(circle at top right, rgba(232, 255, 82, 0.07), transparent 30%),
    linear-gradient(to right, rgba(255, 255, 255, 0.045) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
  background-size:
    auto,
    92px 92px,
    92px 92px;
}

.about-values__inner {
  position: relative;
  display: grid;
  gap: 2.75rem;
}

@media (min-width: 1024px) {
  .about-values__inner {
    grid-template-columns: 24rem minmax(0, 1fr);
    align-items: start;
    gap: 3.5rem;
  }

  .about-values__aside {
    position: sticky;
    top: 11rem;
  }
}

.about-values__eyebrow {
  display: flex;
  align-items: baseline;
  font-family: var(--font-mono);
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  text-transform: uppercase;
  color: rgba(243, 239, 229, 0.72);
}

.about-values__eyebrow::before {
  content: '';
  flex: none;
  width: 0.3rem;
  height: 0.3rem;
  margin-right: 0.55rem;
  background: #e8ff52;
}

/* Display face and weight follow the harvester title directly above, so the
   scene's type carries into the section instead of resetting to the site's
   bold Poppins headings. */
.about-values__title {
  margin-top: 1.4rem;
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(2.6rem, 4.2vw, 4.3rem);
  font-weight: 400;
  line-height: 0.86;
  letter-spacing: -0.045em;
  text-transform: uppercase;
  color: #f3efe5;
}

.about-values__lede {
  max-width: 24rem;
  margin-top: 1.4rem;
  font-size: 0.94rem;
  line-height: 1.6;
  color: rgba(243, 239, 229, 0.64);
}

.about-values__rail {
  margin-top: 2.5rem;
}

.about-values__rail-track {
  height: 1px;
  width: 100%;
  background: rgba(255, 255, 255, 0.14);
}

.about-values__rail-fill {
  height: 100%;
  width: 100%;
  background: #e8ff52;
}

.about-values__rail-tag {
  display: flex;
  align-items: baseline;
  margin-top: 0.85rem;
  font-family: var(--font-mono);
  font-size: 0.6rem;
  font-weight: 700;
  letter-spacing: var(--tracking-code);
  text-transform: uppercase;
  color: rgba(243, 239, 229, 0.44);
}

.about-values__rail-tag em {
  margin-left: auto;
  padding-left: 0.9rem;
  font-style: normal;
  color: #e8ff52;
}

/*
 * Rows, not a two-column card grid. The values arrive from the CMS and there
 * are three of them today, which left the old grid with a card-sized hole in
 * the bottom right and every card padded out to a fixed 13rem to disguise it.
 * Stacked full-width instruments hold any count without a gap and read as a
 * log, which is the register the rest of the scene is in.
 */
.about-values__list {
  display: grid;
  gap: 0.9rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.about-values__row {
  --panel-clip: polygon(0 0, calc(100% - 1.1rem) 0, 100% 1.1rem, 100% 100%, 0 100%);

  position: relative;
  isolation: isolate;
  display: grid;
  grid-template-columns: 3rem minmax(0, 13rem) minmax(0, 1fr);
  align-items: start;
  gap: 0 1.7rem;
  padding: 1.5rem 1.7rem 1.6rem;
  background: rgba(255, 255, 255, 0.16);
  clip-path: var(--panel-clip);
}

.about-values__row::before {
  content: '';
  position: absolute;
  inset: 1px;
  z-index: 0;
  background: #12120c;
  clip-path: var(--panel-clip);
}

.about-values__row > * {
  position: relative;
  z-index: 1;
}

.about-values__index {
  font-family: var(--font-mono);
  font-size: 0.7rem;
  font-weight: 700;
  line-height: 1.5;
  letter-spacing: var(--tracking-code);
  color: #e8ff52;
}

.about-values__term {
  font-family: 'NB International Pro', var(--font-sans);
  font-size: clamp(1.35rem, 1.7vw, 1.75rem);
  font-weight: 400;
  line-height: 1;
  letter-spacing: -0.04em;
  text-transform: uppercase;
  color: #f3efe5;
}

.about-values__detail {
  font-size: 0.94rem;
  line-height: 1.55;
  color: rgba(243, 239, 229, 0.68);
}

@media (max-width: 767px) {
  .about-values__row {
    grid-template-columns: 2.6rem minmax(0, 1fr);
    gap: 1rem;
    padding: 1.3rem 1.35rem 1.4rem;
  }

  .about-values__detail {
    grid-column: 1 / -1;
  }
}

.about-map-frame {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--color-line);
  background: var(--color-smoke);
}

@media (max-width: 1023px) {
  .about-editorial-reverse {
    min-height: auto;
    padding: 6rem 1rem 3rem;
  }

  /* Stacked, so every offset is cleared — including the ones only the mirror
     sets, which would otherwise shift it as a relative nudge. */
  .about-editorial-reverse__image {
    position: relative;
    left: auto;
    right: auto;
    top: auto;
    width: 100%;
    height: min(64svh, 40rem);
  }

  .about-editorial-reverse__copy {
    position: relative;
    left: auto;
    right: auto;
    top: auto;
    width: min(31rem, 92%);
    margin: 3.5rem 0 0 auto;
  }

  .about-editorial {
    min-height: auto;
    padding: 6rem 1rem 1rem;
  }

  .about-editorial__title {
    max-width: 14ch;
    padding: 0;
    font-size: clamp(3.1rem, 9vw, 5.5rem);
  }

  .about-editorial__copy {
    position: relative;
    left: auto;
    top: auto;
    width: min(30rem, 90%);
    margin-top: 4rem;
  }

  .about-editorial__image {
    position: relative;
    right: auto;
    top: auto;
    width: 100%;
    height: min(68svh, 42rem);
    margin-top: 3rem;
  }

  .about-harvester {
    min-height: auto;
  }

  .about-harvester__content {
    width: calc(100% - 2rem);
    padding: 7.4rem 0 14rem;
  }

  .about-harvester__title {
    margin-left: 0;
    font-size: clamp(3.2rem, 11vw, 5rem);
    line-height: 0.88;
  }

  .about-harvester__summary {
    width: min(20rem, 76vw);
    margin-top: 2.5rem;
    margin-left: auto;
    font-size: clamp(1rem, 3.7vw, 1.34rem);
    line-height: 1.22;
  }

  .about-harvester__figures {
    bottom: 0;
    width: min(31rem, 92vw);
    height: auto;
    transform: translateX(-50%);
  }

  .about-command {
    min-height: 100svh;
  }

  .about-command__outro {
    display: none;
  }

  /*
   * No pin below this width, so there is no scroll for an svh offset to ride and
   * the copy just falls under the stage. It stays positioned rather than going
   * static: the stage above it is sticky, and a static box cannot carry a z-index
   * to paint over one.
   */
  .about-command__brief {
    position: relative;
    top: auto;
    left: auto;
    width: auto;
    padding: 3.5rem 1.15rem 4.5rem;
    /*
     * Desktop reads this copy off a map the scrub has already drained to grey; the
     * scrub never runs down here, so the photo stays light and the type needs a
     * ground of its own to sit on.
     */
    background: linear-gradient(
      to bottom,
      rgba(14, 16, 9, 0) 0%,
      rgba(14, 16, 9, 0.88) 14%,
      rgba(14, 16, 9, 0.94) 100%
    );
  }

  .about-briefs {
    padding: 4rem 1.15rem;
  }

  .about-command__boot-readout {
    right: 6%;
    gap: 0.25rem 1.1rem;
    font-size: 0.56rem;
  }

  .about-command__field {
    position: absolute;
    inset: 0;
    min-height: 0;
    margin: 0;
  }
}

@media (min-width: 1024px) and (pointer: fine) {
  .about-hero,
  .about-command {
    cursor: none;
  }

  .about-command__cursor {
    display: grid;
  }
}

@media (max-width: 767px) {
  .about-command__stage {
    --boot-shut-x: 17%;
    --boot-wide-x: 8%;
  }

  /* A block cut to the desktop share of a phone's width is a nick, not a block. */
  .about-command__gate {
    --boot-tab: 5%;
  }

  .about-command__ground::before {
    background-size: 5.75rem 5.75rem;
    background-position: 0.5rem 0.75rem;
  }

  .about-command__boot-readout {
    display: none;
  }

  .about-command__boot-tag {
    padding: 0.5rem 0.8rem;
    font-size: 0.62rem;
  }

  .about-nav {
    padding: 1rem 0.9rem;
  }

  .about-nav__logo {
    min-height: 1.72rem;
    font-size: 1rem;
  }

  .about-nav__toggle {
    font-size: 0.78rem;
  }

  .about-nav__panel {
    padding: 5.5rem 0.9rem 2.5rem;
  }

  .about-command__landing {
    left: 0.9rem;
    right: 0.9rem;
    bottom: 1rem;
    max-width: none;
  }

  .about-command__landing h1 {
    font-size: clamp(3rem, 15vw, 5rem);
  }

  .about-command__landing-link {
    min-height: 3rem;
    margin-top: 1.15rem;
    padding-inline: 1rem 1.35rem;
  }

  .about-harvester__content {
    padding-bottom: 11.5rem;
  }

  .about-harvester__summary {
    width: min(18rem, 82vw);
  }

  .about-harvester__figures {
    bottom: 0;
    width: min(24rem, 112vw);
    height: auto;
    transform: translateX(-50%);
  }

  .about-hero__aside {
    display: none;
  }
}
</style>
