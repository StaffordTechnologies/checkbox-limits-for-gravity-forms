=== Gravity Forms Checkbox Limits ===
Contributors: staffordtechnologies
Donate link: https://staffordtechnologies.net/
Plugin page: https://staffordtechnologies.net/checkbox-limiter/
Tags: gravity forms, checkbox, validation, limits, min max
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add minimum and maximum selection limits to Gravity Forms checkbox fields, with front-end disabling and per-page server validation.

== Description ==

Gravity Forms Checkbox Limits is a free add-on from Stafford Technologies, built on the Gravity Forms Add-On Framework. It adds three settings to every checkbox field in the form editor:

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

== Installation ==

1. Ensure Gravity Forms 2.5+ is installed and active.
2. Go to Plugins → Add New → Upload Plugin.
3. Upload `gf-checkbox-limits-1.0.1.zip` (or `gf-checkbox-limits.zip`) and click Install Now, then Activate.
4. Edit a form, open a Checkbox field, set Min selections / Max selections (and optional message), then Update the form.

== Frequently Asked Questions ==

= Does this change my form structure? =

No. Limits are stored as custom properties on existing checkbox fields. No fields are added or deleted.

= What if I only set a minimum? =

Server-side validation enforces the minimum. The front-end only disables boxes when a maximum is set.

= Multi-page forms? =

Yes. Validation runs only for checkbox fields on the page being submitted (`gform_source_page_number_{formId}`).

= Where do I get support? =

Email Stafford Technologies helpdesk at helpdesk@staffordtechnologies.net.
Plugin docs and support page: https://staffordtechnologies.net/checkbox-limiter/
Company site: https://staffordtechnologies.net/

== Support ==

Stafford Technologies

* Email: helpdesk@staffordtechnologies.net
* Web: https://staffordtechnologies.net/
* Phone: (540) 684-5547

Plugin docs and support: https://staffordtechnologies.net/checkbox-limiter/
Email: helpdesk@staffordtechnologies.net
Full documentation is also included in this readme.txt (Docs and Support on the WordPress Plugins screen link to the plugin page).

== Changelog ==

= 1.0.1 =
* Full Stafford Technologies branding (author, URIs, license header).
* Plugin icons in assets/ (icon-128x128.png, icon-256x256.png) from the official Stafford brand mark.
* Docs and Support links on the Plugins screen (plugin_row_meta).
* Expanded readme with support, FAQ, and branding details.

= 1.0.0 =
* Initial release: field settings, front-end max enforcement, per-page server validation, plugin setting for default message.
