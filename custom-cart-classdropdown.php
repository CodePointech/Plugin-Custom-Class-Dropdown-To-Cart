<?php
/**
 * Plugin Name: Custom Class Dropdown To Cart
 * Description: Adds a custom "Class" dropdown field to cart page. 
 * Version: 2.1
 * Author: CP Technologies
 * Text Domain: custom_class_dropdown_to_cart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include Admin Menu File
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/custom-class-dropdown-admin.php';
}

function is_class_selection_enabled() {
    $enabled = get_option('class_selection_enabled', 'no');
    return $enabled === 'yes';
}

if (is_class_selection_enabled()) {
    add_action('wp_enqueue_scripts', 'custom_class_dropdown_enqueue_scripts');
}

function custom_class_dropdown_enqueue_scripts() {
    wp_enqueue_script(
        'class-selection-script',
        plugins_url('assets/class-selection.js', __FILE__),
        array('jquery'),
        '1.1',
        true
    );

    wp_localize_script('class-selection-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

    wp_enqueue_style(
        'class-selection-style',
        plugins_url('assets/class-selection.css', __FILE__)
    );
}

// Add the AJAX action
add_action('wp_ajax_save_class_selection', 'save_class_selection_ajax');
add_action('wp_ajax_nopriv_save_class_selection', 'save_class_selection_ajax');

function save_class_selection_ajax() {
    if (is_class_selection_enabled() && isset($_POST['selected_class'])) {
        $selected_class = sanitize_text_field($_POST['selected_class']);
        WC()->session->set('selected_class', $selected_class);

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            WC()->cart->cart_contents[$cart_item_key]['selected_class'] = $selected_class;
        }
        WC()->cart->set_session(); // Save changes to the session

        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

// Save class selection in order meta
add_action('woocommerce_checkout_update_order_meta', 'save_class_selection_order_meta');
function save_class_selection_order_meta($order_id) {
    if (is_class_selection_enabled()) {
        $class_selection = WC()->session->get('selected_class');
        if (!empty($class_selection)) {
            update_post_meta($order_id, 'class_selection', $class_selection);
        }
    }
}

// Display class selection in admin order details
add_action('woocommerce_admin_order_data_after_billing_address', 'display_class_selection_in_admin_order', 10, 1);
function display_class_selection_in_admin_order($order) {
    if (is_class_selection_enabled()) {
        $class_selection = get_post_meta($order->get_id(), 'class_selection', true);
        if ($class_selection) {
            echo '<p><strong>' . __('Selected Class', 'woocommerce') . ':</strong> ' . esc_html($class_selection) . '</p>';
        }
    }
}

// Show class selection in customer order view
add_action('woocommerce_order_details_after_order_table', 'display_class_selection_in_customer_order', 10, 1);
function display_class_selection_in_customer_order($order) {
    if (is_class_selection_enabled()) {
        $class_selection = get_post_meta($order->get_id(), 'class_selection', true);
        if ($class_selection) {
            echo '<p><strong>' . __('Selected Class', 'woocommerce') . ':</strong> ' . esc_html($class_selection) . '</p>';
        }
    }
}

// Add custom class dropdown to cart
add_action('woocommerce_proceed_to_checkout', 'add_custom_class_dropdown_to_cart');

function add_custom_class_dropdown_to_cart() {
    if (is_cart() && is_class_selection_enabled()) {
        $default_option = get_option('class_dropdown_default_option', __('Select Your Class', 'custom_class_dropdown_to_cart'));
        $class_options = get_option('class_dropdown_options', []);
        ?>
        <div class="class-dropdown" style="margin-bottom: 20px;">
            <div class="custom-dropdown">
                <select name="class_selection" class="shop_table shop_table_responsive" id="class-selection" required>
                    <option value=""><?php echo esc_html($default_option); ?></option>
                    <?php foreach ($class_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }
}

// Redirect to cart if class not selected
add_action('template_redirect', 'redirect_to_cart_if_class_not_selected');

function redirect_to_cart_if_class_not_selected() {
    if (is_checkout() && !is_wc_endpoint_url('order-received') && is_class_selection_enabled()) {
        $selected_class = WC()->session->get('selected_class');
        if (null === $selected_class || '' === $selected_class) {
            wc_add_notice(__('Velg et kurs fra rullegardinmenyen før du går videre til kassen.', 'custom_class_dropdown_to_cart'), 'error');
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
}
