(function ($) {
	"use strict";

	$(document).on(
		"click",
		".wopb-wishlist-add, " +
		".wopb-wishlist-remove, " +
		".wopb-wishlist-cart-added, " +
		".wopb-menu-wishlist-btn",
		function (e) {
			e.preventDefault();
			const that = $(this);
			if( that.data('login-redirect') ) {
				window.location.href = that.data('login-redirect');
			}
			const _modal = $('.wopb-modal-wrap:first');
			const _modalContent = _modal.find('.wopb-modal-content');
			const modalLoading = _modal.find('.wopb-modal-loading');
			const actionType = that.data("action");
			const emptyWishlist = wopb_wishlist.emptyWishlist;

			if(actionType !== 'add') {
				_modal.find('.wopb-modal-loading').addClass('active');
			}
			if (
				(
					that.data("redirect") === undefined &&
					actionType === 'add' &&
					that.hasClass('wopb-wishlist-active')
				) ||
				actionType === 'menu_block'
			) {
				_modal.removeClass('active');
				_modal.removeClass(_modal.attr('data-close-animation'));
				_modal.removeClass(_modal.attr('data-open-animation'));
				_modal.attr('data-open-animation', '');
				_modal.attr('data-close-animation', '');
				_modal.find('.wopb-modal-content').html('');
				_modal.addClass('active');
				modalLoading.find('.' + that.data('modal-loader')).removeClass('wopb-d-none');
				modalLoading.addClass('active');
			}
			_modalContent.addClass(that.data('modal_content_class'));

			const simpleProduct = that.find('a').hasClass('product_type_simple') && that.find('a').hasClass('add_to_cart_button');

			$.ajax({
				url: wopb_wishlist.ajax,
				type: "POST",
				data: {
					action: "wopb_wishlist",
					post_id: that.data("postid") ?? '',
					type: actionType,
					simpleProduct: simpleProduct,
					wpnonce: wopb_wishlist.security,
				},
				success: function (response) {
					if (response.success) {
						let redirectUrl = that.data("redirect");
						if( response.data.product_redirect ) {
							redirectUrl = response.data.product_redirect;
						}
						if ( ! response.data.product_redirect && ( actionType === "remove" || actionType === "cart_remove" ) ) {
							$('.wopb-wishlist-add[data-postid="'+that.data("postid")+'"]').removeClass('wopb-wishlist-active');
							that.removeWishListItem(_modal);
						}
						if (actionType === "cart_remove_all" && emptyWishlist) {
							let post_ids = that.data("postid");
							if ( $.type(post_ids) === 'number' ) {
								$('.wopb-wishlist-add[data-postid="'+post_ids+'"]').removeClass('wopb-wishlist-active');
							}
							else if ($.type(post_ids) === 'string' && post_ids.includes(",")) {
								post_ids = post_ids.split(",");
								post_ids.forEach(element => {
									$('.wopb-wishlist-add[data-postid="'+element+'"]').removeClass('wopb-wishlist-active');
								});
							}
							$(".wopb-wishlist-modal-content table").remove();
							_modal.removeClass('active');
						}
						if (actionType === "add" || actionType === "menu_block") {
							if(that.hasClass('wopb-wishlist-active')) {
        						$('.wopb-wishlist-modal-content .wopb-loop-variations-form').remove()
							}else{
								that.addClass('wopb-wishlist-active');
							}
							_modal.find('.wopb-modal-content').html(response.data.html);
						}
						if( response.data && ( response.data.wishlist_count || response.data.wishlist_count == 0 ) ) {
							$('.wopb-wishlist-count').text(response.data.wishlist_count);
							$('.wopb-menu-wishlist-count').text(response.data.wishlist_count);
						}

						if ( !simpleProduct && actionType === "cart_remove" ) {
							redirectUrl = that.find('a').prop('href');
						}
						if ( redirectUrl ) {
							window.location.href = redirectUrl;
						}
						setTimeout(function() {
							that.wishListElement(_modal, _modalContent);
						}, 100)
					}else {
						if( response.data.redirect ) {
							window.location.href = response.data.redirect;
						}
					}
				},
				complete: function (data) {
					modalLoading.removeClass('active');
					modalLoading.find('.' + that.data('modal-loader')).addClass('wopb-d-none');
				},
				error: function (xhr) {
					console.log( "Error occured.please try again" + xhr.statusText + xhr.responseText );
				},
			});
		}
	);

	$.fn.removeWishListItem = function(_modal) {
		let that = $(this);
		if (that.closest('tbody').find('tr').length <= 1) {
			that.closest("table").remove();
			$('.wopb-wishlist-modal-content .wopb-wishlist-cart-added').remove();
			_modal.removeClass('active');
		} else {
			that.closest("tr").remove();
		}
	}

	$.fn.wishListElement = function(_modal, _modalContent) {
		$(document).on('wopbModalClosed', function () {
			_modalContent.removeClass($('.wopb-wishlist-add').data('modal_content_class'));
		});
	}


})(jQuery);