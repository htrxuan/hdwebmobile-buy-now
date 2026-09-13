<?php

namespace htrxuan\hdbn;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the Buy Now button next to the normal Add to Cart control, and handles the resulting
 * request. Quantity is always the literal constant 1 -- there is no quantity field anywhere in
 * this plugin's markup or request handling. See HDBN_Repository's docblock for the security
 * design.
 */
final class HDBN_Frontend
{

    private static $instance = null;
    private static $button_done = false;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('template_redirect', array($this, 'maybe_handle_buy_now'), 5);
        add_action('woocommerce_after_add_to_cart_button', array($this, 'render_button'));
        add_filter('render_block', array($this, 'block_fallback'), 10, 2);
    }

    /**
     * The literal quantity added is the constant 1, written directly into the add_to_cart()
     * call below -- there is no variable anywhere in this method that could carry a different
     * value in from the request.
     */
    public function maybe_handle_buy_now()
    {
        if (is_admin() || empty($_GET['hdbn_buy_now'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- a plain "buy this one product" link (like any product-page URL); the quantity is a hardcoded constant below and the price is always WooCommerce's own lookup, so there is no state here for a nonce to meaningfully protect beyond what a normal Add to Cart link already allows.
            return;
        }

        $settings = HDBN_Repository::get_settings();
        if (empty($settings['enabled'])) {
            wp_safe_redirect(wc_get_page_permalink('shop'));
            exit;
        }

        $product_id   = absint(wp_unslash($_GET['hdbn_buy_now'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above.
        $variation_id = isset($_GET['hdbn_variation']) ? absint(wp_unslash($_GET['hdbn_variation'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above.

        $resolved = HDBN_Repository::resolve_product($product_id, $variation_id);
        if (!$resolved) {
            wc_add_notice(__('That product is not available to buy right now.', 'hdwebmobile-buy-now'), 'error');
            wp_safe_redirect(wc_get_page_permalink('shop'));
            exit;
        }

        WC()->cart->empty_cart();
        $added = WC()->cart->add_to_cart($resolved['product_id'], 1, $resolved['variation_id']);

        if (!$added) {
            wp_safe_redirect(get_permalink($resolved['product_id']));
            exit;
        }

        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }

    public function render_button()
    {
        global $product;
        if (self::$button_done || !$product instanceof \WC_Product) {
            return;
        }
        self::$button_done = true;
        $this->output_button($product);
    }

    public function block_fallback($block_content, $block)
    {
        $name = is_array($block) && isset($block['blockName']) ? $block['blockName'] : '';
        if ('woocommerce/add-to-cart-form' !== $name || !is_singular('product')) {
            return $block_content;
        }
        if (self::$button_done) {
            return $block_content;
        }
        global $product;
        if (!$product instanceof \WC_Product) {
            return $block_content;
        }
        self::$button_done = true;
        ob_start();
        $this->output_button($product);
        return $block_content . ob_get_clean();
    }

    private function output_button($product)
    {
        $settings = HDBN_Repository::get_settings();
        if (empty($settings['enabled']) || !$product->is_purchasable() || !$product->is_in_stock()) {
            return;
        }
        if ($product->is_type('variable')) {
            // Simple products only in this version -- a variable product needs a specific,
            // customer-chosen variation, which this plain-link button has no field for.
            return;
        }

        $url = add_query_arg('hdbn_buy_now', $product->get_id(), get_permalink($product->get_id()));
        echo '<a class="button alt hdbn-buy-now" href="' . esc_url($url) . '">' . esc_html($settings['button_text']) . '</a>';
    }
}
