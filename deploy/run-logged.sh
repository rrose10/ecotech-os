#!/bin/sh
# Runs install.sh, shows everything on screen, and saves a copy of the output with the
# Super Admin passwords hidden, so the log can be shared safely.
LOG=/tmp/ecotech-install.log
sh "$(dirname "$0")/install.sh" "$@" 2>&1 | tee /dev/tty \
  | sed -E 's/^(\| (Rose|Shad|Tushar) .*\| )[^|]+\|$/\1(hidden) |/' > "$LOG"
