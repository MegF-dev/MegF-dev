<?php
class emails {
    public function __construct() {
        
        add_action('woocommerce_order_status_completed', [$this,'send_email_to_recipient'], 11, 1);
        add_action('init', [$this, 'schedule_voucher_expiry_check']);
        add_action('check_voucher_expiry_daily', [$this, 'check_and_send_voucher_expiry_emails']);
    }
    public function schedule_voucher_expiry_check() {
        if (!wp_next_scheduled('check_voucher_expiry_daily')) {
            wp_schedule_event(time(), 'daily', 'check_voucher_expiry_daily');
        }
    }
  //   public function trigger_send_email() {
  //     error_log('trigger_send_email was called.');
  //     // Assuming you are supposed to send an email here, add more logs to ensure all steps are reached.
  // }
  
    public function send_email_to_recipient($order_id) {
        if (!$order_id) {
            return;
        }
    
        $order = wc_get_order($order_id);
    
       
        $contains_voucher = false;
        foreach ($order->get_items() as $item_id => $item) {
            if (has_term('vouchers', 'product_cat', $item->get_product_id())) {
                $contains_voucher = true;
                break;
            }
        }
    
        if (!$contains_voucher) {
            return; 
        }
    
        foreach ($order->get_items() as $item_id => $item) {
    
    
            if (has_term('vouchers', 'product_cat', $item->get_product_id())) {
    
              $product_id = $item->get_product_id();
              
              $recipient_email = $item->get_meta('Recipient Address', true);
              $recipient_name = $item->get_meta('Recipient Name', true);
              $custom_message = $item->get_meta('Custom Message', true);
              $sender_name = $item->get_meta('Sender Name', true);
              $voucher_code = $item->get_meta('Gift Voucher Code', true);
              $expiry = $item->get_meta('gift_voucher_expiry', true);
              $voucher_before_tax_value = $item->get_total();
              $voucher_tax = $item->get_total_tax();
              $voucher_value = round($voucher_before_tax_value + $voucher_tax);
  
            if (!empty($sender_name)) {
              $sender_info = "<span style=\"font-size: 16px; line-height: 25.6px; font-family: 'Open Sans', sans-serif;\">From " . esc_html($sender_name) . "</span>";
          } else {
              $sender_info = "";
          }
    
              $product = wc_get_product($product_id);
              $image_id = $product ? $product->get_image_id() : '';
              $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
            if (!empty($recipient_email)) {
                
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
                                                      
                                                      src="' . esc_url($image_url) . '"
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
                                                    $message .= 'You Received a R' . esc_html($voucher_value);
                                                
                                                
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
    
                wp_mail($recipient_email, $subject, $message,$headers);
            }
        }
        }
    }
    public function check_and_send_voucher_expiry_emails() {
  
        $args = [
            'post_type' => 'gift_vouchers',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'gift_voucher_expiry',
                    'value' => date('Y-m-d', strtotime("+6 months")), 
                    'compare' => '<=',
                    'type' => 'DATE'
                ]
            ]
        ];
      
        $query = new WP_Query($args);
      
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
      
                $voucher_id = get_the_ID();
               
                $expiry_date = get_post_meta($voucher_id, 'gift_voucher_expiry', true);
                $recipient_email = get_post_meta($voucher_id, 'gift_voucher_recipient_address', true);          
                $voucher_code_for_email = get_the_title($voucher_id);          
                $current_date = new DateTime();
                $expiry_date_obj = new DateTime($expiry_date);
                $interval = $current_date->diff($expiry_date_obj);
                $days_until_expiry = $interval->days;
                         
                if ($days_until_expiry == 14 || $days_until_expiry == 90 || $days_until_expiry == 180) {
                    error_log('there are' . $days_until_expiry);
                    $email_sent_key = 'email_sent_' . $days_until_expiry . '_days';
                  
                    if (!get_post_meta($voucher_id, $email_sent_key, true)) {   
                      error_log('there are' . $days_until_expiry);                           
                      $this->send_voucher_expiry_email($recipient_email, $days_until_expiry, $voucher_code_for_email, $voucher_id);
                        update_post_meta($voucher_id, $email_sent_key, 'yes'); 
                    }
                }
            }
      
            wp_reset_postdata();
        }
      }
      
  public function send_voucher_expiry_email($recipient_email, $days_until_expiry, $voucher_code_for_email,$voucher_id) {
    error_log('email was sent');

      $voucher_code_for_email = get_the_title($voucher_id);  
      $expiry_message = '';
      switch ($days_until_expiry) {
          case 14:
              $expiry_message = '2 weeks';
              break;
          case 90:
              $expiry_message = '3 months';
              break;
          case 180:
              $expiry_message = '6 months';
              break;
      }
    
      if ($expiry_message !== '') {
          
          $subject = "Voucher Expiry Alert";
          $message = '
          <html lang="en">
    
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
                                                
                                                src="https://binunsonline.warpdemo.co.za/wp-content/uploads/2024/01/expiry-email.png"
                                                alt="Gift Voucher Expiry"
                                                title="Gift Voucher Expiry"
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
                                            font-size: 21px;
                                            font-weight: 400;
                                          "
                                        >
                                        This is a reminder that your Binuns Gift Voucher is going to expire in <strong> ' . $expiry_message . ' </strong>from now!
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
                                                font-size: 15px;
                                                line-height: 22.4px;
                                                font-family: \'Open Sans\', sans-serif;
                                              "
                                              >Redeem your voucher using the following code at checkout</span
                                            >
                                          </p>
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
                                height: 100%;
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
                                  height: 100%;
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
                                              ><strong>' . $voucher_code_for_email .'</strong></span
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
    
    
          wp_mail($recipient_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
    
          $this->update_last_email_sent_date($voucher_id);
      }
    }
    
    public function update_last_email_sent_date($voucher_id) {
    
      update_post_meta($voucher_id, 'last_email_sent', current_time('mysql'));
    }
}

new emails();





