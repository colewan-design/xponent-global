import gsap from 'gsap'

/**
 * v-reveal        — fades/slides the element itself in as it enters the viewport.
 * v-reveal:group  — fades/slides the element's direct children in with a stagger
 *                    (for card grids), rather than animating the container as one block.
 *
 * Registered universally (not client-only) so SSR/prerender can still resolve the
 * directive — the actual work only ever runs client-side, since `mounted` never
 * fires during server rendering.
 *
 * Scheduling is IntersectionObserver, not GSAP's ScrollTrigger, and that choice is
 * load-bearing. ScrollTrigger measures each trigger's scroll offset once, at
 * creation. Nearly every image here is `loading="lazy"` and CMS-supplied, so
 * images land *after* that measurement and push everything below them down the
 * page — the last sections end up with start offsets past the document's real
 * end and never fire, leaving their cards stuck at `opacity: 0` permanently.
 * IntersectionObserver is recomputed by the browser as the layout changes, so it
 * cannot go stale for the same reason.
 */
export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('reveal', {
    mounted(el, binding) {
      if (import.meta.server) return

      // Nothing to restore if we never hide anything — content ships visible and
      // stays visible.
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return

      const isGroup = binding.arg === 'group'
      const targets = isGroup ? Array.from(el.children) : [el]
      if (targets.length === 0) return

      // Cards with hover transitions (Tailwind's `transition` utility) fight GSAP's
      // own inline opacity/transform writes — the CSS transition intercepts them and
      // the reveal never visibly progresses. Suspend the CSS transition for the
      // reveal, then hand it back once GSAP is done so hover effects still work.
      gsap.set(targets, { opacity: 0, y: 28, transition: 'none' })

      const observer = new IntersectionObserver(
        (entries) => {
          if (!entries.some((entry) => entry.isIntersecting)) return
          observer.disconnect()

          gsap.to(targets, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            ease: 'power2.out',
            stagger: isGroup ? 0.12 : 0,
            onComplete: () => gsap.set(targets, { clearProps: 'transition,transform,opacity' }),
          })
        },
        {
          /*
           * The bottom margin fires the reveal a little before the band is fully
           * on screen, so the motion reads as the section arriving rather than
           * as a late correction.
           *
           * The enormous top margin is a correctness fix, not a taste one. An
           * IntersectionObserver only reports *changes* in intersection, and a
           * scroll position can move by more than a viewport in a single frame —
           * which is exactly what happens when you leave the homepage, come back
           * via a client-side navigation, and the router restores your previous
           * scroll offset. Any section the page jumps straight over goes from
           * below the fold to above it without ever intersecting, so its reveal
           * never fires and its contents stay at `opacity: 0` permanently. (A
           * reload hides the bug because it always starts at the top, letting
           * every section enter the viewport normally.)
           *
           * Extending the root upward means "already scrolled past" also counts
           * as intersecting, so anything at or above the fold reveals at once.
           */
          rootMargin: '200000px 0px -8% 0px',
          threshold: 0,
        },
      )

      observer.observe(el)
      el._revealObserver = observer
    },

    unmounted(el) {
      el._revealObserver?.disconnect()
      delete el._revealObserver
    },
  })
})
