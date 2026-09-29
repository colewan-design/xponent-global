<script setup>
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })
const route = useRoute()
const isMenuOpen = ref(false)
const isSearchOpen = ref(false)

/**
 * Every item here points somewhere of its own.
 *
 * Solutions, Industries and Products used to be three labels on one URL
 * (`/solutions`), so a visitor clicking Products landed on Solutions and the
 * utility bar above repeated four of the same links a second time. The three
 * now divide the catalogue the way the business does: Solutions is the
 * capability ranges, Products the stocked SKUs you can put on an order, and
 * Industries the sectors both are specified for.
 */
const nav = [
  { label: 'Solutions', href: '/solutions' },
  { label: 'Industries', href: '/industries' },
  { label: 'Products', href: '/products' },
  { label: 'About', href: '/about' },
  { label: 'Resources', href: '/media/resources' },
  { label: 'Contact', href: '/contact' },
]

const isActive = (href) => route.path === href || (href !== '/' && route.path.startsWith(`${href}/`))
const telHref = (number) => `tel:${number.replace(/\s/g, '')}`

watch(() => route.fullPath, () => { isMenuOpen.value = false })
watch(isMenuOpen, (open) => { document.body.style.overflow = open ? 'hidden' : '' })
onBeforeUnmount(() => { document.body.style.overflow = '' })

function openSearch() {
  isMenuOpen.value = false
  isSearchOpen.value = true
}
</script>

<template>
  <header class="sticky top-0 z-50 bg-white">
    <!--
      Contact details and the positioning line only. This bar used to carry a
      second copy of Industries / Solutions / About / Contact, 40px above the
      same links in the primary nav below it.
    -->
    <div class="hidden bg-steel-950 text-white/70 lg:block">
      <div class="container-retail-wide flex h-9 items-center justify-between gap-8 text-[0.7rem]">
        <p>Global supply. Real world results.</p>
        <div class="flex items-center gap-5">
          <a v-if="settings?.contact_phone" :href="telHref(settings.contact_phone)" class="hover:text-gold">{{ settings.contact_phone }}</a>
          <a v-if="settings?.contact_email" :href="`mailto:${settings.contact_email}`" class="hover:text-gold">{{ settings.contact_email }}</a>
        </div>
      </div>
    </div>

    <div class="border-b border-line bg-white">
      <div class="container-retail-wide flex h-[68px] items-center gap-8">
        <NuxtLink to="/" class="flex shrink-0 items-center gap-2" aria-label="Xponent Global home">
          <span class="block h-10 w-10 overflow-hidden">
            <img src="/logo.png" alt="" class="h-10 w-auto max-w-none" aria-hidden="true" />
          </span>
          <span class="leading-none">
            <span class="block text-[1.15rem] font-extrabold tracking-tight">XPONENT</span>
            <span class="mt-0.5 block text-[0.5rem] font-bold tracking-[0.2em] text-ink/55">GLOBAL</span>
          </span>
        </NuxtLink>

        <nav class="ml-auto hidden items-center gap-8 lg:flex" aria-label="Primary">
          <NuxtLink
            v-for="item in nav"
            :key="item.label"
            :to="item.href"
            class="text-[0.78rem] font-semibold transition-colors hover:text-gold-dark"
            :class="isActive(item.href) ? 'text-gold-dark' : 'text-ink'"
          >{{ item.label }}</NuxtLink>
        </nav>

        <!-- A button, not a link: it was a NuxtLink to /media/resources, which is
             where the "Resources" nav item already goes. It now opens search. -->
        <button
          type="button"
          class="ml-auto hidden h-9 w-9 items-center justify-center text-ink transition-colors hover:text-gold-dark lg:flex"
          aria-label="Search this site"
          @click="openSearch"
        >
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" /></svg>
        </button>
        <NuxtLink to="/contact" class="hidden bg-gold px-5 py-3 text-[0.76rem] font-bold text-steel-950 hover:bg-gold-light lg:block">Request a Quote&nbsp; →</NuxtLink>

        <button type="button" class="ml-auto flex h-10 w-10 items-center justify-center border border-line lg:hidden" :aria-expanded="isMenuOpen" aria-controls="mobile-nav" :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'" @click="isMenuOpen = !isMenuOpen">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path v-if="!isMenuOpen" d="M4 7h16M4 12h16M4 17h16" />
            <path v-else d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>
      </div>
    </div>

    <nav v-if="isMenuOpen" id="mobile-nav" class="border-b border-line bg-white px-5 py-4 lg:hidden" aria-label="Mobile navigation">
      <NuxtLink v-for="item in nav" :key="item.label" :to="item.href" class="block border-b border-line py-3 text-sm font-semibold last:border-0">{{ item.label }}</NuxtLink>
      <!-- The magnifier is desktop-only, so small screens reach search here. -->
      <button
        type="button"
        class="flex w-full items-center gap-2 border-t border-line py-3 text-left text-sm font-semibold"
        @click="openSearch"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" /></svg>
        Search
      </button>
      <NuxtLink to="/contact" class="mt-4 block bg-gold px-5 py-3 text-center text-sm font-bold">Request a Quote</NuxtLink>
    </nav>

    <SiteSearch v-model:open="isSearchOpen" />
  </header>
</template>
