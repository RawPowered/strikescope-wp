# Changelog

## 2026-06-20 — 1.0.0

- Initial public release of standalone StrikeScopeWP plugin
- Standalone plugin bootstrap with activation/deactivation/uninstall
- Full rename to `strikescope_*` identifiers and `strikescope/v1` REST namespace
- Branding admin (logo, colors, merge tags, preview)
- `[strikescope_form]` shortcode and bundled React assets; form UI colors driven by Branding admin CSS variables
- Privacy exporters/erasers for leads and Help Desk tickets
- Removed Florida Parcels integration; property lookup uses Google Maps only (US-wide)
- Phone and contact page slug consolidated under **StrikeScopeWP → Branding** (legacy Contact Form values migrate automatically)
- Distribution hardening: removed client-brand export artifacts, removed internal `tools/` from public repo, added CI distribution-check workflow, aligned release zip whitelist (`release.sh` / `release.ps1`)
