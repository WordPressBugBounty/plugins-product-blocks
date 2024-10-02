(function ($) {
    'use strict'
    $(document).ready(function ($) {
        $('.wopb-compare-modal-content .wopb-loop-variations-form, .wopb-block-item .wopb-product-btn .wopb-loop-variations-form').remove()
        $(document).on('wopbAjaxComplete', function () {
            $(this).variationForm()
        });
        $(this).variationForm()

    });

    $.fn.variationForm = function () {
        //call each variation form
        $(".variations_form").each(function () {
            let that = $(this);
            if( that.hasClass('wopb-variation-init') ) {
                return
            }
            let variationParam = {'form': that}
            let builderCart = that.parents('.wopb-builder-cart:first');
            if (!builderCart.length) {
                let singleProduct = that.parents('.single-product:first').find('.product:first')
                let defaultLoop = that.parents('.product.product-type-variable:first:not([id])').not('.single-product')
                let wopbLoop = that.parents('.wopb-block-content-wrap:first');
                if (defaultLoop.length || wopbLoop.length ) {
                    let product = defaultLoop;
                    let imgItem = 'img:first';
                    let switcherPrice = defaultLoop.find('.wopb-variation-switcher-price');

                    if( wopbLoop.length ) {
                        product = wopbLoop;
                        imgItem = '.wopb-block-image a img:first';
                        switcherPrice = wopbLoop.find('.wopb-product-price');
                        variationParam = {
                            ...variationParam,
                            'srcSelector': 'full_src'
                        }
                    }
                    variationParam = {
                        ...variationParam,
                        'product': product,
                        'imgItem': imgItem,
                        'switcherPrice': switcherPrice,
                        'defaultPriceHtml': switcherPrice.html(),
                        'source': 'loopProduct',
                    }
                    that.wopVariationSwitch(variationParam);
                } else if (singleProduct.length) {
                    variationParam = {
                        ...variationParam,
                        'product': singleProduct,
                        'source': 'singleProduct',
                    }
                    that.wopVariationSwitch(variationParam);
                }

                $(this).wc_variation_form();
            }
        });
    }

    //initialization WooCommerce variation form
    $.fn.wopVariationSwitch = function(variationParam = {}){
        if( $(this).hasClass('wopb-variation-init') ) {
            return
        }
        $(this).addClass('wopb-variation-init')
        let {product, form, source = ''} = variationParam
        let cartBtn = product.find('.add_to_cart_button');
        let defaultCartText = cartBtn.filter(function() {
                return ! $(this).children().length;
            }).first().text();
        if( source !== 'singleProduct' ) {
            product.backupProductImage(variationParam);
        }
        $(form).on("click", ".wopb-variation-swatches .wopb-swatch", function (e) {
            e.preventDefault(product);
            product.parents('.wopb-block-item:first').find('.wopb-product-deals').remove();
            let swatch = $(this);
            let swatchSelect = swatch.closest(".value").find("select");
            let swatchLabel = swatch.parents('tr:first').find('.label label:first');
            let swatchLabelHtml = `<span class="wopb-swatch-label-value">: ${swatch.data('name')}</span>`;
            swatchLabel.find('.wopb-swatch-label-value').remove();
            if (!swatch.hasClass("disabled")) {
                if (
                    swatchSelect.trigger("focusin") &&
                    !swatchSelect.find('option[value="' + swatch.attr("data-value") + '"]').length
                ) {
                    swatch.siblings(".wopb-swatch").removeClass("selected");
                    swatchSelect.val("");
                }else {
                    if(swatch.hasClass("selected")) {
                        swatchSelect.val("");
                        swatch.removeClass("selected");
                    }else {
                        swatch.addClass("selected").siblings(".selected").removeClass("selected");
                        swatchSelect.val(swatch.attr("data-value"));
                        swatchLabel.append(swatchLabelHtml)
                    }
                }
                swatchSelect.trigger('change');
            }

            setTimeout(function () {
                let swatchClass = product.find('.wopb-variation-swatches');
                let selectedClass = product.find('.wopb-swatch.selected');
                let currentAttr = selectedClass.parents('.wopb-variation-swatches:first').attr("data-attribute_name");
                let variationId = selectedClass.attr('data-variation_id');

                if(selectedClass.length == 1) {
                    if( selectedClass.hasClass('wopb-swatch-color') || selectedClass.hasClass('wopb-swatch-image') ) {
                        let variations = JSON.parse(form.attr("data-product_variations"));
                        let found = false;
                        for (const i in variations) {
                            if (found) continue;
                            if (variations.hasOwnProperty(i)) {
                                if (selectedClass.attr('data-value') === variations[i].attributes[currentAttr] && variationId == variations[i].variation_id) {
                                    found = true;
                                    let finalVariation = variations[i];
                                    form.trigger('found_variation', [ finalVariation ]);
                                }
                            }
                        }
                    }
                }else if(source !== 'singleProduct' && selectedClass.length < 1) {
                    form.find('.reset_variations').trigger('click')
                }
                if(
                    source !== 'singleProduct' &&
                    (selectedClass.length != swatchClass.length)
                ) {
                    product.resetCartText(cartBtn, defaultCartText);
                }

                $(form).find("tbody tr").each(function () {
                    let that = $(this);
                    let option = that.find("select option");
                    let selectedOption = option.filter(":selected");
                    let selectedArray = [];
                    option.each(function (e, a) {
                        "" !== a.value && selectedArray.push(a.value);
                    });
                    that.find(".wopb-swatch").each(function () {
                        option = $(this).attr("data-value");
                        if(selectedArray.indexOf(option) > -1) {
                            $(this).removeClass("disabled")
                            $(this).find(".wopb-variation-swatch-tooltip").show();
                        }else {
                            $(this).addClass("disabled");
                            $(this).find(".wopb-variation-swatch-tooltip").hide();
                            selectedOption.length && option === selectedOption.val() && $(this).removeClass("selected");
                        }
                    });
                });
            }, 100)
        })

        if( source !== 'singleProduct' ) {
            $(form).on("found_variation", function (e, variation) {
                if (variation) {
                        let that = $(this);
                        let swatchClass = product.find('.wopb-variation-swatches');
                        let selectedClass = product.find('.wopb-swatch.selected');
                        let selectedVariation = {},
                            variations = that.find('select[name^=attribute]');
                        variations = !variations.length ? that.find('[name^=attribute]:checked') : variations;
                        variations = !variations.length ? that.find('input[name^=attribute]') : variations;

                        variations.each(function () {
                            let thisItem = $(this),
                                attributeName = thisItem.attr('name'),
                                attributeValue = thisItem.val();
                            thisItem.removeClass('error');
                            if (attributeValue.length === 0) {
                                thisItem.addClass('required error');
                            } else {
                                selectedVariation[attributeName] = attributeValue;
                            }
                        });
                        if( selectedClass.length == swatchClass.length ) {
                            if ( variation.is_in_stock ) {
                                product.cartBtnText(cartBtn, variation, selectedVariation)
                            }
                            if (variationParam.defaultPriceHtml) {
                                product.changeVariationPrice(variation, variationParam)
                            }
                            if (variation.wopb_deal) {
                                product.showDeal(variation)
                            }
                        }
                        if( ! variation.is_in_stock ) {
                            product.resetCartText(cartBtn, defaultCartText);
                        }
                        product.changeVariationImage(variation, variationParam);
                        return true;
                }
            })
        }
        $(form).on("click", ".reset_variations", function (event) {
            event.preventDefault()
            $(this).closest("table.variations").find(".wopb-swatch.selected").removeClass("selected");
            $(this).closest("table.variations").find(".wopb-swatch.disabled").removeClass("disabled");
            if( source !== 'singleProduct' ) {
                $('.wopb-swatch-label-value').remove()
                product.parents('.wopb-block-item:first').find('.wopb-product-deals').remove();
                product.resetCartText(cartBtn, defaultCartText);
                if( variationParam.defaultPriceHtml ) {
                    product.resetVariationPrice(variationParam);
                }
                product.resetDefaultImage(variationParam);
            }
        });
    }

    //Show Deal
    $.fn.showDeal = function( variation ){
        let that = $(this)
        that.parents('.wopb-block-item:first').find('.wopb-product-new-meta').after(variation.wopb_deal)
        that.parents('.wopb-block-item:first').find('.wopb-product-deals').each(function (i, obj) {
            loopcounter(obj);
        });
        that.parents('.wopb-block-item:first').find('.wopb-product-deals').css({
            'opacity': 1,
            'transform': 'translate(0,0)',
        });
    }

    // Change the product image when variation found
    $.fn.changeVariationImage = function( variation, variationParam ){
        let thumbnail = $(this).getProductImage(variationParam);
        let attributes = {
            alt: variation.image.alt,
            src: variationParam.srcSelector ? variation.image[variationParam.srcSelector] : variation.image.thumb_src,
        };
        thumbnail.attr(attributes);
    };

    $.fn.resetDefaultImage = function( variationParam ){
        let thumbnail = $(this).getProductImage(variationParam);
        let backupAttr = {
            alt: thumbnail.attr('data-backup_alt'),
            src: thumbnail.attr('data-backup_src'),
            width: thumbnail.attr('data-backup_width'),
            height: thumbnail.attr('data-backup_height')
        }
        thumbnail.attr(backupAttr);
    };

    $.fn.backupProductImage = function(variationParam){
        let thumbnail = $(this).getProductImage(variationParam);

        // Default image backup
        let attr = {
            "data-backup_alt": thumbnail.attr('alt'),
            "data-backup_src": thumbnail.attr('src'),
            "data-backup_width": thumbnail.attr('width'),
            "data-backup_height": thumbnail.attr('height'),
        }
        thumbnail.attr(attr);
    }

    $.fn.getProductImage = function( variationParam = {} ) {
        let {getImage, imgItem} = variationParam
        let thumbnail = ''

        if(getImage ) {
            thumbnail = variationParam.getImage
        }else if( imgItem ) {
            thumbnail = $(this).find(imgItem)
        }
        return thumbnail;
    }

    $.fn.cartBtnText = function( cartBtn, variation, selectedVariation ) {
        cartBtn.each(function () {
            let btn = $(this);
            let btnText = btn;
            if( btn.parents('.wopb-cart-action:first').find('.wopb-cart-tooltip:first').length ) {
                btnText = btn.parents('.wopb-cart-action:first').find('.wopb-cart-tooltip:first');
            }else if( btn.children().length ) {
                return
            }
            let cartText = btn.data('add-to-cart-text');
            if(!cartText) {
                cartText = 'Add To Cart';
            }
            btn.attr('data-variation_id', variation.variation_id);
            btn.attr('data-variation', JSON.stringify(selectedVariation));
            btnText.text(cartText);
            btn.addClass('wopb-loop-add-to-cart-button');
        })
    }

    $.fn.resetCartText = function(cartBtn, defaultCartText) {
        cartBtn.each(function () {
            let btn = $(this);
            let btnText = btn;
            if( btn.parents('.wopb-cart-action:first').find('.wopb-cart-tooltip:first').length ) {
                btnText = btn.parents('.wopb-cart-action:first').find('.wopb-cart-tooltip:first');
            }else if( btn.children().length ) {
                return
            }
            btnText.text(defaultCartText);
            btn.removeClass('wopb-loop-add-to-cart-button');
            btn.removeAttr('data-variation_id');
            btn.removeAttr('data-variation');
        })
    }

    $.fn.changeVariationPrice = function(variation, variationParam) {
        variationParam.switcherPrice.html('');
        if(variation.price_html) {
            variationParam.switcherPrice.html(variation.price_html);
        }else {
            variationParam.switcherPrice.html(variationParam.defaultPriceHtml);
        }
    }

    $.fn.resetVariationPrice = function(variationParam) {
        variationParam.switcherPrice.html('');
        variationParam.switcherPrice.html(variationParam.defaultPriceHtml);
    }

    $(document).on('click', '.wopb-loop-add-to-cart-button', function (e) {
        e.preventDefault();
        let thisBtn      = $(this),
            productId    = thisBtn.data( 'product_id' ),
            variationId  = thisBtn.attr( 'data-variation_id' ),
            variation    = thisBtn.attr( 'data-variation' );

        productId = Math.abs( parseFloat( productId ).toFixed(0) );
        variationId = Math.abs( parseFloat( variationId ).toFixed(0) );

        if ( (isNaN( productId ) || productId === 0) || (isNaN( variationId ) || variationId === 0) ) {
            return true;
        }

        if(thisBtn.is('.wc-variation-is-unavailable')){
            return window.alert( wc_add_to_cart_variation_params.i18n_unavailable_text );
        }

        if ( '' !== variation ) {
            variation = JSON.parse( variation );
        }

        let data = {
            action: 'wopb_variation_loop_add_cart',
            product_id: productId,
            variation_id: variationId,
            variation: variation
        };

        $( document.body ).trigger( 'adding_to_cart', [ thisBtn, data ] );
        thisBtn.addClass( 'loading' );

        // Ajax add to cart request
        $.ajax({
            type: 'POST',
            url: wopb_core.ajax_variation_loop_add_cart,
            data: data,
            dataType: 'json',
            success: function ( response ) {
                if ( ! response ) {
                    return;
                }

                // remove thickbox
                tb_remove();

                if ( response.error && response.product_url ) {
                    window.location = response.product_url;
                    return;
                }
                // Trigger event so themes can refresh other areas.
                $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, thisBtn ] );
                $( document.body ).trigger("update_checkout");

                // Redirect to cart option
                if ( wc_add_to_cart_params.cart_redirect_after_add === 'yes' ) {
                    window.location = wc_add_to_cart_params.cart_url;
                    return;
                }

                thisBtn.removeClass('loading');
                thisBtn.parents('.wopb-cart-action:first').addClass('wopb-active');
            },
            error: function(errorThrown) {
                thisBtn.removeClass('loading');
            },
        });
    })
})(jQuery);