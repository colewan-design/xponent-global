export function apiBaseUrl() {
  const config = useRuntimeConfig()
  return `${config.public.apiBase}/api/v1`
}

/**
 * Wrapper around `useFetch` that points at the versioned API base.
 *
 * The explicit `key` is load-bearing, not a nicety. Nuxt generates an automatic
 * cache key for `useFetch` from the *call site* of the `useFetch` call itself —
 * and every request in this app funnels through the one call below. Without an
 * explicit key, every `useApiFetch(...)` on a page shares a single payload
 * entry and they overwrite each other: whichever request settles last wins, and
 * SSR and client-side navigation settle them in different orders. The symptom is
 * a page that renders one set of sections on a hard reload and a different set
 * after navigating in from a link.
 *
 * Keying on the path plus its params keeps distinct requests distinct while
 * still letting genuinely identical ones dedupe (`/settings` is read by the
 * header, footer and Schedule-a-Visit on every page, and should be fetched once).
 */
export function useApiFetch(path, options = {}) {
  const { key, ...rest } = options
  const route = useRoute()
  const routeKey = route?.path ?? 'global'
  const requestKey = JSON.stringify({
    path,
    params: options.params ?? {},
    query: options.query ?? {},
  })

  return useFetch(path, {
    baseURL: apiBaseUrl(),
    key: key ?? `api:${routeKey}:${requestKey}`,
    ...rest,
  })
}

export function apiPost(path, body) {
  return $fetch(path, {
    baseURL: apiBaseUrl(),
    method: 'POST',
    body,
  })
}
