#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT/frontend"
npm ci
npm run build
cp dist/strikescope-form.js ../assets/js/
cp dist/strikescope-form.css ../assets/css/
echo "Built and copied strikescope-form.js / strikescope-form.css"
