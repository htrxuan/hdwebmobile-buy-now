<?php

namespace htrxuan\hdbn;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings and the ONLY place a Buy Now request is ever resolved into a real, purchasable
 * product.
 *
 * Several competing "Buy Now" / "Direct Checkout" plugins built their skip-the-cart link with
 * the quantity (and, in at least one case, a price override for a "quick deal" variant) taken
 * directly from the URL's own query string and used verbatim when adding the item to the cart
 * (CWE-20, Improper Input Validation). A shopper could edit that link to request a fractional
 * or zero quantity, or otherwise manipulate the price the cart ended up charging.
 *
 * This plugin's Buy Now link carries only a product id (and, for a variable product, a
 * variation id) -- there is no quantity or price parameter in its request surface at all.
 * resolve_product() is the only place that link is ever turned into something purchasable: the
 * quantity added to the cart is always the literal constant 1 (see HDBN_Frontend), and the
 * price is always whatever WooCommerce's own `WC_Cart::add_to_cart()` looks up from the real
 * product object -- never a value carried on the request.
 */
class HDBN_Repository
{
    public static function get_settings()
    {
        $defaults = array(
            'enabled'     => 1,
            'button_text' => __('Buy Now', 'hdwebmobile-buy-now'),
        );
        $settings = get_option('hdbn_settings', array());
        return wp_parse_args(is_array($settings) ? $settings : array(), $defaults);
    }

    public static function save_settings($input)
    {
        $settings = array(
            'enabled'     => !empty($input['enabled']) ? 1 : 0,
            'button_text' => isset($input['button_text']) && '' !== trim($input['button_text'])
                ? sanitize_text_field($input['button_text'])
                : self::get_settings()['button_text'],
        );
        update_option('hdbn_settings', $settings);
        return $settings;
    }

    /**
     * Resolves a (product_id, variation_id) pair into a real, currently-purchasable product to
     * buy -- or null if anything about the request doesn't check out. A variation id is only
     * ever accepted if it genuinely belongs to the given parent product; there is no trust of
     * "this variation id looks plausible" on its own.
     */
    public static function resolve_product($product_id, $variation_id = 0)
    {
        $product_id = absint($product_id);
        if (!$product_id) {
            return null;
        }

        $product = wc_get_product($product_id);
        if (!$product instanceof \WC_Product || !$product->is_purchasable() || !$product->is_in_stock()) {
            return null;
        }

        $variation_id = absint($variation_id);
        if ($variation_id) {
            if (!$product->is_type('variable')) {
                return null;
            }
            $variation = wc_get_product($variation_id);
            if (!$variation instanceof \WC_Product_Variation
                || $variation->get_parent_id() !== $product->get_id()
                || !$variation->is_purchasable()
                || !$variation->is_in_stock()
            ) {
                return null;
            }
            return array('product_id' => $product->get_id(), 'variation_id' => $variation->get_id());
        }

        if ($product->is_type('variable')) {
            // A variable product needs a specific, validated variation -- never guessed or
            // defaulted to "the first one" server-side.
            return null;
        }

        return array('product_id' => $product->get_id(), 'variation_id' => 0);
    }
}
