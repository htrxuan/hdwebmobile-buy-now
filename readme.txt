=== HDWebmobile Buy Now Button ===
Contributors: htrxuan
Donate link: https://paypal.me/htrxuan/20
Tags: woocommerce, buy now, direct checkout, skip cart, conversion
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
Requires Plugins: woocommerce
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Skip the cart with a direct Buy Now button -- price and quantity are always computed server-side from the real product, never accepted from the link.

== Description ==

HDWebmobile Buy Now Button adds a "Buy Now" button next to Add to Cart on simple products. Clicking it clears the cart, adds exactly that one product, and takes the customer straight to checkout.

= Why this plugin exists =
Several competing "Buy Now" / "Direct Checkout" plugins built their skip-the-cart link with the quantity -- and in at least one case a price override for a "quick deal" variant -- taken directly from the URL's own query string and used verbatim when adding the item to the cart (CWE-20, Improper Input Validation). A shopper could edit that link to request an unintended quantity or otherwise manipulate the price the cart ended up charging.

This plugin's Buy Now link carries only a product id -- there is no quantity or price parameter in its request handling at all:

* The quantity added to the cart is always the literal number 1, written directly into the code -- there is no variable that could carry a different value in from a link.
* The price is always whatever WooCommerce's own cart lookup finds for the real product at that moment -- never a value read from the request.
* For a variable product, a variation id is only ever accepted if it genuinely belongs to the product it's claimed for -- never trusted on its own.

= Key Features =
* A Buy Now button next to Add to Cart on every simple product
* Clears the cart first, so checkout total always matches exactly what was clicked
* Customizable button text

= Limitations (please read before installing) =
* Simple products only in this version -- variable products need a variation chosen first
* No quantity selector by design -- always exactly one unit

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hdwebmobile-buy-now` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress. WooCommerce must already be installed and active.
3. Go to **WooCommerce > HDWebmobile > Buy Now** to customize the button text.

== How to Use ==

= 1. It just appears =
The Buy Now button shows automatically on every purchasable simple product.

= 2. One click to checkout =
Clicking it takes the customer straight to checkout with exactly that product in their cart.

== Screenshots ==

1. The Buy Now button on a product page.

== Changelog ==

= 1.0.0 =
* Initial release: a direct-to-checkout Buy Now button, with quantity and price always resolved server-side.
