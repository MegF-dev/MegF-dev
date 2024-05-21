<?php
class redeeming_voucher {
    public function __construct() {        
        add_action('woocommerce_before_checkout_form', [$this,'custom_voucher_form_html']);
        add_action('wp_ajax_validate_voucher_code', [$this,'validate_voucher_code']);
        add_action('wp_ajax_nopriv_validate_voucher_code',  [$this, 'validate_voucher_code']);       
        add_action('woocommerce_cart_calculate_fees', [$this,'apply_voucher_discount'], 10, 1);        
        add_action('woocommerce_thankyou',[$this,'update_voucher_status_or_value'] , 10, 1);
        add_action('woocommerce_checkout_update_order_meta', [$this,'update_voucher_remaining_balance_on_checkout']);
        add_action('woocommerce_review_order_before_order_total', [$this,'display_remaining_voucher_balance']);
        add_action( 'woocommerce_review_order_before_submit', [$this,'custom_zero_order_message_after_payment_methods'] );
        add_action('wp_ajax_remove_voucher_code', [$this,'remove_voucher_code']);
        add_action('wp_ajax_nopriv_remove_voucher_code',  [$this, 'remove_voucher_code']);        
        add_filter('woocommerce_cart_totals_fee_html', [$this, 'add_voucher_remove_link'], 10, 2);
    
    }

   
    public function custom_voucher_form_html() {
        ?>
        <div class="woo-checkout-coupon voucher-redeem" style="background-color:#eaf2f0 !important">
            <div class="ea-coupon-icon" style="margin-left: 20px;    margin-top: 10px;">
            <i class="fa fa-gift" aria-hidden="true"></i>
            </div>
    
            <div class="woocommerce-form-coupon-toggle">
                <div style="font-size: 17px;margin-left: 20px;    margin-top: 10px;" class="woocommerce-info">
                    Do you have a Gift Voucher?
                    <a style="text-decoration: none;color: #4D776D!important; font-size: 16px;" href="#" class="showvoucher"></a>
                </div>
            </div>
    
            <form class="checkout_voucher woocommerce-form-voucher" method="post" style="margin-left: 20px;margin-top: 20px;color: black;opacity: 1;">    
                <p class="form-row form-row-first" style="width: 78%;margin-bottom: 20px;">
                    <input style="box-shadow: 0 6px 10px rgb(0 0 0 / 10%);" type="text" name="voucher_code" class="input-text" placeholder="Enter Your Voucher Code To Redeem" id="voucher_code" value="" />
                </p>
    
                <p class="voucher-submit form-row form-row-last" style="display: flex;justify-content: left;width: 20%;">
                    <button type="submit" class="button" name="apply_voucher" value="Apply Voucher">
                        Apply Voucher
                    </button>
                </p>
    
                <div class="clear"></div>
            </form>
        </div>
        <?php
    }
    public function validate_voucher_code() {
        $voucherCode = isset($_POST['code']) ? sanitize_text_field($_POST['code']) : '';
        $voucherCode = strtolower($voucherCode); 
       
        $args = array(
            'post_type' => 'gift_vouchers',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
      
        $response = array('status' => 'invalid', 'value' => 0);
        $query = new WP_Query($args);
      
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $title = strtolower(get_the_title());           
                if ($title === $voucherCode) {

                    $isRedeemed = get_post_meta(get_the_ID(), 'is_redeemed', true);
                    $expiryDate = get_post_meta(get_the_ID(), 'gift_voucher_expiry', true);

                  $voucherValue = get_post_meta(get_the_ID(), 'gift_voucher_value', true);
                  $currentDate = current_time('Y-m-d');

                if ($voucherValue <= 0 || $isRedeemed === 'yes') {
                    $response['status'] = 'redeemed';
                    break;
                }

                if ($expiryDate < $currentDate) {
                    $response['status'] = 'expired';
                    break;
                }

                  WC()->session->set('applied_voucher_value', $voucherValue);
                  WC()->session->set('applied_voucher_code', $voucherCode);            
                  $response = array('status' => 'valid', 'value' => floatval($voucherValue));
                  
                    break;
                }
            }
            wp_reset_postdata(); 
        }
      
        wp_send_json($response); 
        wp_die();
      }
      
   
    public function apply_voucher_discount($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
    
        $voucher_code = WC()->session->get('applied_voucher_code');
        $voucher_value = WC()->session->get('applied_voucher_value');
    
        if (empty($voucher_code) || empty($voucher_value)) return;
    
        $cart_total_before_voucher = $cart->cart_contents_total + $cart->shipping_total - $cart->get_discount_total();
        $voucher_value_excl_tax = $voucher_value / 1.15; 
    
        if ($voucher_value_excl_tax >= $cart_total_before_voucher) {
            $discount_amount = $cart_total_before_voucher;
            $remaining_balance = $voucher_value - ($cart_total_before_voucher * 1.15);
        } else {
            $discount_amount = $voucher_value_excl_tax;
            $remaining_balance = 0;
        }
    
        $discount_amount = round($discount_amount, 2);
        $cart->add_fee('Voucher Applied', -$discount_amount, true, 'zero-rate');
    
        $remaining_balance = round($remaining_balance, 0);
        WC()->session->set('voucher_remaining_balance', $remaining_balance);
        
    }
    public function display_remaining_voucher_balance() {
        $remaining_balance = WC()->session->get('voucher_remaining_balance');
        
        
    
        if ($remaining_balance > 0) {
            $formatted_balance = wc_price($remaining_balance);
            echo '<div class="remaining-container"><p class="voucher-remaining-balance">Remaining Voucher Balance</p><p>' . $formatted_balance . '</p></div>';
        }
    }
    
   
    public function custom_zero_order_message_after_payment_methods() {
        
        if ( WC()->cart->total == 0 ) {
            echo '<div class="order-message">Order total is R0.00 therefore payment methods are not applicable. Please proceed by clicking "Place Order".</div>';
        }
    }

public function update_voucher_status_or_value($order_id) {
    $order = wc_get_order($order_id);
    $voucher_code = WC()->session->get('applied_voucher_code');
    $remaining_voucher_balance = WC()->session->get('voucher_remaining_balance');

    if (!empty($voucher_code)) {
        $voucher_code = strtolower($voucher_code); 

        $voucher_posts = get_posts([
            'post_type' => 'gift_vouchers',
            'name' => $voucher_code, 
            'post_status' => 'publish',
            'numberposts' => 1
        ]);

        if (!empty($voucher_posts)) {
            $voucher_post = $voucher_posts[0];
            $voucher_value = get_post_meta($voucher_post->ID, 'gift_voucher_value', true);

            if (!empty($remaining_voucher_balance) && $remaining_voucher_balance > 0) {
                
                update_post_meta($voucher_post->ID, 'is_redeemed', 'partially');
              
                
                update_post_meta($voucher_post->ID, 'gift_voucher_value', $remaining_voucher_balance);
            } elseif ($order->get_total_tax() <= 0) {
             
                update_post_meta($voucher_post->ID, 'is_redeemed', 'yes');
            }
        }

        WC()->session->__unset('applied_voucher_value');
        WC()->session->__unset('applied_voucher_code');
        WC()->session->__unset('voucher_remaining_balance');
    }
}

  public function update_voucher_remaining_balance_on_checkout($order_id) {
   
    $voucher_code = WC()->session->get('applied_voucher_code');
    $remaining_balance = WC()->session->get('voucher_remaining_balance');
    
    $voucher_code = strtoupper($voucher_code);
    $args = array(
        'post_type' => 'gift_vouchers',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'gift_voucher_code',
                'value' => $voucher_code,
                'compare' => '='
            )
        )
    );

    $query = new WP_Query($args);

    
    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            update_post_meta($post_id, 'gift_voucher_value', $remaining_balance);
        }
    } else {
        
        error_log("No voucher found with code: $voucher_code");
    }

    wp_reset_postdata();
}
public function add_voucher_remove_link($fee_html, $fee) {
    if ($fee->name === 'Voucher Applied') {
        $remove_link = '<a href="" class="remove-voucher" data-voucher="' . esc_attr(WC()->session->get('applied_voucher_code')) . '">[Remove]</a>';
        $fee_html .= ' ' . $remove_link;
    }
    return $fee_html;
}

public function remove_voucher_code() {
    $voucher_code = isset($_POST['voucher_code']) ? sanitize_text_field($_POST['voucher_code']) : '';

    if (!empty($voucher_code)) {
        WC()->session->__unset('applied_voucher_value');
        WC()->session->__unset('applied_voucher_code');
        WC()->session->__unset('voucher_remaining_balance');
        
        WC()->cart->remove_coupon('Voucher Applied');
        WC()->cart->calculate_totals();

        wp_send_json(['status' => 'removed']);
    } else {
        wp_send_json(['status' => 'error']);
    }

    wp_die();
}
}

new redeeming_voucher();