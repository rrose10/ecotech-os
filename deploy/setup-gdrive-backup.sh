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
  OUT=/var/tmp/gdrive-auth.out
  : > "$OUT"
  # run rclone in the background; its output (link, then the token) goes to $OUT
  ( rclone authorize drive >> "$OUT" 2>&1 & )
  sleep 2

  LOCAL=""
  i=0
  while [ $i -lt 20 ] && [ -z "$LOCAL" ]; do
    LOCAL=$(grep -o 'http://127.0.0.1:53682/auth?state=[A-Za-z0-9_-]*' "$OUT" 2>/dev/null | head -1 || true)
    [ -n "$LOCAL" ] || sleep 1
    i=$((i+1))
  done
  [ -n "$LOCAL" ] || { echo "rclone did not start. Its output:"; cat "$OUT"; pkill -x rclone 2>/dev/null; exit 1; }

  GURL=$(curl -s -o /dev/null -w '%{redirect_url}' "$LOCAL")
  [ -n "$GURL" ] || { echo "Could not get the Google sign-in link."; pkill -x rclone 2>/dev/null; exit 1; }

  echo
  echo "STEP A: copy this whole link, open it in your PC browser, sign in to Google and press Allow:"
  echo
  echo "$GURL"
  echo
  echo "STEP B: the browser will then show an error page (This site can't be reached)."
  echo "        That is expected. Copy the full address from the browser's address bar"
  echo "        (it starts with http://127.0.0.1:53682/?state=...) and paste it below."
  echo
  printf "Paste the address here and press Enter: "
  read -r BACK < /dev/tty
  BACK=$(printf '%s' "$BACK" | tr -d ' "\r')
  curl -s -o /dev/null "$BACK" || true

  i=0
  while [ $i -lt 30 ] && ! grep -q access_token "$OUT"; do sleep 1; i=$((i+1)); done
  pkill -x rclone 2>/dev/null || true
  grep access_token "$OUT" | head -1 > "$TOKEN_FILE" || true
  [ -s "$TOKEN_FILE" ] || { echo "No sign-in result received. rclone said:"; cat "$OUT"; }
  rm -f "$OUT"
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
