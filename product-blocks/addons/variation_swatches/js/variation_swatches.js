(function ($) {
    'use strict';
    $(document).ready(function ($) {
        $('.wopb-compare-modal-content .wopb-loop-variations-form, .wopb-block-item .wopb-product-btn .wopb-loop-variations-form').remove()

        //call each variation form
        $(".variations_form").each(function () {
            // $(this).wc_variation_form();
            $(this).loopVariationSwitcherForm();
        });
    });

    //initialization WooCommerce variation form
    $.fn.loopVariationSwitcherForm = function(){
        return this.each( function(){
            let variationForm = $(this);
            let singleBuilder = $('.wopb-builder-container').find('.wp-block-columns');
            let product = variationForm.parents('.wopb-block-content-wrap:first');
            product = product.length < 1 ? variationForm.parents('.wopb-builder-cart:first') : product;
            product = product.length < 1 && singleBuilder.length > 0 ? singleBuilder : product;
            product = product.length < 1 ? variationForm.closest('.product.product-type-variable') : product;
            product = product.length < 1 ? variationForm.closest('.single-product .product') : product;
            let defaultProductSummary = variationForm.parents('.entry-summary:first');
            let defaultPriceHtml = product.find('.wopb-variation-switcher-price').html();
            if(!defaultPriceHtml) {
                defaultPriceHtml = product.find('.wopb-product-price').html();
            }
            let addToCartButton = product.find('.add_to_cart_button').filter(function() {
                return $(this).children().length === 0;
            }).first();
            let defaultAddToCartButtonText = product.find('.add_to_cart_button').filter(function() {
                return $(this).children().length === 0;
            }).first().text();
            setTimeout(function () {
                 product.backupProductImage();
            }, 100);
            $(variationForm)
            .on("click", ".wopb-variation-swatches .wopb-swatch", function (e) {
                product.parents('.wopb-block-item:first').find('.wopb-product-deals').remove();
                e.preventDefault(product);
                let swatch = $(this);
                let swatchSelect = swatch.closest(".value").find("select");
                let swatchLabel = swatch.parents('tr:first').find('.label label:first');
                let swatchLabelNameHtml = `<span class="wopb-swatch-label-value">: ${swatch.data('name')}</span>`;
                swatchLabel.find('.wopb-swatch-label-value').remove();
                if (!swatch.hasClass("disabled")) {
                    if ((swatchSelect.trigger("focusin"), !swatchSelect.find('option[value="' + swatch.attr("data-value") + '"]').length)) {
                        swatch.siblings(".wopb-swatch").removeClass("selected");
                        swatchSelect.val("");
                    }else {
                        if(swatch.hasClass("selected")) {
                            swatchSelect.val("");
                            swatch.removeClass("selected");
                        }else {
                            swatch.addClass("selected").siblings(".selected").removeClass("selected");
                            swatchSelect.val(swatch.attr("data-value"));
                            swatchLabel.append(swatchLabelNameHtml)
                        }
                    }
                     swatchSelect.trigger('change');
                }

                setTimeout(function () {
                    let swatchesClass = product.find('.wopb-variation-swatches:visible');
                    let selectedClass = product.find('.wopb-swatch.selected');
                    if(selectedClass.length < 1) {
                        product.resetDefaultImage()
                    }

                    if( swatchesClass.length !== selectedClass.length && !defaultProductSummary.length ) {
                        product.resetAddToCartText( addToCartButton, defaultAddToCartButtonText);
                    }
                }, 100)
            })
            .on("woocommerce_variation_select_change", function () {
                setTimeout(function () {
                    $(variationForm).find("tbody tr").each(function () {
                        let thisObject = $(this);
                        let option = thisObject.find("select option");
                        let selectedOption = option.filter(":selected");
                        let selectedOptionArray = [];
                        option.each(function (e, a) {
                            "" !== a.value && selectedOptionArray.push(a.value);
                        });
                        thisObject.find(".wopb-swatch").each(function () {
                            let option = $(this).attr("data-value");
                            if(selectedOptionArray.indexOf(option) > -1) {
                                $(this).removeClass("disabled")
                                $(this).find(".wopb-variation-swatch-tooltip").show();
                            }else {
                                $(this).addClass("disabled");
                                $(this).find(".wopb-variation-swatch-tooltip").hide();
                                selectedOption.length && option === selectedOption.val() && $(this).removeClass("selected");
                            }
                        });
                    });

                    let swatchesClass = product.find('.wopb-variation-swatches:visible');
                    let selectedClass = swatchesClass.find('.wopb-swatch.selected');
                    let currentAttribute = selectedClass.parents('.wopb-variation-swatches:first').attr("data-attribute_name");
                    let variationId = selectedClass.attr('data-variation_id');
                    if(selectedClass.length === 1 && (selectedClass.hasClass('wopb-swatch-color') || selectedClass.hasClass('wopb-swatch-image'))) {
                        let variations = JSON.parse(variationForm.attr("data-product_variations"));
                        let found = false;
                        for(const i in variations) {
                            if(found) continue;
                            if(variations.hasOwnProperty(i)) {
                                if (selectedClass.attr('data-value') === variations[i].attributes[currentAttribute] && variationId == variations[i].variation_id) {
                                    found = true;
                                    product.changeVariationImage(variations[i])
                                }
                            }
                        }
                    }

                }, 100);
            }).on("found_variation", function (e, variation) {
                if (variation && product.find('.woocommerce-product-gallery').length < 1 && singleBuilder.length < 1) {
                    let selectedVariation = {},
					variations = $(this).find( 'select[name^=attribute]' );
                    if ( !variations.length) {
                        variations = $(this).find( '[name^=attribute]:checked' );
                    }
                    if ( !variations.length) {
                        variations = $(this).find( 'input[name^=attribute]' );
                    }

                    variations.each( function() {
                        let thisItem = $( this ),
                            attributeName = thisItem.attr( 'name' ),
                            attributeValue = thisItem.val();
                            thisItem.removeClass( 'error' );
                        if ( attributeValue.length === 0 ) {
                            thisItem.addClass( 'required error' );
                        } else {
                            selectedVariation[attributeName] = attributeValue;
                        }
                    });
                    product.changeVariationImage(variation);
                    if( variation.is_in_stock ) {
                        product.addToCartButtonText(addToCartButton, variation, selectedVariation)
                    }else {
                        product.resetAddToCartText(addToCartButton, defaultAddToCartButtonText);
                    }
                    product.changeVariationPrice(variation, defaultPriceHtml)
                    if(variation.wopb_deal) {
                        product.parents('.wopb-block-item:first').find('.wopb-product-meta').after(variation.wopb_deal)
                        product.parents('.wopb-block-item:first').find('.wopb-product-deals').each(function(i, obj) {
                            loopcounter(obj);
                        });
                        product.parents('.wopb-block-item:first').find('.wopb-product-deals').css({'opacity': 1,'transform': 'translate(0,0)',});
                    }
                    return true;
		        }
            })
            .on("click", ".reset_variations", function () {
                $('.wopb-swatch-label-value').remove()
                product.parents('.wopb-block-item:first').find('.wopb-product-deals').remove();
                if (product.find('.woocommerce-product-gallery').length < 1 && singleBuilder.length < 1) {
                    product.resetAddToCartText(addToCartButton, defaultAddToCartButtonText);
                    product.resetVariationPrice(defaultPriceHtml);
                }

                $(this).closest("table.variations").find(".wopb-swatch.selected").removeClass("selected");
                $(this).closest("table.variations").find(".wopb-swatch.disabled").removeClass("disabled");

                setTimeout(function () {
                    // if(singleBuilder.length < 1) {
                        product.resetDefaultImage();
                    // }
                }, 100)
            });
        });
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

    $.fn.backupProductImage = function(){
        let productThumbnail = $(this).getProductImage();

		// Default image backup
		let backupAttributes = {
			"data-backup_alt": productThumbnail.attr('alt'),
			"data-backup_src": productThumbnail.attr('src'),
			"data-backup_large_image": productThumbnail.attr('data-large_image'),
			"data-backup_width": productThumbnail.attr('width'),
			"data-backup_height": productThumbnail.attr('height'),
			"data-backup_thumb": productThumbnail.parents('.flex-active-slide').data('thumb')
		}
		if( productThumbnail.attr('srcset') ) {
		    backupAttributes["data-backup_srcset"] = productThumbnail.attr( 'srcset' );
		    backupAttributes["data-backup_sizes"] = productThumbnail.attr( 'sizes' );
		}

		productThumbnail.attr(backupAttributes);
    }

    // Change the product image when variation found
	$.fn.changeVariationImage = function(variation){
        // image selector
        let productThumbnail = $(this).getProductImage();
        let flexMatchingNav = $(this).find('.woocommerce-product-gallery .flex-control-nav');
        let thumbSlickCurrentNav = $('.wopb-builder-container')
                .find('.woocommerce-product-gallery__wrapper .wopb-builder-slider-nav .slick-active');
        if(thumbSlickCurrentNav.length > 0) {
                let thumbSlickCurrentImage = '';
                thumbSlickCurrentImage = thumbSlickCurrentNav.find('img[src="' + variation.image.gallery_thumbnail_src + '"]');
                if(thumbSlickCurrentImage.length < 1) {
                    let builderDefaultNavImage = $('.wopb-builder-container').find('.woocommerce-product-gallery__wrapper .wopb-builder-slider-nav .slick-current')
                    builderDefaultNavImage.trigger('click');
                }else {
                    thumbSlickCurrentImage.parents('.slick-active:first').trigger('click');
                }
            }else if(flexMatchingNav.length > 0) {
            let flexMatchingImage = '';
            flexMatchingImage = flexMatchingNav.find('img[src="' + variation.image.gallery_thumbnail_src + '"]');
            if(!flexMatchingImage) {
                let defaultFlexMatchingImageBackup = productThumbnail.parents('.woocommerce-product-gallery__image.flex-active-slide:first').find('a img').data('backup_thumb');
                flexMatchingImage = flexMatchingNav.find('img[src="' + defaultFlexMatchingImageBackup + '"]');
            }
            flexMatchingImage.trigger('click')
        }else {
            let singleProductImageSection = $(this).find('.woocommerce-product-gallery .woocommerce-product-gallery__image');
            let singleProductZoomImage =  singleProductImageSection.find('.zoomImg');

            let ImageSrc = '';
            if(productThumbnail.parents('.woocommerce-LoopProduct-link:first').length > 0) {
                ImageSrc = variation.image.thumb_src;
            }else {
                ImageSrc = variation.image.full_src;
            }
            let attributes = {
                alt: variation.image.alt,
                src: ImageSrc,
                // width: variation.image.full_src_w,
                // height: variation.image.full_src_h
            };

            if( productThumbnail.attr('srcset') ) {
                attributes.srcset = variation.image.srcset;
                attributes.sizes = variation.image.sizes;
            }

            // Change variation image
            productThumbnail.attr(attributes);

            if(singleProductImageSection.length > 0) {
                singleProductImageSection.find('a').attr('href', variation.image.thumb_src)
                singleProductImageSection.find('a img').attr('data-large_image', variation.image.full_src)
            }
            if(singleProductZoomImage.length > 0) {
                let singleProductZoomImageAttributes = {
                    alt: variation.image.alt,
                    src: variation.image.full_src,
                    width: variation.image.full_src_w,
                    height: variation.image.full_src_H
                };
                singleProductZoomImage.attr(singleProductZoomImageAttributes);
            }
        }
	};
    let builderDefaultNav = $('.wopb-builder-container').find('.woocommerce-product-gallery__wrapper .wopb-builder-slider-nav .slick-current');
    $.fn.resetDefaultImage = function(){
		// Image selector
		let productThumbnail = $(this).getProductImage();
		let flexMatchingImage = $(this).find('.woocommerce-product-gallery .flex-control-nav img[src="' + productThumbnail.attr('data-backup_thumb') + '"]');
		if(flexMatchingImage.length > 0) {
		    flexMatchingImage.trigger('click')
        }else if(builderDefaultNav.length > 0) {
            builderDefaultNav.trigger('click');
        } else {
		   // Get backup attributes before reset
            let backupAttributes = {
                alt: productThumbnail.attr('data-backup_alt'),
                src: productThumbnail.attr('data-backup_src'),
                width: productThumbnail.attr('data-backup_width'),
                height: productThumbnail.attr('data-backup_height')
            }
            if( productThumbnail.attr('srcset') ) {
                backupAttributes["srcset"] = productThumbnail.attr( 'data-backup_srcset' );
                backupAttributes["sizes"]  = productThumbnail.attr( 'data-backup_sizes' );
            }
            backupAttributes["data-large_image"]  = productThumbnail.attr( 'data-backup_large_image' );
            productThumbnail.attr(backupAttributes).removeAttr('srcset');
        }
    };

    $.fn.getProductImage = function() {
        let productThumbnail = ''
        let singleProductImage = $(this).find('.woocommerce-product-gallery .woocommerce-product-gallery__image a img');
        let builderProductImage = $('.wopb-builder-container').find('.wopb-product-gallery-wrapper .slick-active img')
        if (singleProductImage.length < 1) { //find image in blocksy theme
            singleProductImage = $(this).find('.woocommerce-product-gallery .flexy .ct-image-container img');
        }
        if (singleProductImage.length > 0) {
            productThumbnail = singleProductImage;
        }else if(builderProductImage.length > 0) {
            productThumbnail = builderProductImage;
        }else{
            //find image in woocommerce loop product
            let wcImageSection = $(this).find('img:first').parents('a:first');
            if(wcImageSection.length > 0) {
                productThumbnail = wcImageSection.find('img.attachment-woocommerce_thumbnail');
                if(productThumbnail.length < 1){
                    productThumbnail = wcImageSection.find('img.wp-post-image');
                }
                if(productThumbnail.length < 1){
                    productThumbnail = wcImageSection.find('img').first();
                }
            }
            //find image in woocommerce loop product

            //find image in product blocks grid
            if(productThumbnail.length < 1){
                productThumbnail = $(this).find('.wopb-block-image a img');
            }
        }
		return productThumbnail;
    }

    $.fn.addToCartButtonText = function(addToCartButton, variation, selectedVariation) {
        let product = $(this);
        let data_add_to_cart_text = addToCartButton.data('add-to-cart-text');
        if(!data_add_to_cart_text) {
            data_add_to_cart_text = 'Add To Cart';
        }

        addToCartButton.attr('data-variation_id', variation.variation_id);
        addToCartButton.attr('data-variation', JSON.stringify(selectedVariation));
        if(product.find('.wopb-product-meta:visible .add_to_cart_button').length > 0) {
            addToCartButton.find('[class^="wopb-tooltip-text-"]').text(data_add_to_cart_text);
        }else {
            addToCartButton.text(data_add_to_cart_text);
        }

        addToCartButton.addClass('wopb-loop-add-to-cart-button');
    }

    $.fn.resetAddToCartText = function(addToCartButton, defaultAddToCartButtonText) {
        let product = $(this);
        if(product.find('.wopb-product-meta:visible .add_to_cart_button').length > 0) {
            addToCartButton.find('[class^="wopb-tooltip-text-"]').text(defaultAddToCartButtonText);
        }else {
            addToCartButton.text(defaultAddToCartButtonText);
        }
        addToCartButton.removeClass('wopb-loop-add-to-cart-button');
        addToCartButton.removeAttr('data-variation_id');
        addToCartButton.removeAttr('data-variation');
    }

    $.fn.changeVariationPrice = function(variation, defaultPriceHtml) {
        let product = $(this);
        let variationSwitcherPrice = product.find('.wopb-variation-switcher-price');
        if(variationSwitcherPrice.length < 1) {
            variationSwitcherPrice = product.find('.wopb-product-price');
        }
        variationSwitcherPrice.html('');
        if(variation.price_html) {
            variationSwitcherPrice.html(variation.price_html);
        }else {
            variationSwitcherPrice.html(defaultPriceHtml);
        }
    }

    $.fn.resetVariationPrice = function(defaultPriceHtml) {
        let product = $(this);
        let variationSwitcherPrice = product.find('.wopb-variation-switcher-price');
        if(variationSwitcherPrice.length < 1) {
            variationSwitcherPrice = product.find('.wopb-product-price');
        }
        variationSwitcherPrice.html('');
        variationSwitcherPrice.html(defaultPriceHtml);
    }
})(jQuery);