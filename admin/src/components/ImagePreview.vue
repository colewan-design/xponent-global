<script setup>
/**
 * Thumbnail for a stored image path.
 *
 * Renders three states rather than just succeeding or vanishing: empty (no path
 * typed yet), loaded, and failed. The failed state matters here — page-content
 * images are typed as free-text paths, so a typo is the common case and a
 * silently blank box would read the same as "no image set".
 */
import { computed, ref, watch } from 'vue'
import { storageUrl } from '../lib/storageUrl'
import AppIcon from './AppIcon.vue'

const props = defineProps({
  path: { type: String, default: '' },
})

const url = computed(() => storageUrl(props.path))
const failed = ref(false)

// A new path deserves a fresh attempt — without this the error state sticks
// after the user corrects a typo.
watch(url, () => {
  failed.value = false
})
</script>

<template>
  <div v-if="url" class="image-preview">
    <a v-if="!failed" :href="url" target="_blank" rel="noopener" class="image-preview-thumb">
      <img :src="url" alt="" @error="failed = true" />
    </a>
    <div v-else class="image-preview-thumb image-preview-thumb--failed">
      <AppIcon name="image" :size="18" />
    </div>

    <div class="image-preview-meta">
      <span :class="failed ? 'image-preview-status--failed' : 'image-preview-status'">
        {{ failed ? 'Image not found at this path' : 'Preview' }}
      </span>
      <span class="image-preview-url">{{ url }}</span>
    </div>
  </div>
</template>
