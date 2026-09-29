<script setup>
// The landing page is data-heavy. Keep the populated instance alive while
// visitors browse another route, so returning home shows the content already
// fetched rather than dropping back to placeholders while the same requests run
// a second time.
definePageMeta({ keepalive: true })

/**
 * Landing page — supply-catalogue layout.
 *
 * Ordered as a buyer reads it: what we supply, why us, what that looks like in
 * the box, who already buys it, proof it works, then the ask. Long-tail copy
 * closes the page, below the ask, so the last thing on screen is an action
 * rather than a paragraph.
 *
 *   hero -> categories -> credibility -> featured stock -> clients ->
 *   case studies -> CTA -> SEO copy
 *
 * Surfaces alternate dark / white / smoke down the page so each band reads as a
 * separate section without needing a rule between them. Sections live alongside
 * in components/Home*.vue.
 */
// `lazy` on every request, deliberately. Without it Nuxt suspends the whole
// route until all six settle, so one slow endpoint holds back the five sections
// that are ready — and the skeletons below would never be reachable, because
// nothing renders until there is nothing left to wait for. Each section now
// paints its own placeholder and swaps in its own content as it lands.
const { data: home, status: homeStatus } = useApiFetch('/page-content/home', { lazy: true })
const { data: clients, status: clientsStatus } = useApiFetch('/partners', {
  params: { type: 'client' },
  lazy: true,
})
const { data: solutions, status: solutionsStatus } = useApiFetch('/solutions', { lazy: true })
const { data: locations } = useApiFetch('/office-locations', { key: 'office-locations', lazy: true })
const { data: caseStudies, status: caseStudiesStatus } = useApiFetch('/posts', {
  params: { type: 'case_study' },
  lazy: true,
})
const { data: news, status: newsStatus } = useApiFetch('/posts', {
  params: { type: 'news' },
  lazy: true,
})
// The mission and vision statements shown in the credibility band live on the
// About page's record, not this one. Keyed and shared with `/about`, so moving
// between the two pages does not re-request it.
const { data: about, status: aboutStatus } = useAboutContent({ lazy: true })

// 'idle' counts as loading: a lazy request that has not started yet has no data
// either, and showing the empty state before the first byte is the flicker these
// placeholders exist to prevent.
const isLoading = (status) => status.value === 'pending' || status.value === 'idle'

const homePending = computed(() => isLoading(homeStatus))
const solutionsPending = computed(() => isLoading(solutionsStatus))
const clientsPending = computed(() => isLoading(clientsStatus))
const guidesPending = computed(() => isLoading(caseStudiesStatus) || isLoading(newsStatus))
const aboutPending = computed(() => isLoading(aboutStatus))

const { mission, vision } = useAboutSections(about)

useSeoMeta({
  title: 'Supplying Confidence. Delivering Certainty.',
  description:
    'Xponent Global is an international total solutions provider in the mining, drilling, oil and gas, construction and energy sector.',
})

const introSection = computed(() => home.value?.data?.sections?.[0])

// PPE has its own page rather than an anchor on /solutions, so it is not one of
// the catalogue categories shown here.
const solutionCategories = computed(
  () => solutions.value?.data?.filter((category) => category.slug !== 'personal-protection-equipment') ?? [],
)

const clientList = computed(() => clients.value?.data ?? [])
</script>

<template>
  <div>
    <HomeHero />
    <HomeTiles :categories="solutionCategories" :pending="solutionsPending" />
    <HomeCredibility
      :section="introSection"
      :pending="homePending"
      :office-count="locations?.data?.length ?? 0"
      :range-count="solutionCategories.length"
      :client-count="clientList.length"
      :mission="mission"
      :vision="vision"
      :briefs-pending="aboutPending"
    />
    <HomeFeatured :categories="solutionCategories" :pending="solutionsPending" />
    <HomeClients :clients="clientList" :pending="clientsPending" />
    <HomeGuides
      :case-studies="caseStudies?.data ?? []"
      :news="news?.data ?? []"
      :pending="guidesPending"
    />
    <HomeCta />
    <HomeSeoBlock :section="introSection" :pending="homePending" />
  </div>
</template>
