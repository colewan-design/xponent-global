<script setup>
/**
 * The offices on a real map, replacing the flat picture the panel used to show.
 *
 * Leaflet with CARTO's Positron basemap: keyless, and its near-monochrome
 * palette is the closest tile set to the black-and-white artwork it succeeds,
 * so the panel reads the same as before at a glance.
 *
 * Leaflet touches `window` at import time, so it is imported dynamically inside
 * `onMounted` and the whole component is rendered under `<ClientOnly>` by its
 * parent. The office list below the map is server-rendered independently, which
 * is what matters for SEO and for anyone the map never loads for.
 *
 * Pins are `divIcon`s rather than Leaflet's default marker: the packaged icon
 * resolves its PNGs by URL relative to the stylesheet, which breaks under a
 * hashed asset build, and an inline SVG takes the site's gold instead.
 */
const props = defineProps({
  locations: { type: Array, default: () => [] },
})

const mapEl = ref(null)
/** Non-reactive: Leaflet mutates its own instance, and a proxy round-trip breaks it. */
let map = null
let resizeObserver = null

const plottable = computed(() =>
  props.locations.filter((l) => Number.isFinite(l.latitude) && Number.isFinite(l.longitude)),
)

function pinIcon(L) {
  return L.divIcon({
    className: 'office-pin',
    // 28x38 teardrop, gold fill with a dark rim so it holds up over pale tiles.
    html: `<svg width="28" height="38" viewBox="0 0 28 38" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
             <path d="M14 37C14 37 26 22.5 26 14A12 12 0 1 0 2 14c0 8.5 12 23 12 23Z"
                   fill="#c4a24a" stroke="#12181c" stroke-width="2" stroke-linejoin="round"/>
             <circle cx="14" cy="14" r="4.5" fill="#12181c"/>
           </svg>`,
    iconSize: [28, 38],
    iconAnchor: [14, 38],
    popupAnchor: [0, -34],
  })
}

function popupHtml(location) {
  const address = [location.address, location.city, location.country].filter(Boolean).join(', ')
  const escape = (value) =>
    String(value ?? '').replace(
      /[&<>"']/g,
      (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c],
    )
  return `<strong class="office-pop-title">${escape(location.label)}</strong>
          <span class="office-pop-address">${escape(address)}</span>`
}

onMounted(async () => {
  if (!mapEl.value || plottable.value.length === 0) return

  const L = (await import('leaflet')).default
  await import('leaflet/dist/leaflet.css')

  map = L.map(mapEl.value, {
    scrollWheelZoom: false, // a map mid-page must not hijack the scroll
    attributionControl: true,
  })

  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19,
  }).addTo(map)

  const icon = pinIcon(L)
  for (const location of plottable.value) {
    L.marker([location.latitude, location.longitude], { icon, title: location.label })
      .addTo(map)
      .bindPopup(popupHtml(location))
  }

  // One office would fit to a meaningless world view, so fall back to a set zoom.
  const bounds = L.latLngBounds(plottable.value.map((l) => [l.latitude, l.longitude]))
  if (plottable.value.length === 1) map.setView(bounds.getCenter(), 11)
  else map.fitBounds(bounds, { padding: [48, 48] })

  // The panel is inside a responsive grid; Leaflet needs telling when it resizes.
  resizeObserver = new ResizeObserver(() => map?.invalidateSize())
  resizeObserver.observe(mapEl.value)
})

onBeforeUnmount(() => {
  resizeObserver?.disconnect()
  resizeObserver = null
  map?.remove()
  map = null
})
</script>

<template>
  <div
    ref="mapEl"
    class="office-map h-80 w-full sm:h-105 lg:h-120"
    role="application"
    aria-label="Map of Xponent Global office and warehouse locations"
  />
</template>

<style>
/* Unscoped: Leaflet builds its panes and popups outside this component's tree,
   so scoped styles would never reach them. */
.office-map {
  background: #eceff1;
}

.office-map .leaflet-container {
  font-family: inherit;
}

.office-pin {
  background: none;
  border: 0;
}

.office-map .leaflet-popup-content-wrapper {
  border-radius: 0;
  box-shadow: 0 2px 12px rgb(0 0 0 / 18%);
}

.office-map .leaflet-popup-content {
  margin: 0.75rem 0.9rem;
  font-size: 0.84rem;
  line-height: 1.5;
}

.office-pop-title {
  display: block;
  font-weight: 700;
  color: #12181c;
}

.office-pop-address {
  display: block;
  margin-top: 0.15rem;
  color: rgb(18 24 28 / 65%);
}

.office-map .leaflet-control-attribution {
  font-size: 0.65rem;
}
</style>
