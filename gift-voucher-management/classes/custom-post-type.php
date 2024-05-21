<?php

class custom_post_type {

    public function __construct () {        
        add_action('init', [$this, 'create_gift_voucher_post_type']);
        add_action('admin_menu', [$this, 'gift_voucher_admin_menu']);
        add_action('admin_init', [$this, 'gift_voucher_admin_init']);
        add_action('admin_head', [$this,'hide_add_new_custom_type']);        
        add_action('add_meta_boxes', [$this, 'gift_voucher_add_meta_boxes']);
        add_action('admin_enqueue_scripts', [$this, 'gift_voucher_admin_scripts']);
        add_action('save_post', [$this, 'gift_voucher_save_postdata']);
        add_filter('manage_gift_vouchers_posts_columns', [$this,'add_gift_vouchers_columns']);
        add_action('manage_gift_vouchers_posts_custom_column',[$this,'gift_vouchers_custom_columns'], 10, 2 );
        add_action('wp_ajax_send_voucher_email',[$this, 'send_voucher_email_callback']);
        add_action('wp_ajax_nopriv_send_voucher_email',[$this, 'send_voucher_email_callback']);
        $this->register_hooks();
        
    }    

   

    public function create_gift_voucher_post_type() {
        $labels = array(
            'name'                  => _x('Gift Vouchers', 'Post Type General Name', 'text_domain'),
            'singular_name'         => _x('Gift Voucher', 'Post Type Singular Name', 'text_domain'),
            'menu_name'             => __('Gift Vouchers', 'text_domain'),
            'name_admin_bar'        => __('Gift Voucher', 'text_domain'),
            'archives'              => __('Voucher Archives', 'text_domain'),
            'attributes'            => __('Voucher Attributes', 'text_domain'),
            'parent_item_colon'     => __('Parent Voucher:', 'text_domain'),
            'all_items'             => __('All Vouchers', 'text_domain'),
            'add_new_item'          => __('Add New Voucher', 'text_domain'),
            'add_new'               => __('Add New', 'text_domain'),
            'new_item'              => __('New Voucher', 'text_domain'),
            'edit_item'             => __('Edit Voucher', 'text_domain'),
            'update_item'           => __('Update Voucher', 'text_domain'),
            'view_item'             => __('View Voucher', 'text_domain'),
            'view_items'            => __('View Vouchers', 'text_domain'),
            'search_items'          => __('Search Vouchers', 'text_domain'),
            'not_found'             => __('Not found', 'text_domain'),
            'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
            'featured_image'        => __('Featured Image', 'text_domain'),
            'set_featured_image'    => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image'    => __('Use as featured image', 'text_domain'),
            'insert_into_item'      => __('Insert into voucher', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this voucher', 'text_domain'),
            'items_list'            => __('Vouchers list', 'text_domain'),
            'items_list_navigation' => __('Vouchers list navigation', 'text_domain'),
            'filter_items_list'     => __('Filter vouchers list', 'text_domain'),
        );

        $args = array(
            'label'                 => __('Gift Voucher', 'text_domain'),
            'description'           => __('Gift Voucher Descriptions', 'text_domain'),
            'capability_type'       => 'post',
            'capabilities'          => array(
            'create_posts'          => true,),
            'map_meta_cap'          => true,
            'labels'                => $labels,
            'public' => true,
                'has_archive' => true,
                'menu_icon'           => 'dashicons-tickets-alt',
                'rewrite' => array('slug' => 'gift-vouchers'),
                'show_in_rest' => true, 
                'supports' => array('title')
        );

        register_post_type('gift_vouchers', $args);
    }

    public function gift_voucher_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=gift_vouchers', 
            'Gift Voucher Settings',           
            'Settings',                       
            'manage_options',                  
            'gift-voucher-settings',          
            [$this, 'gift_voucher_settings_page']    
        );
    }
    public function hide_add_new_custom_type() {
        $screen = get_current_screen();
    
        if ('gift_vouchers' == $screen->post_type) {
            echo '<style type="text/css">
               input[name="filter_action"] { display:none !important; 
            }
            .manage-column.column-gift_voucher_value,.manage-column.column-original_gift_voucher_value {
width:100px !important;
            }
                </style>';
        }
    }
    
  
    
    public function gift_voucher_settings_page() {
        ?>
        <style>
           
        .gift-voucher-settings-container {
            background-color: white; 
            padding: 10px 40px;
           
        }
        .voucher-settings-title {
            margin-bottom:0px !important;
            margin-left:30px !important;
        }
        .gift-voucher-settings-container  th {
            font-size:15px !important;
            font-family: Open-Sans, sans-serif !important;
            color:black !important;
        }
        .voucher-settings-form .button.button-primary {
            background-color: black;
            border-radius: 3px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            padding: 4px 15px;
            border: none;
            font-family: \'Open Sans\', Sans-serif;
            text-transform: capitalize;
            cursor: pointer;
        }
        </style>
        <div class="wrap">
            <h1 class="voucher-settings-title">Gift Voucher Settings</h1>
            <div class='gift-voucher-settings-container'>
                <form class="voucher-settings-form" method="post" action="options.php">
                    <?php
                    settings_fields('gift-voucher-options');
                    do_settings_sections('gift-voucher-settings');
                    submit_button();
                    ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function gift_voucher_admin_init() {
        register_setting('gift-voucher-options', 'gift_voucher_expiry_period');
        add_settings_section('gift-voucher-settings-section', null, null, 'gift-voucher-settings');
        add_settings_field('gift-voucher-expiry-period', 'Voucher Expiry Period (months)', [$this, 'gift_voucher_expiry_period_cb'], 'gift-voucher-settings', 'gift-voucher-settings-section');
    }

    public function gift_voucher_expiry_period_cb() {
        $expiry_period = get_option('gift_voucher_expiry_period', 3);
        echo "<input type='number' id='gift_voucher_expiry_period' name='gift_voucher_expiry_period' value='" . esc_attr($expiry_period) . "' />";
    }

    public function gift_voucher_admin_scripts($hook) {
        global $post_type;

        if ( 'gift_vouchers' === $post_type || 'post.php' === $hook || 'post-new.php' === $hook ) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        }
    }

    public function gift_voucher_add_meta_boxes() {
        add_meta_box(
            'gift_voucher_details',
            'Gift Voucher Details',
            [$this, 'gift_voucher_meta_box_callback'],
            'gift_vouchers'
        );
    }

  
 
    
    public function gift_voucher_meta_box_callback($post) {
   
        wp_nonce_field('gift_voucher_nonce_action', 'gift_voucher_nonce');

        $gift_voucher_code = get_post_meta($post->ID, 'Gift Voucher Code', true);
        $gift_voucher_value = get_post_meta($post->ID, 'gift_voucher_value', true);
        $original_gift_voucher_value = get_post_meta($post->ID, 'original_gift_voucher_value', true);       
        $date_created_display = get_the_date('Y/m/d', $post);
        $date_created = get_the_date('Y/m/d', $post->ID);
        $expiry_period = get_option('gift_voucher_expiry_period', 3);
        $custom_message = get_post_meta($post->ID, 'gift_voucher_message', true);
        $recipient_name = get_post_meta($post->ID, 'gift_voucher_recipient_name', true);
        $recipient_address = get_post_meta($post->ID, 'gift_voucher_recipient_address', true);
        $sender_name = get_post_meta($post->ID, 'gift_voucher_sender_name', true);
        $last_email_sent = get_post_meta($post->ID, 'last_email_sent', true);
        $last_email_sent_display = $last_email_sent ? $last_email_sent : 'Not sent yet';
        $is_redeemed = get_post_meta($post->ID, 'is_redeemed', true);
        $saved_expiry_date = get_post_meta($post->ID, 'gift_voucher_expiry', true);
        $current_date = new DateTime();
        
        if (!$saved_expiry_date) {        
            $date_created_obj = new DateTime($date_created);
            $date_created_obj->modify("+$expiry_period months");
            $saved_expiry_date = $date_created_obj->format('Y/m/d');
            update_post_meta($post->ID, 'gift_voucher_expiry', $saved_expiry_date);
        }


        $expiry_date_obj = new DateTime($saved_expiry_date);
        $is_redeemed = get_post_meta($post->ID, 'is_redeemed', true);
        $status  = '';
        if ($is_redeemed === 'yes') {
            $status = 'Redeemed';
            $class = 'redeemed-voucher';
        } elseif ($is_redeemed === 'partially') {
            $status = 'Partially Redeemed';
            $class = 'partially-redeemed-voucher';
        } elseif ($expiry_date_obj >= $current_date) {
            $status = 'Active';
            $class = 'active-voucher';
        } elseif ($expiry_date_obj < $current_date) {          
            $status = 'Expired';
            $class = 'expired-voucher';
        }

        if ($gift_voucher_value == "0") {
            $status = 'Redeemed';
            $class = 'redeemed-voucher';
        }
        

        echo '<style>    
    
        .gift-voucher-container {
            background-color: white; 
            padding: 10px 40px;        
        }
        .gift-voucher-table input {
            margin-left: 15px;
            width: 300px;
            height: 40px;
            font-size: 15px;
            margin-right: 45px;
        }
        .gift-voucher-table #gift_voucher_value {
            width: 80px;
        }
        #gift_voucher_expiry {
            margin-left:0;
            width: 110px;
        }
        .gift-voucher-table label,.gift-voucher-table th {
            cursor: default;
            color: black;
        }    
        .gift-voucher-table{
            font-family: Open-Sans, sans-serif;
            text-align: left;
            font-size: 15px;
            border-spacing: 0px 25px;
            color: black;
        }
        .gift-voucher-table td {
            display: flex;
            align-items: center;
        }         
        #custom_voucher_message {
            width: 300px;
            margin-left: 15px;
            height: 110px;
            border: 1px solid #bebebe;
            padding: 20px;
        
        }
       
        .active-voucher {
            margin-left: 20px;
            color: #5b841b;
            background: #c6e1c6;
            width: 80px;
            border-radius: 3px;
            padding-top: 5px;
            padding-bottom: 5px;
            text-align: center;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }        
        .expired-voucher {
            margin-left: 20px;
            color: #555555;
            background: #d6d6d6;
            width: 80px;
            border-radius: 3px;
            padding-top: 5px;
            padding-bottom: 5px;
            text-align: center;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .redeemed-voucher {
            margin-left: 20px;
            color: #1b4884;
            background: #c6cde1;
            width: 100px;
            border-radius: 3px;
            padding-top: 5px;
            padding-bottom: 5px;
            text-align: center;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .partially-redeemed-voucher {
            color: #be6208;
            background: #f4cdab;
            width: 170px;
            border-radius: 3px;
            padding-top: 5px;
            padding-bottom: 5px;
            text-align: center;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 35px;
        }
    
        @media (min-width: 1025px) and (max-width: 1380px) {
            .gift-voucher-table  td input {
            margin-right: 0px;
            margin-left:0px !important;
        }
    
        .gift-voucher-table td th label {
            margin-left: 0px  !important;
            margin-right: 45px !important;
        }
        .gift-voucher-container {        
            padding: 10px 10px;
        }
        .gift-voucher-table  #gift_voucher_value {
            width: 50px;
        }
    
        .gift-voucher-table #recipient_name,.gift-voucher-table #custom_voucher_message {
            width: 270px;            
            height: 110px;
            border: 1px solid #c7c7c7;
            margin-right: 35px;
        }
        .gift-voucher-table #recipient_address,.gift-voucher-table #sender_name {
            width: 250px;
        }
        .gift-voucher-table #date-created-label,.gift-voucher-table #status-label {
            margin-left:20px !important;
            margin-right:0px !important;
        }
        #date-expired-label,#email-sent-label {
            margin-left:0px !important
        }
        }
        #send-voucher-email {
            background-color: black;
            border-radius: 3px;
            color: white;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 15px;
            border: none;
            font-family: \'Open Sans\', Sans-serif;
            text-transform: capitalize;
            cursor: pointer;
            margin-left:35px
        }

        .email-success{
            
            margin-left: 25px;
            color: #5b841b;
            font-weight: 600;
        }
       .email-fail {
        margin-left: 25px;
        color: #c52424;
        font-weight: 600;
       }
        </style>';
        echo '<div class="gift-voucher-container">';
            
            ?>
            <?php wp_nonce_field('gift_voucher_nonce_action', 'gift_voucher_nonce'); ?>
                <div id="message-gift-voucher"></div>
                <?php
                echo '<table class="gift-voucher-table">
                        <tbody>';
            echo        '<tr>
                            <th style="vertical-align: baseline !important;"><label>Status:</label></th>
                                <td id="status-label" style="margin-left: 35px;" colspan="3" class="' . esc_attr($class) . '" style="margin-left: 20px;">' . esc_html($status) . '</td>
                        </tr>';
            echo        '<tr>
                        <th>
                            <label for="gift_voucher_value">Current Gift Voucher Value (R):</label>
                        </th>
                            <td>
                            <input style="margin-left: 35px;" id="gift_voucher_value" name="gift_voucher_value" value="' . esc_attr($gift_voucher_value) . '" >
                            </td>
                            </tr>
                            <tr>
                        <th>
                            <label>Original Voucher Value (R):</label>
                        </th>
                        <td>
                        <input style="margin-left: 35px;" id="gift_voucher_value" name="original_gift_voucher_value" value="' . esc_attr($original_gift_voucher_value) . '" >
                        </td>
                          
        
            </tr>';
                
                      
                echo    '<tr>
                            <th class="sendor-heading">
                                <label for="recipient_name">Recipient Name:</label>
                            </th>
                            
                           <td>
                            <input style="margin-left: 35px;" type="text" id="recipient_name" name="recipient_name" value="' . esc_attr($recipient_name) . '" >
                            </td>
                           </tr>
                           <tr>
                            <th class="sendor-heading">
                                <label for="recipient_address">Recipient Address:</label>
                            </th>
                            <td>
                                <input style="margin-left: 35px;" type="text" id="recipient_address" name="recipient_address" value="' . esc_attr($recipient_address) . '" >                               
                            </td>
                        </tr>';

                echo '
                <tr>
                <th class="sendor-heading">
                    <label for="sender_name">Sender Name:</label>
                </th>
                <td>
                <input style="margin-left: 35px;" type="text" id="sender_name" name="sender_name" value="' . esc_attr($sender_name) . '" >
                </td>
                  
               
            </tr>
                <tr>
                            <th class="custom-message-heading">
                                <label for="custom_voucher_message">Custom Message:</label>
                            </th>
                            <td>
                            <textarea style="margin-left: 35px;" id="custom_voucher_message" name="custom_voucher_message" >' . esc_textarea($custom_message) . '</textarea>
                              
                            </td>
                            </tr>
                           ';
                      

                    echo    '  <tr>
                        <th>
                            <label>Date Created:</label>
                        </th>
                            <td id="date-created-label" style="margin-left: 35px;margin-right: 45px !important;">' . esc_html($date_created_display) . '</td>
                            </tr>
                            <tr>
                            <th>
                            <label>Date Expired</label>
                        </th>
                        <td style="margin-left: 35px;">
                        <input type="text" id="gift_voucher_expiry" name="gift_voucher_expiry" value="' . esc_attr($saved_expiry_date) . '" />
                    </td>
                          </tr>';
                          ?>
          <script type="text/javascript">
              jQuery(document).ready(function($) {
                  $('#gift_voucher_expiry').datepicker({
                      dateFormat: 'yy-mm-dd' 
                  });
              });
          </script>
          <?php
                    echo    '<tr>
                    <th class="custom-message-heading">
                        <label>Last Email Notification:</label>
                    </th>
                        <td id="email-sent-label" style="margin-left: 35px;">'. esc_html($last_email_sent_display) .'<button id="send-voucher-email"  data-post-id="' . get_the_ID() . '">Send Email</button>
                        <span class="email-success">Email Sent Successfully</span>
                        <span class="email-fail">Email Failed to Send</span>
                        </td>
                        
                    </tr>';
                    
                echo '</tbody>
                </table>';
        echo '</div>';

    }


    public function gift_voucher_save_postdata($post_id) {    


        
        if (get_post_type($post_id) !== 'gift_vouchers') {
            return;
        }
        if (!isset($_POST['gift_voucher_nonce']) || !wp_verify_nonce($_POST['gift_voucher_nonce'], 'gift_voucher_nonce_action')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }  
        if (isset($_POST['Gift Voucher Code'])) {
            update_post_meta($post_id, 'Gift Voucher Code', sanitize_text_field($_POST['Gift Voucher Code']));
            error_log('Saved voucher code: ' . $_POST['Gift Voucher Code']);
        }
        if (array_key_exists('gift_voucher_value', $_POST)) {
            update_post_meta(
                $post_id,
                'gift_voucher_value',
                sanitize_text_field($_POST['gift_voucher_value'])
            );
        }
        if (array_key_exists('original_gift_voucher_value', $_POST)) {
            update_post_meta(
                $post_id,
                'original_gift_voucher_value',
                sanitize_text_field($_POST['original_gift_voucher_value'])
            );
        }
        if (isset($_POST['gift_voucher_expiry'])) {
            update_post_meta(
                $post_id,
                'gift_voucher_expiry',
                sanitize_text_field($_POST['gift_voucher_expiry'])
            );
        }
        if (array_key_exists('recipient_name', $_POST)) {
            update_post_meta(
                $post_id,
                'gift_voucher_recipient_name',
                sanitize_text_field($_POST['recipient_name'])
            );
        }        
        if (array_key_exists('recipient_address', $_POST)) {
            update_post_meta(
                $post_id,
                'gift_voucher_recipient_address',
                sanitize_text_field($_POST['recipient_address'])
            );
        }    
        if (array_key_exists('sender_name', $_POST)) {
            update_post_meta(
                $post_id,
                'gift_voucher_sender_name',
                sanitize_text_field($_POST['sender_name'])
            );
        }    
        if (array_key_exists('custom_voucher_message', $_POST)) {
            update_post_meta(
                $post_id,
                'gift_voucher_message', 
                sanitize_text_field($_POST['custom_voucher_message'])
            );
        }
        
           
    }
    


    public function add_gift_vouchers_columns($columns) {
        $columns['title'] = 'Voucher Code';
        $date_column = $columns['date'];
        unset($columns['date']);
        $columns['gift_voucher_value'] = 'Current Voucher Value';
        $columns['original_gift_voucher_value'] = 'Original Voucher Value';
        $columns['recipient_email'] = 'Recipient Email';
        $columns['recipient_name'] = 'Recipient Name';
        $columns['sender_name'] = 'Sender Name';
        $columns['status'] = 'Status';
        $columns['date'] = $date_column;
        return $columns;
    }


    public function gift_vouchers_custom_columns($column, $post_id) {
        switch ($column) {
            
                case 'gift_voucher_value':
                    echo  get_post_meta($post_id, 'gift_voucher_value', true);
                    break;
                case 'original_gift_voucher_value':
                    echo get_post_meta($post_id, 'original_gift_voucher_value', true);
                    break;
                    case 'sender_name':
                        echo get_post_meta($post_id, 'gift_voucher_sender_name', true);
                        break;
            case 'recipient_email':
                echo get_post_meta($post_id, 'gift_voucher_recipient_address', true);
                break;
            case 'recipient_name':
                echo get_post_meta($post_id, 'gift_voucher_recipient_name', true);
                break;
                
            case 'status':
                    $is_redeemed = get_post_meta($post_id, 'is_redeemed', true);
                    $get_value =  get_post_meta($post_id, 'gift_voucher_value', true);
                
                    if ($is_redeemed === 'yes' || $get_value == "0") {
                        echo 'Redeemed';
                    } elseif ($is_redeemed === 'partially') {
                        echo 'Partially Redeemed';
                    } else {                        
                        $expiry_date = get_post_meta($post_id, 'gift_voucher_expiry', true);
                        $expiry_date_obj = new DateTime($expiry_date);
                        $current_date = new DateTime();
                        $status = $expiry_date_obj >= $current_date ? 'Active' : 'Expired';
                        
                        echo esc_html($status);
                    }
                break;
                
        }
    }
    
   
    public function register_hooks() {
        
        add_action('redeem_gift_voucher', array($this, 'set_voucher_value_to_zero'), 10, 1);
    }
    public function set_voucher_value_to_zero($post_id) {
    
        update_post_meta($post_id, 'gift_voucher_value', '0');    
        update_post_meta($post_id, 'is_redeemed', 'yes');
    }

    public function send_voucher_email_callback() {
          $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
          
          if ($post_id > 0) {
            
              if (method_exists($this, 'trigger_send_email')) {
                  $this->trigger_send_email($post_id);
                  wp_send_json_success('Email has been sent!');
              } else {
                  wp_send_json_error('Function not found.');
              }
          } else {
              wp_send_json_error('Invalid post ID.');
          }
      }
      public function trigger_send_email($post_id) {
        $post = get_post($post_id);

        if (!$post) {
            error_log('Invalid post ID: ' . $post_id);
        return;
        }
        $recipient_email = get_post_meta($post_id, 'gift_voucher_recipient_address', true);
        $recipient_name = get_post_meta($post_id, 'gift_voucher_recipient_name', true);
        $custom_message = get_post_meta($post_id, 'gift_voucher_message', true);
        $sender_name = get_post_meta($post_id, 'gift_voucher_sender_name', true);
        $voucher_code = get_the_title($post_id);
        $expiry = get_post_meta($post_id, 'gift_voucher_expiry', true);   
        $gift_voucher_value = get_post_meta($post->ID, 'gift_voucher_value', true);
        
        if (!empty($sender_name)) {
            $sender_info = "<span style=\"font-size: 16px; line-height: 25.6px; font-family: 'Open Sans', sans-serif;\">From " . esc_html($sender_name) . "</span>";
        } else {
            $sender_info = "";
        }
        $subject = 'Your Gift Voucher from ' . get_bloginfo('name');
        $message = '<html
        xmlns="http://www.w3.org/1999/xhtml"
        xmlns:v="urn:schemas-microsoft-com:vml"
        xmlns:o="urn:schemas-microsoft-com:office:office"
      >
        <head>
          <!--[if gte mso 9]>
            <xml>
              <o:OfficeDocumentSettings>
                <o:AllowPNG />
                <o:PixelsPerInch>96</o:PixelsPerInch>
              </o:OfficeDocumentSettings>
            </xml>
          <![endif]-->
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          <meta name="x-apple-disable-message-reformatting" />
          <!--[if !mso]><!-->
          <meta http-equiv="X-UA-Compatible" content="IE=edge" />
          <!--<![endif]-->
          <title></title>
      
          <style type="text/css">
            @media only screen and (min-width: 620px) {
              .u-row {
                width: 600px !important;
              }
              .u-row .u-col {
                vertical-align: top;
              }
      
              .u-row .u-col-100 {
                width: 600px !important;
              }
            }
      
            @media (max-width: 620px) {
              .u-row-container {
                max-width: 100% !important;
                padding-left: 0px !important;
                padding-right: 0px !important;
              }
              .u-row .u-col {
                min-width: 320px !important;
                max-width: 100% !important;
                display: block !important;
              }
              .u-row {
                width: 100% !important;
              }
              .u-col {
                width: 100% !important;
              }
              .u-col > div {
                margin: 0 auto;
              }
            }
            body {
              margin: 0;
              padding: 0;
            }
      
            table,
            tr,
            td {
              vertical-align: top;
              border-collapse: collapse;
            }
      
            p {
              margin: 0;
            }
      
            .ie-container table,
            .mso-container table {
              table-layout: fixed;
            }
      
            * {
              line-height: inherit;
            }
      
            a[x-apple-data-detectors="true"] {
              color: inherit !important;
              text-decoration: none !important;
            }
      
            table,
            td {
              color: #000000;
            }
            #u_body a {
              color: #0000ee;
              text-decoration: underline;
            }
            @media (max-width: 480px) {
              #u_content_image_2 .v-src-width {
                width: auto !important;
              }
              #u_content_image_2 .v-src-max-width {
                max-width: 90% !important;
              }
              #u_content_heading_1 .v-container-padding-padding {
                padding: 40px 40px 10px !important;
              }
              #u_content_button_2 .v-size-width {
                width: 50% !important;
              }
            }
          </style>
      
          
          <link
            href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap"
            rel="stylesheet"
            type="text/css"
          />
          <link
            href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap"
            rel="stylesheet"
            type="text/css"
          />
          <link
            href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap"
            rel="stylesheet"
            type="text/css"
          />
         
        </head>
      
        <body
          class="clean-body u_body"
          style="
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            background-color: #b8ccc1;
            color: #000000;
          "
        >
          <table
            id="u_body"
            style="
              border-collapse: collapse;
              table-layout: fixed;
              border-spacing: 0;
              mso-table-lspace: 0pt;
              mso-table-rspace: 0pt;
              vertical-align: top;
              min-width: 320px;
              margin: 0 auto;
              background-color: #b8ccc1;
              width: 100%;
            "
            cellpadding="0"
            cellspacing="0"
          >
            <tbody>
              <tr style="vertical-align: top">
                <td
                  style="
                    word-break: break-word;
                    border-collapse: collapse !important;
                    vertical-align: top;
                  "
                >
                  <div
                    class="u-row-container"
                    style="padding: 0px; background-color: transparent"
                  >
                    <div
                      class="u-row"
                      style="
                        margin: 0 auto;
                        min-width: 320px;
                        max-width: 600px;
                        overflow-wrap: break-word;
                        word-wrap: break-word;
                        word-break: break-word;
                        background-color: transparent;
                      "
                    >
                      <div
                        style="
                          border-collapse: collapse;
                          display: table;
                          width: 100%;
                          height: 100%;
                          background-color: transparent;
                        "
                      >
                        <div
                          class="u-col u-col-100"
                          style="
                            max-width: 320px;
                            min-width: 600px;
                            display: table-cell;
                            vertical-align: top;
                          "
                        >
                          <div
                            style="
                              background-color: #b8ccc1;
                              height: 100%;
                              width: 100% !important;
                            "
                          >
                          <div
                              style="
                                box-sizing: border-box;
                                height: 100%;
                                padding: 0px;
                                border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-right: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                              "
                            >
                              <table
                                id="u_content_image_2"                                    
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                width="100%"
                                border="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      class="v-container-padding-padding"                                        
                                      align="left"
                                    >
                                      <table
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                      >
                                        <tr>
                                          <td
                                            style="
                                              padding-right: 0px;
                                              padding-left: 0px;
                                            "
                                            align="center"
                                          >
                                            <img
                                              align="center"
                                              border="0"
                                              
                                              src="https://binuns.warpdemo.co.za/wp-content/uploads/2023/01/binuns-custom-voucher-600x600.png"
                                              alt="Gift Voucher"
                                              title="Gift Voucher"
                                              style="
                                                outline: none;
                                                text-decoration: none;
                                                -ms-interpolation-mode: bicubic;
                                                clear: both;
                                                display: inline-block !important;
                                                border: none;
                                                height: auto;
                                                float: none;
                                                width: 50%;
                                                max-width: 390px;
                                                margin-bottom: 35px;
                                                margin-top: 35px;
                                              "
                                              width="390"
                                              class="v-src-width v-src-max-width"
                                            />
                                          </td>
                                        </tr>
                                      </table>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
      
                              <table
                                id="u_content_heading_1"                                    
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                width="100%"
                                border="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      class="v-container-padding-padding"
                                      style="
                                        overflow-wrap: break-word;
                                        word-break: break-word;
                                        padding: 10px 10px 10px;                                            
                                      align="left"
                                    >
                                      <h1
                                        style="
                                          margin: 0px;
                                          color: #000000;
                                          line-height: 140%;
                                          text-align: center;
                                          word-wrap: break-word;
                                          font-family: \'Open Sans\', sans-serif;
                                          font-size: 24px;
                                          font-weight: 400;
                                        "
                                      >
                                        <strong>';
                                            $message .= 'You Received a R' . esc_html($gift_voucher_value);
                                        
                                        
                                        $message .= ' Binuns Gift Voucher!</strong>
                                      </h1>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>        

                                                              <table
                                style="font-family: \'Open Sans\', sans-serif"
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                width="100%"
                                border="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      class="v-container-padding-padding"
                                      style="
                                        overflow-wrap: break-word;
                                        word-break: break-word;
                                        padding: 10px 10px;
                                        font-family: \'Open Sans\', sans-serif;
                                      "
                                      align="left"
                                    >
                                      <div
                                        style="
                                          font-size: 14px;
                                          color: #000000;
                                          line-height: 160%;
                                          text-align: center;
                                          word-wrap: break-word;
                                        "
                                      >
                                        <p style="font-size: 14px; line-height: 160%">
                                          <span
                                            style="
                                              font-size: 18px;
                                              line-height: 22.4px;
                                              font-family: \'Open Sans\', sans-serif;
                                            "
                                            >' . esc_html($custom_message) . '</span
                                          >
                                        </p>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
      
                              <table
                                style="font-family: \'Open Sans\', sans-serif"
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                width="100%"
                                border="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      class="v-container-padding-padding"
                                      style="
                                        overflow-wrap: break-word;
                                        word-break: break-word;
                                        padding: 10px 50px;
                                        font-family: \'Open Sans\', sans-serif;
                                      "
                                      align="left"
                                    >
                                      <div
                                        style="
                                          font-size: 14px;
                                          color: #000000;
                                          line-height: 160%;
                                          text-align: center;
                                          word-wrap: break-word;
                                        "
                                      >
                                      <p style=\"font-size: 14px; line-height: 160%\">' . $sender_info . '</p>
                                    
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
      
                           
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p style="    margin-top: 15px;
                  text-align: center;
                  margin-bottom: 15px;
                  font-family: \'Open Sans\', sans-serif;
                  font-weight: 400;">Use your personal voucher code at checkout</p>
                  <div
                    class="u-row-container"
                    style="padding: 0px; background-color: transparent"
                  >
                    <div
                      class="u-row"
                      style="
                        margin: 0 auto;
                        min-width: 320px;
                        max-width: 600px;
                        overflow-wrap: break-word;
                        word-wrap: break-word;
                        word-break: break-word;
                        background-color: transparent;
                      "
                    >
                      <div
                        style="
                          border-collapse: collapse;
                          display: table;
                          width: 100%;
                          height: 100%;
                          background-color: transparent;
                        "
                      >
                        <div
                          class="u-col u-col-100"
                          style="
                            max-width: 320px;
                            min-width: 600px;
                            display: table-cell;
                            vertical-align: top;
                          "
                        >
                          <div
                            style="
                              background-color: #000000;
                              height: auto !important;
                              width: 50% !important;
                              margin: 0 auto !important;
                              border-radius: 0px;
                              -webkit-border-radius: 0px;
                              -moz-border-radius: 0px;
                            "
                          >
                           <div
                              style="
                                box-sizing: border-box;
                                height: auto !important;
                                padding: 0px;
                                border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-right: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-radius: 0px;
                                -webkit-border-radius: 0px;
                                -moz-border-radius: 0px;
                              "
                            >
                              <table
                                style="    margin-bottom: 40px;font-family: \'Open Sans\', sans-serif"
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                width="100%"
                                border="0"
                              >
                                <tbody>
                                  <tr>
                                    <td
                                      class="v-container-padding-padding"
                                      style="
                                        overflow-wrap: break-word;
                                        word-break: break-word;
                                        padding: 15px;
                                        font-family: \'Open Sans\', sans-serif;
                                      "
                                      align="left"
                                    >
                                      <div
                                        style="
                                          font-size: 14px;
                                          color: #ffffff;
                                          line-height: 140%;
                                          text-align: center;
                                          word-wrap: break-word;
                                        "
                                      >
                                      
                                        <p style="font-size: 14px; line-height: 140%">
                                          
                                          <span
                                            style="
                                              font-size: 16px;
                                              line-height: 22.4px;
                                            "
                                            ><strong>'. esc_html($voucher_code) .'</strong></span
                                          >
                                        </p>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>       
                             
                            </div>                               
                          </div>
                          <table
                          id="u_content_button_2"
                          style="font-family: \'Open Sans\', sans-serif"
                          role="presentation"
                          cellpadding="0"
                          cellspacing="0"
                          width="100%"
                          border="0"
                        >
                          <tbody>
                            <tr>
                              <td
                                class="v-container-padding-padding"
                                style="
                                  overflow-wrap: break-word;
                                  word-break: break-word;
                                  padding: 0px 10px 40px;
                                  font-family: \'Open Sans\', sans-serif;
                                "
                                align="left"
                              >
                                <div align="center">
                                  <a
                                    href="https://www.binuns.co.za"
                                    target="_blank"
                                    class="v-button v-size-width"
                                    style="
                                      box-sizing: border-box;
                                      display: inline-block;
                                      text-decoration: none;
                                      -webkit-text-size-adjust: none;
                                      text-align: center;
                                      color: #000000;
                                      background-color: #ffffff;
                                      border-radius: 0px;
                                      -webkit-border-radius: 0px;
                                      -moz-border-radius: 0px;
                                      width: 38%;
                                      max-width: 100%;
                                      overflow-wrap: break-word;
                                      word-break: break-word;
                                      word-wrap: break-word;
                                      mso-border-alt: none;
                                      border-top-color: #000000;
                                      border-top-style: solid;
                                      border-top-width: 1px;
                                      border-left-color: #000000;
                                      border-left-style: solid;
                                      border-left-width: 1px;
                                      border-right-color: #000000;
                                      border-right-style: solid;
                                      border-right-width: 1px;
                                      border-bottom-color: #000000;
                                      border-bottom-style: solid;
                                      border-bottom-width: 1px;
                                      font-size: 14px;
                                    "
                                  >
                                    <span
                                      style="
                                        display: block;
                                        padding: 10px 20px;
                                        line-height: 120%;
                                      "
                                      ><strong
                                        ><span
                                          style="
                                            font-size: 14px;
                                            line-height: 16.8px;
                                          "
                                          >Shop Now</span
                                        ></strong
                                      ></span
                                    >
                                  </a>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                          <table style="width: 100%;    margin-bottom: 30px;">
                          <tbody>
                            <td
                              style="
                              font-family: \'Open Sans\', sans-serif;
                                color: #000000;
                                text-align: center;
                                font-size: 12px;
                              "
                            >
                              Voucher valid until the '. esc_html($expiry) .'.
                              T&Cs Apply.
                            </td>
                          </tbody>
                        </table>
                        </div>                          
                      </div>
                    </div>
                  </div>          
                </td>
              </tr>
            </tbody>
          </table>            
        </body>
      </html>
      ';

        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (wp_mail($recipient_email, $subject, $message, $headers)) {
            error_log('Email successfully sent to ' . $recipient_email);
        } else {
            error_log('Failed to send email to ' . $recipient_email);
        }
    

    }
       
}
new custom_post_type();