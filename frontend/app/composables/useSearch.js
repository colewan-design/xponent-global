/**
 * Site search: the query runner, and the one place that turns an API result
 * into a URL.
 *
 * `/api/v1/search` deliberately returns identifiers rather than links — the site
 * owns its own routing — so the mapping below is the seam between the two. Both
 * the header's results panel and /search read it, because a result that leads
 * somewhere different depending on which of them rendered it is a bug waiting to
 * be filed.
 */

/**
 * Ranges that live on their own page instead of as an anchor on the index.
 *
 * `pages/solutions/index.vue` filters PPE out of the listing and links to it
 * separately, so `/solutions#personal-protection-equipment` is an anchor that
 * does not exist. Anything else added to that pattern belongs here too.
 */
const standaloneSolutionPages = {
  'personal-protection-equipment': '/solutions/personal-protection-equipment',
}

export function searchResultLink(type, result) {
  switch (type) {
    case 'solution':
      if (!result.slug) return '/solutions'
      return standaloneSolutionPages[result.slug] ?? `/solutions#${result.slug}`

    // A product's `slug` is its *category* slug: the catalogue renders one
    // section per category and products have no page of their own, so the
    // category band is the closest address a SKU has.
    case 'product':
      return result.slug ? `/products#${result.slug}` : '/products'

    case 'article':
      return result.slug ? `/news/${result.slug}` : '/media/newsletter'

    // Resources are rows in a list with no individual URL — the download links
    // are on the index itself.
    case 'resource':
      return '/media/resources'

    default:
      return '/'
  }
}

/**
 * Runs a query against the API.
 *
 * Plain `$fetch` rather than `useApiFetch`: this is called from an input
 * handler, not during render, so there is no SSR payload to hydrate and no
 * cache key worth minting. Returns the empty shape on failure so a dropped
 * connection reads as "no results" rather than throwing inside a keystroke
 * handler.
 */
export async function runSearch(query, options = {}) {
  const term = String(query ?? '').trim()

  if (term.length < 2) {
    return { query: term, total: 0, groups: [] }
  }

  try {
    const response = await $fetch('/search', {
      baseURL: apiBaseUrl(),
      params: { q: term },
      signal: options.signal,
    })

    return response?.data ?? { query: term, total: 0, groups: [] }
  } catch (error) {
    // An aborted request is the expected outcome of typing the next character,
    // not a failure worth surfacing.
    if (error?.name === 'AbortError') throw error

    return { query: term, total: 0, groups: [], failed: true }
  }
}
