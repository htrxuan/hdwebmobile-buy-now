# HDWebmobile Buy Now Button

Skip the cart with a direct Buy Now button -- price and quantity are always computed server-side from the real product, never accepted from the link.

- **WordPress.org:** https://wordpress.org/plugins/hdwebmobile-buy-now/
- **Requires:** WordPress 6.9+, WooCommerce, PHP 7.4+
- **License:** GPLv2 or later

## Description

Adds a "Buy Now" button next to Add to Cart on simple products. Clicking it clears the cart, adds exactly that one product, and takes the customer straight to checkout.

## Why this plugin exists

Several competing "Buy Now" / "Direct Checkout" plugins built their skip-the-cart link with the quantity -- and in at least one case a price override for a "quick deal" variant -- taken directly from the URL's own query string and used verbatim when adding the item to the cart (CWE-20, Improper Input Validation). A shopper could edit that link to request an unintended quantity or otherwise manipulate the price the cart ended up charging.

This plugin's Buy Now link carries only a product id -- there is no quantity or price parameter in its request handling at all:

* The quantity added to the cart is always the literal number 1, written directly into the code -- there is no variable that could carry a different value in from a link.
* The price is always whatever WooCommerce's own cart lookup finds for the real product at that moment -- never a value read from the request.
* For a variable product, a variation id is only ever accepted if it genuinely belongs to the product it's claimed for -- never trusted on its own.

## Features

* A Buy Now button next to Add to Cart on every simple product
* Clears the cart first, so checkout total always matches exactly what was clicked
* Customizable button text

## Limitations

* Simple products only in this version -- variable products need a variation chosen first
* No quantity selector by design -- always exactly one unit

## Installation

1. Upload to `/wp-content/plugins/hdwebmobile-buy-now`, or install through the WordPress plugins screen.
2. Activate. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Buy Now** to customize the button text.

## Development

```
includes/
  class-hdbn-activator.php   activation check
  class-hdbn-core.php        bootstraps admin + frontend
  class-hdbn-repository.php  settings + the ONLY place a Buy Now request is resolved -- see its docblock for the security design
  class-hdbn-admin.php       hub tab and settings form
  class-hdbn-frontend.php    the button + its request handler
  class-hdbn-hub.php         shared HDWebmobile admin hub (see the suite's other plugins)
```

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html
