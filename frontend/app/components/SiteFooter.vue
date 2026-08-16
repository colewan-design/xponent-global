<script setup>
/**
 * Footer — the one dark field that closes every page.
 *
 * Office lines come from the same `/office-locations` record the About and
 * Contact pages read, so the footer can never drift from them.
 */
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })

const year = new Date().getFullYear()

const socialLinks = computed(() => {
  if (!settings.value) return []
  return [
    { key: 'facebook_url', label: 'Facebook' },
    { key: 'instagram_url', label: 'Instagram' },
    { key: 'twitter_url', label: 'X' },
    { key: 'youtube_url', label: 'YouTube' },
  ].filter((item) => settings.value[item.key])
})

const columns = [
  {
    title: 'Company',
    links: [
      { label: 'About Us', href: '/about' },
      { label: 'Our Clients', href: '/clients' },
      { label: 'Sustainability', href: '/sustainability' },
      { label: 'Career', href: '/careers' },
      { label: 'Contact Us', href: '/contact' },
    ],
  },
  {
    title: 'Solutions',
    links: [
      { label: 'Exploration & Geotechnical', href: '/solutions#exploration-and-geotechnical' },
      { label: 'Mining & Production', href: '/solutions#mining-and-production-consumables' },
      { label: 'Construction', href: '/solutions#construction' },
      { label: 'Personal Protection Equipment', href: '/solutions/personal-protection-equipment' },
      { label: 'Our Brand Partners', href: '/solutions/our-brand-partners' },
    ],
  },
  {
    title: 'Media',
    links: [
      { label: 'Resources', href: '/media/resources' },
      { label: 'Newsletter', href: '/media/newsletter' },
      { label: 'Gallery', href: '/media/gallery' },
    ],
  },
]

function telHref(number) {
  return `tel:${number.replace(/\s/g, '')}`
}
</script>

<template>
  <footer class="bg-steel-950 text-white">
    <div class="container-retail grid gap-10 py-12 lg:grid-cols-12 lg:gap-8">
      <div class="lg:col-span-4">
        <!-- No filter: the lockup is drawn white-on-transparent with a gold mark,
             which is exactly what this dark field wants. -->
        <img src="/logo.png" alt="Xponent Global" class="h-12 w-auto" />
        <div v-if="settings?.footer_about" class="mt-5 max-w-sm space-y-3 text-[0.86rem] leading-relaxed text-white/60">
          <p v-for="(paragraph, i) in settings.footer_about.split('\n\n')" :key="i">{{ paragraph }}</p>
        </div>

        <div v-if="socialLinks.length" class="mt-6 flex flex-wrap gap-2">
          <a
            v-for="link in socialLinks"
            :key="link.key"
            :href="settings[link.key]"
            target="_blank"
            rel="noopener"
            class="border border-white/20 px-4 py-2 text-[0.78rem] text-white/75 transition-colors hover:border-gold hover:text-gold"
          >
            {{ link.label }}
          </a>
        </div>
      </div>

      <div v-for="column in columns" :key="column.title" class="lg:col-span-2">
        <h2 class="text-[0.86rem] font-bold text-white">{{ column.title }}</h2>
        <ul class="mt-4 space-y-2">
          <li v-for="link in column.links" :key="link.href">
            <NuxtLink :to="link.href" class="text-[0.84rem] leading-snug text-white/60 transition-colors hover:text-gold">
              {{ link.label }}
            </NuxtLink>
          </li>
        </ul>
      </div>

      <div class="lg:col-span-2">
        <h2 class="text-[0.86rem] font-bold text-white">Contact</h2>
        <div class="mt-4 space-y-2 text-[0.84rem]">
          <p v-if="settings?.contact_email">
            <a :href="`mailto:${settings.contact_email}`" class="text-white/60 transition-colors hover:text-gold">
              {{ settings.contact_email }}
            </a>
          </p>
          <p v-if="settings?.contact_phone">
            <a :href="telHref(settings.contact_phone)" class="text-white/60 transition-colors hover:text-gold">
              {{ settings.contact_phone }}
            </a>
          </p>
        </div>

        <ul v-if="locations?.data?.length" class="mt-6 space-y-4">
          <li v-for="office in locations.data" :key="office.id">
            <p class="text-[0.78rem] font-semibold text-white/90">{{ office.label }}</p>
            <address class="mt-1 text-[0.8rem] not-italic leading-relaxed text-white/50">
              {{ [office.address, office.city, office.country].filter(Boolean).join(', ') }}
            </address>
          </li>
        </ul>
      </div>
    </div>

    <div class="border-t border-white/10">
      <p class="container-retail py-5 text-center text-[0.78rem] text-white/40">
        &copy; {{ year }} Xponent Global. All rights reserved.
      </p>
    </div>
  </footer>
</template>
