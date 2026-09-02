# Xponent Global — frontend (Nuxt SSR)

Runs in production as `xponent-global-nuxt.service` on port 3004, behind nginx.
The dev server uses port **3010**, not Nuxt's default 3000, to avoid colliding
with other Nuxt projects on the same machine.

## Toolchain — read this before touching dependencies

Three constraints, all of which produce confusing errors if ignored.

### Node must be >= 22.12 (see `.nvmrc`)

```bash
nvm use $(cat .nvmrc)     # 22.23.2 — the version the server runs
```

Nuxt's page scanner reaches `oxc-parser` through a CommonJS `require()`, and
`oxc-parser` ships ESM only. `require(esm)` is unflagged from Node **22.12.0**,
so on anything older the parser silently fails to load and the build dies with:

```
ERROR oxc-walker: could not resolve a `parseSync` implementation.
      Install `oxc-parser` or `rolldown` ...
```

That message says nothing about Node, which is what makes it expensive to
diagnose. `scripts/deploy.sh` checks the version up front and refuses to build
on anything older. Node 22.6 is **not** sufficient — it predates `require(esm)`.

### Dependency changes need `--legacy-peer-deps`

```bash
npm ci                              # normal install — works fine, no flag needed
npm install --legacy-peer-deps      # adding/changing a dependency
npm audit fix --legacy-peer-deps    # patching
```

npm 10.8.2's dependency resolver crashes while working out Nuxt's peer
dependencies:

```
npm error Cannot read properties of null (reading 'edgesOut')
```

It happens in arborist's `#loadPeerSet`, so it only affects commands that build
an ideal tree — `install`, `update`, `audit fix`. **`npm ci` is unaffected**, which
is why fresh clones and deploys work without any of this. `--legacy-peer-deps`
skips the code path that crashes.

Avoid "fixing" this by relocking with npm 11: it resolves, but it installs none
of the platform-specific native bindings (`@oxc-parser/binding-*`,
`@rolldown/binding-*`) and drops `oxc-parser` itself, producing a tree that
installs cleanly and then cannot build.

### `oxc-parser` is a direct devDependency on purpose

It is an **optional** peer dependency of `oxc-walker`, so a resolver is free to
skip it — and some do. Nuxt cannot build without it. Declaring it directly makes
it a hard requirement of this project rather than a resolver's judgement call.
Do not remove it because "nothing imports it".

## Everyday commands

```bash
npm ci             # install exactly what package-lock.json records
npm run dev        # dev server on http://localhost:3010
npm run build      # production build into .output/
npm run preview    # preview that build locally
```

## Deploying

Use the deploy script from the repository root — never copy `.output` by hand.
Nitro emits absolute-path symlinks inside `.output/server/node_modules`, so a
plain `tar` ships dangling links and every route returns 500.

```bash
nvm use $(cat frontend/.nvmrc)
./scripts/deploy.sh frontend
```

The script packs with `tar -czhf`, verifies on the server that nothing dangles
*before* touching the live build, snapshots, swaps, health-checks, and rolls back
automatically if anything fails.

## Sitemap

`@nuxtjs/sitemap` finds the static pages itself. Article URLs come from
`server/api/__sitemap__/urls.js`, which pages through the posts API — the
endpoint returns 9 per page, so reading only the first page would silently cap
the sitemap once a tenth article is published.
