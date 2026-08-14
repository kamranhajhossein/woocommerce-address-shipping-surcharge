=== WooCommerce Address Shipping Surcharge ===
Contributors: kamranhajhossein
Tags: woocommerce, shipping, surcharge, delivery zones, address
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Apply transparent WooCommerce shipping surcharges using address keywords, cities, and delivery zones.

== Description ==

WooCommerce Address Shipping Surcharge adds a configurable fee when a customer's delivery address matches one of your keyword rules. The most specific matching keyword wins. Debug logging is optional and never stores the full customer address.

The current release supports the classic WooCommerce cart and checkout. Checkout Blocks are not supported yet.

== Installation ==

1. Upload and activate the plugin.
2. Open WooCommerce > Address Shipping Surcharge.
3. Add one rule per line using keyword|amount.
4. Test checkout with matching and non-matching addresses.

== Changelog ==

= 1.0.2 =
* Removed project-specific names and default delivery areas.
* Added backward-compatible migration for existing settings.
* Added uninstall handling and WordPress.org documentation.
* Added automated PHP and security checks.

= 1.0.1 =
* Improved checkout address handling and privacy-safe logging.

= 1.0.0 =
* Initial release.
