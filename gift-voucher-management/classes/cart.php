<?php
class custom_cart {
    public function __construct() {
       
        add_filter('woocommerce_add_cart_item_data', [$this, 'handle_custom_voucher_fields_to_cart_item'], 10, 3);
        add_filter('woocommerce_get_item_data', [$this, 'display_custom_fields_in_cart'], 10, 2);
        add_filter('woocommerce_cart_item_quantity', [$this,'customize_voucher_quantity_display'], 10, 3);    
        add_filter('woocommerce_add_cart_item_data', [$this,'add_custom_voucher_price_to_cart_item'], 10, 3);
        add_action('woocommerce_before_calculate_totals',[$this,'set_custom_voucher_price_for_cart_item'] , 10, 1);
    }

    public function handle_custom_voucher_fields_to_cart_item($cart_item_data, $product_id, $variation_id) {        
        if (isset($_POST['recipient_name'])) {
            $cart_item_data['recipient_name'] = sanitize_text_field($_POST['recipient_name']);
        }

        if (isset($_POST['recipient_address'])) {
            $cart_item_data['recipient_address'] = sanitize_text_field($_POST['recipient_address']);
        }

        if (isset($_POST['sender_name'])) {
            $cart_item_data['sender_name'] = sanitize_text_field($_POST['sender_name']);
        }

        if (isset($_POST['custom_voucher_message'])) {
            $cart_item_data['custom_voucher_message'] = sanitize_text_field($_POST['custom_voucher_message']);
        }

        return $cart_item_data;
    }

    public function display_custom_fields_in_cart($item_data, $cart_item) {
        
        if (isset($cart_item['recipient_name']) && !empty($cart_item['recipient_name'])) {
            $item_data[] = array('name' => '<strong>Recipient Name</strong>', 'value' => sanitize_text_field($cart_item['recipient_name']));
        }

        if (isset($cart_item['recipient_address']) && !empty($cart_item['recipient_address'])) {
            $item_data[] = array('name' => '<strong>Recipient Address</strong>', 'value' => sanitize_text_field($cart_item['recipient_address']));
        }

        if (isset($cart_item['sender_name']) && !empty($cart_item['sender_name'])) {
            $item_data[] = array('name' => '<strong>Sender Name</strong>', 'value' => sanitize_text_field($cart_item['sender_name']));
        }

        if (isset($cart_item['custom_voucher_message']) && !empty($cart_item['custom_voucher_message'])) {
            $item_data[] = array('name' => '<strong>Custom Message</strong>', 'value' => sanitize_text_field($cart_item['custom_voucher_message']));
        }

        return $item_data;
    }
    public function customize_voucher_quantity_display($product_quantity, $cart_item_key, $cart_item) {
    
        if (has_term('vouchers', 'product_cat', $cart_item['product_id'])) {
            return '1'; 
        }
    
        
        return $product_quantity;
    }
     
public function add_custom_voucher_price_to_cart_item($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['custom_voucher_price']) && !empty($_POST['custom_voucher_price'])) {
        $cart_item_data['custom_voucher_price'] = (float) sanitize_text_field($_POST['custom_voucher_price']);
    }
    return $cart_item_data;
}
      
public function set_custom_voucher_price_for_cart_item($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_voucher_price'])) {
            $cart_item['data']->set_price($cart_item['custom_voucher_price']);
        }
    }
}
      
}

new custom_cart();






