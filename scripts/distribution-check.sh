#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
EXT='php|html|js|css|tsx|ts|json|md|txt|svg|sh'
PATTERNS=(
  'sunprise'
  'sunprise\.com'
  '888-419'
  'rare6'
  'Oldsmar'
  'Houston Office'
  'CVC57137'
  'C:\\Users\\'
  'BEGIN (RSA |OPENSSH |EC )?PRIVATE KEY'
  'sk_live_'
  'sk_test_'
  'ghp_'
  'AIza[0-9A-Za-z_-]{35}'
)

hits=0
while IFS= read -r -d '' file; do
  rel="${file#"$ROOT"/}"
  case "$rel" in
    frontend/package-lock.json) continue ;;
    scripts/distribution-check.sh) continue ;;
  esac
  for pattern in "${PATTERNS[@]}"; do
    if grep -qiE "$pattern" "$file"; then
      echo "FORBIDDEN: $rel matches /$pattern/i"
      hits=$((hits + 1))
      break
    fi
  done
done < <(
  find "$ROOT" -type f -regextype posix-egrep -regex ".*\.($EXT)$" \
    ! -path "*/frontend/node_modules/*" \
    ! -path "*/node_modules/*" \
    ! -path "*/.git/*" \
    -print0
)

if [ "$hits" -gt 0 ]; then
  echo "Distribution check failed with $hits hit(s)."
  exit 1
fi
echo "Distribution check OK"
