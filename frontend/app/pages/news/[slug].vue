<script setup>
const route = useRoute()
const { data: response } = await useApiFetch(`/posts/${route.params.slug}`)
const post = computed(() => response.value?.data)

if (!post.value) {
  throw createError({ statusCode: 404, statusMessage: 'Post not found' })
}

useSeoMeta({
  title: post.value?.title,
  description: post.value?.excerpt,
  ogImage: post.value?.cover_image,
})

const isCaseStudy = computed(() => post.value?.type === 'case_study')

/** Where this post is listed — case studies live under Resources, news under Newsletter. */
const backLink = computed(() =>
  isCaseStudy.value
    ? { to: '/media/resources#case-studies', label: 'Back to case studies' }
    : { to: '/media/newsletter', label: 'Back to news & insights' },
)
</script>

<template>
  <div v-if="post">
    <PageHero
      :eyebrow="isCaseStudy ? 'Case Study' : 'Bulletin'"
      :title="post.title"
      :subtitle="post.excerpt"
      :image="post.cover_image ?? '/images/page-hero-bg.jpg'"
    />

    <!-- Article copy on the narrower `container-page` measure: a long read wants a
         shorter line than the catalogue grids do. -->
    <article class="container-page py-10 sm:py-12">
      <div class="max-w-3xl space-y-4 text-[0.95rem] leading-relaxed text-ink/75">
        <p v-for="(paragraph, i) in post.body.split('\n\n')" :key="i">{{ paragraph }}</p>
      </div>

      <div class="mt-10 border-t border-line pt-6">
        <NuxtLink
          :to="backLink.to"
          class="text-[0.86rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
        >
          ← {{ backLink.label }}
        </NuxtLink>
      </div>
    </article>
  </div>
</template>
