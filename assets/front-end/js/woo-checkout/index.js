var WooCheckout = function($scope, $) {
    console.log('working');
    $.blockUI.defaults.overlayCSS.cursor = 'default';
    function render_order_review_template(){
        setTimeout(
            function () {
                $('.ea-checkout-review-order-table').addClass( 'processing' ).block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                $.ajax({
                    type:		'POST',
                    url:		localize.ajaxurl,
                    data:		{
                        action: 'update_order_review_ea'
                    },
                    success:	function( data ) {
                        $( ".ea-checkout-review-order-table" ).replaceWith( data.order_review);
                        setTimeout(function () {
                            $( '.ea-checkout-review-order-table' ).removeClass('processing').unblock();
                        }, 100000)
                    }
                });
            },3000
        );
    }

    $(document).on('click', '.woocommerce-remove-coupon', function(e) {
        render_order_review_template();
    });

    $( 'form.checkout_coupon' ).submit(function (event) {
        render_order_review_template();
    });
};

jQuery(window).on("elementor/frontend/init", function() {
    elementorFrontend.hooks.addAction(
        "frontend/element_ready/eael-woo-checkout.default",
        WooCheckout
    );
});
