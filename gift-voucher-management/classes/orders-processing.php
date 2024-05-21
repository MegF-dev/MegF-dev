<?php
class order_processing {

    public function __construct() {
        
        add_action('woocommerce_order_status_completed', [$this, 'create_gift_voucher_post_on_order_creation'], 10, 3);
        add_filter('woocommerce_order_item_display_meta_key', [$this, 'filter_woocommerce_order_item_display_meta_key'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item',[$this,'save_custom_fields_in_order_meta'], 10, 4 );
    
    }

    public function create_gift_voucher_post_on_order_creation($order_id, $order) {
        $items = $order->get_items();

        foreach ($items as $item) {
            $product_id = $item->get_product_id();

            if (has_term('vouchers', 'product_cat', $product_id)) {                

                $voucher_code = $this->generate_voucher_code_with_order_id($order_id);
                $purchase_date = $order->get_date_created()->format('Y/m/d');
                $expiry_date = $this->calculate_expiry_date($purchase_date);
                $custom_message = $item->get_meta('Custom Message', true);
                $recipient_name = $item->get_meta('Recipient Name', true);
                $recipient_address = $item->get_meta('Recipient Address', true);
                $sender_name = $item->get_meta('Sender Name', true);
                $voucher_before_tax_value = $item->get_total();
                $voucher_tax = $item->get_total_tax();
                $voucher_value = round($voucher_before_tax_value + $voucher_tax, 2);
                $voucher_post = array(
                    'post_title'    => $voucher_code,
                    'post_status'   => 'publish',
                    'post_type'     => 'gift_vouchers',
                    'meta_input'    => array(
                        'gift_voucher_code'    => $voucher_code,
                        'gift_voucher_value'             => $voucher_value,
                        'original_gift_voucher_value'    => $voucher_value,
                        'gift_voucher_expiry'  => $expiry_date,
                        'gift_voucher_message' => $custom_message,
                        'gift_voucher_recipient_name' => $recipient_name,
                        'gift_voucher_recipient_address' => $recipient_address,
                        'gift_voucher_sender_name' => $sender_name,
                    ),
                );

                $voucher_id = wp_insert_post($voucher_post);
                $item->update_meta_data('Gift Voucher Code', $voucher_code);
                $item->update_meta_data('gift_voucher_expiry', $expiry_date);
                $item->update_meta_data('voucher_post_type_id', $voucher_id);
                
                $item->save();
            }
        }
        do_action('voucher_order_completed', $order_id, $voucher_id);
    }
    public function filter_woocommerce_order_item_display_meta_key($display_key, $meta) {        
        if ('gift_voucher_expiry' === $meta->key) {
            
            $display_key = 'Gift Voucher Expiry Date';
        }
        return $display_key;
    }
    
    public function generate_voucher_code_with_order_id($order_id) {
        $randomPart = $this->generateRandomAlphaNumeric(4);

        return $randomPart . '-' . $order_id;
    }
  
    public function generateRandomAlphaNumeric($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
  
    public function calculate_expiry_date($purchase_date) {
        $expiry_period = get_option('gift_voucher_expiry_period', 3); 
        $purchase_date_obj = new DateTime($purchase_date);
        $purchase_date_obj->modify("+{$expiry_period} months");
        return $purchase_date_obj->format('Y/m/d');
    }


    public function save_custom_fields_in_order_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['recipient_name'])) {
            $item->add_meta_data('Recipient Name', $values['recipient_name']);
        }
        if (isset($values['recipient_address'])) {
            $item->add_meta_data('Recipient Address', $values['recipient_address']);
        }
        if (isset($values['sender_name'])) {
            $item->add_meta_data('Sender Name', $values['sender_name']);
        }
        if (isset($values['custom_voucher_message'])) {
            $item->add_meta_data('Custom Message', $values['custom_voucher_message']);
        }
    }
   

    
}

new order_processing();
