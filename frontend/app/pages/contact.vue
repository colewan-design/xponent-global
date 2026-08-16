<script setup>
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })
const { data: locations } = await useApiFetch('/office-locations', { key: 'office-locations' })

// The embedded map below is pinned to the Ormiston (Brisbane) address, but the
// legacy page shows the pin with no caption. Label it from the office record so
// the marker is identifiable, and fall back to the first office if the Australian
// one is ever renamed or removed in admin.
const mappedOffice = computed(() => {
  const offices = locations.value?.data ?? []
  return offices.find((office) => office.country === 'Australia') ?? offices[0] ?? null
})

function formatAddress(office) {
  return [office.address, office.city, office.country].filter(Boolean).join(', ')
}

function telHref(number) {
  return `tel:${number.replace(/\s/g, '')}`
}

useSeoMeta({
  title: 'Contact Us',
  description: 'Get in touch with Xponent Global for enquiries about our products and services.',
})

const enquiryTypes = [
  'Underground Tooling',
  'Underground and Surface Tooling',
  'Drilling Consumables',
  'Top Hammer Drilling Tools',
  'DTH Drilling Tools',
  'Split Set Stabiliser System',
  'Steel Wire Mesh',
  'Mine Cars',
  'Steel Rails and Fasteners',
  'Rail Joints and Bolts',
  'Mining Technology: Instrumentation, Data and Drilling Solutions',
  'Personal Protection Equipment (PPE) Wayne Gumboots',
  'Corewise Core Saw',
  'Discoverer Core Trays',
  'Diamond Core Blades',
  'Core Guides to Automatic Core Saws',
  'Cap Lamp',
]

const regions = ['Asia', 'Australia and Pacific', 'Europe, Middle East, Africa', 'Latin America', 'North America']

function emptyForm() {
  return {
    enquiry_type: enquiryTypes[0],
    region: regions[0],
    country: '',
    name: '',
    email: '',
    company: '',
    phone: '',
    message: '',
    // Honeypot: left blank and hidden from sighted/keyboard users via CSS below.
    // A bot that fills every field it finds trips the backend's `prohibited` rule.
    website: '',
  }
}

const form = reactive(emptyForm())

const submitting = ref(false)
const status = ref('')
const errorMessage = ref('')

async function submit() {
  submitting.value = true
  status.value = ''
  errorMessage.value = ''
  try {
    await apiPost('/contact-enquiries', form)
    status.value = 'success'
    Object.assign(form, emptyForm())
  } catch (e) {
    status.value = 'error'
    errorMessage.value = extractApiErrorMessage(e)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div>
    <PageHero
      eyebrow="Contact Us"
      title="Send Us Your Queries"
      subtitle="Tell us what the project needs. Our team will come back with availability, specification and lead times."
    />

    <section class="container-retail py-8 sm:py-10" aria-label="Enquiry form">
      <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
        <!-- Contact register. -->
        <div class="lg:col-span-4">
          <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
            Get in touch.
          </h2>

          <div class="mt-3 space-y-1 text-[0.88rem]">
            <p v-if="settings?.contact_email">
              <a
                :href="`mailto:${settings.contact_email}`"
                class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              >
                {{ settings.contact_email }}
              </a>
            </p>
            <p v-if="settings?.contact_phone">
              <a
                :href="telHref(settings.contact_phone)"
                class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              >
                {{ settings.contact_phone }}
              </a>
            </p>
          </div>

          <div v-if="locations?.data?.length" class="mt-6">
            <p class="text-[0.66rem] font-bold uppercase tracking-[0.14em] text-ink/45">Our offices</p>
            <ul class="mt-3 grid gap-3">
              <li v-for="office in locations.data" :key="office.id" class="bg-white p-4">
                <h3 class="text-[0.88rem] font-bold text-ink">{{ office.label }}</h3>
                <address class="mt-1 text-[0.82rem] not-italic leading-relaxed text-ink/65">
                  {{ formatAddress(office) }}
                </address>
              </li>
            </ul>
          </div>
        </div>

        <!-- The form itself, on its own white field. -->
        <div class="lg:col-span-8">
          <form class="space-y-4 bg-white p-5 sm:p-6" @submit.prevent="submit">
            <label class="block">
              <span class="field-label">I am enquiring about *</span>
              <select v-model="form.enquiry_type" required class="field">
                <option v-for="type in enquiryTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block">
                <span class="field-label">Region *</span>
                <select v-model="form.region" required class="field">
                  <option v-for="region in regions" :key="region" :value="region">{{ region }}</option>
                </select>
              </label>
              <label class="block">
                <span class="field-label">Country *</span>
                <input v-model="form.country" required type="text" class="field" />
              </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block">
                <span class="field-label">Full name *</span>
                <input v-model="form.name" required type="text" class="field" />
              </label>
              <label class="block">
                <span class="field-label">Email address *</span>
                <input v-model="form.email" required type="email" class="field" />
              </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <label class="block">
                <span class="field-label">Company</span>
                <input v-model="form.company" type="text" class="field" />
              </label>
              <label class="block">
                <span class="field-label">Contact number</span>
                <input v-model="form.phone" type="text" class="field" />
              </label>
            </div>

            <label class="block">
              <span class="field-label">Message *</span>
              <textarea v-model="form.message" required rows="5" class="field"></textarea>
            </label>

            <!-- Honeypot field: off-screen, unreachable by tab, ignored by screen readers.
                 Real visitors never see or fill it; simple bots that auto-fill every
                 input on the page do, and the backend rejects the submission. -->
            <div class="absolute left-[-9999px] top-auto h-0 w-0 overflow-hidden" aria-hidden="true">
              <label>
                Website
                <input v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
              </label>
            </div>

            <p v-if="status === 'success'" class="border-l-2 border-gold-dark bg-smoke px-4 py-3 text-[0.86rem] text-ink/80">
              Thanks — your enquiry has been sent.
            </p>
            <p v-if="status === 'error'" class="border-l-2 border-red-600 bg-red-50 px-4 py-3 text-[0.86rem] text-red-700">
              {{ errorMessage }}
            </p>

            <button
              type="submit"
              :disabled="submitting"
              class="w-full bg-steel-950 px-6 py-3.5 text-[0.86rem] font-semibold text-white transition-colors hover:bg-gold hover:text-steel-950 disabled:opacity-50 sm:w-auto"
            >
              {{ submitting ? 'Sending…' : 'Send Enquiry' }}
            </button>
          </form>
        </div>
      </div>
    </section>

    <section class="container-retail pb-10 sm:pb-12" aria-label="Find us">
      <SectionHead :title="mappedOffice ? mappedOffice.label : 'Find us'" />
      <address v-if="mappedOffice" class="mt-2 text-[0.88rem] not-italic leading-relaxed text-ink/70">
        {{ formatAddress(mappedOffice) }}
      </address>

      <div class="mt-4 border border-line">
        <iframe
          :title="mappedOffice ? `Map of the ${mappedOffice.label} office` : 'Map of the Xponent Global office'"
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3524.3102693967983!2d153.24664247565668!3d-27.50400491632407!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6b913b76f545c375%3A0xb1a4c67f9a535f88!2s255%20Wellington%20St%2C%20Ormiston%20QLD%204160%2C%20Australia!5e0!3m2!1sen!2sin!4v1720692445000!5m2!1sen!2sin"
          class="h-96 w-full border-0"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        />
      </div>
    </section>

    <ScheduleVisit />
  </div>
</template>
