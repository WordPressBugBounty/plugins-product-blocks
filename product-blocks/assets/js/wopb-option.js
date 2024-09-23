(function($) {
    'use strict';

    $('.wopb-select2').selectWoo();
    // Product Tab Blocks support Showing in Backend Editor
    $(document).on( 'click', '.block-editor-page .wc-tabs li', function(e) {
        e.preventDefault();
        $('.wc-tabs li').removeClass('active');
        $(this).addClass('active');
        const selectId = $(this).attr('aria-controls');
        $('.woocommerce-Tabs-panel').hide();
        $('.woocommerce-Tabs-panel#'+selectId).show();
    });

    // Color Picker Support for Products Attributes
    if ( $.isFunction( $.fn.wpColorPicker ) ) {
        $( '.wopb-color-picker' ).wpColorPicker();
    }

    // Block Editor Page
    if ( $('body').hasClass('block-editor-page') ) {
        $('body').addClass( 'wopb-editor-'+wopb_option.width );
    }

    // Saved Template Action Button
    const savedBtn = $('.wopb-saved-templates-action');
    if ( savedBtn.length > 0 ) {
        $('.page-title-action').addClass( 'wopb-save-templates-pro' ).text( savedBtn.data('text') ).attr( 'target', '_blank' ).attr( 'href', savedBtn.data('link') );
    }
     // Saved Template Back Button URL Change
     $(document).ready( function() {
        if ( $('.block-editor-page').length > 0 && wopb_option.post_type == 'wopb_templates' ) {
            setTimeout( function() {
                if ( $('.edit-post-fullscreen-mode-close').length > 0 ) {
                    $('.edit-post-fullscreen-mode-close')[0].href = wopb_option.saved_template_url
                }
            }, 100);
        }
    });

    // Popup Flip Feature Image
	$('#flipimage-feature-image').on( 'click', '#upload_feature_image_button', function(e) {
        e.preventDefault();
        const button_id = $(this).attr('id');
		const field_id = button_id.replace('_button', '');
        let mdeia = wp.media({
            title: $(this).data( 'uploader_title' ),
            button: { text: $(this).data( 'uploader_button_text' ) },
            multiple: false
        }).open().on('select', function (e) {
            const attachment = mdeia.state().get('selection').first().toJSON();
            $('#'+field_id).val(attachment.id);
            $('#flipimage-feature-image .wopb-flip-image img').attr('src',attachment.url).show();
            $('#flipimage-feature-image .wopb-flip-image img').show();
            $('#' + button_id).attr('id', 'remove_feature_image_button');
            $('#remove_feature_image_button').text('Remove Flip Image');
        });
	});
    // Remove Flip Feature Image
	$('#flipimage-feature-image').on( 'click', '#remove_feature_image_button', function(e) {
		e.preventDefault();
		$( '#upload_feature_image' ).val( '' );
		$( '#flipimage-feature-image .wopb-flip-image img' ).attr( 'src', '' );
		$( '#flipimage-feature-image .wopb-flip-image img' ).hide();
		$( this ).attr( 'id', 'upload_feature_image_button' );
		$( '#upload_feature_image_button' ).text( 'Set Flip Image' );
    });


    // Open Media Library in Product Image Attributes (Variation Swatches)
    $('#wopb-term-upload-img-btn').on( 'click', function (e) {
        e.preventDefault();
        let object = $(this);
        let mdeia = wp.media({
            title: 'Attribute Term Image',
            multiple: false
        }).open().on('select', function (e) {
            let selectedImage = mdeia.state().get('selection').first().toJSON();
            object.parent().prev("#wopb-term-img-thumbnail").find("img").attr("src", selectedImage.sizes.thumbnail.url);
            object.parent().find("#wopb-term-img-remove-btn").removeClass('wopb-d-none');
            object.parent().find('#wopb-term-img-input').val(selectedImage.id);
        });
    });
    // Remove Image from Product Image Attributes (Variation Swatches)
    $('#wopb-term-img-remove-btn').click(function (e) {
        $(this).parent().prev("#wopb-term-img-thumbnail").find("img").attr("src", wopb_option.url + 'assets/img/wopb-fallback-img.png');
        $(this).parent().find('#wopb-term-img-input').val('');
    });

    // Dashboard Submenu Support Active & Inactive Class
    $(document).on('click', '#wopb-dashboard-wopb-settings-tab li a, #toplevel_page_wopb-settings ul li a', function(e) {
        let value = $(this).attr('href')
        if (value) {
            value = value.split('#');
            if (typeof value[1] != 'undefined' && value[1].indexOf('demoid') < 0 && value[1]) {
                $('#toplevel_page_wopb-settings ul li a').closest('ul').find('li').removeClass('current');
                $(this).closest('li').addClass('current'); // Submenu click
                $('#toplevel_page_wopb-settings ul li a[href$='+value[1]+']').closest('li').addClass('current'); // Dash Nav Menu click
                if (value[1] == 'home') {
                    $('#toplevel_page_wopb-settings ul li.wp-first-item').addClass('current');
                }
            }
        }
    });
    // Dashboard Submenu Support Active & Inactive Class
    $('#toplevel_page_wopb-settings ul > li').removeClass('current');
    $('#toplevel_page_wopb-settings ul > li > a').each(function (e) {
        const selector = $(this).attr('href');
        if ( selector && selector.indexOf("?page=wopb-settings") > 0 ) {
            if ( $(this).hasClass('wp-first-item') != false ) {
                $(this).attr('href' , selector+'#home' )
            } else if ( wopb_option.settings ) {
                if ( (selector.indexOf('#builder') > 0 && wopb_option.settings?.wopb_builder != 'true') ||
                    (selector.indexOf('#custom-font') > 0 && wopb_option.settings?.wopb_custom_font != 'true') ||
                    (selector.indexOf('#size-chart') > 0 && wopb_option.settings?.wopb_size_chart != 'true') ||
                    (selector.indexOf('#saved-templates') > 0 && wopb_option.settings?.wopb_templates != 'true')
                ) {
                    $(this).hide();
                }
            }
        } else if ( selector.indexOf("?page=go_productx_pro") > 0 ) {
            $(this).attr('target', '_blank');
        }
        let pram = (selector.indexOf('#') > 0
                ? selector.split('#')[1]
                :'home'
            )
        if ( window.location.hash == '#' + pram ) {
            $(this).parent('li').addClass('current');
        }
        let menuId = 'productx-submenu-' + pram;
        $(this).attr( 'id', menuId );
        let menu = $('#' + menuId);
        // menu.parents('.toplevel_page_wopb-settings').removeClass('wp-not-current-submenu').addClass('wp-has-current-submenu wp-menu-open');
    });


    // Custom Font Support Add
    $(".wopb-font-variation-action").on('click', function(e) {
        const content = $('.wopb-custom-font-copy')[0].outerHTML;;
        $(this).before( content.replace("wopb-custom-font-copy", "wopb-custom-font wopb-font-open") );
    });
    $(document).on('click', ".wopb-custom-font-close", function(e) {
        $(this).closest('.wopb-custom-font-container').removeClass('wopb-font-open');
    });
    $(document).on('click', ".wopb-custom-font-edit", function(e) {
        $(this).closest('.wopb-custom-font-container').addClass('wopb-font-open');
    });
    $(document).on('click', ".wopb-custom-font-delete", function(e) {
        $(this).closest('.wopb-custom-font').remove();
    });
    $(document).on('click', '.wopb-font-upload', function(e) {
        const that = $(this);
        $(this).addClass('rty')
        const wopbCustomFont = wp.media({
            title: 'Add Font',
            button: { text: 'Add New Font' },
            library: {
                type: that.attr('type')
            },
            multiple: false,
        });
        wopbCustomFont.on(
            'select',
            function () {
                const attachment = wopbCustomFont.state().get( 'selection' ).first().toJSON();
                const allowedExtensions = that.attr('extension');
                const fileExtension = attachment.url.split('.').pop().toLowerCase();
                if (fileExtension !== allowedExtensions) {
                    if (confirm(`Invalid file type. Please upload ${allowedExtensions.toUpperCase()} file`)) {
                        wopbCustomFont.open()
                    } else {
                        return;
                    }
                }else {
                    that.closest('.wopb-font-file-list').find('input').val(attachment.url)
                }
            }
        );
        wopbCustomFont.open();
    });
    $(document).on('change', '.wopb-font-file-list input', function(e) {
        const that = $(this);
        if( that.val() ) {
            const allowedExtension = that.parents('.wopb-font-file-list:first').find('.wopb-font-upload').attr('extension');
            const fileUrl = that.val().trim();
            if (!fileUrl.toLowerCase().endsWith('.' + allowedExtension)) {
                alert(`Please enter a valid URL ending with .${allowedExtension} extension`);
            }
        }
    });

    // Name Your Price Addon Toggle
    function namePriceCheck(that, isVariable = false) {
        const selector = isVariable ? that.closest('.variable_pricing') : that.closest('.show_if_simple');
        if ( that.is(':checked') ) {
            selector.find('.wopb-name-price-wrap').show();
        } else {
            selector.find('.wopb-name-price-wrap').hide();
        }
    }
    
    $('.wopb-name-price-enable').each(function() {
        namePriceCheck($(this));
    });

    $(document).on('click', '.woocommerce_variation', function() {
        if ( $('.wopb-name-price-enable').length > 0 ) {
            namePriceCheck( $(this).find('.wopb-name-price-enable'), true );
        }
    });

    $(document).on('change', '.wopb-name-price-enable', function() {
        namePriceCheck($(this));
    });

    // Move notice into after heading
    $(document).ready( function() {
        const noticeWrapper = $('.wopb-notice-wrapper');
        if ( noticeWrapper.length > 0  ) {
            setTimeout( function() {
                noticeWrapper.each(function(e){
                    const notice = $(this);
                    if($('#wpwrap .wrap .wp-header-end').length>0) {
                        $('#wpwrap .wrap .wp-header-end').after(notice);
                    } else {
                        $('#wpwrap .wrap h1').after(notice);
                    }
                });
            }, 100);
        }
    });

    $('.wopb-open-media').on('click', function () {
        let that = $(this)
        let target = that.parent().find(that.attr('data-target'))
        let target2 = that.parent().find(that.attr('data-target2'))
        let wopbMedia = wp.media({
            title: that.attr('data-title') ?? 'Insert File',
            button: {
                text: that.attr('data-btn-text') ?? 'Add New File'
            },
            multiple: false,
            library: {
                type : that.attr('data-library-type') ?? '',
            }
        }).on(
            'select',
            function () {
                if(target.length > 0) {
                    let file = wopbMedia.state().get('selection').first().toJSON();
                    target.val( file.url );
                    if ( target2.length > 0 ) {
                        target2.val( file.id )
                    }
                }
            }
        ).open();
    })

    // -------------------------------
    // Product Video Addon [START]
    // -------------------------------
    $('.wopb-video-meta-fields input[name=wopb_video_type]').on('change', function () {
        let that = $(this)
        $('.wopb-video-meta-fields .wopb-video-type-tab').removeClass('wopb-active')
        that.parents('.wopb-video-type-tab:first').addClass('wopb-active')
        let mediaDepend = that.parents('.wopb-video-meta-fields:first').find('.wopb-media-depend');
        // that.parents('.wopb-video-meta-fields:first').find('#wopb_video_url').val('');
        if(that.val() === 'youtube') {
            mediaDepend.addClass('wopb-d-none')
        }else {
            mediaDepend.removeClass('wopb-d-none')
        }
    })
    $('.wopb-video-meta-fields input[name=wopb_video_autoplay]').on('change', function () {
        let hoverField = $(this).parents('.wopb-video-meta-fields:first').find('.wopb_video_hover_field');
        if($(this).prop('checked') == true) {
            hoverField.addClass('wopb-d-none')
        }else {
            hoverField.removeClass('wopb-d-none')
        }
    })
    // -------------------------------
    // Product Video Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Size Chart Addon [START]
    // -------------------------------
    const chartControlCol = $(".wopb-sc-control-col");
    let chartInputRow = $(".wopb-sc-input-row");

    //Add column when click plus
    chartControlCol.on('click', '.wopb-sc-add-item', function (e) {
        let index = chartControlCol.find( '.wopb-sc-add-item' ).index( $(this) );
        $(this).parent().after( $(this).parent().clone() );
        chartInputRow.each(function (i) {
            let column = $(this).find( '.wopb-sc-input-col' ).eq( index );
            let content = column.clone();
            column.after(content);
            // let input = content.find( '.wopb-sc-solumn' );
            if(column.is('td')) {
                // input.val('')
            }
            // input.attr('name', 'wopb_sc_column[' + i + '][]')
        })
        chartControlCol.find('.wopb-sc-del-item').removeClass('wopb-d-none')
    });

    //Remove column when click minus
    chartControlCol.on('click', '.wopb-sc-del-item', function (e) {
        let delColumn = chartControlCol.find('.wopb-sc-del-item');
        let index = delColumn.index( $(this) );
        chartInputRow.each(function () {
            $(this).find( '.wopb-sc-input-col' ).eq( index ).remove();
        })
        $(this).parent().remove();
        if( delColumn.length === 2 ) {
            delColumn.addClass('wopb-d-none');
        }
    });

    //Add row when click plus
    $(document).on('click', '.wopb-sc-input-row .wopb-sc-add-item', function (e) {
        let row = $(this).parents('.wopb-sc-input-row:first');
        let content = row.clone();
        row.after(content);
        // let input = content.find( '.wopb-sc-solumn' );
        chartInputRow = $(".wopb-sc-input-row");
        // input.val('')
        // input.attr('name', 'wopb_sc_column[' + chartInputRow.index( content ) + '][]')
        chartInputRow.find('.wopb-sc-del-item').removeClass('wopb-d-none')
    });

    //Remove row when click minus
    $(document).on('click', '.wopb-sc-input-row .wopb-sc-del-item', function (e) {
        let delRow = chartInputRow.find('.wopb-sc-del-item');
        $(this).parents('.wopb-sc-input-row:first').remove();
        if( delRow.length === 2 ) {
            delRow.addClass('wopb-d-none');
        }
        chartInputRow = $(".wopb-sc-input-row");
    });
    $('.post-type-wopb-size-chart form#post').on('submit', function (e) {
        const columnObj = [];
        chartInputRow.each(function (i) {
            columnObj[i] = []
            $(this).find('.wopb-sc-solumn').each(function () {
                columnObj[i].push( $(this).val() )
            })
        })
        $('#wopb-size-chart-table #wopb-sc-column-array').val(JSON.stringify(columnObj));
        return true;
    })

    //Change table heading
    $('#wopb-size-chart-table #wopb-sc-heading-position').on('change', function () {
        let that = $(this);
        $('.wopb-sc-head-col').find('.wopb-sc-solumn').val(that.attr('data-content-default'));
        if( that.prop('checked') ) {
            $('.wopb-sc-head-col').find('.wopb-sc-solumn').val(that.attr('data-content-default'));
            $('td.wopb-sc-input-col').removeClass('wopb-sc-head-col')
            chartInputRow.first().find('td.wopb-sc-input-col').addClass('wopb-sc-head-col')
        }else {
            chartInputRow.each(function () {
                let inputCol = $(this).find('td.wopb-sc-input-col');
                inputCol.removeClass('wopb-sc-head-col')
                inputCol.first().addClass('wopb-sc-head-col')
            })
        }
        $('.wopb-sc-head-col').find('.wopb-sc-solumn').val(that.attr('data-head-default'));
    })

    //Hide field if apply on all product
    $('#wopb-size-chart-assign #wopb-sc-all-product').on('change', function () {
        if( $(this).prop('checked') ) {
            $('.wopb-sc-category, .wopb-sc-include-products').addClass('wopb-d-none');
        }else {
            $('.wopb-sc-category, .wopb-sc-include-products').removeClass('wopb-d-none');
        }
    })
    // -------------------------------
    // Size Chart Addon [STOP]
    // -------------------------------

})( jQuery );