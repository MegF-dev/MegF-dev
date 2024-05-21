jQuery(document).ready(function ($) {
    var imgUrl =
        "/wp-content/plugins/stock-management-plugin/images/binuns-logo-reduced.png";
    var imgElement =
        '<img src="' +
        imgUrl +
        '" style="margin-right: 5px;margin-top: -5px;vertical-align: middle;">';

    if (
        $("body").hasClass("edit-php") &&
        $("body").hasClass("post-type-gift_vouchers")
    ) {
        $("h1.wp-heading-inline").prepend(imgElement);
    }
});
jQuery(document).ready(function ($) {
    $(".email-success").hide();
    $(".email-fail").hide();
    $("#send-voucher-email").on("click", function (e) {
        e.preventDefault();

        var post_id = $(this).data("post-id");

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                action: "send_voucher_email",
                post_id: post_id,
            },
            success: function (response) {
                $(".email-success").show();
                $(".email-fail").hide();
            },
            error: function (response) {
                $(".email-fail").show();
                $(".email-success").hide();
            },
        });
    });
});
