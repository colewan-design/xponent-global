<script setup>
/**
 * Error page. Nuxt renders this outside the layout, so the header and footer are
 * mounted here explicitly.
 */
const props = defineProps({
  error: { type: Object, required: true },
})

useSeoMeta({
  title: props.error.statusCode === 404 ? 'Page not found' : 'Something went wrong',
})

const isNotFound = computed(() => props.error.statusCode === 404)

const links = [
  { label: 'Solutions', href: '/solutions' },
  { label: 'About Us', href: '/about' },
  { label: 'Our Clients', href: '/clients' },
  { label: 'Resources', href: '/media/resources' },
  { label: 'Careers', href: '/careers' },
  { label: 'Contact Us', href: '/contact' },
]

function goHome() {
  clearError({ redirect: '/' })
}
</script>

<template>
  <div class="flex min-h-screen flex-col bg-white">
    <SiteHeader />

    <main class="container-retail flex-1 py-12 sm:py-16">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <div class="lg:col-span-6">
          <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Error {{ error.statusCode }}</p>
          <h1 class="mt-2 text-[clamp(1.5rem,3vw,2.2rem)] font-bold leading-tight tracking-tight text-ink">
            {{ isNotFound ? 'Page not found.' : 'Something went wrong.' }}
          </h1>
          <p class="mt-3 max-w-md text-[0.9rem] leading-relaxed text-ink/70">
            {{
              isNotFound
                ? "The page you're looking for doesn't exist or may have moved."
                : 'An unexpected error occurred. Please try again in a moment.'
            }}
          </p>

          <div class="mt-5 flex flex-wrap gap-3">
            <button
              class="bg-steel-950 px-6 py-3 text-[0.84rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950"
              @click="goHome"
            >
              Back to homepage
            </button>
            <NuxtLink
              to="/contact"
              class="border border-line bg-white px-6 py-3 text-[0.84rem] font-semibold text-ink transition-colors hover:border-gold hover:text-gold-dark"
            >
              Contact us
            </NuxtLink>
          </div>
        </div>

        <div class="lg:col-span-5 lg:col-start-8">
          <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Try one of these</p>
          <ul class="mt-3 grid gap-px bg-line sm:grid-cols-2">
            <li v-for="link in links" :key="link.href">
              <NuxtLink
                :to="link.href"
                class="block bg-white px-4 py-3 text-[0.86rem] text-ink/80 transition-colors hover:text-gold-dark"
              >
                {{ link.label }}
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </main>

    <SiteFooter />
  </div>
</template>
