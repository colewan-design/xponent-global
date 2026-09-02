#!/usr/bin/env bash
#
# Nightly backup for xponent-global.
#
# Two things here are irreplaceable and neither lives in git:
#
#   1. The MariaDB database — every contact enquiry, job application,
#      newsletter subscriber and all CMS content. The seeder only recreates the
#      original fixtures, so a lost database means lost real data.
#   2. backend/storage/app — uploaded artwork and applicant CVs. Laravel's own
#      nested .gitignore excludes this tree with a blanket `*`, so a fresh clone
#      has none of it.
#
# The DB password is read out of the application's .env at run time rather than
# copied into a second file, so rotating it in one place keeps backups working.
# It is written to a mode-600 temp file for the duration of the dump so it never
# appears in the process list, and that file is removed on every exit path.

set -euo pipefail

APP_DIR="/var/www/xponent-global/xponent-api"
ENV_FILE="${APP_DIR}/.env"
DEST="/var/backups/xponent-global"
DB_KEEP_DAYS=14
FILES_KEEP_DAYS=7

STAMP="$(date +%Y%m%d-%H%M%S)"
TMP_CNF=""

cleanup() {
  [ -n "${TMP_CNF}" ] && [ -f "${TMP_CNF}" ] && rm -f "${TMP_CNF}"
  return 0
}
trap cleanup EXIT

fail() {
  echo "[xponent-backup] FAILED: $*" >&2
  exit 1
}

[ -r "${ENV_FILE}" ] || fail "cannot read ${ENV_FILE}"

# Last assignment wins, and surrounding single or double quotes are stripped —
# .env values are frequently quoted and mysqldump must get the raw value.
env_get() {
  sed -n "s/^${1}=//p" "${ENV_FILE}" \
    | tail -1 \
    | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'\$//"
}

DB_NAME="$(env_get DB_DATABASE)"
DB_USER="$(env_get DB_USERNAME)"
DB_PASS="$(env_get DB_PASSWORD)"
DB_HOST="$(env_get DB_HOST)"
DB_PORT="$(env_get DB_PORT)"

[ -n "${DB_NAME}" ] || fail "DB_DATABASE missing from .env"
[ -n "${DB_USER}" ] || fail "DB_USERNAME missing from .env"

mkdir -p "${DEST}"
chmod 700 "${DEST}"

TMP_CNF="$(mktemp)"
chmod 600 "${TMP_CNF}"
cat > "${TMP_CNF}" <<EOF
[client]
user=${DB_USER}
password=${DB_PASS}
host=${DB_HOST:-127.0.0.1}
port=${DB_PORT:-3306}
EOF

# ---------------------------------------------------------------- database ---
DB_TMP="${DEST}/.db-${STAMP}.sql.gz.partial"
DB_OUT="${DEST}/db-${STAMP}.sql.gz"

# --single-transaction gives a consistent snapshot without locking the site out.
mysqldump --defaults-extra-file="${TMP_CNF}" \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --events \
  --default-character-set=utf8mb4 \
  "${DB_NAME}" 2>/dev/null | gzip -9 > "${DB_TMP}" \
  || fail "mysqldump failed"

# A dump that ends early still produces a valid-looking file, so check both that
# gzip is intact and that the payload actually reaches the end-of-dump marker.
gzip -t "${DB_TMP}" || fail "database dump is not valid gzip"
zcat "${DB_TMP}" | tail -5 | grep -q "Dump completed" \
  || fail "database dump is truncated (no completion marker)"

mv "${DB_TMP}" "${DB_OUT}"
chmod 600 "${DB_OUT}"

# ------------------------------------------------------------------- files ---
FILES_TMP="${DEST}/.files-${STAMP}.tar.gz.partial"
FILES_OUT="${DEST}/files-${STAMP}.tar.gz"

tar -czf "${FILES_TMP}" -C "${APP_DIR}" storage/app \
  || fail "storage/app archive failed"
gzip -t "${FILES_TMP}" || fail "storage archive is not valid gzip"

mv "${FILES_TMP}" "${FILES_OUT}"
chmod 600 "${FILES_OUT}"

# --------------------------------------------------------------------- env ---
# Restoring needs the app's own configuration back, including APP_KEY — without
# it every encrypted value and existing session is unreadable.
ENV_OUT="${DEST}/env-${STAMP}.txt"
cp "${ENV_FILE}" "${ENV_OUT}"
chmod 600 "${ENV_OUT}"

# ---------------------------------------------------------------- rotation ---
# Only ever removes this script's own artefacts, by name pattern, and only after
# the new ones are safely in place.
find "${DEST}" -maxdepth 1 -name 'db-*.sql.gz'    -mtime "+${DB_KEEP_DAYS}"    -delete
find "${DEST}" -maxdepth 1 -name 'env-*.txt'      -mtime "+${DB_KEEP_DAYS}"    -delete
find "${DEST}" -maxdepth 1 -name 'files-*.tar.gz' -mtime "+${FILES_KEEP_DAYS}" -delete
# Any .partial left behind means a previous run died mid-write.
find "${DEST}" -maxdepth 1 -name '.*.partial' -mtime +1 -delete

DB_SIZE="$(du -h "${DB_OUT}" | cut -f1)"
FILES_SIZE="$(du -h "${FILES_OUT}" | cut -f1)"
TOTAL="$(du -sh "${DEST}" | cut -f1)"

echo "[xponent-backup] ok  db=${DB_SIZE}  files=${FILES_SIZE}  dir=${TOTAL}  (${STAMP})"
