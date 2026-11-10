(function ($) {
  "use strict";

  const { block, unblock } = window.wopbHelpers || {};
  const { loopcounter } = window || {};

  // *************************************
  // Filter
  // *************************************
  $(".wopb-filter-wrap li a").on("click", function (e) {
    e.preventDefault();

    if ($(this).closest("li").hasClass("filter-item")) {
      let that = $(this),
        parents = that.closest(".wopb-filter-wrap"),
        wrap = that.closest(".wopb-block-wrapper");

      parents.find("a").removeClass("filter-active");
      that.addClass("filter-active");

      let filterWrapper = $(this).parents(".wopb-block-wrapper:first");
      const post_ID =
        parents.parents(".wopb-shortcode").length != 0
          ? parents.parents(".wopb-shortcode").data("postid")
          : parents.data("postid");
      let widgetBlockId = "";
      let widgetBlock = $(this).parents(".widget_block:first");
      if (widgetBlock.length > 0) {
        let widget_items = widgetBlock.attr("id").split("-");
        widgetBlockId = widget_items[widget_items.length - 1];
      }

      $.ajax({
        url: wopb_core.ajax_filter,
        type: "POST",
        data: {
          action: "wopb_filter",
          taxtype: parents.data("taxtype"),
          taxonomy: that.data("taxonomy"),
          blockId: parents.data("blockid"),
          postId: post_ID,
          blockName: parents.data("blockname"),
          currentUrl: parents.data("current-url"),
          widgetBlockId: widgetBlockId,
          wpnonce: wopb_core.security,
        },
        beforeSend: function () {
          block(filterWrapper);
          wrap.addClass("wopb-loading-active");
        },
        success: function (data) {
          wrap.find(".wopb-wrapper-main-content").html(data);
        },
        complete: function () {
          unblock(filterWrapper);
          wrap.removeClass("wopb-loading-active");

          filterWrapper.find(".wopb-product-deals").each(function (i, obj) {
            loopcounter(obj);
          });
        },
        error: function (xhr) {
          console.error(
            "An error occurred. Please try again.", xhr.statusText, xhr.responseText,
          );
          wrap.removeClass("wopb-loading-active");
        },
      });
    }
  });
})(jQuery);
