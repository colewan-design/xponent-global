<script setup>
/**
 * Gallery — a flat grid of site photography with a lightbox.
 *
 * Thumbnails are plain photographs on hairline-bordered cells; the duotone plate
 * treatment is reserved for the page heroes, so a photo library reads as itself.
 */
const { data: gallery } = await useApiFetch('/gallery')

useSeoMeta({
  title: 'Gallery',
  description: 'Photos from Xponent Global sites and operations.',
})

const images = computed(() => gallery.value?.data ?? [])

const activeIndex = ref(null)
const titleId = 'gallery-lightbox-title'

function open(index) {
  activeIndex.value = index
}
function close() {
  activeIndex.value = null
}
function next() {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value + 1) % images.value.length
}
function prev() {
  if (activeIndex.value === null) return
  activeIndex.value = (activeIndex.value - 1 + images.value.length) % images.value.length
}

function handleKeydown(event) {
  if (activeIndex.value === null) return
  if (event.key === 'Escape') close()
  if (event.key === 'ArrowRight') next()
  if (event.key === 'ArrowLeft') prev()
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onBeforeUnmount(() => document.removeEventListener('keydown', handleKeydown))

// The lightbox owns the viewport while it's open.
watch(activeIndex, (index) => {
  if (import.meta.client) document.body.style.overflow = index === null ? '' : 'hidden'
})
onBeforeUnmount(() => {
  if (import.meta.client) document.body.style.overflow = ''
})
</script>

<template>
  <div>
    <PageHero
      eyebrow="Media"
      title="Gallery"
      subtitle="Photographs from our sites and operations."
      :image="images[0]?.image ?? '/images/page-hero-bg.jpg'"
    />

    <section class="container-retail py-8 sm:py-10" aria-label="Photo gallery">
      <SectionHead title="From the field" link-label="All resources" link-to="/media/resources" />

      <div v-reveal:group class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <button
          v-for="(image, index) in images"
          :key="image.id"
          class="group flex flex-col border border-line text-left transition-colors hover:border-gold focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-dark"
          :aria-label="image.caption ? `Open photo: ${image.caption}` : `Open photo ${index + 1}`"
          @click="open(index)"
        >
          <div class="aspect-16/10 overflow-hidden bg-smoke">
            <CmsImage
              :src="image.image"
              :alt="image.caption || 'Xponent Global site photo'"
              loading="lazy"
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
          </div>
          <p v-if="image.caption" class="line-clamp-2 p-3 text-[0.82rem] leading-snug text-ink/70">
            {{ image.caption }}
          </p>
        </button>
      </div>
    </section>

    <!-- Lightbox. -->
    <div
      v-if="activeIndex !== null"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
      class="fixed inset-0 z-50 flex items-center justify-center bg-steel-950/95 p-4 sm:p-10"
      @click.self="close"
    >
      <h2 :id="titleId" class="sr-only">{{ images[activeIndex].caption || 'Gallery photo viewer' }}</h2>

      <p class="pointer-events-none absolute left-5 top-5 text-[0.82rem] text-white/60 sm:left-8 sm:top-8">
        {{ activeIndex + 1 }} / {{ images.length }}
      </p>

      <button
        class="absolute right-5 top-5 flex h-11 w-11 items-center justify-center border border-white/25 text-white transition-colors hover:border-gold hover:text-gold sm:right-8 sm:top-8"
        aria-label="Close"
        autofocus
        @click="close"
      >
        ✕
      </button>

      <button
        class="absolute left-4 z-10 flex h-11 w-11 items-center justify-center border border-white/25 text-white transition-colors hover:border-gold hover:text-gold sm:left-8"
        aria-label="Previous photo"
        @click="prev"
      >
        ‹
      </button>

      <figure class="relative max-h-full">
        <CmsImage
          :src="images[activeIndex].image"
          :alt="images[activeIndex].caption || 'Xponent Global site photo'"
          class="max-h-[78vh] max-w-4xl object-contain"
        />
        <figcaption v-if="images[activeIndex].caption" class="mt-4 text-center text-[0.84rem] text-white/60">
          {{ images[activeIndex].caption }}
        </figcaption>
      </figure>

      <button
        class="absolute right-4 z-10 flex h-11 w-11 items-center justify-center border border-white/25 text-white transition-colors hover:border-gold hover:text-gold sm:right-8"
        aria-label="Next photo"
        @click="next"
      >
        ›
      </button>
    </div>
  </div>
</template>
