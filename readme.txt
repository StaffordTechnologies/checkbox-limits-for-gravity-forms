=== Checkbox Limits for Gravity Forms ===
Contributors: staffordtechnologies
Donate link: https://staffordtechnologies.net/
Tags: gravityforms, checkbox, validation, limits, forms
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add minimum and maximum selection limits to Gravity Forms checkbox fields, with front-end disabling and per-page server validation.

== Description ==

Checkbox Limits for Gravity Forms is a free add-on from Stafford Technologies. It is not affiliated with, endorsed by, or sponsored by Gravity Forms or Rocketgenius, Inc. Gravity Forms (version 2.5 or later) must be installed and active for this plugin to work.

It adds three settings to every checkbox field in the form editor:

* **Min selections** — require at least N checked boxes
* **Max selections** — allow at most N checked boxes (remaining boxes are disabled in the browser once the max is reached)
* **Selection limit message** — optional custom message with `{min}` and `{max}` placeholders

Features:

* Works with multi-page forms (validates only fields on the current page)
* Front-end enforcement re-applies after AJAX page renders (`gform_post_render` / init scripts)
* Server-side validation via `gform_validation`
* Optional global default validation message under Forms → Settings → Checkbox Limits
* No paid add-ons required
* Uninstall-safe (uses the Add-On Framework uninstall; stores limits as field properties on the form)

Author: Stafford Technologies — https://staffordtechnologies.net/
Plugin docs and support: https://staffordtechnologies.net/checkbox-limiter/
Support email: helpdesk@staffordtechnologies.net

== Installation ==

1. Ensure Gravity Forms 2.5+ is installed and active.
2. Go to Plugins → Add New → Upload Plugin, or install from the WordPress.org plugin directory.
3. Upload `checkbox-limits-for-gravity-forms-1.0.3.zip` (or search for Checkbox Limits for Gravity Forms) and click Install Now, then Activate.
4. Edit a form, open a Checkbox field, set Min selections / Max selections (and optional message), then Update the form.

== Frequently Asked Questions ==

= Does this require Gravity Forms? =

Yes. Gravity Forms 2.5 or later must be installed and active. This plugin is an add-on and will not load without it. It is not affiliated with Gravity Forms or Rocketgenius.

= Does this change my form structure? =

No. Limits are stored as custom properties on existing checkbox fields. No fields are added or deleted.

= What if I only set a minimum? =

Server-side validation enforces the minimum. The front-end only disables boxes when a maximum is set.

= Multi-page forms? =

Yes. Validation runs only for checkbox fields on the page being submitted (`gform_source_page_number_{formId}`).

= Is this free? =

Yes. Checkbox Limits for Gravity Forms is free ($0).

= Where do I get support? =

Email Stafford Technologies helpdesk at helpdesk@staffordtechnologies.net.
Plugin docs and support page: https://staffordtechnologies.net/checkbox-limiter/
Company site: https://staffordtechnologies.net/

== Changelog ==

= 1.0.3 =
* WordPress.org directory preparation: renamed plugin to "Checkbox Limits for Gravity Forms" (slug `checkbox-limits-for-gravity-forms`) so the name does not begin with the Gravity Forms trademark.
* Updated text domain to match the new slug.
* Updated readme for WordPress.org format; Tested up to WordPress 7.1.
* Moved plugin icons to SVN assets (not bundled in the plugin zip).
* Clarified that the plugin is not affiliated with Gravity Forms / Rocketgenius and requires Gravity Forms 2.5+.

= 1.0.2 =
* Security hardening: sanitize min/max/message on form save and at validation output; strip HTML from admin-entered messages; omit validation messages from front-end init scripts; harden POST checkbox counting against non-scalar values; absint page numbers.
* Removed support phone number from readme.
* Documented that the plugin is free ($0).

= 1.0.1 =
* Full Stafford Technologies branding (author, URIs, license header).
* Plugin icons (icon-128x128.png, icon-256x256.png) from the official Stafford brand mark.
* Docs and Support links on the Plugins screen (plugin_row_meta).
* Expanded readme with support, FAQ, and branding details.

= 1.0.0 =
* Initial release: field settings, front-end max enforcement, per-page server validation, plugin setting for default message.

== Upgrade Notice ==

= 1.0.3 =
WordPress.org naming and slug update. Functionally equivalent to 1.0.2 with branding and readme changes for directory listing. Requires Gravity Forms 2.5+.
