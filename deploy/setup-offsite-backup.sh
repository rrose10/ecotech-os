#!/bin/sh
# Installs a daily job that copies EcoTech OS database backups to every data disk on the NAS.
# Usage (as root, on the NAS):  sh deploy/setup-offsite-backup.sh
set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/backups"

cat > /usr/local/bin/ecotech-backup-sync.sh <<SCRIPT
#!/bin/sh
# Copy DB backups to each mounted data disk (/srv/dev-disk-by-*), keep them in sync.
SRC="$SRC"
[ -d "\$SRC" ] || exit 0
copy_to() {
  dest="\$1/ecotech-backups"
  mkdir -p "\$dest"
  cp -u "\$SRC"/*.sql "\$SRC"/*.tar.gz "\$dest/" 2>/dev/null || true
  # monthly accounts PDFs
  mkdir -p "\$dest/reports"
  cp -u "\$SRC"/reports/*.pdf "\$dest/reports/" 2>/dev/null || true
  # keep the 60 newest copies per destination (database dumps and upload archives separately)
  ls -1t "\$dest"/*.sql 2>/dev/null | tail -n +61 | while read f; do rm -f "\$f"; done
  ls -1t "\$dest"/*.tar.gz 2>/dev/null | tail -n +61 | while read f; do rm -f "\$f"; done
}
# Destinations: shared folder "Office" (disk sdb1) and "Backup Office" (backup disk sda1).
FOUND=0
for o in /srv/dev-disk-by-*/Office /srv/dev-disk-by-*/"Backup Office"; do
  if [ -d "\$o" ]; then copy_to "\$o"; FOUND=1; fi
done
# Fallback if neither shared folder exists: every mounted data disk.
if [ "\$FOUND" = 0 ]; then
  for d in /srv/dev-disk-by-*; do
    mountpoint -q "\$d" 2>/dev/null && copy_to "\$d"
  done
fi
SCRIPT
chmod 755 /usr/local/bin/ecotech-backup-sync.sh

# App backs up at 02:00 (container time, UTC); sync twice daily so the copy never lags long.
cat > /etc/cron.d/ecotech-backup <<CRON
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
30 3 * * * root /usr/local/bin/ecotech-backup-sync.sh
30 15 * * * root /usr/local/bin/ecotech-backup-sync.sh
CRON
chmod 644 /etc/cron.d/ecotech-backup

/usr/local/bin/ecotech-backup-sync.sh
echo "Offsite backup installed. Copies:"
ls -la /srv/dev-disk-by-*/Office/ecotech-backups/ "/srv/dev-disk-by-"*"/Backup Office/ecotech-backups/" 2>/dev/null || true
