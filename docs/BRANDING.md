# Branding

StrikeScopeWP stores branding in the `strikescope_branding` option.

## Settings

| Field | Used by |
|-------|---------|
| Company name | Form header fallback, email copy, `%%COMPANY_NAME%%` |
| Logo attachment ID | Form header, emails (`%%LOGO_URL%%`) |
| Colors (primary, accent, background, etc.) | Admin preview, email HTML merge tags, React CSS vars |
| Phone display | Form footer, emails (`%%PHONE%%`, `%%PHONE_DISPLAY%%`) — **single source** in Branding |
| Footer text | Email footer, form trust badge, `%%FOOTER_TEXT%%` |
| Contact page slug | Auto-mount detection + `%%CONTACT_PAGE_URL%%` — **single source** in Branding |

## Merge tags

Branding merge tags are applied in `StrikeScope_Branding::apply_merge_tags()` and wired into `strikescope_form_apply_merge_tags`.

Standard tags: `%%COMPANY_NAME%%`, `%%LOGO_URL%%`, `%%SITE_URL%%`, `%%CONTACT_PAGE_URL%%`, `%%COLOR_PRIMARY%%`, `%%COLOR_SECONDARY%%`, `%%COLOR_BACKGROUND%%`, `%%COLOR_BORDER%%`, `%%COLOR_MUTED%%`, `%%FOOTER_TEXT%%`, `%%PHONE_DISPLAY%%`.

Lead/ticket tags (from core): `%%FIRST_NAME%%`, `%%SERVICE_LIST%%`, etc.

## Frontend

`wp_localize_script` passes `logoUrl`, `companyName`, `footerText`, and `colors` (primary, accent, background, secondary, border, muted, text) to `window.strikeScopeForm` via the `strikescope_form_localize_config` filter. The React form applies these as CSS custom properties (`--slf-primary`, etc.) on `.strikescope-form-branded`.

## Defaults on activation

Neutral blue palette; company name defaults to site title; placeholder SVG logo until a media attachment is set.
