<?php

namespace htrxuan\hdbn;

if (!defined('ABSPATH')) {
    exit;
}

class HDBN_Admin
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        require_once HDBN_PLUGIN_DIR . 'includes/class-hdbn-hub.php';
        add_filter('hdwebmobile_hub_tabs', array($this, 'register_hub_tabs'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_hub_tabs($tabs)
    {
        $tabs['buy-now'] = array(
            'label'  => __('Buy Now', 'hdwebmobile-buy-now'),
            'order'  => 157,
            'render' => array($this, 'render_page'),
        );
        return $tabs;
    }

    public function register_settings()
    {
        register_setting('hdbn_settings_group', 'hdbn_settings', array('htrxuan\\hdbn\\HDBN_Repository', 'save_settings'));
    }

    public function render_page()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'hdwebmobile-buy-now'));
        }

        $settings = HDBN_Repository::get_settings();
        ?>
        <p><?php esc_html_e('Adds a Buy Now button next to Add to Cart on simple products, taking the customer straight to checkout with just that product. Quantity is always exactly one, and the price is always looked up fresh from the product itself -- there is nothing else for a shopper to select or submit.', 'hdwebmobile-buy-now'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('hdbn_settings_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable', 'hdwebmobile-buy-now'); ?></th>
                    <td><label><input type="checkbox" name="hdbn_settings[enabled]" value="1" <?php checked(!empty($settings['enabled'])); ?> /> <?php esc_html_e('Show the Buy Now button', 'hdwebmobile-buy-now'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hdbn_button_text"><?php esc_html_e('Button text', 'hdwebmobile-buy-now'); ?></label></th>
                    <td><input type="text" id="hdbn_button_text" name="hdbn_settings[button_text]" value="<?php echo esc_attr($settings['button_text']); ?>" style="width:100%;max-width:300px;" /></td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'hdwebmobile-buy-now')); ?>
        </form>
        <?php
    }
}
