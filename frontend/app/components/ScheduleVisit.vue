<script setup>
// The legacy site repeats this block on 7 of its 12 pages (home, about, clients,
// sustainability, resources, newsletter, career), so it lives here rather than
// inline on any one page. Its legacy body copy is Lorem Ipsum and is not carried over.
const { data: settings } = await useApiFetch('/settings', { key: 'site-settings' })

const hours = computed(() => [
  { label: 'Monday — Friday', value: settings.value?.hours_weekdays },
  { label: 'Saturday', value: settings.value?.hours_saturday },
  { label: 'Sunday', value: settings.value?.hours_sunday },
])

function telHref(number) {
  return `tel:${number.replace(/\s/g, '')}`
}
</script>

<template>
  <section
    v-if="settings?.hours_weekdays"
    id="schedule-a-visit"
    class="container-retail pb-10 sm:pb-12"
    aria-label="Schedule a visit"
  >
    <div class="grid gap-6 bg-smoke p-6 sm:p-8 lg:grid-cols-12 lg:gap-8">
      <div class="lg:col-span-5">
        <h2 class="text-[clamp(1.4rem,2.4vw,1.85rem)] font-bold leading-tight tracking-tight text-ink">
          Schedule a visit.
        </h2>
        <p class="mt-3 max-w-md text-[0.9rem] leading-relaxed text-ink/70">
          Our lines are open during the hours listed. Call ahead and we'll have the right person ready
          for you.
        </p>

        <div class="mt-3 space-y-1 text-[0.86rem]">
          <p v-if="settings.contact_phone">
            <a :href="telHref(settings.contact_phone)" class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink">
              {{ settings.contact_phone }}
            </a>
            <template v-if="settings.contact_phone_alt">
              <span class="mx-2 text-ink/30">/</span>
              <a
                :href="telHref(settings.contact_phone_alt)"
                class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              >
                {{ settings.contact_phone_alt }}
              </a>
            </template>
          </p>
          <p v-if="settings.contact_email">
            <a
              :href="`mailto:${settings.contact_email}`"
              class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
            >
              {{ settings.contact_email }}
            </a>
            <template v-if="settings.contact_email_alt">
              <span class="mx-2 text-ink/30">/</span>
              <a
                :href="`mailto:${settings.contact_email_alt}`"
                class="font-medium text-gold-dark underline underline-offset-4 hover:text-ink"
              >
                {{ settings.contact_email_alt }}
              </a>
            </template>
          </p>
        </div>
      </div>

      <!-- `content-start` keeps the rows their own height rather than stretching
           them to match the taller column beside them. -->
      <dl class="grid content-start gap-px bg-line lg:col-span-7">
        <div
          v-for="row in hours"
          :key="row.label"
          class="flex flex-wrap items-baseline justify-between gap-4 bg-white px-5 py-4"
        >
          <dt class="text-[0.9rem] font-semibold text-ink">{{ row.label }}</dt>
          <dd class="text-[0.9rem] text-ink/70">{{ row.value }}</dd>
        </div>
      </dl>
    </div>
  </section>
</template>
