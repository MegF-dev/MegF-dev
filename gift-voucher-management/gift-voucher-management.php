<?php
/**
 * Plugin Name: Gift Voucher Management
 * Plugin URI: https://www.warpdevelopment.com/
 * Description: Custom made plugin that allows the management of gift vouchers
 * Author: Warp Development
 * Version: 1.0.0
 * Author URI: https://www.warpdevelopment.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wpc
 * Requires PHP: 8.0
 * Requires at least: 6.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/classes/custom-post-type.php';
require_once __DIR__ . '/classes/orders-processing.php';
require_once __DIR__ . '/classes/custom-voucher.php';
require_once __DIR__ . '/classes/cart.php';
require_once __DIR__ . '/classes/emails.php';
require_once __DIR__ . '/classes/redeeming-voucher.php';
require_once __DIR__ . '/classes/reporting.php';

function add_jquery_ui() {
  wp_enqueue_script('jquery-ui-datepicker');
  wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
}
add_action('admin_enqueue_scripts', 'add_jquery_ui');

function enqueue_custom_js_css() {  
  wp_enqueue_style('voucher-styles', plugin_dir_url(__FILE__) . 'css/voucher-styles.css', array(), '1.0.0', 'all');
  wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . 'js/custom.js', array('jquery'), '', true );
  wp_localize_script('custom-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
  
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js_css');

function enqueue_sweetalert2() {  
  wp_enqueue_style('sweetalert2-css', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
  wp_enqueue_script('sweetalert2-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_sweetalert2');

function gift_voucher_admin_scripts($hook) {
  global $post_type;
  
  if (('edit.php' === $hook || 'post.php' === $hook || 'post-new.php' === $hook) && 'gift_vouchers' === $post_type) {
      // Correct handle is 'my-gift-voucher-admin-js', same as used in wp_enqueue_script
      wp_enqueue_script('my-gift-voucher-admin-js',  plugin_dir_url(__FILE__)  . '/js/gift-voucher-admin.js', array('jquery'), null, true);
      wp_localize_script('my-gift-voucher-admin-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
      ));
  }
}


add_action('admin_enqueue_scripts', 'gift_voucher_admin_scripts');

function custom_include_product_on_specific_page($query) {
  if (is_admin() || !is_page(34287)) {
      return;
  }

  $specific_product_id = 34277;

  $product_ids = $query->get('post__in');

  if (is_array($product_ids)) {
    
      $product_ids[] = $specific_product_id;
  } else {
     
      $product_ids = array($specific_product_id);
  }

  $query->set('post__in', $product_ids);
}




