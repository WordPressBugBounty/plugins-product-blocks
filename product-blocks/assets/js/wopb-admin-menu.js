(function ($) {
  "use strict";

  // Dashboard Submenu Support Active & Inactive Class
  $(document).on(
    "click",
    "#wopb-dashboard-wopb-settings-tab li a, #toplevel_page_wopb-settings ul li a",
    function (e) {
      let value = $(this).attr("href");
      if (value) {
        value = value.split("#");
        if (
          typeof value[1] != "undefined" &&
          value[1].indexOf("demoid") < 0 &&
          value[1]
        ) {
          $("#toplevel_page_wopb-settings ul li a")
            .closest("ul")
            .find("li")
            .removeClass("current");
          $(this).closest("li").addClass("current"); // Submenu click

          if (value[1].includes('/')) {
            const splits = value[1].split('/');
            const escapedValue = splits[0] + '\\/' + splits[1];
                        $("#toplevel_page_wopb-settings ul li a[href$=" + escapedValue + "]")
              .closest("li")
              .addClass("current"); // Dash Nav Menu click
          } else {

            $("#toplevel_page_wopb-settings ul li a[href$=" + value[1] + "]")
              .closest("li")
              .addClass("current"); // Dash Nav Menu click
          }



          if (value[1] == "overview") {
            $("#toplevel_page_wopb-settings ul li.wp-first-item").addClass(
              "current"
            );
          }
        }
      }
    }
  );
  // Dashboard Submenu Support Active & Inactive Class
  $("#toplevel_page_wopb-settings ul > li").removeClass("current");
  $("#toplevel_page_wopb-settings ul > li > a").each(function (e) {
    const selector = $(this).attr("href");
    if (selector && selector.indexOf("?page=wopb-settings") > 0) {
      if ($(this).hasClass("wp-first-item") != false) {
        $(this).attr("href", selector + "#overview");
      } else if (wopb_admin_menu.settings) {
        if (
          (selector.indexOf("#builder") > 0 &&
            wopb_admin_menu.settings?.wopb_builder != "true") ||
          (selector.indexOf("#custom-font") > 0 &&
            wopb_admin_menu.settings?.wopb_custom_font != "true") ||
          (selector.indexOf("#size-chart") > 0 &&
            wopb_admin_menu.settings?.wopb_size_chart != "true") ||
          (selector.indexOf("#saved-templates") > 0 &&
            wopb_admin_menu.settings?.wopb_templates != "true")
        ) {
          $(this).hide();
        }
      }
    } else if (selector.indexOf("?page=go_productx_pro") > 0) {
      $(this).attr("target", "_blank");
    }
    let pram = selector.indexOf("#") > 0 ? selector.split("#")[1] : "overview";
    if (window.location.hash == "#" + pram) {
      $(this).parent("li").addClass("current");
    }
    let menuId = "productx-submenu-" + pram;
    $(this).attr("id", menuId);
    // let menu = $("#" + menuId);
    // menu.parents('.toplevel_page_wopb-settings').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
  });

})(jQuery);
