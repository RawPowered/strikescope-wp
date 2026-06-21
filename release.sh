#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

VERSION="$(grep -E '^\s*\*\s*Version:' strikescope-wp.php | head -1 | sed -E 's/.*Version:[[:space:]]*([^[:space:]]+).*/\1/')"
if [ -z "$VERSION" ]; then
  echo "Could not read Version from strikescope-wp.php" >&2
  exit 1
fi

ZIP="strikescope-wp-${VERSION}.zip"
rm -f "$ZIP"

zip -r "$ZIP" \
  assets \
  config \
  docs \
  includes \
  languages \
  templates \
  admin \
  wp-org-assets \
  readme.txt \
  README.md \
  CHANGELOG.md \
  LICENSE \
  strikescope-wp.php \
  uninstall.php

echo "Created $ZIP"
