# Xponent Global

A rebuild of the Xponent Global marketing site as three coordinated apps:

- **`backend/`** — Laravel API (PHP 8.2+, SQLite by default). Owns all content and forms: solutions catalogue, clients/brand partners/affiliations, gallery, resources, job openings/applications, posts (news & case studies), office locations, editable page copy, and site settings. It also owns the commerce side — products, warehouses, stock and customer orders.
- **`admin/`** — Vue 3 + Vite SPA. Internal CMS used to manage everything in the backend.
- **`frontend/`** — Nuxt 3 (SSR) public marketing site. Renders all public pages from the backend API.

## Prerequisites

- PHP 8.2+ and Composer
- Node.js 20+ and npm

## First-time setup

```bash
# 1. Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# 2. Admin
cd ../admin
npm install
cp .env.example .env

# 3. Frontend
cd ../frontend
npm install
cp .env.example .env
```

## Running locally

Start all three in separate terminals:

```bash
# Terminal 1 — API (http://localhost:8010)
cd backend && php artisan serve --port=8010

# Terminal 2 — Admin (http://localhost:5174)
cd admin && npm run dev

# Terminal 3 — Public site (http://localhost:3010)
cd frontend && npm run dev
```

Both the backend (8010, not Laravel's default 8000) and the frontend (3010, not Nuxt's default 3000) run on non-default ports specifically to avoid colliding with any other local Laravel/Nuxt projects on the same machine — dev servers commonly default to 8000/3000 and silently auto-fall-back to the next port (8001/3001) if that's busy, which just moves the collision rather than avoiding it.

Log in to the admin at http://localhost:5174 with the seeded account:

- **Email:** `admin@xponent-global.com`
- **Password:** `password`

(There's also an `editor@xponent-global.com` / `password` account with the `editor` role, for testing role-based scenarios.)

Change these before deploying anywhere real.

## Architecture notes

- **Auth:** the admin SPA authenticates via Laravel Sanctum's SPA (cookie/session) mode. The public Nuxt site never authenticates — it only reads public endpoints and posts to a handful of rate-limited, honeypot-protected write endpoints (contact enquiries, newsletter signup, job applications). `SANCTUM_STATEFUL_DOMAINS` in `backend/.env` intentionally lists **only** the admin origin — adding the public site's origin there would force CSRF/session handling onto its anonymous form submissions.
- **Database:** SQLite by default (`backend/database/database.sqlite`) for zero-config local dev. To use the local XAMPP MySQL instead, see the commented block in `backend/.env.example`.
- **File storage:** uploads (images, resumes, documents) go through Laravel's `public` disk (`php artisan storage:link` required) and are returned as absolute URLs in every API response.
- **Content model:** most site sections (solutions catalogue, clients/partners, gallery, jobs, resources, posts) are full CRUD resources in the admin. The narrative marketing copy on About/Sustainability/Careers/Home is modeled as an ordered list of `{heading, body, image}` blocks per page (`PageContent`), editable from **Page Content** in the admin without needing a full page-builder.
- **API base:** all endpoints live under `/api/v1`. Public routes are open reads plus a few rate-limited writes; everything under `/api/v1/admin/*` requires an authenticated admin/editor session.
- **Commerce (orders / products / inventory):** admin-only, with no public endpoints — the site markets the catalogue through **Solutions**, which stays separate marketing content, while **Products** are the priced, stocked SKUs behind it.
  - `stock_movements` is an append-only ledger and is the source of truth; `inventory_items.quantity` is a running total written in the same transaction. A mistake is corrected by posting a reversing movement, never by editing history. All of this lives in `App\Services\InventoryService` — nothing else is allowed to change a balance.
  - Orders drive stock through their status: **confirmed/processing** reserves it (`available = quantity − reserved`), **shipped/delivered** deducts it, and moving back out of those states — including **cancelled** — hands it back. Two markers on the order (`stock_reserved_at`, `stock_deducted_at`) record what has actually been applied, so re-saving an order never double-deducts.
  - Stock may be over-*committed* (a reservation beyond what is on the shelf is a backorder, and available goes negative to show it) but never over-*issued* — a despatch that would take on-hand below zero is refused.
  - Order lines snapshot the SKU, name and price at the time they are added, so renaming or repricing a product never rewrites past orders. A line with no product behind it (freight, handling, a one-off fabrication) is priced but holds no stock.

## Ports

| App | Port |
|---|---|
| Backend (Laravel) | 8010 |
| Admin (Vue) | 5174 |
| Frontend (Nuxt) | 3010 |
