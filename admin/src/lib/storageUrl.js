const base = import.meta.env.VITE_API_URL ?? 'http://localhost:8010'

/**
 * Resolves a stored file path to a browsable URL.
 *
 * The admin's page-content endpoint returns *raw* paths on purpose (e.g.
 * "seed/IMG_2919.jpg") so that edits round-trip without the client having to
 * strip a host back off before saving — only the public API resolves them. That
 * leaves the admin with no URL to preview, which this fills in.
 *
 * It mirrors the server's `FileUrl::resolve()`, which is
 * `Storage::disk('public')->url($path)` → `{APP_URL}/storage/{path}`. The
 * admin's VITE_API_URL is the same origin as the API's APP_URL in both local
 * and production configs, so the two agree.
 */
export function storageUrl(path) {
  if (!path) return null

  const trimmed = String(path).trim()
  if (!trimmed) return null

  // Already a full URL, a protocol-relative URL, or a root-absolute path —
  // leave it alone rather than nesting it under /storage.
  if (/^(https?:)?\/\//i.test(trimmed) || trimmed.startsWith('/')) {
    return trimmed
  }

  return `${base.replace(/\/$/, '')}/storage/${trimmed.replace(/^\/+/, '')}`
}
