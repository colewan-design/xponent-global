/**
 * Sync CMS artwork from the backend's storage disk into `public/cms/`, resizing
 * and re-encoding on the way through.
 *
 * Two problems, one pass:
 *
 * 1. `public/cms/` is a copy of the backend's storage disk, so it goes stale the
 *    moment someone uploads through the admin. Running this before every build
 *    (see the `pre*` hooks in package.json) keeps the bundled copy current;
 *    CmsImage's fallback to the API URL covers the window in between.
 *
 * 2. The originals are camera files — several are over 4 MB, and `nuxt.config`
 *    disables @nuxt/image's resizing because the site deploys to static hosting
 *    with no server to run IPX. Resizing here, at build time, is the one place
 *    it can happen without a runtime image service.
 *
 * Incremental: a destination file whose mtime matches its source is left alone,
 * so repeat builds do no work. Destinations with no surviving source are kept
 * and reported; set CMS_IMAGE_PRUNE=1 from a fully seeded checkout to clear
 * artwork deleted in the admin out of the bundle. See the prune block below for
 * why that direction is opt-in.
 */
import { Buffer } from 'node:buffer'
import { existsSync } from 'node:fs'
import { mkdir, readdir, readFile, stat, unlink, utimes, writeFile } from 'node:fs/promises'
import path from 'node:path'
import process from 'node:process'
import { fileURLToPath } from 'node:url'
import sharp from 'sharp'

const here = path.dirname(fileURLToPath(import.meta.url))

// Overridable so a build machine that keeps the two apps somewhere other than
// this repo layout can still point at the real storage disk.
const SOURCE_DIR =
  process.env.CMS_IMAGE_SOURCE ?? path.resolve(here, '../../backend/storage/app/public/seed')
const DEST_DIR = path.resolve(here, '../public/cms/seed')

// Wide enough for the full-bleed page heroes on a 2x laptop display; every other
// use on the site is smaller. Portrait shots are bounded on height by the same
// number so a tall image can't slip through at many times the file size.
const MAX_EDGE = 1920
const JPEG_QUALITY = 80

const IMAGE_EXTENSIONS = new Set(['.jpg', '.jpeg', '.png', '.webp'])

function formatBytes(bytes) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

/**
 * Re-encode one image, returning the bytes to write.
 *
 * Returns the untouched original when processing would make the file bigger —
 * true of the client logos, which are already tuned exports where a re-encode
 * only adds generation loss and weight.
 */
async function optimise(sourceBytes, extension) {
  // `.rotate()` with no angle applies the EXIF orientation and drops the flag.
  //
  // Without it, a phone photo carrying `Orientation=6` is re-encoded from its raw
  // pixel buffer and the flag is stripped on the way out — so a picture the
  // browser used to turn upright ships permanently on its side. Seven of the
  // bundled images were rotated this way, two of them case-study covers.
  const image = sharp(sourceBytes, { failOn: 'none' }).rotate()

  // `metadata()` describes the *stored* buffer and is unaffected by anything
  // queued on the pipeline, so the stored dimensions have to be swapped by hand
  // for the orientations that turn the image a quarter turn (5–8). Testing the
  // unswapped axis would resize a portrait photo against its long edge.
  const meta = await sharp(sourceBytes, { failOn: 'none' }).metadata()
  const quarterTurned = meta.orientation >= 5 && meta.orientation <= 8
  const width = quarterTurned ? meta.height : meta.width
  const height = quarterTurned ? meta.width : meta.height

  const oversized = (width ?? 0) > MAX_EDGE || (height ?? 0) > MAX_EDGE
  if (oversized) {
    image.resize(MAX_EDGE, MAX_EDGE, { fit: 'inside', withoutEnlargement: true })
  }

  if (extension === '.png') {
    // PNG here means a logo with transparency to preserve, so it stays PNG
    // rather than being flattened onto a guessed background colour.
    image.png({ compressionLevel: 9, palette: true })
  } else if (extension === '.webp') {
    image.webp({ quality: JPEG_QUALITY })
  } else {
    image.jpeg({ quality: JPEG_QUALITY, progressive: true, mozjpeg: true })
  }

  const output = await image.toBuffer()
  return output.byteLength < sourceBytes.byteLength ? output : sourceBytes
}

async function main() {
  if (!existsSync(SOURCE_DIR)) {
    // A frontend-only build (CI without the backend checked out) is legitimate:
    // whatever is already committed under public/cms/ ships as-is. Warn, don't
    // fail — this must never be the reason a deploy breaks.
    console.warn(`[cms-images] source not found, keeping existing copies: ${SOURCE_DIR}`)
    return
  }

  await mkdir(DEST_DIR, { recursive: true })

  const entries = await readdir(SOURCE_DIR, { withFileTypes: true })
  const images = entries
    .filter((entry) => entry.isFile() && IMAGE_EXTENSIONS.has(path.extname(entry.name).toLowerCase()))
    .map((entry) => entry.name)

  // An empty source is not an instruction to empty the bundle. The tracked copy
  // under public/cms/ is the only one in the repo — the originals live under
  // backend/storage/app/public/, which Laravel's .gitignore excludes — so a
  // checkout whose storage disk has not been seeded yet would otherwise prune
  // every image the site ships with.
  if (images.length === 0) {
    console.warn(`[cms-images] no images at source, keeping existing copies: ${SOURCE_DIR}`)
    return
  }

  let copied = 0
  let skipped = 0
  let sourceTotal = 0
  let destTotal = 0

  for (const name of images) {
    const sourcePath = path.join(SOURCE_DIR, name)
    const destPath = path.join(DEST_DIR, name)
    const sourceStat = await stat(sourcePath)
    sourceTotal += sourceStat.size

    // mtime is carried across on write, so an unchanged source matches its
    // destination and costs one stat instead of a decode.
    //
    // Compared with a one-second tolerance rather than for equality: `utimes`
    // takes its times as floating-point seconds, so the sub-millisecond
    // precision NTFS records does not survive the round trip and an exact
    // comparison never matches — which silently reprocesses all 63 files on
    // every build.
    if (existsSync(destPath)) {
      const destStat = await stat(destPath)
      if (Math.abs(destStat.mtimeMs - sourceStat.mtimeMs) < 1000) {
        skipped += 1
        destTotal += destStat.size
        continue
      }
    }

    const sourceBytes = await readFile(sourcePath)
    let outputBytes

    try {
      outputBytes = await optimise(sourceBytes, path.extname(name).toLowerCase())
    } catch (error) {
      // A file sharp cannot decode still belongs in the bundle — the site
      // referencing it is a content problem, not a build problem.
      console.warn(`[cms-images] could not process ${name}, copying as-is: ${error.message}`)
      outputBytes = sourceBytes
    }

    await writeFile(destPath, outputBytes)
    await utimes(destPath, sourceStat.atime, sourceStat.mtime)

    destTotal += outputBytes.byteLength
    copied += 1

    if (outputBytes !== sourceBytes) {
      const saved = sourceStat.size - outputBytes.byteLength
      const percent = Math.round((saved / sourceStat.size) * 100)
      console.log(
        `[cms-images] ${name}  ${formatBytes(sourceStat.size)} -> ${formatBytes(outputBytes.byteLength)}  (-${percent}%)`,
      )
    }
  }

  // Prune anything the backend no longer has, so deleted artwork does not live
  // on in the deployed bundle.
  //
  // Opt-in, because the source disk is only authoritative on a machine that has
  // been fully seeded. Laravel's .gitignore excludes backend/storage/app/public/,
  // so an ordinary checkout holds whatever subset that developer happened to
  // seed — and pruning against a partial source deletes the tracked copies under
  // public/cms/, which are the only ones in the repo.
  //
  // That is not hypothetical: a build from a checkout with 6 of the 69 images
  // pruned 54 of the site's 58 references, and every one of them 404'd in
  // production until they were restored from git. The empty-source guard above
  // is the same reasoning — a partial source is no more trustworthy than an
  // empty one, it just fails less visibly.
  const keep = new Set(images)
  let pruned = 0
  const orphans = (await readdir(DEST_DIR)).filter((name) => !keep.has(name))

  if (orphans.length > 0 && process.env.CMS_IMAGE_PRUNE === '1') {
    for (const name of orphans) {
      await unlink(path.join(DEST_DIR, name))
      pruned += 1
    }
  } else if (orphans.length > 0) {
    console.warn(
      `[cms-images] keeping ${orphans.length} bundled image(s) with no source at ${SOURCE_DIR}. ` +
        `Run with CMS_IMAGE_PRUNE=1 from a fully seeded checkout to remove them.`,
    )
  }

  console.log(
    `[cms-images] ${copied} processed, ${skipped} unchanged, ${pruned} pruned — ` +
      `${formatBytes(sourceTotal)} -> ${formatBytes(destTotal)}`,
  )
}

await main()
