jQuery(document).ready(function ($) {
    $("#shop-vouchers").click(function () {
        $("html, body").animate(
            {
                scrollTop: $("#voucher-grid").offset().top,
            },
            1000
        );
    });
});
jQuery(document).ready(function ($) {
    function hideCustomVoucherPrices() {
        if ($("body").hasClass("page-id-34287")) {
            $(".product_cat-custom-voucher").each(function () {
                $(this).find(".price").hide();
            });
        }
    }

    hideCustomVoucherPrices();

    $(document).ajaxComplete(function () {
        setTimeout(function () {
            hideCustomVoucherPrices();
        }, 300);
    });
});

jQuery(document).ready(function ($) {
    $(".voucher-submit button").on("click", function (e) {
        e.preventDefault();

        var voucherCode = $("#voucher_code").val().trim();
        if (!voucherCode) {
            Swal.fire("", "Please enter a voucher code.", "warning");
            return;
        }
        $.ajax({
            url: ajax_object.ajaxurl,
            method: "POST",
            data: {
                action: "validate_voucher_code",
                code: voucherCode,
            },
            success: function (response) {
                if (response.status === "valid") {
                    Swal.fire({
                        title: "Voucher Successfully Applied",
                        customClass: {
                            popup: "success-voucher-popup",
                        },
                    });

                    $(document.body).trigger("update_checkout");
                    $("#voucher_code").val("");
                } else if (response.status === "expired") {
                    Swal.fire({
                        customClass: {
                            popup: "custom-style-voucher-popup",
                        },
                        icon: "error",
                        title: "Invalid Voucher Code",
                        text: "This voucher code has expired. Please enter a valid voucher code.",
                    });
                    $("#voucher_code").val("");
                } else if (response.status === "redeemed") {
                    Swal.fire({
                        icon: "error",
                        title: "Invalid Voucher Code",
                        text: "This voucher has already been redeemed.",
                    });
                    $("#voucher_code").val("");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Invalid Voucher Code",
                        text: "The voucher code entered could not be found. Please try again.",
                    });

                    $("#voucher_code").val("");
                }
            },
        });
    });
});
jQuery(document).ready(function ($) {
    $(document).on("click", ".remove-voucher", function (e) {
        e.preventDefault();

        var voucherCode = $(this).data("voucher");

        $.ajax({
            type: "POST",
            url: wc_checkout_params.ajax_url,
            data: {
                action: "remove_voucher_code",
                voucher_code: voucherCode,
            },
            success: function (response) {
                if (response.status === "removed") {
                    location.reload();
                    setTimeout(function () {
                        Swal.fire({
                            title: "Voucher Successfully Removed",
                            customClass: {
                                popup: "success-voucher-popup",
                            },
                        });
                    }, 3500);
                } else {
                    Swal.fire({
                        title: "Error",
                        text: "Failed to remove the voucher. Please try again.",
                        icon: "error",
                        customClass: {
                            popup: "error-voucher-popup",
                        },
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Error",
                    text: "An error occurred while removing the voucher. Please try again.",
                    icon: "error",
                    customClass: {
                        popup: "error-voucher-popup",
                    },
                });
            },
        });
    });
});
jQuery(document).ready(function ($) {
    $(document).on("click", ".remove-voucher", function (e) {
        toastr.info("Removing Voucher...");
    });
});
