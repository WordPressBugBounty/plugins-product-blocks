!function(t){"use strict";t(document).on("click",".wopb-wishlist-add, .wopb-wishlist-remove, .wopb-wishlist-cart-added, .wopb-menu-wishlist-btn",(function(a){a.preventDefault();const s=t(this);s.data("login-redirect")&&(window.location.href=s.data("login-redirect"));const o=t(".wopb-modal-wrap:first"),i=o.hasClass("wopb-quick-view-wrapper"),e=o.find(".wopb-modal-content"),d=o.find(".wopb-modal-loading"),l=s.data("action"),n=wopb_wishlist.emptyWishlist;i||("add"!==l&&o.find(".wopb-modal-loading").addClass("active"),(void 0===s.data("redirect")&&"add"===l&&s.hasClass("wopb-wishlist-active")||"menu_block"===l)&&(o.removeClass("active"),o.removeClass(o.attr("data-close-animation")),o.removeClass(o.attr("data-open-animation")),o.attr("data-open-animation",""),o.attr("data-close-animation",""),o.find(".wopb-modal-content").html(""),o.addClass("active"),d.find("."+s.data("modal-loader")).removeClass("wopb-d-none"),d.addClass("active")),e.addClass(s.data("modal_content_class")));const c=s.find("a").hasClass("product_type_simple")&&s.find("a").hasClass("add_to_cart_button");t.ajax({url:wopb_wishlist.ajax,type:"POST",data:{action:"wopb_wishlist",post_id:s.data("postid")??"",type:l,simpleProduct:c,wpnonce:wopb_wishlist.security},success:function(a){if(a.success)if(i)t('.wopb-wishlist-add[data-postid="'+s.data("postid")+'"]').addClass("wopb-wishlist-active");else{let i=s.data("redirect");if(a.data.product_redirect&&(i=a.data.product_redirect),a.data.product_redirect||"remove"!==l&&"cart_remove"!==l||(t('.wopb-wishlist-add[data-postid="'+s.data("postid")+'"]').removeClass("wopb-wishlist-active"),s.removeWishListItem(o)),"cart_remove_all"===l&&n){let a=s.data("postid");"number"===t.type(a)?t('.wopb-wishlist-add[data-postid="'+a+'"]').removeClass("wopb-wishlist-active"):"string"===t.type(a)&&a.includes(",")&&(a=a.split(","),a.forEach((a=>{t('.wopb-wishlist-add[data-postid="'+a+'"]').removeClass("wopb-wishlist-active")}))),t(".wopb-wishlist-modal-content table").remove(),o.removeClass("active")}"add"!==l&&"menu_block"!==l||(s.hasClass("wopb-wishlist-active")?t(".wopb-wishlist-modal-content .wopb-loop-variations-form").remove():s.addClass("wopb-wishlist-active"),o.find(".wopb-modal-content").html(a.data.html)),a.data&&(a.data.wishlist_count||0==a.data.wishlist_count)&&(t(".wopb-wishlist-count").text(a.data.wishlist_count),t(".wopb-menu-wishlist-count").text(a.data.wishlist_count)),c||"cart_remove"!==l||(i=s.find("a").prop("href")),i&&(window.location.href=i),setTimeout((function(){s.wishListElement(o,e)}),100)}else a.data.redirect&&(window.location.href=a.data.redirect)},complete:function(t){i||(d.removeClass("active"),d.find("."+s.data("modal-loader")).addClass("wopb-d-none"))},error:function(t){console.log("Error occured.please try again"+t.statusText+t.responseText)}})})),t.fn.removeWishListItem=function(a){let s=t(this);s.closest("tbody").find("tr").length<=1?(s.closest("table").remove(),t(".wopb-wishlist-modal-content .wopb-wishlist-cart-added").remove(),a.removeClass("active")):s.closest("tr").remove()},t.fn.wishListElement=function(a,s){t(document).on("wopbModalClosed",(function(){s.removeClass(t(".wopb-wishlist-add").data("modal_content_class"))}))}}(jQuery);