(function($) {
    'use strict';

    preorderCheckboxCheck();

    $("#_wopb_preorder_simple").change(function () {
        if ($(this).prop('checked') == true) {
            $('ul.product_data_tabs .productx_options a').trigger('click')
        }
        preorderCheckboxCheck();
    });

    function preorderCheckboxCheck() {
        if ($("#_wopb_preorder_simple").prop('checked') == true) {
            $("#wopb-woocommerce-preorder-field-group").show();
            $("#wopb-woocommerce-preorder-field-group").find('.wopb_required').attr("required", "required");
            $("#wopb-preorder-select-instruction").hide();
        } else {
            $("#wopb-woocommerce-preorder-field-group").hide();
            $("#wopb-woocommerce-preorder-field-group").find('.wopb_required').removeAttr("required");
            $("#wopb-preorder-select-instruction").show();
        }
    }
})( jQuery );
