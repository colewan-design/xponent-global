# Completion Report — Audit Remediation

**Date:** 2026-09-02
**Baseline:** `86a7525` (working tree clean, level with `origin/main` at start)
**Scope:** `backend/`, `frontend/`, `admin/`, and the xponent-global deployment on the VPS

Follows the system-wide audit of the same date, which raised eleven findings (F1–F11).
**Eight are fixed and verified in production. Three remain**, none of them defects I can close
without input.

Every "verified" claim below was confirmed against production — a live probe, a restore, or a
measured response — not inferred from source.

---

## Summary

| # | Finding | Severity | State |
|---|---------|----------|-------|
| F1 | Notification mail silently discarded | Critical | ✅ Fixed |
| F2 | Admin login accepted unlimited attempts | Critical | ✅ Fixed |
| F3 | No backups of database or uploads | Critical | ✅ Fixed on-box — **offsite open** |
| F4 | Public downloads are placeholder text stubs | Medium | ⏸ Blocked on content |
| F5 | Commerce module built but inert | Medium | ⏸ Product decision |
| F6 | 12 PHP dependency advisories | Medium | ✅ Fixed |
| F7 | 24 npm advisories (frontend + admin) | Medium | ✅ Fixed |
| F8 | No CI/CD or deploy safety | Medium | ✅ Deploy script — **full CI open** |
| F9 | Sitemap listed no article pages | Medium | ✅ Fixed |
| F10 | Mail sent inside the request | Low | ✅ Fixed |
| F11 | Post/job bodies are plain textareas | Low | ⏸ Dependency decision |

Backend test suite: **45 → 48 passing** (180 assertions).

---

## What was fixed

### F1 — Notification mail was being discarded, not just undelivered

Production ran `MAIL_MAILER=log` with `LOG_LEVEL=warning`. Laravel's log mailer writes at *debug*
level, which a `warning` threshold filters out — so notifications were neither emailed nor logged.
Three real submissions (2 enquiries, 1 job application, latest 2026-08-16) had produced no
notification of any kind.

**Fixed:** SMTP against the domain's own mailbox on the HostGator/Exim host.

```
MAIL_MAILER=smtp        MAIL_HOST=mail.xponent-global.com
MAIL_SCHEME=smtps       MAIL_PORT=465
MAIL_USERNAME="support@xponent-global.com"
MAIL_FROM_ADDRESS="support@xponent-global.com"
ADMIN_NOTIFICATION_EMAIL="support@xponent-global.com"
```

**A second defect surfaced while fixing this.** The notification recipient was
`admin@xponent-global.com`, and that mailbox **does not exist** — the host answers
`550 No Such User Here`. Fixing only the mailer would have swapped silent discards for silent
bounces. Probed all three addresses: `admin@` rejects, `support@` and `info@` accept.

**Also worth knowing:** the password begins with `#`, which dotenv treats as a comment. Unquoted it
parses as an *empty* password and auth fails for no visible reason. It is quoted, and Laravel was
verified to read it back as 12 characters with the `#` intact.

**Verified:** real submission through nginx → php-fpm → SMTP, `HTTP 201`, mail delivered, no errors
logged. Test record removed afterwards.

### F2 — Admin login accepted unlimited password attempts

`POST /api/v1/auth/login` sat outside the `throttle:10,1` group protecting the other public
endpoints. Laravel 11+ ships no default throttle on the `api` group, and `AuthController` did no
manual limiting.

```
before   422 ×12          12 attempts, 0 blocked
after    422 ×5, 429 ×4   blocked at 5, Retry-After: 59
```

**Fixed:** `throttle:5,1` on the login route. Two regression tests added — one asserting the sixth
attempt is rejected, one asserting a person who mistypes twice can still log in, because a rate
limit that locks out its own admin is not a fix.

### F3 — No backups existed (on-box half fixed)

Root crontab empty, no backup timers, no dump scripts. MariaDB was the only copy of all enquiries,
applications, subscribers and CMS content.

**Fixed:** `xponent-backup.timer`, nightly at 02:20 UTC.

Each run captures three things:
- the database (`mysqldump --single-transaction`, gzipped — 20K)
- `storage/app` (56M) — uploaded artwork and applicant CVs, which Laravel's own nested `.gitignore`
  excludes with a blanket `*`, so a fresh clone has none of them
- `.env` — restoring without `APP_KEY` leaves every encrypted value unreadable

Retention: 14 days for dumps and env, 7 for the uploads archive. Dumps are checked for gzip
integrity **and** the `Dump completed` marker, so a truncated dump fails loudly rather than sitting
there looking valid.

**Verified by restoring, not by checking a file exists.** The dump was loaded into a scratch
database and compared against live:

```
tables            live=30  restored=30
contact_enquiries live=2   restored=2      posts          live=5   restored=5
job_applications  live=1   restored=1      solution_items live=47  restored=47
newsletter_subs   live=3   restored=3      gallery_images live=16  restored=16
```

**Still open:** everything lives on the same VPS. That covers a dropped table or a bad deploy, not
losing the box. Offsite needs a destination.

### F6 — PHP dependency advisories

```
league/commonmark   2.8.3  -> 2.10.0    cleared 10 advisories (8 high, 2 medium)
guzzlehttp/guzzle   7.15.1 -> 7.15.2    cleared  2 advisories (1 high, 1 medium)
```

Both stayed inside their major versions, so no application change was needed. `composer audit` is
clean locally and on the server.

### F7 — npm advisories

```
frontend   10 vulnerabilities (7 high)  ->  1 low
admin       2 vulnerabilities (1 high)  ->  0

undici 8.8.0 -> 8.10.1 · nuxt 4.5.0 -> 4.5.2 · sharp 0.34.5 -> 0.35.4
postcss 8.5.22 -> 8.5.26 · nanoid -> 3.3.18
```

The remaining advisory is a low-severity esbuild file-read affecting the **development server** on
Windows, nested under `fontless`. It cannot be lifted without a breaking change upstream and has no
production reach.

This one took four attempts because three toolchain problems were stacked, each producing an error
that points somewhere else. Recorded in full in `frontend/README.md`; in short:

1. **npm 10.8.2 crashes** building an ideal tree for this project — arborist `#loadPeerSet`,
   `Cannot read properties of null (reading 'edgesOut')`. Blocks `install` / `update` / `audit fix`.
   **Not** `npm ci`, which is why clones and deploys were never affected.
   → worked around with `--legacy-peer-deps`, which skips the failing code path.
2. **`oxc-parser` is an *optional* peer** of `oxc-walker`, so any resolver may skip it — npm 11
   does, and Nuxt cannot build without it.
   → declared as a direct devDependency, making it a hard requirement of this project.
3. **`oxc-walker` loads `oxc-parser` via CommonJS `require()`**, but `oxc-parser` is ESM-only.
   `require(esm)` is unflagged only from **Node 22.12**. Node 20 fails as
   `Set.prototype.difference is not a function`; Node 22.6 fails as
   `could not resolve a parseSync implementation`. Neither message mentions Node.
   → pinned to Node 22.23.2, matching the server.

**Dead end worth avoiding:** relocking with npm 11 installs *none* of the native platform bindings
(`@oxc-parser/binding-*`, `@rolldown/binding-*`) and drops `oxc-parser` — producing a tree that
installs cleanly and then cannot build.

### F8 — Deploy safety (`scripts/deploy.sh`)

```bash
./scripts/deploy.sh {frontend|admin|backend|all} [--skip-build] [--keep-snapshot]
```

The ordering is the point: it verifies the extracted archive **on the server** and refuses to touch
the live build if anything is wrong, then snapshots, swaps, health-checks, and rolls back
automatically on failure.

Each target guards the way that target actually fails:

- **frontend** — packs with `tar -czhf`, rejects any dangling symlink or missing entrypoint, checks
  Node ≥ 22.12 before building
- **admin** — refuses to ship a bundle that does not reference the live API host, and checks every
  asset `index.html` names (a stale SPA index still returns 200 on its own)
- **backend** — runs the test suite *before* shipping, never copies `.env`, `storage/` or `vendor/`,
  migrates, rebuilds caches, restarts the queue worker

**Still open:** this is a safer manual deploy, not CI. Nothing runs tests on push.

### F9 — Sitemap listed no article pages

```
before   12 URLs, 0 articles, robots.txt had no Sitemap: line
after    17 URLs, 5 articles with <lastmod>, robots.txt points at the sitemap
```

The new source (`frontend/server/api/__sitemap__/urls.js`) pages through the posts API rather than
reading the first page only — the endpoint returns 9 per page, so a naive version would have
silently capped the sitemap at nine articles once a tenth was published. If the API is unreachable
it logs and returns nothing, so the sitemap degrades to its static routes instead of 500-ing.

### F10 — Mail sent inside the request

```
contact form response   3810ms  ->  819ms       (4.6x)
worker journal          App\Mail\NewContactEnquiryMail ......... 3s DONE
queue tables            0 pending, 0 failed
```

Both mailables now implement `ShouldQueue`, and `xponent-global-queue.service` runs the worker with
`--tries=3 --backoff=10 --max-time=3600`. The ~3s SMTP round trip still happens — in the worker, not
in the visitor's request.

**Deployed worker first, then the mailables**, so there was never a window where a notification could
be queued with nothing to process it.

A regression test asserts the mail is *queued* and explicitly **not sent inline**, so the round trip
cannot quietly return to the request path.

---

## What remains

| # | Item | Blocked on |
|---|------|-----------|
| F3 | Offsite backup destination | A destination — another host you own, or an S3-compatible bucket |
| F4 | Real PDFs for `/media/resources` | The six documents, or approval to unpublish the section |
| F5 | Commerce direction | Build the storefront, use it as internal back-office, or shelve it |
| F8 | Full CI pipeline | A platform choice; the deploy script covers the immediate risk |
| F11 | Rich text editor | Whether to take on TipTap/Quill as a dependency |

**F4 has a daily cost.** All six downloads are 175–266 byte `.txt` stubs. A visitor downloading
"Company Profile & Capability Statement" receives a text placeholder. Unpublishing is a one-line
change if the real documents are far off.

---

## Incidents and corrections

### A deploy took the site down for ~2 minutes

The first frontend deploy returned 500 on every route. Nitro emits at least one entry in
`.output/server/node_modules` as a **symlink to an absolute path on the build machine**
(`hookable` → `.../.nitro/hookable@6.1.1/`). A plain `tar` preserves that link, so it lands on the
server dangling:

```
Cannot find package 'hookable' imported from .output/server/node_modules/unhead/dist/server.mjs
```

A snapshot had been taken first, so rollback was immediate and the site returned to 200. The cause
is now the central guard in `scripts/deploy.sh`, and the guard was tested against a real dangling
link rather than assumed — it catches the bad tree and passes a good one.

### Three corrections to the original audit

1. **F9 overstated the problem.** The audit said there was no sitemap. There was —
   `@nuxtjs/sitemap` generates it at runtime, so it is not a file under `public/`, which is where I
   looked. Testing the live URL would have caught this. The real gap was narrower: no article URLs.

2. **F7's severity was overstated.** The audit said `undici` mattered most because it was "the HTTP
   client inside the long-running SSR process". It is **not shipped in the production bundle at
   all** — the deployed `server/node_modules` confirms this. It is a build-time dependency of Nuxt,
   so the exposure was to the build machine, not to visitors.

3. **"Pin to Node 22" was incomplete advice.** Node 22.6 is not sufficient; `require(esm)` needs
   22.12+. Node 22.6.0 also ships the same npm 10.8.2 with the resolver bug, so changing Node alone
   fixed nothing.

---

## Changes

### Repository (uncommitted at time of writing)

```
M  admin/package-lock.json                    postcss 8.5.22 -> 8.5.26
M  backend/.env.example                       documents production SMTP shape + the # quoting trap
M  backend/app/Mail/NewContactEnquiryMail.php implements ShouldQueue
M  backend/app/Mail/NewJobApplicationMail.php implements ShouldQueue
M  backend/composer.lock                      commonmark, guzzle
M  backend/config/mail.php                    admin_address fallback -> support@ (admin@ does not exist)
M  backend/routes/api_v1.php                  throttle:5,1 on login
M  backend/tests/Feature/AuthTest.php         +2 rate-limit tests
M  backend/tests/Feature/PublicApiTest.php    +1 queued-not-inline test
M  frontend/README.md                         toolchain constraints, deploy, sitemap
M  frontend/nuxt.config.ts                    sitemap.sources
M  frontend/package.json                      oxc-parser devDep, engines >=22.12
M  frontend/package-lock.json                 dependency updates
M  frontend/public/robots.txt                 Sitemap: directive
?  frontend/.nvmrc                            22.23.2
?  frontend/server/api/__sitemap__/urls.js    dynamic article URLs
?  scripts/deploy.sh                          deploy with verification and auto-rollback
?  deploy/README.md                           what the server-side files do, how to install them
?  deploy/systemd/*.service, *.timer          queue worker + nightly backup units
?  deploy/bin/xponent-backup.sh               the backup script itself
?  COMPLETION-REPORT.md                       this document
```

### Server configuration — now tracked in `deploy/`

These run on the VPS but sit outside the application directories, so no application deploy touches
them and no diff reveals when they drift. They were originally installed by hand; they are now
**version-controlled and deployable like everything else**. The copies in `deploy/` were fetched
back from the running server, so the repository matches production rather than recollection.

```
deploy/systemd/xponent-backup.service        -> /etc/systemd/system/   root:root 644
deploy/systemd/xponent-backup.timer          -> /etc/systemd/system/   root:root 644   enabled
deploy/systemd/xponent-global-queue.service  -> /etc/systemd/system/   root:root 644   enabled
deploy/bin/xponent-backup.sh                 -> /usr/local/bin/        root:root 700
```

```bash
./scripts/deploy.sh infra     # install or update all of the above
```

Safe to re-run; it never touches `.env` or backup artefacts. Run it whenever a file in `deploy/`
changes — nothing else picks these up.

### Server changes that remain untracked, deliberately

```
xponent-api/.env    MAIL_MAILER, MAIL_SCHEME, MAIL_HOST, MAIL_PORT, MAIL_USERNAME,
                    MAIL_PASSWORD, MAIL_FROM_ADDRESS, ADMIN_NOTIFICATION_EMAIL
```

Holds live credentials and must never enter the repository. `backend/.env.example` documents its
shape, and the nightly backup includes a copy.

The nginx site config (`/etc/nginx/sites-available/xponent-global`) was **not** modified by this
work. Adopting it into `deploy/` is worth doing, but as a deliberate change rather than a side
effect of this one.

Pre-change copies kept on the server, all mode 600:

```
/root/xponent-api.env.pre-smtp-20260902
/root/xponent-composer.lock.pre-update-20260902
/root/nginx-bak-archive-20260902/            three nginx configs removed earlier the same day
```

These are safe to delete once the changes have been running long enough to trust.

---

## Operational notes

**Deploying** — always use the script; never copy `.output` by hand.

```bash
nvm use $(cat frontend/.nvmrc)      # 22.23.2 — required, see frontend/README.md
./scripts/deploy.sh frontend
```

**Changing frontend dependencies** — `npm ci` needs no flags, but anything that resolves a new tree
does:

```bash
npm install --legacy-peer-deps
npm audit fix --legacy-peer-deps
```

**Backups** — `systemctl list-timers xponent-backup.timer` to check scheduling,
`journalctl -u xponent-backup.service` for results. Artefacts in `/var/backups/xponent-global`.

**Queue** — notifications now depend on `xponent-global-queue.service`. If it stops, mail is not
lost (it accumulates in `jobs`) but it is not delivered either. `failed_jobs` captures permanent
failures.

---

## Verified healthy

- 48/48 backend tests, 180 assertions
- `composer audit` clean; admin npm clean; frontend 1 low (dev-server only)
- All hosts 200 — `www`, `api`, `admin`, plus `/sitemap.xml` and `/robots.txt`
- Six services active: nginx, php8.3-fpm, mariadb, `xponent-global-nuxt`,
  `xponent-global-queue`, `xponent-backup.timer`
- Queue: 0 pending, 0 failed
- No leftover deploy or snapshot directories under `/var/www/xponent-global`
- TLS valid to 14 Nov 2026, certbot timer active
- Production config correct: `APP_ENV=production`, `APP_DEBUG=false`, caches warm

---

## Unrelated observation

`~/.npmrc` on the build machine sets `strict-ssl=false`, disabling TLS verification for every npm
install, and holds a plaintext registry auth token. Neither is part of this audit's scope, but both
are worth addressing independently.
