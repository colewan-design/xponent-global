/**
 * Cookie consent — one choice, stored in a first-party cookie.
 *
 * The choice lives in `cookie_consent` so it survives sessions and is readable
 * during SSR: the banner therefore never flashes for a returning visitor, and
 * the server-rendered HTML already matches what the client will show.
 *
 * The reactive value is held in `useState` rather than in the `useCookie` ref
 * itself. Two `useCookie()` calls for the same name return independent refs, so
 * a write from the banner would not wake anything else watching the cookie —
 * the shared state key is what links them.
 *
 * `analyticsAllowed` is the flag any future measurement script must read before
 * it loads. Nothing on this site sets a non-essential cookie today, so it has no
 * consumer yet; it exists so that when one is added it is gated from the start
 * rather than bolted on afterwards.
 */
const CONSENT_COOKIE = 'cookie_consent'
const CONSENT_STATE = 'cookie-consent'
const ONE_YEAR = 60 * 60 * 24 * 365

export function useCookieConsent() {
  const stored = useCookie(CONSENT_COOKIE, {
    maxAge: ONE_YEAR,
    sameSite: 'lax',
    path: '/',
  })

  const choice = useState(CONSENT_STATE, () => stored.value ?? null)

  const hasChosen = computed(() => choice.value === 'accepted' || choice.value === 'rejected')
  const analyticsAllowed = computed(() => choice.value === 'accepted')

  function set(value) {
    choice.value = value
    stored.value = value
  }

  /** Reopens the banner. The stored choice is cleared, so nothing is assumed. */
  function reset() {
    choice.value = null
    stored.value = null
  }

  return {
    choice,
    hasChosen,
    analyticsAllowed,
    accept: () => set('accepted'),
    reject: () => set('rejected'),
    reset,
  }
}
