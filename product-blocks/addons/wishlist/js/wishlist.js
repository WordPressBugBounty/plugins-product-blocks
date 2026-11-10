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
			const _modal = $('.wopb-modal-wrap:first');
			const quickViewOpen = _modal.hasClass('wopb-quick-view-wrapper');
			const _modalContent = _modal.find('.wopb-modal-content');
			const modalLoading = _modal.find('.wopb-modal-loading');
			const actionType = that.data("action");
			if( ! quickViewOpen ) {
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

			}
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
						if( ! quickViewOpen ) {
							let redirectUrl = that.data("redirect");
							if (response.data.product_redirect) {
								redirectUrl = response.data.product_redirect;
							}
							if (!response.data.product_redirect && (actionType === "remove" || actionType === "cart_remove")) {
								$('.wopb-wishlist-add[data-postid="' + that.data("postid") + '"]').removeClass('wopb-wishlist-active');
								that.removeWishListItem(_modal);
							}
							let firstClick = false;
							if (actionType === "add" || actionType === "menu_block") {
								if (that.hasClass('wopb-wishlist-active')) {
									$('.wopb-wishlist-modal-content .wopb-loop-variations-form').remove()
								} else {
									firstClick = true;
									that.addClass('wopb-wishlist-active');
								}
								_modal.find('.wopb-modal-content').html(response.data.html);
							}
							if (response.data && (response.data.wishlist_count || response.data.wishlist_count == 0)) {
								$('.wopb-wishlist-count').text(response.data.wishlist_count);
								$('.wopb-menu-wishlist-count').text(response.data.wishlist_count);
							}

							if (!simpleProduct && actionType === "cart_remove") {
								redirectUrl = that.find('a').prop('href');
							}
							if (redirectUrl && !firstClick) {
								window.location.href = redirectUrl;
							}
							setTimeout(function () {
								that.wishListElement(_modal, _modalContent);
							}, 100)
						}else {
							$('.wopb-wishlist-add[data-postid="' + that.data("postid") + '"]').addClass('wopb-wishlist-active');
						}
					}
				},
				complete: function (data) {
					if( ! quickViewOpen ) {
						modalLoading.removeClass('active');
						modalLoading.find('.' + that.data('modal-loader')).addClass('wopb-d-none');
					}
				},
				error: function (xhr) {
					console.error( "An error occurred. Please try again.", xhr.statusText, xhr.responseText );
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