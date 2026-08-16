<script setup>
const { data: posts } = await useApiFetch('/posts', { params: { type: 'news' } })

useSeoMeta({
  title: 'Newsletter',
  description: 'Stay informed with the latest Xponent Global updates, market trends, and industry commentary.',
})

const email = ref('')
const submitting = ref(false)
const status = ref('')
const errorMessage = ref('')

async function subscribe() {
  submitting.value = true
  status.value = ''
  try {
    await apiPost('/newsletter-subscribers', { email: email.value })
    status.value = 'success'
    email.value = ''
  } catch (e) {
    status.value = 'error'
    errorMessage.value = extractApiErrorMessage(e)
  } finally {
    submitting.value = false
  }
}

const showUnsubscribe = ref(false)
const unsubscribeEmail = ref('')
const unsubscribing = ref(false)
const unsubscribeStatus = ref('')

async function unsubscribe() {
  unsubscribing.value = true
  unsubscribeStatus.value = ''
  try {
    await apiPost('/newsletter-subscribers/unsubscribe', { email: unsubscribeEmail.value })
    unsubscribeStatus.value = 'success'
    unsubscribeEmail.value = ''
  } catch (e) {
    unsubscribeStatus.value = extractApiErrorMessage(e)
  } finally {
    unsubscribing.value = false
  }
}

const news = computed(() => posts.value?.data ?? [])
</script>

<template>
  <div>
    <PageHero
      eyebrow="Media"
      title="Newsletter"
      subtitle="Updates, market trends and industry commentary — delivered as we publish them."
      :image="news[0]?.cover_image ?? '/images/page-hero-bg.jpg'"
    />

    <!-- One dark band per page: the subscription panel. -->
    <section class="container-retail py-8 sm:py-10" aria-label="Subscribe">
      <div class="grid gap-6 bg-steel-950 p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-5">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-white">
            Subscribe for updates.
          </h2>
          <p class="mt-3 max-w-md text-[0.9rem] leading-relaxed text-white/70">
            Our latest news and insights, straight to your inbox. No more than we'd want to receive
            ourselves.
          </p>
        </div>

        <div class="lg:col-span-7">
          <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="subscribe">
            <label class="min-w-0 flex-1">
              <span class="sr-only">Email address</span>
              <input v-model="email" type="email" required placeholder="you@company.com" class="field" />
            </label>
            <button
              type="submit"
              :disabled="submitting"
              class="shrink-0 bg-gold px-7 py-3 text-[0.86rem] font-semibold text-steel-950 transition-colors hover:bg-gold-light disabled:opacity-50"
            >
              {{ submitting ? 'Subscribing…' : 'Subscribe' }}
            </button>
          </form>

          <p v-if="status === 'success'" class="mt-3 text-[0.86rem] text-gold">You're subscribed — thank you.</p>
          <p v-if="status === 'error'" class="mt-3 text-[0.86rem] text-red-400">{{ errorMessage }}</p>

          <button
            type="button"
            class="mt-4 text-[0.8rem] text-white/50 underline underline-offset-4 transition-colors hover:text-white"
            :aria-expanded="showUnsubscribe"
            @click="showUnsubscribe = !showUnsubscribe"
          >
            Unsubscribe from the newsletter
          </button>

          <form v-if="showUnsubscribe" class="mt-3 flex flex-col gap-3 sm:flex-row" @submit.prevent="unsubscribe">
            <label class="min-w-0 flex-1">
              <span class="sr-only">Email address to unsubscribe</span>
              <input
                v-model="unsubscribeEmail"
                type="email"
                required
                placeholder="you@company.com"
                class="field"
              />
            </label>
            <button
              type="submit"
              :disabled="unsubscribing"
              class="shrink-0 border border-white/30 px-7 py-3 text-[0.86rem] font-semibold text-white transition-colors hover:border-gold hover:text-gold disabled:opacity-50"
            >
              {{ unsubscribing ? 'Removing…' : 'Unsubscribe' }}
            </button>
          </form>
          <p v-if="unsubscribeStatus === 'success'" class="mt-3 text-[0.84rem] text-white/60">
            You've been unsubscribed.
          </p>
          <p v-else-if="unsubscribeStatus" class="mt-3 text-[0.84rem] text-red-400">{{ unsubscribeStatus }}</p>
        </div>
      </div>
    </section>

    <section class="container-retail pb-10 sm:pb-12" aria-label="News and insights">
      <SectionHead title="News &amp; insights" link-label="All resources" link-to="/media/resources" />

      <div v-if="news.length" v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <NuxtLink
          v-for="post in news"
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
            <h3 class="text-[0.95rem] font-bold leading-snug text-ink group-hover:text-gold-dark">
              {{ post.title }}
            </h3>
            <p v-if="post.excerpt" class="mt-2 line-clamp-3 text-[0.82rem] leading-relaxed text-ink/65">
              {{ post.excerpt }}
            </p>
          </div>
        </NuxtLink>
      </div>

      <p v-else class="mt-6 border border-line bg-smoke p-6 text-[0.88rem] text-ink/60">No news posted yet.</p>
    </section>

    <ScheduleVisit />
  </div>
</template>
