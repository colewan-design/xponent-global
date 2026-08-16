# Feature Audit — Xponent Global Rebuild

Audit performed 2026-07-25 by grepping/testing the actual codebase (not from memory) after the initial three-app build. All items below were subsequently fixed and re-verified in a real browser (Playwright) plus a 22-test backend Feature suite — see `backend/tests/Feature/`.

## Real gaps (code exists but doesn't fully do what it implies)

| # | Issue | Detail | Status |
|---|---|---|---|
| 1 | Honeypot is inert | Backend validates a `website` field with a `prohibited` rule on `/contact-enquiries`, but the contact form never renders or sends that field. Bots and real users produce an identical request. | ✅ Fixed — hidden honeypot field added to the contact form (off-screen, unreachable by tab, `aria-hidden`); covered by `PublicApiTest::test_contact_enquiry_honeypot_field_rejects_bots`. |
| 2 | Role field is decorative | `User.role` (`admin`/`editor`) exists and is seeded, but no controller checks it. An `editor` session has identical permissions to `admin`, including deleting other admin accounts and changing Settings. | ✅ Fixed — `EnsureUserIsAdmin` middleware (`role.admin` alias) now guards `/admin/users` and `/admin/settings`; admin sidebar hides those links for editors; router also redirects editors away. Covered by `Admin/AuthorizationTest`. |
| 3 | No pagination controls in admin | Backend paginates several admin list endpoints but `useResource`'s `meta` is captured and never rendered. Past page 1, there's no way to reach the rest of the records. | ✅ Fixed — new `Pagination.vue` wired into Enquiries, Subscribers, Posts, Resources, Jobs, Job Applications, Gallery, Partners. |
| 4 | Newsletter unsubscribe doesn't exist | `NewsletterSubscriber.status` supports `unsubscribed`, but nothing (no endpoint, no link, no UI) can ever set it. | ✅ Fixed — `POST /api/v1/newsletter-subscribers/unsubscribe` (always returns success to avoid email-enumeration) + an unsubscribe form on the Newsletter page. |

## UX / polish gaps

| # | Issue | Detail | Status |
|---|---|---|---|
| 5 | Generic form error messages | Contact/newsletter/apply forms show "Something went wrong" on any failure instead of Laravel's actual field-level validation messages. | ✅ Fixed — new `extractApiErrorMessage()` composable surfaces the real `errors` payload (e.g. "The email field must be a valid email address.") on all three public forms. |
| 6 | No modal keyboard accessibility | No focus trap or Escape-to-close on any modal (admin `Modal.vue`, `JobApplyModal.vue`, gallery lightbox); no `role="dialog"`/`aria-modal`. | ✅ Fixed — all three now trap Tab focus, close on Escape, auto-focus on open, and carry `role="dialog"`/`aria-modal`/`aria-labelledby`. Gallery lightbox also gained arrow-key navigation and real `alt` text. |
| 7 | No custom Nuxt error page | 404s/500s fell back to Nuxt's default unstyled error page. | ✅ Fixed — branded `app/error.vue` with header/footer, matching copy for 404 vs. other errors. |
| 8 | No catch-all route in admin router | An unmatched admin URL rendered blank instead of a 404. | ✅ Fixed — `NotFoundPage.vue` + `/:pathMatch(.*)*` catch-all route. |
| 9 | No search in admin list pages | Only the filters explicitly built (enquiry status, application status/job, partner type tabs) existed — no search box for Posts, Resources, Jobs, or Gallery. | ✅ Fixed — shared `FiltersBySearch` backend trait + `SearchInput.vue` wired into Posts (title/excerpt), Resources (title/description), Jobs (title/department/location), Gallery (caption), and Partners (name, alongside its existing type tabs). |
| 10 | Sort order is manual-number-only | No drag-and-drop or easy reordering across Gallery/Partners/Solutions — just a number input. | ✅ Fixed — ↑/↓ controls that swap `sort_order` with the adjacent item via the existing update endpoints (no new backend routes needed). Full drag-and-drop across pages was out of scope; reordering works within a loaded page/list. |
| 11 | No PWA manifest / touch icons | Only `favicon.ico` existed. | ✅ Fixed — generated `apple-touch-icon.png` / `icon-192.png` / `icon-512.png` from the real logo (via `sharp`), plus `site.webmanifest` wired into `nuxt.config.ts`. |

## Deferred scope (addressed or explicitly still out of scope)

| # | Item | Decision |
|---|---|---|
| 12 | Email notifications on new enquiry/application | ✅ Added — `NewContactEnquiryMail` / `NewJobApplicationMail`, sent to `config('mail.admin_address')` (`ADMIN_NOTIFICATION_EMAIL` in `.env`). With `MAIL_MAILER=log` (the dev default) these land in `storage/logs/laravel.log` — no external SMTP required. |
| 13 | Automated tests | ✅ Added a 22-test baseline Feature suite: auth login/logout/me + the 500→401 regression, public endpoints (settings, solutions, jobs, contact honeypot, newsletter subscribe/unsubscribe), one full admin CRUD cycle + search on Job Openings, and role-based authorization (editor vs. admin, self-delete guard). Not full coverage of all 14 resources. |
| 14 | CI/CD pipeline, Dockerfiles | **Still not done.** Requires platform choices (hosting target, CI provider) that weren't specified. |
| 15 | Rich text editor for post/job body | **Still not done.** Plain textareas remain, matching the legacy site. Swapping in a WYSIWYG (TipTap/Quill) is a real dependency decision better made deliberately than bundled into this pass. |

## Content notes (not code defects — no fix possible without real source data)

- Resource "documents" are placeholder `.txt` stand-ins, not real PDFs. Admin can re-upload real ones once available.
- "Construction" solution category has only 2 generic items since the legacy site had zero real content there.
- Brand-partner names are placeholders ("Brand Partner 1") since the legacy site never disclosed real names, only logos. (Client names were resolved in the third pass below.)

## Second pass — legacy page sweep (2026-08-15)

Re-crawled `https://www.xponent-global.com/` end to end (no `sitemap.xml`, no `robots.txt` — both 404), then probed ~100 candidate filenames to find pages not reachable from the nav.

**Legacy inventory: 15 HTML pages.** The 12 in the nav all have routes in the rebuild. Three are live but orphaned — nothing on the site links to them:

| Orphan page | Content | Rebuild status |
|---|---|---|
| `where-we-operate.html` | Brisbane · Taguig warehouse · Davao head office · Hong Kong | Already covered — `/about` `#we-operate` block + Office Locations resource |
| `our-affiliations.html` | 7 industry bodies (Chamber of Mines PH, AustCham, CCIAP, ANZCHAM PH, PMSEA, PMEA, DIWATA) | Already covered — `/about` `#our-affiliations` block + Partners |
| `solutions.html` (plural, distinct from the linked `solution.html`) | Richer Exploration & Geotechnical catalogue | **Was missing content — now folded in (below)** |

Two nav items point at `#` with no file behind them (`construction.html` and `media.html` both 404): **Construction** (Solutions dropdown) and **Media** (top-level). Nothing to port.

### Content folded into `DatabaseSeeder::seedSolutions()`

The DTH and Discoverer sections turned out to exist on the linked `solution.html` too, inside collapsed tab panels the first pass missed. Exploration items went 8 → 12:

- **DTH Drilling Tools** — new item, legacy copy verbatim.
- **Discoverer Core Trays** — replaces the generic "Core Trays". Restores the brand name, the "proven in the field since 1993" provenance, the 8-point feature list, and the accessory range (core markers, lids and lid clips, locking links, aluminium ID tags).
- **Bits sub-breakdown** — added Button Drills, Rotary Drill Bits, Impregnated Diamond Bits alongside the existing "Bits and Reamers" parent, which now names the full range.
- **Coring System** / **Drill Rods, Casing and Subs** — upgraded to the legacy copy (BQ–PQ3 thread sizes; the affiliate-developed rod joint load / bending / pull / make-break testing claim).
- **Image mapping corrected.** `sol-image-00.jpg`, `sol-image-01.png`, `sol-image-02.png` were referenced by the legacy pages but never imported — now downloaded into `storage/app/public/seed/`. `sol-image-06.png` had been attached to "Reverse-Circulation System"; on both legacy pages it is the DTH image, so it moved to the new DTH item and RC took `sol-image-02.png`.

Descriptions written for Button Drills, Rotary Drill Bits, and Impregnated Diamond Bits are **ours, not legacy** — those three are sub-tab labels with no body copy on the legacy site. Everything else above is the legacy wording (only an obvious "core core storage" typo fixed).

### Incidental fix: long item copy was unreachable

`solutions/index.vue` rendered item descriptions in a `line-clamp-3` card with no click-through, so the existing multi-paragraph "Bits and Reamers" copy was already truncated on the site and the new Discoverer feature list would have been too. Cards now get a Read more / Show less toggle (`aria-expanded`, `whitespace-pre-line` when open) whenever a description is multi-line or over 160 characters — 8 of the 25 cards.

Verified: `migrate:fresh --seed` clean, 22/22 backend tests pass, and `/solutions` SSR output carries all 12 exploration items with working toggles.

## Third pass — `solution.html` camp facilities (2026-08-15)

Re-aggregated the linked `solution.html` end to end. The Exploration and Mining/Production sections are label-only lists on that page and were already fully covered (and enriched from `solutions.html`) by the second pass — nothing new there. The **Mining Camp Facilities** section was the remaining gap: the legacy page carries full specification lists, and the seeder had compressed each one to a single paraphrased sentence.

### Content folded into `DatabaseSeeder::seedSolutions()`

Camp facilities stays at 4 items, but three of them gained their real legacy copy:

- **Global Quality Standard** — was a bare list of six standard codes; now carries the legacy lead-in and each standard's title (Structural Design Actions, Permanent/Imposed Actions, Wind Load Actions, Earthquake Load Actions, Steel Structures, Cold-Formed Steel Structures). Also **moved to first** in the category, matching the legacy page where the block sits directly under the CIMC intro, ahead of the accommodation units.
- **Upper Level** / **Lower Level** — were one-line summaries; now carry the full legacy specification: galvanised cladding, 15.15m x 4.45m including services frame, AS1170.2-2002 wind region A1 / terrain category 2, and the itemised fitout with model numbers (Daikin FTXS25KVMA / RXS25KVMA, Rheem MPI-325 325L model 55132505, Haier 130L HBF130W), ensuite breakdown, electrical/data provisioning, and the 9-point per-bedroom furniture list.
- **Building** — the 44 / 47 / 60 room configurations were collapsed into one sentence; now broken out per size with exact module counts, including the details that sentence dropped (the 3-bedroom & communications module on the 47 and 60, and the separate disabled laundry services building on the 60).

Item titles now use the legacy "Relocatable Accommodation" wording rather than the shortened "Accommodation".

All of the above is legacy wording, reflowed from the page's nested `<ul>` markup into the same plain-line list style already used by the Discoverer Core Trays entry. No new images needed — `solution-1.png`, `solution-lower.png`, `solution-2.png` were already imported.

Verified: `migrate:fresh --seed` clean, 22/22 backend tests pass, and `/solutions` SSR output carries all 25 cards with the new spec text and 9 working Read more toggles (was 8 — the four camp items are now all multi-line).

## Fourth pass — About page (`aboutus.html`) (2026-08-15)

Extracted the legacy About page in full and diffed it section by section against the `about` PageContent, Office Locations, and Partners. The page has **seven** sections — the six anchored ones plus one the nav never links to.

Intro, vision, mission and core values were already exact ports, including the `main-cl-06` / `main-cl-07` logo pairing (easy to invert, since the legacy page renders PMEA and DIWATA out of image order). Six gaps were found and fixed:

| Gap | Fix |
|---|---|
| **"Schedule a Visit" section missing entirely** — no anchor, sits after Affiliations, which is likely why it was missed | New `hours_weekdays` / `hours_saturday` / `hours_sunday` settings + a Schedule a Visit section on `/about`. Its legacy body copy is Lorem Ipsum, so only the hours were carried over. |
| **Three affiliation descriptions truncated** | Restored the closing sentence on Australian Chamber ("…every region of Australia"), CCIAP ("…productive diversity…"), and PMSEA ("…key to a great nation"). |
| **Office addresses lost province/postcode** | Restored `Metro Manila 1630` and `Davao Del Sur 8000`; Brisbane's `Queensland 4160` moved into `city` so it survives rendering. Order now matches the legacy page (Warehouse before Head Office). |
| **No site-wide phone; invented contact email** | Added `contact_phone` / `contact_phone_alt` (+61 448 918 582, +614 0841 9779) and `contact_email_alt`. `contact_email` was `info@xponent-global.com`, which appears nowhere on the legacy site — now `roma@xponent-global.com`, with `connie@` as secondary. **Revert this if the client owns an `info@` inbox.** |
| **`map-img.jpg` unused** | The legacy Where We Operate leads with a map. Added as a `Where We Operate` PageContent section (heading + image, no body) so it stays admin-editable, rendered above the office cards. |
| **Legal entity name dropped** | `Xponent Global Limited` restored as the lead line of the About XGL block, and added as a `company_legal_name` setting. |

All new settings are exposed in the admin Settings form. No migration was needed: the settings controller writes arbitrary keys, and the legacy phone/email numbers are **footer-level, not per-office** — `where-we-operate.html` shows one shared contact block, not one per site — so `office_locations` did not need contact columns.

### Still blocked — needs a decision from the client

The legacy source contradicts itself on the company address. Every page footer says **61 Hawkesbury Valley Way, Sydney, NSW 2756**; Where We Operate says **251-255 Wellington Street, Ormiston, Queensland 4160**. Different city and state. The rebuild has no footer address, so nothing is currently wrong — deliberately left alone rather than guessing.

Verified: `migrate:fresh --seed` clean, 22/22 backend tests pass, admin builds, and `/about` SSR output carries all seven sections with the map image resolving 200.

## Fifth pass — `our-clients.html` aggregation (2026-08-15)

Re-read the legacy clients page in full. Three blocks on it:

| Block | Status |
|---|---|
| Intro copy ("The client base of Xponent Global ranges from…") | Already in `frontend/app/pages/clients.vue`, verbatim |
| 12-logo carousel (`cl-logo-01..12.jpg`, no captions, empty `alt`) | Logos already imported; **names were placeholders — now resolved** |
| A `d-none` "client-box" grid (7 industry bodies) and a `d-none` "SCHEDULE A VISIT" block | Hidden on the live page. The 7 bodies are the same set already seeded as `affiliation` partners (from `our-affiliations.html`); the schedule block is Lorem Ipsum. Nothing new to port. |

### Client names recovered from the logo artwork

The legacy markup gives no names at all — every carousel `<img>` has `alt=""`. Names below were read off the logo images themselves; the five that are pure marks or bare initials were confirmed by matching the artwork against the company's own current logo.

| # | Client | Evidence | Website |
|---|---|---|---|
| 01 | Philsaga Mining Corporation | "PMC" pyramid mark — matches `philsaga.com/theme/images/pmc/logo@2x.png` | https://www.philsaga.com |
| 02 | SBF Philippines Drilling Resources Corporation | Yellow rig mark — matches sbfdrilling.com logo (Davao-based, as is XGL's head office) | https://www.sbfdrilling.com |
| 03 | Quest Exploration Drilling (Philippines) Inc. | "QED" + blue wave — matches qedrill.com logo | https://www.qedrill.com |
| 04 | Apex Mining Co., Inc. | Gold twin-peak mark, no text — matches apexmines.com header logo | https://www.apexmines.com |
| 05 | Geodrill | Wordmark legible in logo | https://www.geodrill.ltd |
| 06 | SR Metals, Inc. | Wordmark legible in logo ("SRMI") | — (no official site found) |
| 07 | Philex Mining Corporation | Wordmark legible in logo | https://philexmining.com.ph |
| 08 | Major Drilling | Wordmark legible in logo | https://www.majordrilling.com |
| 09 | OceanaGold | Wordmark legible in logo | https://oceanagold.com |
| 10 | Filminera Resources Corporation | Wordmark legible in logo | https://filminera.ph |
| 11 | Capital Drilling | Wordmark legible in logo | https://www.capdrill.com |
| 12 | Lepanto Consolidated Mining Company | Gold "L"+copper-symbol mark, no text — matches lepantomining.com header logo | https://www.lepantomining.com |

`DatabaseSeeder::seedPartners()` now seeds these names and URLs in carousel order instead of `Client 01…12`.

`frontend/app/pages/clients.vue` was updated to use them: each card now renders the client name under the logo, and the card itself becomes an `<a target="_blank" rel="noopener noreferrer">` to `website_url` when one exists (11 of 12 — SR Metals has no site, so its card stays a plain `<div>`). This goes beyond the legacy carousel, which showed bare logos with no captions and no links.

Verified: `migrate:fresh --seed` clean, all 12 rows carry the right name/logo/URL pairing, 22/22 backend tests pass, and `/clients` SSR output renders 12 named cards with 11 outbound links.

## Sixth pass — `resources.html` aggregation (2026-08-15)

Re-read the legacy Resources page in full. It has three titled sections; the rebuild had all of the *content* but was missing structure from two of them.

| Legacy section | Content | Rebuild status |
|---|---|---|
| **Technical Documents** | Lead line, then two cards: "Product Datasheets" (High-Pressure Drill Pipe, Modular Rig Components) and "Safety & Compliance" (HSE Protocols, ISO Certifications) | Items already seeded as `resources`. **Lead line was missing — now added.** |
| **Case Studies** | Lead line + Gold Exploration in West Africa, Sustainable Construction in the Middle East | Both already seeded as `case_study` posts and rendered. **Lead line was missing — now added.** |
| **News & Insights** | Lead line + 3 headlines (deep-well drilling, energy transition, AI-driven surveys) | Posts already seeded, but the **whole section was absent from `/media/resources`** — it only appeared on `/media/newsletter`. **Now restored.** |

The page also carries the same `d-none` "SCHEDULE A VISIT" Lorem Ipsum block found on the other legacy pages. Nothing to port.

### Changes

- **News & Insights section added**, fetching `posts?type=news` and linking each headline to `/news/{slug}`. On the legacy page these headlines are `href="#"` dead links, so the rebuild is strictly better here — the articles are real and reachable.
- **Three legacy lead lines restored** verbatim: "Get the specs, data sheets, and certifications you need to ensure quality and compliance." under Technical Documents, "Discover how Xponent Global delivers real-world impact across diverse sectors." under Case Studies, and "Stay informed with our latest updates, market trends, and industry commentary." under News & Insights.
- **The aggregated copy is stored as data, not hardcoded strings.** A new `resources` PageContent record (seeded in `seedPageContent()`) holds the three section headings and lead lines; `resources.vue` reads them from `/page-content/resources` and destructures positionally, the same idiom `about.vue` uses. Static labels remain as fallbacks so the page still renders if the record is deleted. `Resources` was added to the admin PageContent editor's page list, so the copy is editable alongside Home / About / Sustainability / Careers.

The two sub-group labels (Product Datasheets, Safety & Compliance) stay in code rather than PageContent: on the legacy page they are card titles inside the Technical Documents section, not section titles, and they map 1:1 to the `category` enum the resource list filters on.

Note on structure: the legacy page nests Product Datasheets and Safety & Compliance as *cards inside* the Technical Documents section, so its lead line covers all three document groups. The rebuild renders the three as peer sections (driven by the `category` enum), so that lead line is attached to the first group only rather than duplicated.

Legacy duplication was deliberate and is preserved: `newsletter.html` shows the same three News & Insights headlines, and the rebuild's `/media/newsletter` still lists them too.

Not changed: `/media/newsletter` has the same News & Insights heading with its lead line missing. Same legacy sentence, same defect, but a different page than this pass covered — flagged rather than silently fixed.

Verified: `migrate:fresh --seed` clean, 22/22 backend tests pass, admin builds, and `/media/resources` SSR output renders all five section headings, the three lead lines (served from the PageContent API, not the fallbacks), and 5 post links (2 case studies + 3 news); the new `/news/{slug}` targets return 200.

## Seventh pass — `gallery.html` aggregation (2026-08-15)

Re-read the legacy Gallery page in full. It is a bare Bootstrap lightbox grid: **16 photos, nothing else.** No captions, no categories, no filter tabs, no titles, no lead copy — every `<img>` carries `alt=""`. All 16 files (`gallery-img-01.jpg` … `-16.jpg`) were already imported and already seeded in the correct order, so **no content was missing.**

### The real gap: captions were never written, so the a11y work had nothing to render

`gallery_images.caption` exists, the admin can edit it, and `media/gallery.vue` already consumes it three ways — grid `alt`, lightbox trigger `aria-label`, and the lightbox's `sr-only` heading. But the seeder created all 16 rows with `caption => null`, so every one of those fell through to its generic fallback:

- 16 identical `alt="Xponent Global site photo"` values in the grid
- 16 identical `aria-label="Open photo"` triggers — indistinguishable to a screen-reader user tabbing the grid
- `"Gallery photo viewer"` as the dialog heading for every photo
- the admin's caption search (UX gap #9) matched nothing, on any query

So AUDIT item 6's "real `alt` text" was true of the component and false of the data. Captions are now seeded for all 16, written from the photographs themselves: site-visit and drill-crew shots, core-yard and core-tray inspection, a mineralised hand specimen, ridgeline and access-track landscapes, and the fern/regrowth close-ups.

**This changes nothing visually.** The public page never renders `caption` as visible text, so the grid still matches the legacy layout exactly — the captions exist only for assistive tech and admin search. Legacy parity is preserved while the `alt=""` accessibility defect is not inherited.

### Checked and *not* a defect: EXIF orientation

Six of the sixteen (05, 07, 10, 12, 14, 15) are phone photos carrying EXIF `Orientation=6` (rotate 90° CW) — their stored pixels are sideways. This is worth flagging because an image pipeline that re-encodes without honouring EXIF renders them rotated. It is fine here: `nuxt.config.ts` sets `image: { provider: 'none' }`, so `NuxtImg` emits a plain `<img>` pointing at the original file, browsers apply `image-orientation: from-image` by default, and nothing in `app/assets/css/main.css` overrides it. **If the image provider is ever switched to IPX or another resizer, this needs re-checking** — sharp does not auto-rotate unless explicitly told to.

Verified: `migrate:fresh --seed` clean, 22/22 backend tests pass, and `/media/gallery` SSR output carries 16 unique non-empty `alt` values and 16 unique `aria-label`s, with zero `alt=""` and no remaining generic fallbacks.

## Fifth pass — Career page (`career.html`) (2026-08-15)

Extracted the legacy Career page and diffed it against the `careers` PageContent, Job Openings, and `careers.vue`. This page was already an unusually faithful port — **one** gap, and it turned out not to be a Career-page issue at all.

Already correct, verified field by field:

- Hero, "Join a Global Force Powering Innovation and Progress" intro, and the four "Why Work With Us?" blocks (Global Projects, Technology-Driven, Culture of Excellence, People First) — exact copy, correct order.
- All three openings match exactly on title, location, employment type and summary: Field Engineer – Mining Operations (Western Australia, full-time), HSE Officer – Oil & Gas (Qatar, contract), Business Development Manager (Dubai / Remote, full-time).
- The page's single content image (`gallery-img-02.jpg`) is the one already seeded on the intro block.

Note the legacy page has **no apply mechanism** — no form, no button, no mailto. The rebuild's `JobApplyModal` + job applications pipeline is an addition beyond the legacy site, not a port of it.

### The one gap, and a correction to the fourth pass

"Schedule a Visit" was missing here too — but checking all 12 legacy pages shows it appears on **seven**: home, about, clients, sustainability, resources, newsletter, career. The fourth pass treated it as About-specific and inlined the markup into `about.vue`; that was wrong.

Extracted to `frontend/app/components/ScheduleVisit.vue`, which reads the `hours_*` and `contact_*` settings itself (keyed `site-settings` so pages don't each refetch). Now used on:

- `/careers` — this pass's actual fix
- `/about` — inline copy removed; its now-unused `/settings` fetch removed with it

**Not yet added to the remaining five** (`/`, `/clients`, `/sustainability`, `/media/resources`, `/media/newsletter`) — that's beyond this page's scope and is a one-line `<ScheduleVisit />` per page whenever wanted.

Verified: 22/22 backend tests pass, and both `/careers` and `/about` SSR render the block exactly once each with hours and phone numbers resolving.

## Incidental fix: dev port collisions with another local project

While re-verifying these fixes, both the Laravel backend (port 8000/8001) and the Nuxt frontend (port 3000) were found colliding with an unrelated pre-existing project on the same machine (`C:\uniglobal`). Per user direction, `exponent-global` was moved to dedicated, less-common ports to avoid this permanently:

- Backend: **8010** (was 8000)
- Frontend: **3010** (was 3000)
- Admin stays on 5174 (no conflict observed)

All `.env` / `.env.example` files, `nuxt.config.ts`, `admin/src/lib/api.js`, and the root `README.md` were updated accordingly.
