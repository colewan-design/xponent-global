<script setup>
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })
const year = new Date().getFullYear()
const telHref = (number) => `tel:${number.replace(/\s/g, '')}`

const columns = [
  { title: 'Quick links', links: [
    { label: 'Home', href: '/' }, { label: 'About', href: '/about' }, { label: 'Solutions', href: '/solutions' },
    { label: 'Industries', href: '/solutions' }, { label: 'Products', href: '/solutions' }, { label: 'Resources', href: '/media/resources' }, { label: 'Contact', href: '/contact' },
  ]},
  { title: 'Industries', links: [
    { label: 'Mining', href: '/solutions' }, { label: 'Exploration', href: '/solutions#exploration-and-geotechnical' },
    { label: 'Construction', href: '/solutions#construction' }, { label: 'Energy', href: '/solutions' }, { label: 'Infrastructure', href: '/solutions' },
  ]},
  { title: 'Resources', links: [
    { label: 'Case studies', href: '/media/resources' }, { label: 'Technical bulletins', href: '/media/resources' },
    { label: 'Product catalogues', href: '/media/resources' }, { label: 'News', href: '/media/newsletter' },
  ]},
]
</script>

<template>
  <footer class="bg-steel-950 text-white">
    <div class="container-retail grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-[1.5fr_0.7fr_0.8fr_0.8fr_1fr]">
      <div>
        <img src="/logo.png" alt="Xponent Global" class="h-11 w-auto" />
        <p class="mt-4 max-w-[285px] text-[0.66rem] leading-relaxed text-white/58">
          Supplying critical industries with the equipment, consumables and support they need to build stronger, more sustainable futures.
        </p>
        <p class="mt-3 text-[0.62rem] font-bold">People. Supply. Progress.</p>
      </div>

      <div v-for="column in columns" :key="column.title">
        <h2 class="text-[0.7rem] font-bold">{{ column.title }}</h2>
        <ul class="mt-3 space-y-1.5">
          <li v-for="link in column.links" :key="`${column.title}-${link.label}`"><NuxtLink :to="link.href" class="text-[0.62rem] text-white/55 hover:text-gold">{{ link.label }}</NuxtLink></li>
        </ul>
      </div>

      <div>
        <h2 class="text-[0.7rem] font-bold">Contact</h2>
        <div class="mt-3 space-y-2 text-[0.62rem] leading-relaxed text-white/60">
          <p v-if="settings?.contact_phone"><a :href="telHref(settings.contact_phone)" class="hover:text-gold">☎ &nbsp;{{ settings.contact_phone }}</a></p>
          <p v-if="settings?.contact_email"><a :href="`mailto:${settings.contact_email}`" class="hover:text-gold">✉ &nbsp;{{ settings.contact_email }}</a></p>
          <p>⌖ &nbsp;Vancouver, BC<br />&nbsp;&nbsp;&nbsp;&nbsp;Canada</p>
        </div>
        <div class="mt-4 flex gap-4 text-[0.68rem] font-bold text-white/75"><span>in</span><span>▶</span></div>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div class="container-retail flex flex-wrap justify-between gap-3 py-4 text-[0.55rem] text-white/38">
        <p>© {{ year }} Xponent Global. All rights reserved.</p>
        <p>Privacy Policy &nbsp;&nbsp;&nbsp; Terms of Use</p>
      </div>
    </div>
  </footer>
</template>
