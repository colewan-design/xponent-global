/**
 * The About page's CMS sections, named and with their fallback copy attached.
 *
 * `/page-content/about` returns an ordered array, so every consumer has to know
 * that vision is `sections[1]` and mission is `sections[2]`. That ordering — and
 * the two generations of fallback copy below — lived in `pages/about.vue` while
 * that page was the only reader. The landing page's credibility band now shows
 * the same mission and vision, so it moved here rather than being copied: two
 * copies of the fallback tables would drift the moment one page's wording was
 * revised.
 */

/**
 * Superseded wording, kept only so `normalizeAboutSection` can recognise it.
 *
 * A record still carrying one of these strings is a seeded default nobody has
 * edited yet, which should show the current copy instead. A record that differs
 * from both tables is a real edit and is left exactly as entered.
 */
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

/** CMS bodies store paragraph breaks as a blank line. */
export const splitParagraphs = (text) =>
  (text ?? '')
    .split('\n\n')
    .map((paragraph) => paragraph.trim())
    .filter(Boolean)

/**
 * Fetches `/page-content/about` under a fixed key.
 *
 * The key is shared deliberately. `useApiFetch` scopes its automatic key to the
 * current route, so the landing page and the About page would otherwise each
 * hold their own copy of the same payload and re-request it on every navigation
 * between the two. Naming it once here dedupes them, the way `office-locations`
 * already is across the pages that read it.
 *
 * `lazy` is left to the caller: About blocks on this content because its opening
 * scene is built from it, the landing page does not because its band is one of
 * eight and must not hold back the other seven.
 */
export function useAboutContent(options = {}) {
  return useApiFetch('/page-content/about', { key: 'page-content-about', ...options })
}

/**
 * Names the ordered sections and applies the fallback copy.
 *
 * Takes the `data` ref from `useAboutContent` rather than fetching, so a caller
 * that needs the request's `status` for a skeleton keeps hold of it.
 */
export function useAboutSections(about) {
  const sections = computed(() => toValue(about)?.data?.sections ?? [])

  const section = (index, key) =>
    computed(() => normalizeAboutSection(sections.value[index], legacyAboutSectionCopy[key], aboutSectionCopy[key]))

  return {
    sections,
    intro: section(0, 'intro'),
    vision: section(1, 'vision'),
    mission: section(2, 'mission'),
    coreValues: section(3, 'coreValues'),
    whereWeOperate: section(4, 'whereWeOperate'),
    affiliationsIntro: section(5, 'affiliations'),
  }
}
