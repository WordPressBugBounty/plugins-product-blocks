(function($) {
    'use strict';

    let delayInMilliseconds = 5000;
    
    // ------------------------
    // Compare Modal Open
    // ------------------------
    $(document).on('click',
        '.wopb-compare-btn, ' +
        '.wopb-compare-product-list-modal .wopb-add-to-compare-btn, ' +
        '.wopb-compare-remove, ' +
        '.wopb-compare-clear-btn, ' +
        '.wopb-compare-nav-item, ' +
        '.wopb-lets-compare-btn, ' +
        '.wopb-menu-compare-btn',
        function(e) {
            e.preventDefault();
            const that = $(this);
            const _modal = $('.wopb-modal-wrap:first');
            const _modalContent = _modal.find('.wopb-modal-content');
            const modalLoading = _modal.find('.wopb-modal-loading');
            const productListBody = that.parents('.wopb-product-list-body:first');
            const compareWrapper = that.parents('.wopb-compare-wrapper:first');
            _modal.attr('data-open-animation', that.data('open-animation'));
            _modal.attr('data-close-animation', that.data('close-animation'));
            _modal.attr('data-modal-class', that.data('modal_wrapper_class'));
            $('.wopb-no-product').addClass('wopb-d-none');

            if(that.data('action') === 'redirect' && that.data('redirect')) {
                window.location.href = that.data("redirect");
            }else {
                if(that.data('added-action') !== 'redirect') {
                    if(
                        that.data('action') === 'add' ||
                        that.data('action') === 'nav_popup' ||
                        that.data('action') === 'menu_block'
                    ) {
                        if(that.data('added-action') !== 'product_list') {
                            _modal.removeClass('active');
                            _modal.removeClass(_modal.attr('data-close-animation'));
                            _modal.removeClass($('.wopb-compare-btn').data('modal_wrapper_class'));
                            _modalContent.html('');
                            if (that.data('added-action') && that.data('added-action') !== 'message' && !that.data("redirect")) {
                                _modal.removeClass(_modal.attr('data-open-animation'));
                                modalLoading.find('.' + that.data('modal-loader')).removeClass('wopb-d-none');
                            }
                            _modal.addClass('active');
                            modalLoading.addClass('active');
                        }
                        block(productListBody)
                    }else if(that.data('action') === 'remove') {
                        block(compareWrapper)
                    }else if(that.data('action') === 'clear') {
                        block(compareWrapper)
                    }
                    _modal.addClass(that.data('modal_wrapper_class'));
                }
            }

            $.ajax({
                url: wopb_compare.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_compare',
                    postid: that.data('postid') ?? '',
                    type: that.data('action'),
                    added_action: that.data('added-action'),
                    wpnonce: wopb_compare.security
                },
                success: function(response) {
                    let response_html = response.data.html;
                    if (that.data("redirect")) {
                        window.location.href = that.data("redirect");
                    }
                    if(that.data('action') === 'add' && response_html) {
                        if (that.data('added-action') && that.data('added-action') === 'message') {
                            setTimeout(function () {
                                _modal.removeClass('wopb-modal-toast-wrapper active');
                            }, delayInMilliseconds);
                        }
                        if(that.parents('.wopb-compare-product-list-modal:first').length && $('.wopb-compare-item-' + that.data('postid')).length) {
                            $('.wopb-compare-product-list-modal:first').find('.wopb-compare-item-' + that.data('postid')).remove();
                        }
                        that.addClass('wopb-compare-active');
                    }
                    if(that.data('action') === 'remove') {
                        if(response.data.compare_count > response.data.demo_column) {
                            $('.wopb-compare-item-' + that.data('postid')).remove();
                            $('.wopb-compare-btn[data-postid="' + that.data("postid") + '"]').removeClass('wopb-compare-active');
                        }else {
                            compareWrapper.replaceWith(response_html)
                        }
                    }else if(that.data('action') === 'clear') {
                        compareWrapper.replaceWith(response_html)
                    }else {
                        if(that.data('added-action') === 'product_list' && that.data('action') == 'add') {
                            compareWrapper.replaceWith(response_html)
                            $('.wopb-compare-product-list-modal').removeClass('wopb-d-none');
                        }else {
                            _modalContent.html(response_html);
                        }
                    }
                    $('.wopb-compare-nav-item').find('.wopb-compare-count').html(response.data.compare_count);
                    $('.wopb-menu-compare-count').html(response.data.compare_count);
                },
                complete:function() {
                    if (
                        that.data('action') === 'add' ||
                        that.data('action') === 'nav_popup' ||
                        that.data('action') === 'menu_block'
                    ) {
                        modalLoading.removeClass('active');
                        modalLoading.find('.' + that.data('modal-loader')).addClass('wopb-d-none');
                        unblock(productListBody)
                    }else if(that.data('action') === 'clear') {
                        $('.wopb-compare-btn').removeClass('wopb-compare-active')
                    }
                    unblock(compareWrapper)
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });
    });
    $(document).on('click', '.wopb-compare-add-btn', function(e) { //Compare Product Add Open Button
        $('.wopb-compare-product-list-modal').removeClass('wopb-d-none');
    });
    $(document).on('input', '.wopb-compare-product-list-modal .wopb-search-input', function(e) {
        const that = $(this)
        if((that.val() && that.val().length >= 3) || !that.val()) {
            get_product_list(that)
        }
    });
    function get_product_list(that) {
        block(that.parents('.wopb-product-list-body:first').find('.wopb-product-list'))
        $.ajax({
            url: wopb_compare.ajax_product_list,
            type: 'POST',
            data: {
                action: 'wopb_product_list',
                search: that.val() ?? '',
                wpnonce: wopb_compare.security
            },
            success: function(response) {
                that.parents('.wopb-compare-product-list-modal:first').find('.wopb-product-list').html(response.data.html)
            },
            complete:function() {
                unblock(that.parents('.wopb-product-list-body:first').find('.wopb-product-list'))
            },
        });
    }
    $(document).on( 'click', '.wopb-compare-product-list-modal, .wopb-compare-product-list-modal .wopb-product-list-close', function (e) {
        if (
            (
                $(e.target).hasClass('wopb-compare-product-list-modal')
                || $(e.target).hasClass('wopb-product-list-close')
                || $(e.target).parents('.wopb-product-list-close:first').length
            )
            && !$('.wopb-compare-product-list-modal').hasClass('wopb-d-none')
        ) {
            $('.wopb-compare-product-list-modal').addClass('wopb-d-none');
        }
    });

    // ------------------------
    // Add to cart on input
    // ------------------------
    $(document).on('input', '.wopb-quantity-wrapper .input-text.qty', function(e){
        if($(this).val() === '' || $(this).val() < 1) {
            $(this).val(1)
        }
    })

    // ------------------------
    // Quick Add Action
    // ------------------------
    $(document).on("click",".wopb-add-to-cart.ajax_add_to_cart",function(e) {
        const that = $(this);
        if (!that.parent('.wopb-cart-action').hasClass('wopb-active')) {
            e.preventDefault();
        }
        let compareWrapper = that.parents('.wopb-compare-modal:first')
        let quantity = compareWrapper.find('.wopb-compare-item-' + that.data('postid') + ' .wopb-quantity-wrapper input.qty').val()
        $.ajax({
            url: wopb_compare.ajax,
            type: 'POST',
            data: {
                action: 'wopb_addcart',
                postid: that.data('postid'),
                quantity: quantity ? quantity : 1,
                wpnonce: wopb_compare.security
            },
            beforeSend: function() {
                that.addClass('loading');
            },
            success: function(data) {
                that.parent('.wopb-cart-action').addClass('wopb-active');
            },
            complete:function() {
                that.removeClass('loading');
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    });

    let block = function( $node ) {
        if ( ! is_blocked( $node ) ) {
            $node.addClass( 'wopb-processing' );
            $node.append( '<div class="wopb-block-overlay"></div>' );
        }
    };

    let unblock = function( $node ) {
        $node.removeClass( 'wopb-processing' ).unblock();
    };

    let is_blocked = function( $node ) {
        return $node.is( '.wopb-processing' ) || $node.parents( '.wopb-processing' ).length;
    };

})( jQuery );