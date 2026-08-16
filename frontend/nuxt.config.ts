import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  modules: ['@nuxt/image', '@nuxtjs/sitemap', '@vueuse/nuxt', '@nuxt/fonts'],

  css: ['~/assets/css/main.css'],

  // Poppins carries the whole retail layout; IBM Plex Mono is used only for the
  // page-hero eyebrows and the few spec-sheet labels that survive from the
  // catalogue language.
  fonts: {
    families: [
      { name: 'Poppins', provider: 'google', weights: [300, 400, 500, 600, 700, 800] },
      { name: 'IBM Plex Mono', provider: 'google', weights: [400, 500, 700] },
    ],
  },

  vite: {
    plugins: [tailwindcss()],
  },

  app: {
    head: {
      titleTemplate: '%s · Xponent Global',
      htmlAttrs: { lang: 'en' },
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
        { rel: 'manifest', href: '/site.webmanifest' },
      ],
      meta: [{ name: 'theme-color', content: '#0a0c0d' }],
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8010',
    },
  },

  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'http://localhost:3010',
  },

  // Static hosting has no server to run IPX's on-demand image resizing, so
  // skip it entirely and let NuxtImg render plain <img> tags with the
  // original src (also sidesteps IPX's build-time prerendering, which
  // errors on Windows for remote https:// URLs — colons aren't valid in
  // Windows paths).
  image: {
    provider: 'none',
  },

  // 3010, not Nuxt's default 3000, to avoid colliding with other local Nuxt
  // projects on the same machine.
  devServer: {
    port: 3010,
  },
})
