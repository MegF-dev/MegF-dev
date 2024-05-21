<?php
class reporting {

    public function __construct() {        
        // add_action('admin_menu', [$this, 'gift_vouchers_add_reporting_page']);  
        add_action('restrict_manage_posts',[$this, 'add_date_range_filter']);  
        add_action('pre_get_posts', [$this, 'filter_by_date_range']);
        
        add_action('admin_head',[$this, 'hide_default_dates_dropdown']);
    }

    public function hide_default_dates_dropdown() {
        global $pagenow, $typenow;
    
        if ($pagenow == 'edit.php' && $typenow == 'gift_vouchers') {
            echo '<style>
                #filter-by-date {
                    display: none !important; 
                }
                #posts-filter {
                    
                    background-color: white !important;
                    padding: 30px !important;
                }
                #wpbody {
                    background-color: white !important;
                }
                .subsubsub,.wp-heading-inline {
                    padding-left: 30px !important;
                   
                }
                .subsubsub {
                    margin-top: 15px;
                }
                .wp-heading-inline {
                    margin-top: 20px !important;
                }
                .voucher-stats {
                    margin-top: 75px !important;
                    margin-bottom: 15px;
                }
                .voucher-stats p{
                    color: black;
                    font-size: 14px;
                    font-family: Open-Sans, Sans-serif !important;
                    
                }
                .subsubsub li,.alignleft.actions input,#bulk-action-selector-top {
                    font-family: Open-Sans, Sans-serif !important;
                }
                .page-title-action {
                    background-color: black !important;
                    border-radius: 3px !important;
                    color: white !important;
                    font-weight: 600 !important;
                    font-size: 13px !important;
                    padding: 2px 15px !important;
                    border: none !important
                    font-family: "Open Sans", Sans-serif !important;
                    text-transform: capitalize !important;
                    cursor: pointer !important;
                    border: 1px solid #ffffff !important;
                }
                #post-query-submit,#search-submit,input#doaction {
                    background-color: black;
                    border-radius: 3px;
                    color: white;
                    font-weight: 600;
                    font-size: 13px;
                    padding: 0px 15px;
                    border: none;
                    font-family: \'Open Sans\', Sans-serif;
                    text-transform: capitalize;
                    cursor: pointer;
                }
                .voucher-stats {
                    display: grid;
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                    position: relative;
                    left: 0;
                }
               h1.wp-heading-inline {
                    font-size: 24px !important;
                    font-family: Open-Sans, Sans-serif !important;                   
                    color: black !important;
                    font-weight: 600 !important;
                }
                .tablenav.top {
                    margin-bottom: 150px;
                }
                .alignleft.actions a   {
                    background-color: white;
                    border-radius: 3px;
                    color: black;
                    font-weight: 600;
                    font-size: 13px;
                    padding: 0px 15px;    
                    font-family: \'Open Sans\', Sans-serif;
                    text-transform: capitalize;
                    cursor: pointer;
                    border: 1px solid black;
                }
                .tablenav.top .alignleft.actions.bulkactions {
                    
                    margin-bottom: 15px !important;
                }
            
                .tablenav.top .alignleft.actions.bulkactions input{
                    margin-right: -8px !important;
                }
                .active-vouchers-report::before, .redeemed-vouchers-report::before,.expired-vouchers-report::before,.partialredeemed-vouchers-report::before {
                    content: "";
                    display: inline-block;
                    width: 13px;
                    height: 13px;
                    border-radius: 50%;
                    background-color: #5b841b;
                    margin-right: 10px;
                    position: relative;
                    top: 2px;
                  }
                  .expired-vouchers-report::before {
                    background-color:#555555;
                  }
                  .redeemed-vouchers-report::before{
                    background-color:#1b4884;
                  }
                  .partialredeemed-vouchers-report::before {
                    background-color:#be6208;        
                  }
                  .active-vouchers-report p,.expired-vouchers-report p,.partialredeemed-vouchers-report p, .redeemed-vouchers-report p {
                    margin-bottom: 0px;
                    display: inline;
                  }

                  .active-vouchers-report p:last-child,.expired-vouchers-report p:last-child,.redeemed-vouchers-report p:last-child,.partialredeemed-vouchers-report p:last-child {    
                    display: block;
                    margin-left: 22px;
                    margin-top: 5px;
                  }


            </style>';
        }
    }
    
    
    public function add_date_range_filter() {
        if (get_current_screen()->id !== 'edit-gift_vouchers') return;
    
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
        $clear_url = remove_query_arg(array('start_date', 'end_date'));
        ?>
        <div class="alignleft actions">
            <input type="text" name="start_date" id="start_date" placeholder="Start Date" value="<?php echo $start_date; ?>" autocomplete="off"/>
            <input type="text" name="end_date" id="end_date" placeholder="End Date" value="<?php echo $end_date; ?>" autocomplete="off"/>
            <input type="submit" name="post-query-submit" id="post-query-submit" class="button" value="Filter">
            <a href="<?php echo esc_url($clear_url); ?>" class="button">Clear</a>
            <script>
                jQuery(document).ready(function($) {
                    $('#start_date, #end_date').datepicker({
                        dateFormat: 'yy-mm-dd'
                    });
                });
            </script>
        </div>
        <?php
        
        $this->display_voucher_stats($start_date, $end_date);
    }
    public function display_voucher_stats($start_date, $end_date) {
       
        $active_count = $expired_count = $redeemed_count = $partially_redeemed_count = 0;
        $active_total = $expired_total = $redeemed_total = $partially_redeemed_total = 0;
    
        $args = array(
            'post_type'      => 'gift_vouchers',
            'posts_per_page' => -1, 
            'post_status'    => 'publish',
            'date_query'     => array(
                array(
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ),
            ),
        );
        $voucher_query = new WP_Query($args);
    
        if ($voucher_query->have_posts()) {
            while ($voucher_query->have_posts()) {
                $voucher_query->the_post();
                $post_id = get_the_ID();
                $voucherValue = (float)get_post_meta($post_id, 'gift_voucher_value', true);
                $originalVoucherValue = (float)get_post_meta($post_id, 'original_gift_voucher_value', true);              
                $expiry_date = get_post_meta($post_id, 'gift_voucher_expiry', true);
                $expiry_date_obj = new DateTime($expiry_date);
                $current_date = new DateTime();
                $is_redeemed = get_post_meta($post_id, 'is_redeemed', true);
                $get_value =  get_post_meta($post_id, 'gift_voucher_value', true);
            
                if ($is_redeemed === 'yes' || $get_value == "0") {
                    $redeemed_count++;
                    $redeemed_total += $originalVoucherValue;
                } elseif ($is_redeemed === 'partially') {
                    $partially_redeemed_count++;
                    $partially_redeemed_total += $voucherValue;
                } elseif ($expiry_date_obj >= $current_date){                        
                    $active_count++;
                    $active_total += $voucherValue;
                    
                }elseif ($expiry_date_obj < $current_date){                        
                    $expired_count++;
                    $expired_total += $voucherValue;
                    
                }

            }
        }
        wp_reset_postdata();
    
        echo '<div class="voucher-stats">';
        echo '<div class="active-vouchers-report"><p style="margin-bottom:-10px">Number of Active Vouchers: <strong>' . $active_count . '</strong></p><p>Total Value: <strong>R' . number_format($active_total, 2) . '</strong></p></div>';       
        echo '<div class="redeemed-vouchers-report"><p style="margin-bottom:-10px">Number of Redeemed Vouchers: <strong>' . $redeemed_count . '</strong></p><p>Total Value: <strong>R' . number_format($redeemed_total, 2) . '</strong></p></div>';
        echo '<div class="expired-vouchers-report"><p style="margin-bottom:-10px">Number of Expired Vouchers: <strong>' . $expired_count . '</strong></p><p>Total Value: <strong>R' . number_format($expired_total, 2) . '</strong></p></div>';
        echo '<div class="partialredeemed-vouchers-report"><p style="margin-bottom:-10px">Number of Partially Redeemed Vouchers: <strong>' . $partially_redeemed_count . '</strong></p><p>Total Value: <strong>R' . number_format($partially_redeemed_total, 2) . '</strong></p></div>';
        echo '</div>';
    }
    
    
    
    public function filter_by_date_range($query) {
        
        if (!is_admin() || !$query->is_main_query() || get_current_screen()->id !== 'edit-gift_vouchers') {
            return;
        }
            
        if (isset($_GET['start_date'], $_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $start_date = sanitize_text_field($_GET['start_date']);
            $end_date = sanitize_text_field($_GET['end_date']);
              
            $query->set('date_query', array(
                array(
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ),
            ));
        }
    }   
        
}

 

new reporting();
