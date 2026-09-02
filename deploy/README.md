# Server-side configuration

Files here run on the VPS but live **outside** the application directories, so nothing in a normal
diff reveals them. They are tracked so the running server can be reconstructed, reviewed and
audited from the repository rather than from memory.

These are the exact files installed on the server — fetched back from it, not written from
recollection.

| Repo path | Installed to | Owner/mode |
|-----------|--------------|------------|
| `systemd/xponent-backup.service` | `/etc/systemd/system/` | `root:root 644` |
| `systemd/xponent-backup.timer` | `/etc/systemd/system/` | `root:root 644` |
| `systemd/xponent-global-queue.service` | `/etc/systemd/system/` | `root:root 644` |
| `bin/xponent-backup.sh` | `/usr/local/bin/` | `root:root 700` |

## Installing or updating

```bash
./scripts/deploy.sh infra
```

That copies each file, fixes ownership and permissions, reloads systemd, restarts the queue worker
and reports the state of both units. It is safe to re-run — nothing is destructive, and it never
touches `.env` or backup artefacts.

Run it whenever a file in this directory changes. Nothing else picks these up: application deploys
leave them alone entirely.

## What these do

**`xponent-global-queue.service`** runs the Laravel queue worker. Notification mail depends on it —
`NewContactEnquiryMail` and `NewJobApplicationMail` implement `ShouldQueue`, so if this worker stops,
notifications are not lost (they accumulate in the `jobs` table) but they are not delivered either.
`--max-time=3600` makes the worker exit hourly so `Restart=always` brings it back with any newly
deployed code, which also caps memory growth.

**`xponent-backup.timer` / `.service` / `xponent-backup.sh`** take the nightly backup at 02:20 UTC:
the database, `backend/storage/app` (uploaded artwork and applicant CVs, which Laravel's nested
`.gitignore` excludes with a blanket `*`), and `.env` — restoring without `APP_KEY` leaves every
encrypted value unreadable. Dumps are kept 14 days, upload archives 7.

The script verifies each dump for gzip integrity *and* the `Dump completed` marker, so a truncated
dump fails loudly instead of sitting there looking valid.

> **Backups are on-box only.** This protects against a dropped table, a bad deploy or corruption.
> It does **not** protect against losing the VPS. An offsite copy still needs a destination.

## Not tracked here

- **`xponent-api/.env`** — holds live credentials and must never enter the repository.
  `backend/.env.example` documents its shape. The nightly backup includes a copy.
- **nginx site config** (`/etc/nginx/sites-available/xponent-global`) — pre-existing and not modified
  by this work. Worth tracking eventually, but adopting it should be a deliberate change of its own
  rather than a side effect.

## Checking state

```bash
systemctl status xponent-global-queue.service
systemctl list-timers xponent-backup.timer
journalctl -u xponent-backup.service -n 20
ls -la /var/backups/xponent-global
```
