# StrikeScopeWP

WordPress plugin for contractor sites: multi-step lead forms, lightweight CRM, Help Desk, branded emails, and optional REST/webhooks.

[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](LICENSE)
[![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue)](https://wordpress.org/)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777)](https://www.php.net/)

## Features

- **Form** — React multi-step quote + Help Desk flow via `[strikescope_form]`
- **CRM** — Lead custom post type, statuses, map, CSV export
- **Help Desk** — Tickets, threaded email, inbound webhook support
- **Branding** — Logo, colors, phone, contact slug, merge tags (form + emails)
- **Integrations** — Cloudflare Turnstile, Google/Outlook calendar, API keys, outbound webhooks (all optional)

Data stays in your WordPress database unless you configure third-party services.

## Quick start

1. Install from **[Releases](https://github.com/GatoGodMode/strikescope-wp/releases)** (`strikescope-wp-x.x.x.zip`) or clone this repo and run `release.ps1` / `release.sh` after building assets.
2. **Plugins → Add New → Upload Plugin** → activate **StrikeScopeWP**.
3. Create a contact page with the shortcode:

   ```
   [strikescope_form]
   ```

4. **StrikeScopeWP → Branding** — company name, logo, colors, phone, contact page slug, footer text.
5. **StrikeScopeWP → Contact Form** — notification emails, Turnstile, templates, Help Desk.

## Configuration

| What | Where |
|------|--------|
| Logo, colors, phone, contact slug, footer | **StrikeScopeWP → Branding** |
| Notification emails, Turnstile, services, Help Desk | **StrikeScopeWP → Contact Form** |
| API keys, webhooks, calendar OAuth | **StrikeScopeWP → Integrations** / **API / Webhooks** |

Phone and contact page slug have a **single source of truth** in Branding (used by the form, emails, and auto-mount).

## Build from source

Requires **Node 20+** and **npm**.

```bash
# Build React bundle into assets/
./build-assets.sh          # Linux/macOS
./build-assets.ps1         # Windows PowerShell

# WordPress-ready zip (runtime files only — no frontend/ or node_modules/)
./release.sh
./release.ps1
```

## Releases & CI

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| [Distribution check](.github/workflows/distribution-check.yml) | push, PR | Blocks client-brand leaks and credential patterns |
| [Release](.github/workflows/release.yml) | tag `v*` | Builds assets, runs scrub, uploads `strikescope-wp-{version}.zip` to GitHub Releases |

Tag a release:

```bash
git tag v1.0.0
git push origin v1.0.0
```

## Privacy

Lead and ticket data are stored locally. Optional calls may go to Cloudflare Turnstile, OpenStreetMap Nominatim (geocoding), Google Maps links, calendar providers, or webhooks you configure. See [readme.txt](readme.txt) for the WordPress.org privacy summary.

## Docs

- [Branding & merge tags](docs/BRANDING.md)
- [WordPress.org submission checklist](docs/WORDPRESS_ORG.md)
- [Changelog](CHANGELOG.md)

## Author

**uncooluser** — [WordPress.org profile](https://profiles.wordpress.org/uncooluser/)

## Support

[Buy Me a Coffee](https://buymeacoffee.com/josephedwards)

## License

[GPL-2.0-or-later](LICENSE)
