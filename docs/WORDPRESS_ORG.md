# WordPress.org submission — StrikeScopeWP

## Prerequisites

1. WordPress.org account: [profiles.wordpress.org/uncooluser](https://profiles.wordpress.org/uncooluser/)
2. Plugin slug: **strikescope-wp** via [Plugin Developer Center](https://wordpress.org/plugins/developers/)
3. Clean release zip from `release.sh` or `release.ps1` with **Distribution check** CI passing

## SVN layout

```text
strikescope-wp/
  trunk/          # Plugin files (readme.txt Stable tag must match header)
  tags/1.0.0/     # Release snapshot
  assets/         # icon-256.png, banner-772x250.png, screenshot-*.png
```

Checkout:

```bash
svn co https://plugins.svn.wordpress.org/strikescope-wp
```

## Commit checklist

- [ ] `readme.txt` Donate link: `https://buymeacoffee.com/josephedwards`
- [ ] No phone-home telemetry
- [ ] GPL-compatible code only
- [ ] Admin forms use nonces + capability checks
- [ ] Privacy section present in readme.txt
- [ ] Run [Plugin Check](https://wordpress.org/plugins/plugin-check/) — fix ERROR-level findings
- [ ] Test `[strikescope_form]` and REST `strikescope/v1` on clean WP install
- [ ] Distribution check workflow green (see `.github/workflows/distribution-check.yml` for blocked patterns)

## External services (disclose in FAQ)

- Cloudflare Turnstile (`challenges.cloudflare.com`) — optional
- Geocoding / maps — optional
- Google Calendar / Outlook OAuth — optional
- Leaflet map tiles — optional CDN

## QA matrix (pre-submit)

| Test | Expected |
|------|----------|
| Activate | No PHP notices; **StrikeScopeWP** menu |
| Branding save | Preview updates |
| Shortcode page | Form renders |
| Lead submit | `strikescope_lead` CPT created |
| Help Desk toggle | Card show/hide |
| REST POST submit | 200 JSON response |
| Deactivate | Crons cleared |
| Distribution check | CI OK |

## Release build

```bash
cd frontend && npm ci && npm run build
cp dist/strikescope-form.js ../assets/js/
cp dist/strikescope-form.css ../assets/css/
cd .. && ./release.sh
```

Unzip the artifact and confirm it contains only plugin runtime files (no `frontend/`, `tools/`, or `node_modules/`).

Legacy production sites using the old Code Snippet deploy remain unchanged until separately migrated.
