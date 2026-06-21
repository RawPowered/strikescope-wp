=== StrikeScopeWP ===
Contributors: uncooluser
Donate link: https://buymeacoffee.com/josephedwards
Tags: contact-form, crm, helpdesk, leads, webhooks
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Multi-step contact forms, lead CRM, Help Desk, and email automation.

== Description ==

StrikeScopeWP adds a modern multi-step contact form, lead management, Help Desk tickets, and notification emails to WordPress.

**Features**

* `[strikescope_form]` shortcode embed
* Lead CRM with statuses, map view, CSV export
* Help Desk with customer/staff email templates
* REST API namespace `strikescope/v1`
* Optional Cloudflare Turnstile, calendar OAuth, webhooks
* Branding settings for logo, colors, and email merge tags

== Installation ==

1. Upload the plugin zip via **Plugins → Add New → Upload Plugin**, or install from WordPress.org when available.
2. Activate the plugin.
3. Place `[strikescope_form]` on your contact page.
4. Set logo, colors, phone, and contact slug under **StrikeScopeWP → Branding**; configure notifications under **StrikeScopeWP → Contact Form**.

== Frequently Asked Questions ==

= Does the form call external services? =

Optional features may contact third parties when you configure them: Cloudflare Turnstile, geocoding/maps, Google Calendar or Outlook OAuth, and inbound email webhooks.

= Where is lead data stored? =

In your WordPress database as custom post types and post meta. Data is not sent to the plugin author.

== Privacy ==

This plugin stores contact form submissions (name, email, phone, address, project details) and optional Help Desk tickets in your WordPress database. Data is not sent to the plugin author. Optional integrations (Cloudflare Turnstile, Google Calendar, email inbound webhooks) may transmit data to third parties when configured by the site administrator.

Personal data export and erasure hooks are registered for leads and Help Desk tickets.

== Screenshots ==

1. StrikeScopeWP dashboard
2. Multi-step contact form
3. Help Desk ticket list

== Changelog ==

= 1.0.0 =
* Initial public release: forms, CRM, Help Desk, branding admin, REST API.

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
