#!/usr/bin/env bash
#
# Deploy xponent-global to the VPS.
#
#   ./scripts/deploy.sh frontend      Nuxt SSR build  -> xponent-ssr/.output
#   ./scripts/deploy.sh admin         Vue SPA build   -> xponent-admin/
#   ./scripts/deploy.sh backend       Laravel source  -> xponent-api/
#   ./scripts/deploy.sh infra         deploy/         -> systemd units + backup script
#   ./scripts/deploy.sh all           infra, then backend, admin, frontend
#
# Options:
#   --skip-build    reuse the existing local build output
#   --keep-snapshot leave the pre-deploy snapshot on the server after success
#
# Why this script exists
# ----------------------
# Deploys were a manual build-and-copy, and one of them took the site down. Nitro
# emits at least one entry in .output/server/node_modules as a SYMLINK to an
# absolute path on the build machine (hookable -> .../.nitro/hookable@6.1.1/).
# A plain `tar` preserves that link, so it lands on the server pointing at a
# directory that does not exist. Everything looks healthy — the archive extracts,
# the service starts — and then every page returns 500:
#
#   Cannot find package 'hookable' imported from .../unhead/dist/server.mjs
#
# So this script packs with `tar -czhf` (dereference), and refuses to swap the
# live build until it has confirmed on the server that nothing dangles. It also
# snapshots first and rolls back automatically if the health check fails, so a
# bad deploy costs seconds rather than however long it takes someone to notice.

set -euo pipefail

SSH_HOST="${XPONENT_SSH_HOST:-xponent}"
REMOTE_ROOT="/var/www/xponent-global"
SITE_URL="https://www.xponent-global.com"
API_URL="https://api.xponent-global.com"
ADMIN_URL="https://admin.xponent-global.com"

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
STAMP="$(date +%Y%m%d-%H%M%S)"
SKIP_BUILD=0
KEEP_SNAPSHOT=0
TARGET=""

RED=$'\033[31m'; GRN=$'\033[32m'; YEL=$'\033[33m'; DIM=$'\033[2m'; OFF=$'\033[0m'
step() { printf '\n%s==>%s %s\n' "$GRN" "$OFF" "$*"; }
info() { printf '    %s\n' "$*"; }
warn() { printf '%s !! %s%s\n' "$YEL" "$*" "$OFF"; }
die()  { printf '%s !! %s%s\n' "$RED" "$*" "$OFF" >&2; exit 1; }

while [ $# -gt 0 ]; do
  case "$1" in
    frontend|admin|backend|infra|all) TARGET="$1" ;;
    --skip-build)    SKIP_BUILD=1 ;;
    --keep-snapshot) KEEP_SNAPSHOT=1 ;;
    -h|--help) sed -n '2,28p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) die "unknown argument: $1" ;;
  esac
  shift
done

[ -n "$TARGET" ] || die "usage: ./scripts/deploy.sh {frontend|admin|backend|infra|all} [--skip-build] [--keep-snapshot]"

remote() { ssh -o BatchMode=yes -o ConnectTimeout=30 "$SSH_HOST" "$@"; }

# Health checks run on the server so a flaky local connection cannot be mistaken
# for a broken deploy.
http_code() { remote "curl -sS -o /dev/null -w '%{http_code}' --max-time 25 '$1'" 2>/dev/null || echo 000; }

check_urls() {
  local label="$1"; shift
  local failed=0 url code
  for url in "$@"; do
    code="$(http_code "$url")"
    if [ "$code" = "200" ]; then
      printf '    %s200%s  %s\n' "$GRN" "$OFF" "$url"
    else
      printf '    %s%s%s  %s\n' "$RED" "$code" "$OFF" "$url"
      failed=1
    fi
  done
  [ "$failed" -eq 0 ] || return 1
  info "$label healthy"
}

preflight() {
  step "Preflight"
  remote true 2>/dev/null || die "cannot reach '$SSH_HOST' over SSH"
  info "ssh to $SSH_HOST ok"
  remote "test -d $REMOTE_ROOT" || die "$REMOTE_ROOT missing on the server"
  local free
  free="$(remote "df -Pm $REMOTE_ROOT | awk 'NR==2{print \$4}'")"
  [ "$free" -gt 500 ] || die "only ${free}MB free on the server; refusing to deploy"
  info "${free}MB free on the target filesystem"
}

# ------------------------------------------------------------------- infra --
# systemd units and the backup script live outside the application directories,
# so no application deploy touches them and no diff reveals when they drift.
# Keeping them in deploy/ and shipping them from here is what stops them being
# invisible.
deploy_infra() {
  local dir="$REPO_ROOT/deploy"
  [ -d "$dir/systemd" ] || die "no deploy/systemd directory"

  step "Deploying server configuration"
  local archive="$dir/.deploy-infra.tar.gz"
  rm -f "$archive"
  ( cd "$dir" && tar --warning=no-timestamp -czhf "$archive" systemd bin )
  scp -o BatchMode=yes -q "$archive" "$SSH_HOST:/tmp/xponent-infra.tar.gz"
  rm -f "$archive"

  remote "set -e
    rm -rf /tmp/infra-staging && mkdir -p /tmp/infra-staging
    tar -xzf /tmp/xponent-infra.tar.gz -C /tmp/infra-staging
    rm -f /tmp/xponent-infra.tar.gz

    # These files are authored on Windows, where the checkout carries CRLF even
    # though git stores LF (see .gitattributes). A shell script with CRLF fails
    # on Linux in a way that reads as nonsense, and a systemd unit picks up a
    # trailing \r inside ExecStart. Strip it here so the working tree's line
    # endings cannot decide whether the deploy works.
    find /tmp/infra-staging -type f -exec sed -i 's/\r\$//' {} +

    for f in /tmp/infra-staging/systemd/*; do
      install -o root -g root -m 644 \"\$f\" /etc/systemd/system/\$(basename \"\$f\")
    done
    # 700: the backup script reads DB credentials out of .env at run time.
    for f in /tmp/infra-staging/bin/*; do
      install -o root -g root -m 700 \"\$f\" /usr/local/bin/\$(basename \"\$f\")
    done
    rm -rf /tmp/infra-staging

    bash -n /usr/local/bin/xponent-backup.sh || { echo 'backup script has a syntax error'; exit 1; }
    systemctl daemon-reload
    systemctl enable --now xponent-backup.timer >/dev/null 2>&1
    systemctl enable xponent-global-queue.service >/dev/null 2>&1
    # Restart rather than reload: the worker holds PHP in memory.
    systemctl restart xponent-global-queue.service
  "
  sleep 3

  step "Health check"
  local queue timer
  queue="$(remote 'systemctl is-active xponent-global-queue.service')"
  timer="$(remote 'systemctl is-enabled xponent-backup.timer')"
  printf '    queue worker: %s\n    backup timer: %s\n' "$queue" "$timer"
  [ "$queue" = "active" ] || die "queue worker is not running after install"
  [ "$timer" = "enabled" ] || die "backup timer is not enabled after install"
  remote "systemctl list-timers xponent-backup.timer --no-pager --no-legend | sed 's/^/    next: /'"
}

# ------------------------------------------------------------------ frontend --
deploy_frontend() {
  local dir="$REPO_ROOT/frontend"
  local remote_dir="$REMOTE_ROOT/xponent-ssr"
  local archive="$dir/.deploy-output.tar.gz"

  if [ "$SKIP_BUILD" -eq 0 ]; then
    # Nuxt's page scanner reaches oxc-parser through a CommonJS require(), and
    # oxc-parser is ESM-only — so the build needs a Node that supports
    # require(esm), which is unflagged from 22.12.0. On anything older it fails
    # as "oxc-walker: could not resolve a parseSync implementation", which says
    # nothing about Node. Fail here instead, with the actual reason.
    local node_major node_minor
    node_major="$(node -p 'process.versions.node.split(".")[0]')"
    node_minor="$(node -p 'process.versions.node.split(".")[1]')"
    if [ "$node_major" -lt 22 ] || { [ "$node_major" -eq 22 ] && [ "$node_minor" -lt 12 ]; }; then
      die "frontend build needs Node >= 22.12 (require(esm)); this shell has $(node -v).
    Run:  nvm use \$(cat frontend/.nvmrc)"
    fi
    info "node $(node -v)"

    step "Building frontend"
    ( cd "$dir" \
      && NUXT_PUBLIC_SITE_URL="$SITE_URL" NUXT_PUBLIC_API_BASE="$SITE_URL" npm run build )
  else
    info "skipping build (--skip-build)"
  fi
  [ -f "$dir/.output/server/index.mjs" ] || die "no build at frontend/.output"

  step "Packing (dereferencing symlinks)"
  # -h is the whole point: without it, Nitro's absolute-path symlinks ship as
  # dangling links and the site 500s on every route.
  rm -f "$archive"
  ( cd "$dir" && tar --warning=no-timestamp -czhf "$archive" .output )
  info "archive $(du -h "$archive" | cut -f1)"

  step "Uploading"
  scp -o BatchMode=yes -q "$archive" "$SSH_HOST:/tmp/xponent-output.tar.gz"
  rm -f "$archive"

  step "Verifying the archive on the server, before touching the live build"
  remote "set -e
    cd '$remote_dir'
    rm -rf .deploy-staging && mkdir -p .deploy-staging
    tar -xzf /tmp/xponent-output.tar.gz -C .deploy-staging
    rm -f /tmp/xponent-output.tar.gz
    test -f .deploy-staging/.output/server/index.mjs || { echo 'MISSING server/index.mjs'; exit 1; }
    dangling=\$(find .deploy-staging/.output -xtype l | wc -l)
    if [ \"\$dangling\" -ne 0 ]; then
      echo \"DANGLING SYMLINKS: \$dangling\"
      find .deploy-staging/.output -xtype l | head -10
      exit 1
    fi
    echo '    no dangling symlinks, entrypoint present'
  " || { remote "rm -rf '$remote_dir/.deploy-staging'"; die "archive failed verification — live build untouched"; }

  step "Swapping in the new build"
  remote "set -e
    cd '$remote_dir'
    rm -rf .deploy-snapshot
    mv .output .deploy-snapshot
    mv .deploy-staging/.output .output
    rmdir .deploy-staging
    chown -R www-data:www-data .output
    systemctl restart xponent-global-nuxt.service
  "
  sleep 5

  step "Health check"
  if check_urls "frontend" "$SITE_URL/" "$SITE_URL/about" "$SITE_URL/solutions" "$SITE_URL/sitemap.xml"; then
    if [ "$KEEP_SNAPSHOT" -eq 0 ]; then
      remote "rm -rf '$remote_dir/.deploy-snapshot'"
      info "snapshot removed"
    else
      info "snapshot kept at $remote_dir/.deploy-snapshot"
    fi
  else
    warn "health check failed — rolling back"
    remote "set -e
      cd '$remote_dir'
      rm -rf .output.failed && mv .output .output.failed
      mv .deploy-snapshot .output
      chown -R www-data:www-data .output
      systemctl restart xponent-global-nuxt.service
    "
    sleep 5
    check_urls "frontend (rolled back)" "$SITE_URL/" || die "ROLLBACK ALSO FAILED — the site is down, investigate now"
    die "deploy rolled back; the bad build is at $remote_dir/.output.failed"
  fi
}

# --------------------------------------------------------------------- admin --
deploy_admin() {
  local dir="$REPO_ROOT/admin"
  local remote_dir="$REMOTE_ROOT/xponent-admin"
  local archive="$dir/.deploy-dist.tar.gz"

  if [ "$SKIP_BUILD" -eq 0 ]; then
    step "Building admin"
    ( cd "$dir" && npm run build )
  fi
  [ -f "$dir/dist/index.html" ] || die "no build at admin/dist"

  # The bundle bakes in VITE_API_URL at build time. A build that picked up the
  # dev .env ships an admin that cannot reach the live API, and it looks fine
  # until someone tries to log in.
  if ! grep -rq "$API_URL" "$dir/dist/assets/"*.js 2>/dev/null; then
    die "built admin does not reference $API_URL — check admin/.env.production"
  fi
  info "bundle targets $API_URL"

  step "Packing and uploading"
  rm -f "$archive"
  ( cd "$dir" && tar --warning=no-timestamp -czhf "$archive" dist )
  scp -o BatchMode=yes -q "$archive" "$SSH_HOST:/tmp/xponent-admin.tar.gz"
  rm -f "$archive"

  step "Swapping in the new build"
  remote "set -e
    cd '$REMOTE_ROOT'
    rm -rf /tmp/admin-staging && mkdir -p /tmp/admin-staging
    tar -xzf /tmp/xponent-admin.tar.gz -C /tmp/admin-staging
    rm -f /tmp/xponent-admin.tar.gz
    test -f /tmp/admin-staging/dist/index.html || { echo 'archive bad'; exit 1; }
    rm -rf .admin-snapshot && cp -a xponent-admin .admin-snapshot
    rm -rf xponent-admin/* && cp -a /tmp/admin-staging/dist/. xponent-admin/
    chown -R www-data:www-data xponent-admin
    rm -rf /tmp/admin-staging
  "

  step "Health check"
  # index.html returning 200 proves nothing on an SPA — a stale index referencing
  # deleted hashed assets still serves. Check the assets it actually names.
  local asset_check
  asset_check="$(remote "set -e
    idx=\$(curl -sS --max-time 25 '$ADMIN_URL/')
    bad=0
    for a in \$(echo \"\$idx\" | grep -oE '(src|href)=\"/assets/[^\"]+\"' | grep -oE '/assets/[^\"]+'); do
      c=\$(curl -sS -o /dev/null -w '%{http_code}' --max-time 25 \"$ADMIN_URL\$a\")
      [ \"\$c\" = '200' ] || { echo \"BAD \$c \$a\"; bad=1; }
    done
    [ \$bad -eq 0 ] && echo OK || echo FAIL
  ")"

  if echo "$asset_check" | grep -q '^OK$'; then
    info "index.html and every referenced asset resolve"
    [ "$KEEP_SNAPSHOT" -eq 1 ] || remote "rm -rf '$REMOTE_ROOT/.admin-snapshot'"
  else
    warn "asset check failed — rolling back"
    printf '%s\n' "$asset_check"
    remote "set -e
      cd '$REMOTE_ROOT'
      rm -rf xponent-admin/* && cp -a .admin-snapshot/. xponent-admin/
      chown -R www-data:www-data xponent-admin
    "
    die "admin deploy rolled back"
  fi
}

# ------------------------------------------------------------------- backend --
deploy_backend() {
  local dir="$REPO_ROOT/backend"
  local remote_dir="$REMOTE_ROOT/xponent-api"

  step "Running the test suite before shipping backend code"
  ( cd "$dir" && php artisan test ) || die "tests failed — not deploying"

  step "Syncing source"
  # Deliberately narrow: application code and composer manifests only. Never
  # .env, never storage/, never vendor/ — the server installs its own.
  local archive="$dir/.deploy-src.tar.gz"
  rm -f "$archive"
  ( cd "$dir" && tar --warning=no-timestamp -czhf "$archive" app bootstrap config database resources routes composer.json composer.lock artisan )
  scp -o BatchMode=yes -q "$archive" "$SSH_HOST:/tmp/xponent-api.tar.gz"
  rm -f "$archive"

  remote "set -e
    cd '$remote_dir'
    cp composer.lock /root/xponent-composer.lock.pre-$STAMP
    tar -xzf /tmp/xponent-api.tar.gz -C '$remote_dir'
    rm -f /tmp/xponent-api.tar.gz
    chown -R www-data:www-data app bootstrap config database resources routes composer.json composer.lock artisan
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3
    php artisan migrate --force 2>&1 | tail -3
    php artisan config:cache >/dev/null && php artisan route:cache >/dev/null
    chown -R www-data:www-data bootstrap/cache
    # The worker holds the old code in memory until told otherwise.
    php artisan queue:restart >/dev/null 2>&1 || true
  "

  step "Health check"
  check_urls "backend" "$API_URL/up" "$API_URL/api/v1/settings" "$API_URL/api/v1/solutions" \
    || die "backend health check failed — investigate; source is already swapped"
}

# ---------------------------------------------------------------------- main --
preflight

case "$TARGET" in
  frontend) deploy_frontend ;;
  admin)    deploy_admin ;;
  backend)  deploy_backend ;;
  infra)    deploy_infra ;;
  # infra first: the queue worker must exist before code that queues into it.
  all)      deploy_infra; deploy_backend; deploy_admin; deploy_frontend ;;
esac

step "Post-deploy summary"
remote "systemctl is-active xponent-global-nuxt.service xponent-global-queue.service php8.3-fpm nginx | paste -sd' ' - | sed 's/^/    services: /'"
remote "cd '$REMOTE_ROOT/xponent-api' && php artisan tinker --execute='echo \"    queue: \".DB::table(\"jobs\")->count().\" pending, \".DB::table(\"failed_jobs\")->count().\" failed\".PHP_EOL;' 2>/dev/null | tail -1"

printf '\n%sDeployed %s at %s%s\n\n' "$GRN" "$TARGET" "$STAMP" "$OFF"
