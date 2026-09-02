/**
 * Dynamic sitemap entries for published posts.
 *
 * `@nuxtjs/sitemap` discovers the static pages under `app/pages/` on its own,
 * but `/news/[slug]` is a parameterised route — the module cannot know which
 * slugs exist, so before this source the sitemap listed 12 static URLs and not
 * one article. This endpoint supplies the missing half; it is wired in via
 * `sitemap.sources` in `nuxt.config.ts`.
 *
 * Two things worth keeping in mind if you edit this:
 *
 * 1. `/posts` is paginated (9 per page). Fetching only the first page would
 *    quietly cap the sitemap at 9 articles the moment a tenth is published —
 *    the kind of bug nobody notices, because the sitemap keeps working. So we
 *    follow `meta.last_page` to the end.
 *
 * 2. A sitemap is not worth a 500. If the API is unreachable the sitemap should
 *    degrade to its static routes rather than take the page down with it, so
 *    failures are logged and swallowed.
 */
export default defineEventHandler(async () => {
  const config = useRuntimeConfig()
  const baseURL = `${config.public.apiBase}/api/v1`

  // A published post with no `published_at` gets no <lastmod> rather than a
  // fabricated one — an invented date is worse than an absent one to a crawler.
  const toEntry = (post) => ({
    loc: `/news/${post.slug}`,
    ...(post.published_at ? { lastmod: post.published_at } : {}),
  })

  try {
    const entries = []
    let page = 1
    let lastPage = 1

    do {
      const response = await $fetch('/posts', { baseURL, query: { page } })
      const posts = response?.data ?? []

      entries.push(...posts.filter((post) => post?.slug).map(toEntry))

      lastPage = Number(response?.meta?.last_page) || 1
      page += 1
    } while (page <= lastPage)

    return entries
  } catch (error) {
    console.error('[sitemap] could not load posts, falling back to static routes only:', error?.message ?? error)

    return []
  }
})
