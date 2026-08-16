<script setup>
const { data: page } = await useApiFetch('/page-content/resources')
const { data: resources } = await useApiFetch('/resources')
const { data: caseStudies } = await useApiFetch('/posts', { params: { type: 'case_study' } })
const { data: news } = await useApiFetch('/posts', { params: { type: 'news' } })

useSeoMeta({
  title: 'Resources',
  description:
    'Technical documents, datasheets, safety & compliance resources, case studies, and news from Xponent Global.',
})

// Section titles and lead lines come from the `resources` PageContent record so they stay
// admin-editable. Only the first document group carries a lead line: on the legacy page it
// leads the whole documents area, with datasheets and safety listed as cards beneath it.
const sections = computed(() => page.value?.data?.sections ?? [])
const [documents, caseStudiesIntro, newsIntro] = sections.value

const groups = [
  { key: 'technical_document', label: documents?.heading || 'Technical Documents', blurb: documents?.body },
  { key: 'datasheet', label: 'Product Datasheets' },
  { key: 'safety_compliance', label: 'Safety & Compliance' },
]

function itemsFor(key) {
  return resources.value?.data?.filter((item) => item.category === key) ?? []
}

const studies = computed(() => caseStudies.value?.data ?? [])
const bulletins = computed(() => news.value?.data ?? [])
</script>

<template>
  <div>
    <PageHero
      eyebrow="Media"
      title="Resources"
      subtitle="Technical documents, datasheets, safety and compliance material, case studies and bulletins."
    />

    <!-- Jump bar, as on Solutions: three document registers plus the editorial. -->
    <nav class="border-b border-line bg-smoke" aria-label="Resource sections">
      <div class="container-retail retail-rail flex items-center gap-1 overflow-x-auto">
        <a
          v-for="group in groups"
          :key="group.key"
          :href="`#${group.key}`"
          class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark"
        >
          {{ group.label }}
        </a>
        <a href="#case-studies" class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark">
          Case Studies
        </a>
        <a href="#news" class="shrink-0 whitespace-nowrap px-3 py-3 text-[0.82rem] text-ink/85 transition-colors hover:text-gold-dark">
          News &amp; Insights
        </a>
      </div>
    </nav>

    <!--
      Document registers. Each group is a manifest: a download is a row, not a
      card — the list is meant to be scanned.
    -->
    <section
      v-for="group in groups"
      :id="group.key"
      :key="group.key"
      class="container-retail py-8 sm:py-10"
      :aria-label="group.label"
    >
      <SectionHead :title="group.label" />
      <p v-if="group.blurb" class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">{{ group.blurb }}</p>

      <div v-if="itemsFor(group.key).length" v-reveal:group class="mt-6 grid gap-px bg-line">
        <article
          v-for="item in itemsFor(group.key)"
          :key="item.id"
          class="flex flex-wrap items-center justify-between gap-6 bg-white p-5"
        >
          <div class="min-w-0 flex-1">
            <h3 class="text-[0.95rem] font-bold text-ink">{{ item.title }}</h3>
            <p v-if="item.description" class="mt-1.5 max-w-2xl text-[0.84rem] leading-relaxed text-ink/65">
              {{ item.description }}
            </p>
          </div>

          <a
            :href="item.file"
            target="_blank"
            rel="noopener"
            class="shrink-0 border border-line px-5 py-2.5 text-[0.82rem] font-semibold text-ink transition-colors hover:border-gold hover:text-gold-dark"
          >
            Download
          </a>
        </article>
      </div>

      <p v-else class="mt-6 border border-line bg-smoke p-6 text-[0.88rem] text-ink/60">
        Nothing published in this category yet.
      </p>
    </section>

    <!-- Case studies. -->
    <section id="case-studies" class="container-retail pb-8 sm:pb-10" aria-label="Case studies">
      <SectionHead :title="caseStudiesIntro?.heading || 'Case Studies'" />
      <p v-if="caseStudiesIntro?.body" class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">
        {{ caseStudiesIntro.body }}
      </p>

      <div v-if="studies.length" v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2">
        <NuxtLink
          v-for="study in studies"
          :key="study.id"
          :to="`/news/${study.slug}`"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <div v-if="study.cover_image" class="aspect-video overflow-hidden bg-smoke">
            <CmsImage
              :src="study.cover_image"
              alt=""
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>
          <div class="flex flex-1 flex-col p-5">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Case study</p>
            <h3 class="mt-2 text-[1rem] font-bold leading-snug text-ink group-hover:text-gold-dark">
              {{ study.title }}
            </h3>
            <p v-if="study.excerpt" class="mt-2 text-[0.84rem] leading-relaxed text-ink/65">{{ study.excerpt }}</p>
          </div>
        </NuxtLink>
      </div>

      <p v-else class="mt-6 border border-line bg-smoke p-6 text-[0.88rem] text-ink/60">
        No case studies published yet.
      </p>
    </section>

    <!-- Bulletins. -->
    <section id="news" class="container-retail pb-10 sm:pb-12" aria-label="News and insights">
      <SectionHead :title="newsIntro?.heading || 'News & Insights'" link-label="Newsletter" link-to="/media/newsletter" />
      <p v-if="newsIntro?.body" class="mt-2 max-w-3xl text-[0.9rem] leading-relaxed text-ink/70">
        {{ newsIntro.body }}
      </p>

      <div v-if="bulletins.length" v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <NuxtLink
          v-for="post in bulletins"
          :key="post.id"
          :to="`/news/${post.slug}`"
          class="group flex flex-col border border-line transition-colors hover:border-gold"
        >
          <div v-if="post.cover_image" class="aspect-video overflow-hidden bg-smoke">
            <CmsImage
              :src="post.cover_image"
              alt=""
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>
          <div class="flex flex-1 flex-col p-4">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Bulletin</p>
            <h3 class="mt-2 text-[0.95rem] font-bold leading-snug text-ink group-hover:text-gold-dark">
              {{ post.title }}
            </h3>
          </div>
        </NuxtLink>
      </div>

      <p v-else class="mt-6 border border-line bg-smoke p-6 text-[0.88rem] text-ink/60">No news published yet.</p>
    </section>

    <ScheduleVisit />
  </div>
</template>
