<script setup>
/**
 * CMS artwork, served from the frontend's own bundle rather than the API host.
 *
 * The API hands back absolute URLs into the backend's storage disk — in
 * production `https://exponent-global-api.salidumay.com/storage/seed/x.jpg`.
 * Those same files are copied into `public/cms/`, so this component rewrites the
 * URL to the local copy and the site keeps its imagery when the API host is
 * slow, unreachable, or on the far side of a cold start.
 *
 * Artwork uploaded through the admin *after* the last frontend build has no
 * local copy yet, so a failed local load falls back to the original API URL
 * once. New content still appears; it just costs one extra request until the
 * next build re-syncs `public/cms/`.
 */
const props = defineProps({
  src: { type: String, default: '' },
})

// Everything the backend serves publicly sits under this path, whatever the
// host in front of it, so matching on the path alone keeps dev (localhost:8010)
// and production (the api subdomain) on the same code path.
const STORAGE_MARKER = '/storage/'

const localSrc = computed(() => {
  if (!props.src) return ''
  const at = props.src.indexOf(STORAGE_MARKER)
  if (at === -1) return props.src
  return `/cms/${props.src.slice(at + STORAGE_MARKER.length)}`
})

// Tracked separately from the computed so the error handler can swap in the
// remote URL without fighting it. Re-keyed on `src` so a prop change (the
// gallery lightbox stepping between plates) starts again from the local copy
// instead of inheriting the previous image's fallback.
const resolved = ref(localSrc.value)

watch(localSrc, (next) => {
  resolved.value = next
})

function onLocalMiss() {
  if (resolved.value !== props.src) resolved.value = props.src
}
</script>

<template>
  <img v-if="resolved" :src="resolved" @error="onLocalMiss" />
</template>
