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
# Destinations: the shared folder named "office" (any case) on any data disk, plus every data disk root.
DESTS=""
for o in /srv/dev-disk-by-*/[Oo]ffice /srv/dev-disk-by-*/[Oo]ffice[-_]*; do
  [ -d "\$o" ] && DESTS="\$DESTS \$o"
done
for d in /srv/dev-disk-by-*; do
  mountpoint -q "\$d" 2>/dev/null && DESTS="\$DESTS \$d"
done
for base in \$DESTS; do
  dest="\$base/ecotech-backups"
  mkdir -p "\$dest"
  cp -u "\$SRC"/*.sql "\$SRC"/*.tar.gz "\$dest/" 2>/dev/null || true
  # keep the 60 newest copies per destination (database dumps and upload archives separately)
  ls -1t "\$dest"/*.sql 2>/dev/null | tail -n +61 | while read f; do rm -f "\$f"; done
  ls -1t "\$dest"/*.tar.gz 2>/dev/null | tail -n +61 | while read f; do rm -f "\$f"; done
done
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
ls -la /srv/dev-disk-by-*/ecotech-backups/ /srv/dev-disk-by-*/[Oo]ffice*/ecotech-backups/ 2>/dev/null || true
