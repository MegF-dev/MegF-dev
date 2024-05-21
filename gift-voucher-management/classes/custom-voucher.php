<?php
class custom_voucher {
    public function __construct() {
        add_action('woocommerce_before_add_to_cart_button', [$this, 'add_custom_voucher_fields_to_product_form']);
    }

    public function add_custom_voucher_fields_to_product_form() {
        global $product;

        if (is_a($product, 'WC_Product') && has_term('custom-voucher', 'product_cat', $product->get_id())) {
        echo '
            <script>
    
            jQuery(document).ready(function ($) {
                $(".single_add_to_cart_button").on("click", function (e) {
                    e.preventDefault();
            
                    var customAmount = parseFloat($("#custom_voucher_price").val()); // Ensure this is a number for comparison
                    var recipientName = $("#recipient_name").val();
                    var recipientAddress = $("#recipient_address").val();
            
                    
                    if (customAmount < 100 || customAmount > 30000) {
                        Swal.fire({
                            icon: "error",
                            text: "Please enter a value between R100 and R30000."
                        });
                        return;
                    }
            
                    if (!customAmount) {
                        Swal.fire({
                            icon: "warning",
                            text: "Please enter a gift voucher amount"
                        });
                        return;
                    }
            
                    if (!recipientName || !recipientAddress) {
                        Swal.fire({
                            icon: "warning",
                            text: "Please enter a recipient name and address"
                        });
                        return;
                    }
            
                    var confirmMsg = `You entered R${customAmount}. Click OK to add to cart.`;
                    Swal.fire({
                        title: "Confirm Voucher Amount",
                        text: confirmMsg,
                        icon: "question",
                        showCancelButton: true,
                        customClass: {
                            popup: "style-custom-voucher-popup",
                          },
                        confirmButtonText: "OK",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).off("click").click();
                        }
                    });
                });
            });
            
            </script>    
            <style>
                div:where(.swal2-icon).swal2-warning,div:where(.swal2-icon).swal2-question {
                    border-color: #4c776d !important;
                    color: #4c776d !important;
                }
    
                div:where(.swal2-container) .swal2-html-container {
                    color: black  !important;
                    font-family: "Open Sans", sans-serif !important;
                    font-size: 16px !important;
                    margin-top: 10px !important;
                }
                #swal2-title.swal2-title {
                    font-size:28px !important
                }
                div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm {
                    background-color:#AEC6C0 !important
                }
                div:where(.swal2-icon).swal2-warning {
                    margin-bottom: 35px !important;
                }
                .swal2-shown.swal2-height-auto {
                    padding-right:0 !important
                }
                div:where(.swal2-container) button:where(.swal2-styled):focus {
                    outline: none !important;
                }
                
                div:where(.swal2-container) button:where(.swal2-styled).swal2-confirm:focus {
                    box-shadow: none !important;
                }
                label[for=custom_voucher_price] {
                color: black;
                    font-family: \'Open Sans\';
                    font-weight: 700;
                    font-size: 24px;
                }
                #custom_voucher_price {
                    font-weight: 700;
                    color: #000000;
                    font-size: 30px;
                    padding: 0px;
                    padding-left: 10px;
                    margin-bottom: 40px;
                    border: 1px solid black;
                    border-radius: 3px;
                    width: 150px;
                    margin-left: 10px;
                }
                .woocommerce-Price-amount.amount {
                    display:none !important;
                }
            </style>';
    echo ' <div style="margin-top: -30px;" class="field">
                <label for="custom_voucher_price">Enter Your Amount (R):</label>
                <input type="number" id="custom_voucher_price" name="custom_voucher_price" min="10" max="10000" step="1" required>
            </div>';
    
        }
        if (is_a($product, 'WC_Product') && has_term('vouchers', 'product_cat', $product->get_id())) {
        ?>
            <div class="custom-voucher-fields">
            <div class="custom-col-1">
                <div class="field">
                    <label for="sender_name"></label>
                    <input type="text" id="sender_name" name="sender_name" placeholder="Sender Name (optional)">
                </div>
                <div class="field">
                    <label for="recipient_name"></label>
                    <input type="text" id="recipient_name" name="recipient_name" placeholder="Recipient Name *" required>
                </div>
            </div>
                <div style="margin-bottom: 20px;" class="field">
                    <label for="recipient_address"></label>
                    <input type="text" id="recipient_address" name="recipient_address" placeholder="Recipient Email *" required>
                </div>
            
                <div class="field">
                    <label for="custom_voucher_message"></label>
                    <textarea id="custom_voucher_message" name="custom_voucher_message" placeholder="Message (optional)" maxlength="250" rows="4" style="resize: none;"></textarea>
                </div> 
            </div>
        <?php
        }
    }
}

new custom_voucher();
