<script setup>
/**
 * Cookie notice, pinned to the foot of the viewport until a choice is made.
 *
 * Deliberately not a modal: it does not trap focus or block the page. The only
 * cookies set before a choice is made are the ones needed to run the site, so
 * there is nothing to hold the visitor hostage over — measurement is what waits
 * on the answer, via `analyticsAllowed` in useCookieConsent.
 *
 * Accept and Decline carry equal visual weight on purpose; a greyed-out decline
 * next to a bright accept is a dark pattern, and regulators treat it as one.
 *
 * Being fixed, the bar would otherwise sit on top of the footer's bottom bar.
 * Its height is measured rather than hard-coded because the copy wraps to three
 * lines on a phone and one on a desktop — so the reserved space is padded onto
 * the body and kept in sync as the viewport changes.
 */
const { hasChosen, accept, reject } = useCookieConsent()

const banner = ref(null)
const { height } = useElementSize(banner)

function reserveSpace(px) {
  document.body.style.paddingBottom = px > 0 ? `${Math.ceil(px)}px` : ''
}

watch([height, hasChosen], ([h, chosen]) => {
  if (import.meta.server) return
  reserveSpace(chosen ? 0 : h)
})

onBeforeUnmount(() => reserveSpace(0))
</script>

<template>
  <div
    v-if="!hasChosen"
    ref="banner"
    class="fixed inset-x-0 bottom-0 z-[60] border-t border-white/15 bg-steel-950"
    role="region"
    aria-label="Cookie notice"
  >
    <div
      class="container-retail flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between lg:gap-8"
    >
      <p class="max-w-3xl text-[0.84rem] leading-relaxed text-white/75">
        We use cookies that are needed to run this site. With your permission we would also like to
        measure how it is used, so we can improve it.
      </p>

      <div class="flex shrink-0 gap-3">
        <button
          type="button"
          class="border border-white/25 px-5 py-2.5 text-[0.84rem] font-semibold text-white transition-colors hover:border-gold hover:text-gold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold"
          @click="reject"
        >
          Decline
        </button>
        <button
          type="button"
          class="bg-gold px-5 py-2.5 text-[0.84rem] font-semibold text-ink transition-colors hover:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold"
          @click="accept"
        >
          Accept
        </button>
      </div>
    </div>
  </div>
</template>
