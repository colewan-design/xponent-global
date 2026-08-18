<script setup>
/**
 * Three stacked bars: contact strip, masthead, section nav.
 *
 * The whole header pins as one unit, so the section nav stays reachable from
 * anywhere on a long page. Each bar therefore needs its own opaque background —
 * a transparent one would let content scroll through it.
 *
 * Two of the nav entries (Solutions, Media) have sub-sections and open a sheet
 * under the bar. Hover opens it, but it is a real disclosure too:
 * focusing the link opens it, Escape closes it, and a tap on touch (where there
 * is no hover) just follows the link to the section's own page.
 */
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })

const route = useRoute()

const isMenuOpen = ref(false)
/** Which desktop sheet is open, by index into `nav`. Null when none is. */
const openSheet = ref(null)
/** Which section is expanded in the mobile panel, by label. */
const openMobileSection = ref(null)

const nav = [
  { label: 'About Us', href: '/about' },
  {
    label: 'Solutions',
    href: '/solutions',
    blurb: 'Tooling, consumables, steel wire, camp facilities and construction supply.',
    links: [
      { label: 'Exploration & Geotechnical', href: '/solutions#exploration-and-geotechnical' },
      { label: 'Mining & Production Consumables', href: '/solutions#mining-and-production-consumables' },
      { label: 'Construction', href: '/solutions#construction' },
      { label: 'Mining Camp Facilities', href: '/solutions#mining-camp-facilities' },
      { label: 'Steel Wire Products', href: '/solutions#steel-wire-products' },
      { label: 'Wire Mesh, Gabions & Fencing', href: '/solutions#wire-mesh-and-gabions' },
      { label: 'Personal Protection Equipment', href: '/solutions/personal-protection-equipment' },
      { label: 'Our Brand Partners', href: '/solutions/our-brand-partners' },
    ],
  },
  { label: 'Our Clients', href: '/clients' },
  { label: 'Sustainability', href: '/sustainability' },
  {
    label: 'Media',
    href: '/media/resources',
    blurb: 'Documents, datasheets, case studies, bulletins and site photography.',
    links: [
      { label: 'Resources', href: '/media/resources' },
      { label: 'Newsletter', href: '/media/newsletter' },
      { label: 'Gallery', href: '/media/gallery' },
    ],
  },
  { label: 'Career', href: '/careers' },
  { label: 'Contact Us', href: '/contact' },
]

const activeSection = computed(() => (openSheet.value === null ? null : nav[openSheet.value]))

// A grace period on leaving: without it, the few pixels of dead space between the
// nav row and the sheet close the menu while the pointer is travelling into it.
let closeTimer = null

function openNow(index) {
  cancelClose()
  openSheet.value = nav[index].links ? index : null
}

function cancelClose() {
  if (closeTimer) clearTimeout(closeTimer)
  closeTimer = null
}

function scheduleClose() {
  cancelClose()
  closeTimer = setTimeout(() => {
    openSheet.value = null
  }, 160)
}

function isActive(href) {
  const path = href.split('#')[0]
  return route.path === path || route.path.startsWith(`${path}/`)
}

function telHref(number) {
  return `tel:${number.replace(/\s/g, '')}`
}

function handleKeydown(event) {
  if (event.key !== 'Escape') return
  openSheet.value = null
  isMenuOpen.value = false
}

onMounted(() => window.addEventListener('keydown', handleKeydown))

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  cancelClose()
  document.body.style.overflow = ''
})

// The mobile panel owns the viewport while it's open.
watch(isMenuOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

watch(
  () => route.fullPath,
  () => {
    openSheet.value = null
    isMenuOpen.value = false
    openMobileSection.value = null
  },
)
</script>

<template>
  <header class="sticky top-0 z-50">
    <!-- 1 — Contact strip -->
    <div class="bg-smoke">
      <p class="container-retail-wide flex flex-wrap items-center justify-center gap-x-3 gap-y-1 py-2 text-center text-[0.8rem] leading-snug text-ink/80">
        <span class="font-semibold uppercase tracking-wide">Supplying confidence. Delivering certainty.</span>
        <span class="hidden text-ink/30 sm:inline">|</span>
        <a
          v-if="settings?.contact_email"
          :href="`mailto:${settings.contact_email}`"
          class="underline underline-offset-2 hover:text-gold-dark"
        >
          {{ settings.contact_email }}
        </a>
        <template v-if="settings?.contact_phone">
          <span class="hidden text-ink/30 sm:inline">|</span>
          <a :href="telHref(settings.contact_phone)" class="underline underline-offset-2 hover:text-gold-dark">
            {{ settings.contact_phone }}
          </a>
        </template>
      </p>
    </div>

    <!-- 2 — Masthead -->
    <div class="border-b border-line bg-white">
      <div class="container-retail-wide flex items-center gap-4 py-3 lg:gap-8 lg:py-4">
        <!--
          The packaged logo is a white-on-transparent lockup drawn for dark
          grounds — on this white masthead its "XPONENT" simply disappears and
          only the gold mark and "GLOBAL" survive. So the mark is cropped out of
          it (the X occupies the leftmost ~59% of the artwork's height-width
          ratio) and the wordmark is typeset beside it, which reads at any
          surface colour. The footer, being dark, still uses the full lockup.
        -->
        <NuxtLink to="/" class="flex shrink-0 items-center gap-2.5" aria-label="Xponent Global — home">
          <span class="block aspect-[59/100] h-9 overflow-hidden lg:h-10">
            <img src="/logo.png" alt="" aria-hidden="true" class="h-full w-auto max-w-none" />
          </span>
          <span class="leading-none">
            <span class="block text-[1.3rem] font-extrabold tracking-tight text-ink lg:text-[1.5rem]">Xponent</span>
            <span class="mt-0.5 block text-[0.55rem] font-semibold uppercase tracking-[0.18em] text-ink/50">
              Global
            </span>
          </span>
        </NuxtLink>

        <p class="hidden min-w-0 flex-1 text-[0.82rem] leading-snug text-ink/60 lg:block">
          International total solutions provider to the mining, drilling, oil &amp; gas, construction
          and energy sectors.
        </p>

        <nav class="hidden shrink-0 items-center gap-6 text-[0.8rem] text-ink/85 xl:flex" aria-label="Utility">
          <NuxtLink to="/media/resources" class="transition-colors hover:text-gold-dark">Resources</NuxtLink>
          <NuxtLink to="/media/newsletter" class="transition-colors hover:text-gold-dark">Newsletter</NuxtLink>
          <NuxtLink to="/careers" class="transition-colors hover:text-gold-dark">Careers</NuxtLink>
        </nav>

        <NuxtLink
          to="/contact"
          class="hidden shrink-0 bg-steel-950 px-5 py-2.5 text-[0.82rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950 sm:block"
        >
          Get in Touch
        </NuxtLink>

        <button
          type="button"
          class="ml-auto flex h-10 w-10 shrink-0 items-center justify-center border border-line text-ink transition-colors hover:border-ink/40 lg:hidden"
          :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'"
          :aria-expanded="isMenuOpen"
          aria-controls="mobile-nav"
          @click="isMenuOpen = !isMenuOpen"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path v-if="!isMenuOpen" d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
            <path v-else d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
          </svg>
        </button>
      </div>
    </div>

    <!-- 3 — Section nav. `relative` anchors the sheet to the foot of the header,
         so it follows the header's real height rather than a guess. -->
    <div class="relative hidden border-b border-line bg-smoke lg:block">
      <div class="container-retail-wide">
        <nav class="retail-rail flex min-w-0 items-center gap-1 overflow-x-auto" aria-label="Primary" @mouseleave="scheduleClose">
          <div v-for="(item, index) in nav" :key="item.label" class="shrink-0">
            <NuxtLink
              :to="item.href"
              class="flex items-center gap-1.5 whitespace-nowrap px-3 py-3 text-[0.82rem] transition-colors hover:text-gold-dark"
              :class="openSheet === index || isActive(item.href) ? 'font-semibold text-gold-dark' : 'text-ink/85'"
              :aria-current="isActive(item.href) ? 'page' : undefined"
              :aria-expanded="item.links ? openSheet === index : undefined"
              aria-controls="section-sheet"
              @mouseenter="openNow(index)"
              @focus="openNow(index)"
              @click="openSheet = null"
            >
              {{ item.label }}
              <svg
                v-if="item.links"
                width="11"
                height="11"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                aria-hidden="true"
                class="transition-transform"
                :class="openSheet === index ? 'rotate-180' : ''"
              >
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </NuxtLink>
          </div>
        </nav>
      </div>

      <div
        v-if="activeSection"
        id="section-sheet"
        class="absolute inset-x-0 top-full border-t border-line bg-white shadow-lg shadow-black/10"
        @mouseenter="cancelClose"
        @mouseleave="scheduleClose"
      >
        <div class="container-retail-wide relative py-7">
          <button
            type="button"
            class="absolute right-4 top-5 flex h-8 w-8 items-center justify-center border border-line text-ink/60 transition-colors hover:border-ink/40 hover:text-ink"
            aria-label="Close menu"
            @click="openSheet = null"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
            </svg>
          </button>

          <div class="grid gap-8 lg:grid-cols-12">
            <div class="lg:col-span-3">
              <h2 class="text-[1.05rem] font-bold tracking-tight text-ink">{{ activeSection.label }}</h2>
              <p class="mt-2 max-w-xs text-[0.84rem] leading-relaxed text-ink/65">{{ activeSection.blurb }}</p>
              <NuxtLink
                :to="activeSection.href"
                class="mt-3 inline-block text-[0.84rem] font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
                @click="openSheet = null"
              >
                View all
              </NuxtLink>
            </div>

            <ul class="grid gap-x-6 gap-y-1 lg:col-span-8 lg:grid-cols-3">
              <li v-for="link in activeSection.links" :key="link.label">
                <NuxtLink
                  :to="link.href"
                  class="block py-1.5 text-[0.86rem] leading-snug text-ink/80 transition-colors hover:text-gold-dark"
                  @click="openSheet = null"
                >
                  {{ link.label }}
                </NuxtLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile panel. Below the masthead rather than over it, so the toggle stays
         visible and the page behind is clearly replaced rather than dimmed. -->
    <div
      v-if="isMenuOpen"
      id="mobile-nav"
      class="max-h-[calc(100vh-8rem)] overflow-y-auto border-b border-line bg-white lg:hidden"
    >
      <nav class="container-retail-wide py-2" aria-label="Primary">
        <div v-for="item in nav" :key="item.label" class="border-b border-line last:border-b-0">
          <div class="flex items-center justify-between gap-3">
            <NuxtLink
              :to="item.href"
              class="flex-1 py-3.5 text-[0.92rem] font-semibold"
              :class="isActive(item.href) ? 'text-gold-dark' : 'text-ink'"
              :aria-current="isActive(item.href) ? 'page' : undefined"
            >
              {{ item.label }}
            </NuxtLink>

            <!-- Separate control: the row itself must stay a link to the parent page. -->
            <button
              v-if="item.links"
              type="button"
              class="flex h-9 w-9 shrink-0 items-center justify-center border border-line text-ink/60 transition-colors hover:border-ink/40"
              :aria-label="`${openMobileSection === item.label ? 'Hide' : 'Show'} ${item.label} sections`"
              :aria-expanded="openMobileSection === item.label"
              @click="openMobileSection = openMobileSection === item.label ? null : item.label"
            >
              <svg
                width="13"
                height="13"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                aria-hidden="true"
                class="transition-transform"
                :class="openMobileSection === item.label ? 'rotate-180' : ''"
              >
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>

          <div
            v-if="item.links"
            class="grid transition-[grid-template-rows] duration-300 ease-out"
            :class="openMobileSection === item.label ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
          >
            <div class="overflow-hidden">
              <ul class="border-l border-line pb-3 pl-4">
                <li v-for="link in item.links" :key="link.label">
                  <NuxtLink :to="link.href" class="block py-2 text-[0.86rem] text-ink/70 hover:text-gold-dark">
                    {{ link.label }}
                  </NuxtLink>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <NuxtLink to="/contact" class="my-4 block bg-steel-950 px-5 py-3.5 text-center text-[0.86rem] font-semibold text-white">
          Get in Touch
        </NuxtLink>
      </nav>
    </div>
  </header>
</template>
