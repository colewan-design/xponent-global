<script setup>
/**
 * Canonical URL and og:url for every page on the site.
 *
 * `xponent-global.com` and `www.xponent-global.com` are separate origins as far
 * as a search engine is concerned, so once both resolve here the same page can
 * be indexed twice and its ranking split between the two. A canonical link names
 * one true URL per page and collapses them back into one document.
 *
 * The origin comes from `useSiteConfig()` — the same value `@nuxtjs/sitemap`
 * builds <loc> entries from (`site.url` in nuxt.config, supplied per environment
 * as NUXT_PUBLIC_SITE_URL). Reading both from one source means the canonical
 * tags and the sitemap cannot disagree about which hostname is the real one.
 *
 * Declared here rather than per page so a new page cannot be added without one.
 * `route.path` excludes the query string on purpose: `/solutions?page=2` is the
 * same document as `/solutions` for indexing, and pointing every variant at the
 * bare path is the behaviour we want.
 */
const route = useRoute()
const siteConfig = useSiteConfig()

const canonicalUrl = computed(() => {
  // site.url carries a trailing slash in some configurations and not others;
  // route.path always opens with one, so trim to avoid emitting a double slash.
  const origin = (siteConfig.url ?? '').replace(/\/+$/, '')
  return `${origin}${route.path}`
})

useHead({
  link: [{ rel: 'canonical', href: canonicalUrl }],
})

// What Facebook, LinkedIn and Slack read when someone shares a link. Left
// unset, they fall back to whichever URL the sharer happened to copy.
useSeoMeta({
  ogUrl: canonicalUrl,
})
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
    <CookieConsent />
  </div>
</template>
