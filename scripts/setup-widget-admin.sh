#!/bin/bash
# Setup admin password for widget unlocks
# Stores a salted SHA-256 hash at /etc/smartoversikt/widget_admin.hash with strict permissions
# Usage: sudo ./scripts/setup-widget-admin.sh

set -euo pipefail

HASH_DIR="/etc/smartoversikt"
HASH_FILE="${HASH_DIR}/widget_admin.hash"
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "${EUID}" -ne 0 ]; then
  echo -e "${RED}This script must be run as root (sudo).${NC}"
  exit 1
fi

if ! command -v sha256sum >/dev/null 2>&1 && ! command -v shasum >/dev/null 2>&1; then
  echo -e "${RED}Missing sha256 utility (sha256sum or shasum). Install coreutils.${NC}"
  exit 1
fi

mkdir -p "$HASH_DIR"
chmod 700 "$HASH_DIR"

# Prompt twice for the password
read -s -p "Set admin password: " PASS1; echo
read -s -p "Confirm admin password: " PASS2; echo
if [ "$PASS1" != "$PASS2" ]; then
  echo -e "${RED}Passwords do not match.${NC}"
  exit 1
fi

# Hash the password
if command -v sha256sum >/dev/null 2>&1; then
  HASH=$(printf '%s' "$PASS1" | sha256sum | awk '{print $1}')
else
  HASH=$(printf '%s' "$PASS1" | shasum -a 256 | awk '{print $1}')
fi
unset PASS1 PASS2

# Write the hash with strict permissions
umask 077
printf '%s\n' "$HASH" > "$HASH_FILE"
chmod 600 "$HASH_FILE"

# Test read back
if [ ! -s "$HASH_FILE" ]; then
  echo -e "${RED}Failed to write hash file: $HASH_FILE${NC}"
  exit 1
fi

echo -e "${GREEN}Admin password configured. Unlocks will now require password.${NC}"
