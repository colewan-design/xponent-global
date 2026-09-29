/**
 * Optimise the hand-committed artwork under `public/` — in place.
 *
 * `sync-cms-images.mjs` resizes the artwork that comes from the backend's
 * storage disk, but only that: it reads `backend/storage/app/public/seed` and
 * writes `public/cms/seed`. Everything else under `public/` is dropped in by
 * hand and ships at whatever size it was exported at, which is how the site
 * came to serve a 4032x2268 phone photo as its hero poster (3.2 MB, on the
 * critical path of nearly every page) and a 3963x1322 logo displayed at 48
 * pixels tall (1.9 MB, eager, in the header and footer of every page).
 *
 * Deliberately NOT wired into the build. It rewrites tracked files, and a
 * prebuild hook that quietly mutates the repo is what made `sync-cms-images`
 * delete 54 images once already. Run it by hand after adding artwork:
 *
 *   npm run optimise:static
 *
 * Idempotent: an image already within bounds is re-encoded only if that makes
 * it smaller, and left alone otherwise, so repeat runs converge and stop.
 */
import { existsSync } from 'node:fs'
import { readdir, readFile, stat, writeFile } from 'node:fs/promises'
import path from 'node:path'
import process from 'node:process'
import { fileURLToPath } from 'node:url'
import sharp from 'sharp'

const here = path.dirname(fileURLToPath(import.meta.url))
const PUBLIC_DIR = path.resolve(here, '../public')

// Matches sync-cms-images: wide enough for a full-bleed hero on a 2x laptop
// display, and every other use on the site is smaller.
const MAX_EDGE = 1920
const JPEG_QUALITY = 80

// An already-correct image must not be rewritten for a rounding-error saving —
// see the note in `optimise`. Only a re-encode that wins at least this much
// justifies the generation loss it costs.
const MIN_SAVING = 0.05

// The logo is a 3:1 lockup rendered at h-9/h-10 in the header (cropped to the
// mark) and h-12 in the footer — 48 CSS pixels tall at the largest. 800px wide
// is ~267 tall, still over 5x the tallest use, which covers any display density
// without carrying a print-resolution master into every page load.
const LOGO_MAX_WIDTH = 800

// `public/cms/seed` is sync-cms-images' output and is managed there; the fonts
// and videos are not images. Everything else under public/ is fair game.
const SKIP_DIRS = new Set(['cms', 'fonts', 'videos', '_fonts', '_nuxt'])

const IMAGE_EXTENSIONS = new Set(['.jpg', '.jpeg', '.png', '.webp'])

function formatBytes(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

async function* walk(dir) {
  for (const entry of await readdir(dir, { withFileTypes: true })) {
    if (entry.isDirectory()) {
      if (SKIP_DIRS.has(entry.name)) continue
      yield* walk(path.join(dir, entry.name))
    } else if (entry.isFile() && IMAGE_EXTENSIONS.has(path.extname(entry.name).toLowerCase())) {
      yield path.join(dir, entry.name)
    }
  }
}

/**
 * Re-encode one image, returning the bytes to write.
 *
 * Returns the original when processing would not make it smaller — true of
 * assets that are already tuned exports, where a re-encode only costs
 * generation loss.
 */
async function optimise(sourceBytes, extension, maxWidth, maxHeight) {
  // See the same call in sync-cms-images.mjs: `.rotate()` applies the EXIF
  // orientation and clears the flag, so a re-encode cannot leave a phone photo
  // permanently on its side. `metadata()` still reports the stored buffer, so
  // the quarter-turn orientations need their axes swapped by hand.
  const image = sharp(sourceBytes, { failOn: 'none' }).rotate()

  const meta = await sharp(sourceBytes, { failOn: 'none' }).metadata()
  const quarterTurned = meta.orientation >= 5 && meta.orientation <= 8
  const width = quarterTurned ? meta.height : meta.width
  const height = quarterTurned ? meta.width : meta.height

  // A null bound means "unconstrained on this axis" — sharp's own convention,
  // and it rejects Infinity outright.
  const tooWide = (width ?? 0) > maxWidth
  const tooTall = maxHeight !== null && (height ?? 0) > maxHeight
  if (tooWide || tooTall) {
    image.resize(maxWidth, maxHeight, { fit: 'inside', withoutEnlargement: true })
  }

  if (extension === '.png') {
    // Transparency to preserve, so it stays PNG rather than being flattened
    // onto a guessed background colour.
    image.png({ compressionLevel: 9 })
  } else if (extension === '.webp') {
    image.webp({ quality: JPEG_QUALITY })
  } else {
    image.jpeg({ quality: JPEG_QUALITY, progressive: true, mozjpeg: true })
  }

  const output = await image.toBuffer()

  // Re-encoding a lossy source at a fixed quality almost always yields a few
  // bytes less, so "smaller" alone would rewrite every JPEG and WebP on every
  // run and stack generation loss each time. Keep the new bytes only when the
  // image actually needed resizing, or when the saving is big enough to be
  // worth one re-encode. Anything else converges and stops.
  const resized = tooWide || tooTall
  const saving = 1 - output.byteLength / sourceBytes.byteLength
  const worthIt = resized ? output.byteLength < sourceBytes.byteLength : saving >= MIN_SAVING

  return worthIt ? output : sourceBytes
}

async function main() {
  if (!existsSync(PUBLIC_DIR)) {
    console.error(`[static-images] no public directory at ${PUBLIC_DIR}`)
    process.exitCode = 1
    return
  }

  let changed = 0
  let untouched = 0
  let before = 0
  let after = 0

  for await (const file of walk(PUBLIC_DIR)) {
    const relative = path.relative(PUBLIC_DIR, file).split(path.sep).join('/')
    const isLogo = relative === 'logo.png'
    const maxWidth = isLogo ? LOGO_MAX_WIDTH : MAX_EDGE
    // The logo is bounded on width alone — it is a wide lockup, and bounding a
    // 3:1 image on height as well would leave it far larger than it needs to be.
    const maxHeight = isLogo ? null : MAX_EDGE

    const sourceBytes = await readFile(file)
    const sourceSize = (await stat(file)).size
    before += sourceSize

    let outputBytes
    try {
      outputBytes = await optimise(sourceBytes, path.extname(file).toLowerCase(), maxWidth, maxHeight)
    } catch (error) {
      console.warn(`[static-images] could not process ${relative}, leaving as-is: ${error.message}`)
      after += sourceSize
      untouched += 1
      continue
    }

    if (outputBytes === sourceBytes) {
      after += sourceSize
      untouched += 1
      continue
    }

    await writeFile(file, outputBytes)
    after += outputBytes.byteLength
    changed += 1

    const percent = Math.round(((sourceSize - outputBytes.byteLength) / sourceSize) * 100)
    console.log(
      `[static-images] ${relative}  ${formatBytes(sourceSize)} -> ${formatBytes(outputBytes.byteLength)}  (-${percent}%)`,
    )
  }

  console.log(
    `[static-images] ${changed} optimised, ${untouched} already minimal — ` +
      `${formatBytes(before)} -> ${formatBytes(after)}`,
  )
}

await main()
