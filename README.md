# WooCommerce Address Shipping Surcharge

Automatically apply transparent shipping surcharges based on customer address keywords, cities, and delivery zones in WooCommerce.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.5%2B-21759B?logo=wordpress&logoColor=white)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-8.0%2B-96588A?logo=woocommerce&logoColor=white)](https://woocommerce.com/)
[![License](https://img.shields.io/badge/License-GPL--2.0%2B-blue.svg)](LICENSE)

## Features

- Address keyword-based surcharges
- City and delivery-zone detection
- Real-time checkout recalculation
- Customizable surcharge rules
- Custom fee label
- Transparent fee display in cart and checkout
- Does not create a new shipping method
- Persian address normalization
- WooCommerce Classic Checkout support
- Privacy-conscious WooCommerce debug logs

## Rule format

Add one rule per line:

```text
چیتگر|200000
پاکدشت|300000
پردیس|300000
```

Format:

```text
keyword|surcharge_amount
```

Use the same currency unit configured in WooCommerce.

When multiple keywords match, the most specific (longest) keyword wins.

## Requirements

- WordPress 6.5 or newer
- WooCommerce 8.0 or newer
- PHP 7.4 or newer
- WooCommerce Classic Cart and Checkout

## Installation

1. Go to **Plugins > Add New > Upload Plugin**.
2. Upload the ZIP file.
3. Activate **WooCommerce Address Shipping Surcharge**.
4. Open its settings under WooCommerce.
5. Add your rules and save.
6. Test checkout with a matching address.

## Privacy

Debug logging is disabled by default. When enabled, the plugin logs the matched rule and surcharge amount without storing the customer's full address.

## Limitations

- The current release supports WooCommerce Classic Cart and Checkout.
- WooCommerce Checkout Blocks are not supported yet.

## Uninstall

Removing the plugin deletes its current settings and any settings migrated from releases before 1.0.2.

## Changelog

### 1.0.2

- Removed project-specific class names and default delivery areas
- Added backward-compatible settings migration
- Added WordPress.org metadata, uninstall handling, and automated checks

### 1.0.1

- Disabled debug logging by default
- Removed full customer addresses from debug logs
- Fixed shipping-address selection when a separate delivery address is used
- Removed hidden surcharge display mode for pricing transparency
- Added compatibility and privacy documentation

### 1.0.0

- Initial release

## Author

**Kamran Hajhossein**  
https://webrabin.com

## License

GPL v2 or later.
