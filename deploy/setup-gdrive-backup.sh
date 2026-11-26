#!/bin/sh
# Copies the accounts PDFs (backups/reports) to Google Drive with rclone, twice a day.
# Usage (as root, on the NAS):
#   sh deploy/setup-gdrive-backup.sh --authorize     (asks you to open a Google link in your browser)
#   sh deploy/setup-gdrive-backup.sh /tmp/gdrive-token.json   (token made elsewhere with "rclone authorize drive")
set -e

TOKEN_FILE=/tmp/gdrive-token.json
[ -n "$1" ] && [ "$1" != "--authorize" ] && TOKEN_FILE="$1"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/backups/reports"
CONF=/etc/ecotech-rclone.conf
REMOTE=gdrive
FOLDER="EcoTech-Accounts"

if ! command -v rclone >/dev/null 2>&1; then
  echo "Installing rclone..."
  ARCH=$(uname -m)
  case "$ARCH" in
    x86_64) RARCH=amd64 ;;
    aarch64|arm64) RARCH=arm64 ;;
    armv7l) RARCH=arm-v7 ;;
    *) RARCH="" ;;
  esac
  if [ -n "$RARCH" ] && command -v unzip >/dev/null 2>&1 && curl -fsSL -o /tmp/rclone.zip "https://downloads.rclone.org/rclone-current-linux-$RARCH.zip"; then
    rm -rf /tmp/rclone-unzip && mkdir -p /tmp/rclone-unzip
    unzip -q -o /tmp/rclone.zip -d /tmp/rclone-unzip
    install -m 755 /tmp/rclone-unzip/*/rclone /usr/local/bin/rclone
  else
    apt-get update -qq && apt-get install -y -qq rclone
  fi
fi

if [ "$1" = "--authorize" ]; then
  echo
  echo "Open the link below in your PC browser (the one that starts with http://127.0.0.1:53682),"
  echo "sign in to Google and press Allow. Then come back to this window."
  echo
  rclone authorize drive 2>&1 | tee /tmp/gdrive-auth.out
  grep access_token /tmp/gdrive-auth.out | head -1 > "$TOKEN_FILE"
  rm -f /tmp/gdrive-auth.out
fi

[ -s "$TOKEN_FILE" ] || { echo "Token file $TOKEN_FILE is missing or empty."; exit 1; }

rm -f "$CONF"
RCLONE_CONFIG="$CONF" rclone config create "$REMOTE" drive config_is_local false token "$(cat "$TOKEN_FILE")" >/dev/null
chmod 600 "$CONF"
rm -f "$TOKEN_FILE"

export RCLONE_CONFIG="$CONF"
rclone mkdir "$REMOTE:$FOLDER"
mkdir -p "$SRC"

# copy (never deletes anything in Drive); only new or changed files are uploaded
cat > /usr/local/bin/ecotech-gdrive-sync.sh <<SCRIPT
#!/bin/sh
export RCLONE_CONFIG=$CONF
[ -d "$SRC" ] || exit 0
rclone copy "$SRC" "$REMOTE:$FOLDER" --log-file=/var/log/ecotech-gdrive.log --log-level NOTICE
SCRIPT
chmod 755 /usr/local/bin/ecotech-gdrive-sync.sh

cat > /etc/cron.d/ecotech-gdrive <<CRON
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
15 5 * * * root /usr/local/bin/ecotech-gdrive-sync.sh
15 17 * * * root /usr/local/bin/ecotech-gdrive-sync.sh
CRON
chmod 644 /etc/cron.d/ecotech-gdrive

/usr/local/bin/ecotech-gdrive-sync.sh
echo "Google Drive backup is ready. Files in Drive folder '$FOLDER':"
rclone ls "$REMOTE:$FOLDER" | head -20 || true
echo "(upload runs at 05:15 and 17:15 NAS time; PDFs are created on the 1st of each month)"
