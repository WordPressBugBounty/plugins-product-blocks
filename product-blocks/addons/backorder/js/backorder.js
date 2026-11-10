(function ($) {
  "use strict";

  //stock status check when document load
  changeBackOrderStockStatus($("#_manage_stock"), $("#_stock_status"));
  $(document).on("woocommerce_variations_loaded", function () {
    $("select[id^=variable_stock_status").each(function () {
      let manageStockObjectHtml = $(this)
        .parent()
        .parent()
        .parent()
        .find("input[name^=variable_manage_stock]");
      changeBackOrderStockStatus(manageStockObjectHtml, $(this));
    });
  });

  //stock status change simple product
  $("#_stock_status, #_manage_stock").change(function () {
    let manageStock = $(this).parent().parent().parent().find("#_manage_stock");
    let stockStatus = $(this).parent().parent().parent().find("#_stock_status");
    changeBackOrderStockStatus(manageStock, stockStatus);
  });

  //stock status change variable product
  $(document).on(
    "change",
    "select[id^=variable_stock_status], input[name^=variable_manage_stock]",
    function () {
      let manageStockObjectHtml = $(this)
        .parent()
        .parent()
        .parent()
        .find("input[name^=variable_manage_stock]");
      let statusSelectHtml = $(this)
        .parent()
        .parent()
        .parent()
        .find("select[id^=variable_stock_status]");
      changeBackOrderStockStatus(manageStockObjectHtml, statusSelectHtml);
    },
  );

  //common function of backorder show for both simple and variable
  function changeBackOrderStockStatus(manageStockObject, statusObject) {
    let backorderSection = statusObject
      .parent()
      .parent()
      .parent()
      .find(".wopb-backorder-field-group");
    if (
      statusObject.val() == "onbackorder" &&
      manageStockObject.prop("checked") == false
    ) {
      backorderSection.show(400);
      backorderSection.find(".wopb-required").prop("required", true);
    } else {
      backorderSection.hide(400);
      backorderSection.find(".wopb-required").prop("required", false);
    }
  }

  //change add to cart text if variable backorder
  $("form.variations_form").each(function () {
    let variationForm = $(this);
    let cartText = variationForm.find(".single_add_to_cart_button").text();
    $(variationForm)
      .on("show_variation", function (event, variation) {
        let backOrderText = $(this)
          .find(".wopb-singlepage-backorder-message")
          .find(".wopb-single-variation-backorder-text")
          .val();
        $(".single_add_to_cart_button").text(
          backOrderText ? backOrderText : cartText,
        );
      })
      .on("hide_variation", function (event) {
        $(".single_add_to_cart_button").text(cartText);
      });
  });
})(jQuery);
