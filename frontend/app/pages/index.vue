<script setup>
// The landing page is data-heavy. Keep the populated instance alive while
// visitors browse another route, so returning home shows the content already
// fetched rather than dropping back to placeholders while the same requests run
// a second time.
definePageMeta({ keepalive: true })

/**
 * Landing page — supply-catalogue layout.
 *
 * Structure follows the Uniglobal storefront's retail homepage: a statement
 * masthead, merchandising tiles, a grey provenance field, the service band, a
 * browsable directory of what we supply, proof, editorial, and long-tail copy at
 * the foot. Sections live alongside in components/Home*.vue.
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

// 'idle' counts as loading: a lazy request that has not started yet has no data
// either, and showing the empty state before the first byte is the flicker these
// placeholders exist to prevent.
const isLoading = (status) => status.value === 'pending' || status.value === 'idle'

const homePending = computed(() => isLoading(homeStatus))
const solutionsPending = computed(() => isLoading(solutionsStatus))
const clientsPending = computed(() => isLoading(clientsStatus))
const guidesPending = computed(() => isLoading(caseStudiesStatus) || isLoading(newsStatus))

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
    <HomeAbout :section="introSection" :pending="homePending" />
    <HomeBenefits :office-count="locations?.data?.length ?? 0" :range-count="solutionCategories.length" />
    <HomeDirectory :categories="solutionCategories" :pending="solutionsPending" />
    <HomeClients :clients="clientList" :pending="clientsPending" />
    <HomeGuides
      :case-studies="caseStudies?.data ?? []"
      :news="news?.data ?? []"
      :pending="guidesPending"
    />
    <HomeSeoBlock :section="introSection" :pending="homePending" />
  </div>
</template>
