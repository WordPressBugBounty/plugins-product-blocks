(function($) {
    'use strict';

    // ------------------------
    // Quick Add to Quickview Button
// ------------------------
    $(document).on('click', '.wopb-quickview-btn, .wopb-quick-view-navigation .wopb-nav-arrow', function(e) {
        let that = $(this);
        e.preventDefault();
        const _modal = $('.wopb-modal-wrap:first');
        const _modalContent = _modal.find('.wopb-modal-content');
        const modalLoading = _modal.find('.wopb-modal-loading');
        const afterModalContent = _modal.find('.wopb-after-modal-content');
        let form = _modal.find('form.cart');
        afterModalContent.html();
        _modal.removeClass('active');
        _modal.removeClass(_modal.attr('data-close-animation'));
        _modal.attr('data-open-animation', that.data('open-animation'));
        _modal.attr('data-close-animation', that.data('close-animation'));
        _modal.attr('data-modal-class', that.data('modal_wrapper_class'));
        _modal.removeClass(that.data('modal_wrapper_class'));
        form.removeAttr('data-redirect');
        form.removeAttr('data-cart_type');

        if(that.data('postid')){
            $.ajax({
                url: wopb_core.ajax_quick_view,
                type: 'POST',
                data: {
                    action: 'wopb_quickview',
                    postid: that.data('postid'),
                    postList: that.data('list'),
                    wpnonce: wopb_quickview.security
                },
                beforeSend: function() {
                    _modalContent.html('');
                    _modal.removeClass(that.data('close-animation'));
                    _modal.addClass(that.data('open-animation'));
                    _modal.addClass('active');
                    modalLoading.find('.' + that.data('modal-loader')).removeClass('wopb-d-none');
                    _modal.addClass(that.data('modal_wrapper_class'));
                    modalLoading.addClass('active');
                },
                success: function(data) {
                    _modalContent.html(data);
                    afterModalContent.html('')
                    afterModalContent.append(
                        _modalContent.find('.wopb-quick-view-navigation').removeClass('wopb-d-none'),
                        _modalContent.find('.wopb-quick-view-zoom.wopb-zoom-2')
                    );
                    setTimeout(function() {
                        that.quickViewElement(_modal, _modalContent, afterModalContent);
                    }, 100);
                },
                complete:function() {
                    modalLoading.removeClass('active');
                    modalLoading.find('.' + that.data('modal-loader')).addClass('wopb-d-none');
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });
        }
    });

    // ------------------------
    // Quick View Element Initialize
    // ------------------------

    $.fn.quickViewElement = function(_modal, _modalContent, afterModalContent) {
        _modal.find(".ct-increase").remove();
        _modal.find(".ct-decrease").remove();

        //quick view image nav slier
        let sliderNav = $('.wopb-quick-view-image .wopb-quick-slider-nav');
        const vertical = (sliderNav.data('position') == 'left' || sliderNav.data('position') == 'right') ? true : false
        sliderNav.slick({
            slidesToShow: Number(sliderNav.data('collg')),
            vertical: vertical,
            asNavFor: '.wopb-quick-slider',
            focusOnSelect: true,
            dots: false,
            pauseOnHover: true,
            verticalSwiping:true,
            infinite: false,
            responsive: [
                {
                    breakpoint: 992,
                    settings: {
                        vertical: vertical,
                        slidesToShow: Number(sliderNav.data('colsm'))
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        vertical: vertical,
                        slidesToShow: Number(sliderNav.data('colxs'))
                    }
                }
            ],
            arrows: sliderNav.data('arrow') == 1 ? true : false,
            prevArrow: '<div class="wopb-slick-prev-nav"><svg viewBox="0 0 492 287" xmlns="http://www.w3.org/2000/svg"><path transform="translate(0 -.96)" d="m485.97 252.68-224.38-245.85c-4.2857-4.3102-9.9871-6.1367-15.585-5.8494-5.6186-0.28724-11.3 1.5392-15.586 5.8494l-224.4 245.85c-8.0384 8.0653-8.0384 21.159 0 29.225s21.081 8.0653 29.12 0l210.86-231.05 210.84 231.05c8.0384 8.0653 21.08 8.0653 29.119 0 8.0384-8.0645 8.0384-21.159 0-29.225z"/></svg></div>',
            nextArrow: '<div class="wopb-slick-next-nav"><svg viewBox="0 0 492 287" xmlns="http://www.w3.org/2000/svg"><path transform="translate(0 -.96)" d="m485.97 252.68-224.38-245.85c-4.2857-4.3102-9.9871-6.1367-15.585-5.8494-5.6186-0.28724-11.3 1.5392-15.586 5.8494l-224.4 245.85c-8.0384 8.0653-8.0384 21.159 0 29.225s21.081 8.0653 29.12 0l210.86-231.05 210.84 231.05c8.0384 8.0653 21.08 8.0653 29.119 0 8.0384-8.0645 8.0384-21.159 0-29.225z"/></svg></div>',
            rtl: $('html').attr('dir') && $('html').attr('dir') == 'rtl' ? true : false,
        }).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
            let totalSlides = slick.slideCount;
            sliderNav.find('.wopb-slick-next-nav').show();
            sliderNav.find('.wopb-slick-prev-nav').show();
            if (nextSlide === totalSlides - 1) {
              sliderNav.find('.wopb-slick-next-nav').hide();
            } else if(nextSlide === 0) {
              sliderNav.find('.wopb-slick-prev-nav').hide();
            }
        });
        sliderNav.find('.wopb-slick-prev-nav').hide();

        //quick view image thumbnail slier
        let quickSlider = $('.wopb-quick-view-image .wopb-quick-slider');
        quickSlider.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
            dots: quickSlider.data('dots') ? true : false,
            infinite: false,
            asNavFor: quickSlider.parents('.wopb-quick-view-image:first').find('.wopb-quick-slider-nav').length ? '.wopb-quick-slider-nav' : '',
            arrows: quickSlider.data('arrow') == 1 ? true : false,
            prevArrow: '<div class="wopb-slick-prev-large"><svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m145.19 238.58 215.5-215.5c5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0l-225.1 225.1c-5.3 5.3-5.3 13.8 0 19.1l225.1 225c2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4c5.3-5.3 5.3-13.8 0-19.1l-215.4-215.5z"></path></svg></div>',
            nextArrow: '<div class="wopb-slick-next-large"><svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m360.73 229.08-225.1-225.1c-5.3-5.3-13.8-5.3-19.1 0s-5.3 13.8 0 19.1l215.5 215.5-215.5 215.5c-5.3 5.3-5.3 13.8 0 19.1 2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4l225.1-225.1c5.3-5.2 5.3-13.8 0.1-19z"/></svg></div>',
            rtl: $('html').attr('dir') && $('html').attr('dir') == 'rtl' ? true : false,
        }).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
            let totalSlides = slick.slideCount;
            quickSlider.find('.wopb-slick-next-large').show();
            quickSlider.find('.wopb-slick-prev-large').show();
            if (nextSlide === totalSlides - 1) {
                quickSlider.find('.wopb-slick-next-large').hide();
            } else if(nextSlide === 0) {
                quickSlider.find('.wopb-slick-prev-large').hide();
            }
        });
        quickSlider.find('.wopb-slick-prev-large').hide();
                  
        const form_variation = _modal.find(".variations_form");
        const wc_product_gallery = _modal.find(".woocommerce-product-gallery");

        wc_product_gallery.each( function() {
            jQuery( this ).trigger( 'wc-product-gallery-before-init', [ this, wc_single_product_params ] );

            jQuery( this ).wc_product_gallery( wc_single_product_params );

            jQuery( this ).trigger( 'wc-product-gallery-after-init', [ this, wc_single_product_params ] );

            jQuery( this ).wc_product_gallery(  );
        } );

        let quickWrapper = $('.wopb-quick-view-wrapper')
        let galleryWrapper = quickWrapper.find('.wopb-quick-slider')
        let variationParam = {
            'product' : _modalContent,
            'form' : form_variation,
            'galleryWrapper' : galleryWrapper,
            'nav' : quickWrapper.find('.wopb-quick-slider-nav'),
            'defaultSlide' : quickWrapper.find('.wopb-quick-slider-nav .slick-current'),
            'defaultNavImage' : quickWrapper.find('.wopb-quick-slider-nav .slick-current img').attr('src'),
            'defaultProductImage' : galleryWrapper.find('.slick-active img:first').attr('src'),
            'source': 'singleProduct',
        }
        form_variation.wc_variation_form();
        quickWrapper.variationChange(variationParam)
        if(wopb_quickview.isVariationSwitchActive == 'true') {
            form_variation.wopVariationSwitch(variationParam);
        }

        // Start image zoom 1 on hover in quick
        const imageWrapper = $('.wopb-quick-view-image');
        const mainImage = imageWrapper.find(".wopb-main-image");
        const outer = imageWrapper.find(".wopb-zoom-image-outer");
        const inner = imageWrapper.find(".wopb-zoom-image-inner");
      
        mainImage.on("mousemove", handleMouseMove);
        mainImage.on("mouseleave", handleMouseLeave);
        function handleMouseMove(event) {
            inner.find('img').attr('src', $(this).attr('src'))
            outer.css("display", "block");
      
            let { width, height } = this.getBoundingClientRect();
            let xAxis = (event.offsetX / width) * 100;
            let yAxis = (event.offsetY / height) * 100;
            if (xAxis > 74 ) {
                xAxis = 74;
            }
            if (yAxis > 74) {
                yAxis = 74;
            }
            inner.css("transform", `translate(-${xAxis}%, -${yAxis}%)`);
        }
      
        function handleMouseLeave() {
            outer.css("display", "none");
        }
        // End image zoom 1 on hover in quick

        // Start Image zoom 2 popup in quick view
        $(document).on('click', '.wopb-quick-view-image .wopb-main-image', function(e) {
            const zoomImage = afterModalContent.find('.wopb-quick-view-zoom.wopb-zoom-2');
            zoomImage.find('img').attr('src', $(this).attr('src'));
            zoomImage.removeClass('wopb-d-none')
        })
        // End Image zoom 2 popup in quick view

        $(document).on('click', '.wopb-quick-view-zoom .wopb-zoom-close', function(e) {
            afterModalContent.find('.wopb-quick-view-zoom.wopb-zoom-2').addClass('wopb-d-none');
        })

        $(document).on('submit', '.wopb-quick-view-content form', function(e){
            e.preventDefault();
            let that = $(this);
            if( that.parents('.wopb-external-product:first').length ) {
                window.open(that.attr('action'))
                return;
            }
            let modalBody = _modal.find('.wopb-modal-body');
            let variation_selectors = modalBody.find('.variations_form .variations select[data-attribute_name]');
            let variation = {};
            variation_selectors.each(function() {
                let attribute_name = that.data('attribute_name');
                variation[attribute_name] = that.val();
            });
            $.ajax({
                url: wopb_core.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_addcart',
                    postid: modalBody.data('product_id'),
                    variationId: modalBody.find('input[name=variation_id]').val(),
                    quantity: modalBody.find('.qty').val(),
                    variation: variation,
                    cartType: that.data('cart_type') ?? '',
                    wpnonce: wopb_core.security
                },
                beforeSend: function() {
                    if(that.data('cart_type') === 'buy_now') {
                        modalBody.find('.wopb-quickview-buy-btn.single_add_to_cart_button').addClass('loading')
                    }else {
                        modalBody.find('.single_add_to_cart_button:not(.wopb-quickview-buy-btn)').addClass('loading')
                    }
                },
                success: function(response) {
                    if(that.data('cart_type') === 'buy_now' && that.data('redirect')) {
                        window.location.href = that.data('redirect');
                    }else {
                        if (response.message) {
                            modalBody.find('.woocommerce-message').removeClass('wopb-d-none');
                            modalBody.find('.woocommerce-message').html(response.message);
                            $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, that ] );
                            $( document.body ).trigger("update_checkout");
                            modalBody.animate({
                                scrollTop: modalBody.find('.woocommerce-message').offset().top - modalBody.offset().top + modalBody.scrollTop()
                            }, 300);
                        }
                    }
                },
                complete:function() {
                    modalBody.find('.single_add_to_cart_button').removeClass('loading')
                    if( modalBody.data('modal_close_after_cart') === 'yes' && that.data('cart_type') !== 'buy_now' ) {
                        _modal.find('.wopb-modal-close').trigger('click')
                    }
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });
        });
    }

    $(document).on('click', '.wopb-quickview-buy-btn:not(.disabled)', function(e){
        let that = $(this);
        let form = that.parents('form.cart:first');
        form.attr('data-redirect', that.data('redirect'));
        form.attr('data-cart_type', that.data('cart_type'));
        form.trigger('submit');
    });
})( jQuery );