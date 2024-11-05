(function($) {
    'use strict';

    let errorElementGroup = wopb_core.errorElementGroup;
    let errorElement = $.parseHTML(errorElementGroup['errorElement']);


    // Set View Counter and Recently View Product (Run after 3sec)
    setTimeout( function() {
        if ( $('body.single.single-product').length > 0 ) {
            const postID = $('body').attr('class').split( /\s+/ ).filter( v => v.includes('postid-') )[0].split('-')[1];
            if ( postID ) {
                wp.apiFetch({
                    path: '/wopb/views',
                    method: 'POST',
                    data: { postID, wpnonce: wopb_core.security }
                })
            }
        }
    }, 3000 );

    

    // ----------------------------
    // ----------------------------
    // ----------------------------
    setTimeout(function() {
        builderSliderInit()
    }, 200);
    function builderSliderInit() {
        $('.wopb-builder-slider-nav').each(function () {
            const that = $(this)
            const vertical = (that.data('position') == 'left' || that.data('position') == 'right') ? true : false
            that.slick({
                slidesToShow: Number(that.data('collg')),
                vertical: vertical,
                asNavFor: '.wopb-builder-slider-for',
                focusOnSelect: true,
                dots: false,
                pauseOnHover: true,
                verticalSwiping:true,
                infinite: true,
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            vertical: false,
                            slidesToShow: Number(that.data('colsm'))
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            vertical: false,
                            slidesToShow: Number(that.data('colxs'))
                        }
                    }
                ],
                arrows: that.data('arrow') == 1 ? true : false,
                prevArrow: '<div class="wopb-slick-prev-nav"><svg viewBox="0 0 492 287" xmlns="http://www.w3.org/2000/svg"><path transform="translate(0 -.96)" d="m485.97 252.68-224.38-245.85c-4.2857-4.3102-9.9871-6.1367-15.585-5.8494-5.6186-0.28724-11.3 1.5392-15.586 5.8494l-224.4 245.85c-8.0384 8.0653-8.0384 21.159 0 29.225s21.081 8.0653 29.12 0l210.86-231.05 210.84 231.05c8.0384 8.0653 21.08 8.0653 29.119 0 8.0384-8.0645 8.0384-21.159 0-29.225z"/></svg></div>',
                nextArrow: '<div class="wopb-slick-next-nav"><svg viewBox="0 0 492 287" xmlns="http://www.w3.org/2000/svg"><path transform="translate(0 -.96)" d="m485.97 252.68-224.38-245.85c-4.2857-4.3102-9.9871-6.1367-15.585-5.8494-5.6186-0.28724-11.3 1.5392-15.586 5.8494l-224.4 245.85c-8.0384 8.0653-8.0384 21.159 0 29.225s21.081 8.0653 29.12 0l210.86-231.05 210.84 231.05c8.0384 8.0653 21.08 8.0653 29.119 0 8.0384-8.0645 8.0384-21.159 0-29.225z"/></svg></div>',
                rtl: $('html').attr('dir') && $('html').attr('dir') == 'rtl' && !vertical ? true : false,
            });

            that.on('mouseenter', '.slick-slide', function (e) {
                if ($(this).closest('.wopb-builder-slider-nav').data('view') == 'onhover') {
                    const index = $(e.currentTarget).data('slick-index');
                    $(this).trigger('click');
                }
            });

        });

       $('.wopb-builder-slider-for').each(function () {
           const that = $(this)
           that.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                fade: true,
                dots: false,
                infinite: true,
                asNavFor: that.parents('.wopb-product-wrapper:first').find('.wopb-builder-slider-nav').length ? '.wopb-builder-slider-nav' : '',
                arrows: that.data('arrow') == 1 ? true : false,
                prevArrow: '<div class="wopb-slick-prev-large"><svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m145.19 238.58 215.5-215.5c5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0l-225.1 225.1c-5.3 5.3-5.3 13.8 0 19.1l225.1 225c2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4c5.3-5.3 5.3-13.8 0-19.1l-215.4-215.5z"></path></svg></div>',
                nextArrow: '<div class="wopb-slick-next-large"><svg enable-background="new 0 0 477.175 477.175" version="1.1" viewBox="0 0 477.18 477.18"><path d="m360.73 229.08-225.1-225.1c-5.3-5.3-13.8-5.3-19.1 0s-5.3 13.8 0 19.1l215.5 215.5-215.5 215.5c-5.3 5.3-5.3 13.8 0 19.1 2.6 2.6 6.1 4 9.5 4s6.9-1.3 9.5-4l225.1-225.1c5.3-5.2 5.3-13.8 0.1-19z"/></svg></div>',
                rtl: $('html').attr('dir') && $('html').attr('dir') == 'rtl' ? true : false,
            });
        });

        let form = $('.wopb-builder-cart .variations_form')
        let wooGalleryWrapper = $('.woocommerce-product-gallery__wrapper')
        let variationParam = {
            'product': form.parents('.wopb-builder-cart:first') ,
            'form': form,
            'galleryWrapper': wooGalleryWrapper.find('.wopb-product-gallery-wrapper'),
            'nav': wooGalleryWrapper.find('.wopb-builder-slider-nav'),
            'defaultNav': wooGalleryWrapper.find('.wopb-builder-slider-nav .slick-current'),
            'defaultProductImage': wooGalleryWrapper.find('.slick-active img:first'),
            'getImage': wooGalleryWrapper.find('.wopb-product-gallery-wrapper .slick-active img'),
            'navItem': '.slick-active',
            'source': 'singleProduct',
        }
        if( form.find('.wopb-variation-selector').length ) {
            form.wopVariationSwitch(variationParam);
        }
        wooGalleryWrapper.variationChange(variationParam)
   }

    $(document).on('click', '.wopb-product-zoom', function(e){
        e.preventDefault();

        $('.wopb-builder-slider-for .slick-slide').trigger('zoom.destroy'); // remove zoom

        const slickIndex = $('.wopb-builder-slider-for .slick-current').attr('data-slick-index');
        const pswpElement = $( '.pswp' )[0],
              options = { index: parseInt(slickIndex) };

        let items = [];
        $('.wopb-builder-slider-for .slick-slide img').each(function(i, obj) {
            items.push({
                alt : $(obj).attr('alt'),
                src : $(obj).attr('src'),
                w : $(obj).data('width'),
                h : $(obj).data('height'),
            });
        });

        var photoswipe = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options );
        photoswipe.init();
        photoswipe.options.escKey = true;

        // photoswipe.listen('close', function() {
        //     $('.wopb-builder-slider-for .slick-slide').zoom({magnify: 2});
        // });
    });

    $(document).ready(function() {
        setTimeout(function() {
            let zoomWrapper = $('.wopb-product-zoom-wrapper');
            let slickSLide = $('.wopb-product-zoom-wrapper .wopb-builder-slider-for .slick-slide');
            if ( zoomWrapper.attr('data-hover-zoom') &&
                typeof slickSLide.zoom != 'undefined'
            ) {
                slickSLide.zoom({magnify: 2});
            }
        }, 200);
    });

    // ----------------------------
    // ----------------------------
    // ----------------------------



    // ------------------------
    // Builder Cart Options
    // ------------------------
    if ($('.wopb-cart-button').length > 0) {
        const plus = '<span class="wopb-builder-cart-plus"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"><path d="M31 12h-11v-11c0-0.552-0.448-1-1-1h-6c-0.552 0-1 0.448-1 1v11h-11c-0.552 0-1 0.448-1 1v6c0 0.552 0.448 1 1 1h11v11c0 0.552 0.448 1 1 1h6c0.552 0 1-0.448 1-1v-11h11c0.552 0 1-0.448 1-1v-6c0-0.552-0.448-1-1-1z"></path></svg></span>';
        const minus = '<span class="wopb-builder-cart-minus"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"><path d="M0 13v6c0 0.552 0.448 1 1 1h30c0.552 0 1-0.448 1-1v-6c0-0.552-0.448-1-1-1h-30c-0.552 0-1 0.448-1 1z"></path></svg></span>';
        const selector = $('.wopb-cart-button').closest('.cart').find('.quantity');
        const type = $('.wopb-cart-button').closest('.wopb-product-wrapper').data('type');
        if (type == 'both') {
            selector.append(plus);
            selector.prepend(minus);
        } else if (type == 'left') {
            selector.prepend('<span class="wopb-builder-cart-icon-left">'+plus+minus+'</span>');
        } else if (type == 'right') {
            selector.append('<span class="wopb-builder-cart-icon-right">'+plus+minus+'</span>');
        }

    }
    $(document).on(
        'mousedown',
        '.wopb-builder-cart-plus, ' +
        '.wopb-builder-cart-minus, ' +
        '.wopb-add-to-cart-plus, ' +
        'wopb-add-to-cart-minus',
        function(e){
        e.preventDefault();
    });
    $(document).on('click', '.wopb-builder-cart-plus', function(e){
        e.preventDefault();
        const parents = $(this).closest('.quantity');
        const parentQuantity = parents.find('.input-text');
        let max = parentQuantity.attr('max');
        let min = parentQuantity.attr('min');
        let step = parentQuantity.attr('step') ? Number(parentQuantity.attr('step')) : 1;
        let _val = parseInt(parentQuantity.val());

        if(isNaN(_val)) {
            _val = 1;
        }else if(min) {
            _val = Math.max(Math.ceil((_val + 1) / step) * step, min);
        }else {
            _val = _val + step;
        }
        if(max && _val >= max) {
            _val = max;
        }
        parentQuantity.val( _val );
        $('.wopb-builder-cart-minus').removeClass('disable');
    });
    $(document).on('click', '.wopb-builder-cart-minus', function(e){
        e.preventDefault();
        const parents = $(this).parents('.quantity:first');
        const parentQuantity = parents.find('.input-text');
        let min = parentQuantity.attr('min');
        let step = parentQuantity.attr('step') ? Number(parentQuantity.attr('step')) : 1;
        let _val = parseInt(parentQuantity.val());
        if(isNaN(_val)) {
            _val = min ? min : 1;
        }else {
            _val = _val - step;
            if (_val < 2) {
                $(this).addClass('disable');
            }
        }
        if(min && _val <= min) {
            _val = min;
        }
        parentQuantity.val( _val );
        $('.wopb-builder-cart-plus').removeClass('disable');
    });



    // *************************************
    // Flex Menu
    // *************************************
    $(document).ready(function(){
        if ($('.wopb-flex-menu').length > 0) {
            const menuText = $('ul.wopb-flex-menu').data('name');
            $('ul.wopb-flex-menu').flexMenu({linkText: menuText, linkTextAll: menuText, linkTitle: menuText});
        }
    });

    // *************************************
    // Loadmore Append
    // *************************************
    $(document).on('click', '.wopb-loadmore-action.wopb-ajax-loadmore', function(e){
        e.preventDefault();

        let that    = $(this),
            parents = that.closest('.wopb-block-wrapper'),
            paged   = parseInt(that.data('pagenum')),
            pages   = parseInt(that.data('pages'));

        let filterWrapper = $(this).parents('.wopb-block-wrapper:first');
        if(that.hasClass('wopb-disable')){
            return
        }else{
            paged++;
            that.data('pagenum', paged);
        }
        const post_ID = (parents.parents('.wopb-shortcode').length != 0) ? parents.parents('.wopb-shortcode').data('postid') : that.data('postid');
        let widgetBlockId = '';
        let widgetBlock = $(this).parents('.widget_block:first');
        if(widgetBlock.length > 0) {
            let widget_items = widgetBlock.attr('id').split("-");
            widgetBlockId = widget_items[widget_items.length-1]
        }
        $.ajax({
            url: wopb_core.ajax_load_more,
            type: 'POST',
            data: {
                action: 'wopb_load_more',
                paged: paged ,
                blockId: that.data('blockid'),
                postId: post_ID,
                blockName: that.data('blockname'),
                filterAttributes: that.data('filter-attributes'),
                builder: that.data('builder'),
                widgetBlockId: widgetBlockId,
                wpnonce: wopb_core.security
            },
            beforeSend: function() {
                block(filterWrapper);
                parents.addClass('wopb-loading-active');
            },
            success: function(data) {
                $(data).insertBefore( parents.find('.wopb-loadmore-insert-before') );
                if(paged == pages){
                    that.addClass('wopb-disable');
                }else{
                    that.removeClass('wopb-disable');
                }
            },
            complete:function() {
                unblock(filterWrapper);
                parents.removeClass('wopb-loading-active');
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                parents.removeClass('wopb-loading-active');
            },
        });
    });


    // *************************************
    // Filter
    // *************************************
    $('.wopb-filter-wrap li a').on('click', function(e){
        e.preventDefault();

        if($(this).closest('li').hasClass('filter-item')){
            let that    = $(this),
                parents = that.closest('.wopb-filter-wrap'),
                wrap = that.closest('.wopb-block-wrapper');

                parents.find('a').removeClass('filter-active');
                that.addClass('filter-active');

            let filterWrapper = $(this).parents('.wopb-block-wrapper:first');
            const post_ID = (parents.parents('.wopb-shortcode').length != 0) ? parents.parents('.wopb-shortcode').data('postid') : parents.data('postid');
            let widgetBlockId = '';
            let widgetBlock = $(this).parents('.widget_block:first');
            if(widgetBlock.length > 0) {
                let widget_items = widgetBlock.attr('id').split("-");
                widgetBlockId = widget_items[widget_items.length-1]
            }

            $.ajax({
                url: wopb_core.ajax_filter,
                type: 'POST',
                data: {
                    action: 'wopb_filter',
                    taxtype: parents.data('taxtype'),
                    taxonomy: that.data('taxonomy'),
                    blockId: parents.data('blockid'),
                    postId: post_ID,
                    blockName: parents.data('blockname'),
                    currentUrl: parents.data('current-url'),
                    widgetBlockId: widgetBlockId,
                    wpnonce: wopb_core.security
                },
                beforeSend: function() {
                    block(filterWrapper);
                    wrap.addClass('wopb-loading-active');
                },
                success: function(data) {
                    wrap.find('.wopb-wrapper-main-content').html(data);
                },
                complete:function() {
                    unblock(filterWrapper);
                    wrap.removeClass('wopb-loading-active');

                    filterWrapper.find('.wopb-product-deals').each(function(i, obj) {
                        loopcounter(obj);
                    });
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                    wrap.removeClass('wopb-loading-active');
                },
            });
        }
    });


    // *************************************
    // Pagination Number
    // *************************************
    function showHide(parents, pageNum, pages) {
        if (pageNum == 1) {
            parents.find('.wopb-prev-page-numbers').hide()
            parents.find('.wopb-next-page-numbers').show()
        } else if (pageNum == pages){
            parents.find('.wopb-prev-page-numbers').show()
            parents.find('.wopb-next-page-numbers').hide()
        } else {
            parents.find('.wopb-prev-page-numbers').show()
            parents.find('.wopb-next-page-numbers').show()
        }

        if (pageNum > 3) {
            parents.find('.wopb-first-dot').show();
        } else {
            parents.find('.wopb-first-dot').hide();
        }
        if(pageNum > 2) {
            parents.find('.wopb-first-pages').show()
        }else{
            parents.find('.wopb-first-pages').hide()
        }

        if (pages > pageNum + 2) {
            parents.find('.wopb-last-dot').show()
        } else{
            parents.find('.wopb-last-dot').hide()
        }
        if(pages > pageNum + 1){
            parents.find('.wopb-last-pages').show()
        }else{
            parents.find('.wopb-last-pages').hide()
        }
    }

    function serial(parents, pageNum, pages){
        let datas = pageNum <= 2 ? [1,2,3] : ( pages == pageNum ? [pages-2,pages-1, pages] : [pageNum-1,pageNum,pageNum+1] )
        let i = 0
        parents.find('.wopb-center-item').each(function() {
            if(pageNum == datas[i]){
                $(this).addClass('pagination-active')
            }
            $(this).attr('data-current', datas[i]).find('a').text(datas[i])
            i++
        });
    }

    $(document).on('click', '.wopb-pagination-ajax-action li', function(e){
        e.preventDefault();

        let that    = $(this),
            parents = that.closest('.wopb-pagination-ajax-action'),
            wrap = that.closest('.wopb-block-wrapper');

        let pageNum = 1;
        let pages = parents.attr('data-pages');

        if( that.data('current') ){
            pageNum = Number(that.attr('data-current'))
            parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
            serial(parents, pageNum, pages)
            showHide(parents, pageNum, pages)
        } else {
            if (that.hasClass('wopb-prev-page-numbers')) {
                pageNum = Number(parents.attr('data-paged')) - 1
                parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
                // parents.find('li[data-current="'+pageNum+'"]').addClass('pagination-active')
                serial(parents, pageNum, pages)
                showHide(parents, pageNum, pages)
            } else if (that.hasClass('wopb-next-page-numbers')) {
                pageNum = Number(parents.attr('data-paged')) + 1
                parents.attr('data-paged', pageNum).find('li').removeClass('pagination-active')
                // parents.find('li[data-current="'+pageNum+'"]').addClass('pagination-active')
                serial(parents, pageNum, pages)
                showHide(parents, pageNum, pages)
            }
        }

        const post_ID = (parents.parents('.wopb-shortcode').length != 0) ? parents.parents('.wopb-shortcode').data('postid') : parents.data('postid');

        let filterWrapper = $(this).parents('.wopb-block-wrapper:first');
        let widgetBlockId = '';
        let widgetBlock = $(this).parents('.widget_block:first');
        if(widgetBlock.length > 0) {
            let widget_items = widgetBlock.attr('id').split("-");
            widgetBlockId = widget_items[widget_items.length-1]
        }
        if(pageNum){
            $.ajax({
                url: wopb_core.ajax_pagination,
                type: 'POST',
                data: {
                    action: 'wopb_pagination',
                    paged: pageNum,
                    blockId: parents.data('blockid'),
                    postId: post_ID,
                    blockName: parents.data('blockname'),
                    builder: parents.data('builder'),
                    filterAttributes: parents.data('filter-attributes'),
                    widgetBlockId: widgetBlockId,
                    wpnonce: wopb_core.security
                },
                beforeSend: function() {
                    block(filterWrapper);
                    wrap.addClass('wopb-loading-active');
                },
                success: function(data) {
                    wrap.find('.wopb-block-items-wrap').html(data);
                    if($(window).scrollTop() > wrap.offset().top){
                        $([document.documentElement, document.body]).animate({
                            scrollTop: wrap.offset().top - 50
                        }, 100);
                    }

                },
                complete:function() {
                    unblock(filterWrapper);
                    wrap.removeClass('wopb-loading-active');
                    filterWrapper.find('.wopb-product-deals').each(function(i, obj) {
                        loopcounter(obj);
                    });
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                    wrap.removeClass('wopb-loading-active');
                },
            });
        }
    });

    // *************************************
    // SlideShow
    // *************************************
    gridProductSlide();
    if( ! $('.wopb-product-blocks-slide').length ) {
        setTimeout(function() {
            gridProductSlide();
        }, 10000);
    }
    function gridProductSlide() {
        if( ! $('.edit-post-layout').length ) {
            $('.wopb-product-blocks-slide').each(function () {
                const that = $(this)
                const slideBreakpoint = that.data('slidestoshow').split('-');
                that.slick({
                    arrows:         that.data('showarrows') ? true : false,
                    dots:           that.data('showdots') ? true : false,
                    fade:           that.data('fade') ? true : false,
                    infinite:       true,
                    speed:          500,
                    slidesToShow:   parseInt(slideBreakpoint[0]),
                    slidesToScroll: 1,
                    responsive: [
                        {
                            breakpoint: 992,
                            settings: {
                                slidesToShow: parseInt(slideBreakpoint[1]),
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: parseInt(slideBreakpoint[2]),
                                slidesToScroll: 1
                            }
                        }
                    ],
                    autoplay:       that.data('autoplay') ? true : false,
                    autoplaySpeed:  that.data('slidespeed') || 3000,
                    cssEase:        "linear",
                    prevArrow:      that.parent().find('.wopb-slick-prev').html(),
                    nextArrow:      that.parent().find('.wopb-slick-next').html(),
                    rtl:            $('html').attr('dir') && $('html').attr('dir') == 'rtl' ? true : false,
                });
                // that.show();
            });
        }
    }

    // *************************************
    // Variable Product Not Added in Cart
    // *************************************
    $(document).on("click",".add_to_cart_button",function(e) {
        if(!$(this).hasClass('wopb-loop-add-to-cart-button')) {
            if($(this).hasClass('ajax_add_to_cart')){
                const urlData = $(this).attr('href');
                if(!urlData.includes('?add-to-cart=')){
                    if(urlData.includes('http')){
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        window.location.href = urlData;
                    }
                }
            }
        }
    });

    // Loopcounter
    window.loopcounter = function (idWarp) {
        if (typeof idWarp != 'undefined') {
            let date = $(idWarp).data('date');
            if (typeof date != 'undefined') {
                var start = new Date(date),
                end = new Date(),
                diff = new Date(start - end),
                time = diff / 1000 / 60 / 60 / 24;

                var day = parseInt(time);
                var hour = parseInt(24 - (diff / 1000 / 60 / 60) % 24);
                var min = parseInt(60 - (diff / 1000 / 60) % 60);
                var sec = parseInt(60 - (diff / 1000) % 60);

                counterDate(idWarp, day, hour, min, sec);

                var interval = setInterval(function () {
                    if (sec == 0 && min != 0) {
                        min--;
                        sec = 60;
                    }
                    if (min == 0 && sec == 0 && hour != 0) {
                        hour--;
                        min = 59;
                        sec = 60;
                    }
                    if (min == 0 && sec == 0 && hour == 0 && day != 0) {
                        day--;
                        hour = 23;
                        min = 59;
                        sec = 60;
                    }
                    if (min == 0 && sec == 0 && hour == 0 && day == 0) {
                        clearInterval(interval);
                    } else {
                        sec--;
                    }
                    counterDate(idWarp, day, hour, min, sec);
                }, 1000);

                function counterDate(id, day, hour, min, sec) {
                    if (time < 0) { day = hour = min = sec = 0; }
                    $(id).find('.wopb-deals-counter-days').html(counterDoubleDigit(day));
                    $(id).find('.wopb-deals-counter-hours').html(counterDoubleDigit(hour));
                    $(id).find('.wopb-deals-counter-minutes').html(counterDoubleDigit(min));
                    $(id).find('.wopb-deals-counter-seconds').html(counterDoubleDigit(sec));
                }
                function counterDoubleDigit(arg) {
                    if (arg.toString().length <= 1) {
                        arg = ('0' + arg).slice(-2);
                    }
                    return arg;
                }
            }
        }
    }

    if (typeof loopcounter !== 'undefined') {
        if ($('.wopb-product-deals').length > 0) {
            $('.wopb-product-deals').each(function(i, obj) {
                loopcounter(obj);
            });
        }
    }



    // ------------------------
    // Quick Add Action
    // ------------------------
    $(document).on("click",".wopb-cart-action .ajax_add_to_cart",function(e) {
        const that = $(this);
        if (!that.parent('.wopb-cart-action').hasClass('wopb-active')) {
            e.preventDefault();
        }
        let quantity = that.parents('form.cart:first').find('input.qty').val()
        let sliderBlock = that.parents('.wopb-product-slider-block:first')
        let cartAction = that.parents('.wopb-cart-action:first');
        $.ajax({
            url: wopb_core.ajax,
            type: 'POST',
            data: {
                action: 'wopb_addcart',
                postid: that.data('postid') ?? that.data('product_id'),
                quantity: quantity ? quantity : 1,
                wpnonce: wopb_core.security
            },
            beforeSend: function() {
                if (sliderBlock.length) {
                    that.addClass('loading');
                }
            },
            success: function(data) {
                if( data.success ) {
                    cartAction.addClass('wopb-active');
                }
            },
            complete:function() {
                if (sliderBlock.length) {
                    cartAction.find('.wopb-view-cart').removeClass('wopb-d-none');
                    cartAction.find('.wopb-product-cart.ajax_add_to_cart, .quantity').remove();
                    that.removeClass('loading');
                }
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    });

    // ------------------------
    // Modal Close When Click Outside
    // ------------------------
    $(document).on( 'click', '.wopb-modal-wrap', function (e) { // Outside Click
        if (
			!$(e.target).hasClass('wopb-wishlist-remove') &&
            !$(e.target).hasClass('ajax_add_to_cart') &&
			$(e.target).parents('.wopb-modal-body').length === 0 &&
			$(e.target).parents('.wopb-quick-view-navigation').length === 0 &&
			$(e.target).parents('.wopb-after-modal-content:first').length === 0 &&
            $(e.target).closest('.wopb-modal-content').length === 0
        ) {
            $(this).modalClose();
        }
    });
    $(document).on('click', '.wopb-modal-close, .wopb-modal-continue', function(e){ // Close Button
        e.preventDefault();
        $(this).modalClose();
        if(
            $(this).hasClass('wopb-modal-continue') &&
            !$(this).parents('.wopb-modal-content:first').length &&
            $(this).attr('data-redirect')
        ) {
            window.location.href = $(this).attr('data-redirect');
        }
    });
    document.addEventListener('keydown', function(e) { // ESC Keydown Close
        if(e.keyCode === 27){
            $(this).modalClose();
        }
    });

    $.fn.modalClose = function() {
        const _modal = $('.wopb-modal-wrap.active');
        const afterModalContent = _modal.find('.wopb-after-modal-content');
        const _modalContent =  _modal.find('.wopb-modal-content');
        if(_modal.attr('data-close-animation')) {
            _modal.removeClass(_modal.attr('data-open-animation'));
            _modal.addClass(_modal.attr('data-close-animation'));
            const animationDuration = (parseFloat(_modalContent.css('animation-duration')) * 1000) + parseInt(20);
            setTimeout(function() {
                $(document).trigger('wopbModalClosed', [_modal]);
                _modal.removeClass(_modal.attr('data-modal-class'));
                afterModalContent.html('');
            }, animationDuration);
        }else {
            _modal.removeClass('active');
            $(document).trigger('wopbModalClosed',[_modal]);
        }
    }



    // ------------------------
    // Quick Add to Cart Quantity
    // ------------------------
    $(document).on('change', '.wopb-add-to-cart-quantity', function(e){
        e.preventDefault();
        let _val = $(this).val();
        let max = $(this).attr('max')
        const selector = $(this).closest('form.cart').find('.single_add_to_cart_button');
        if ( typeof max !== typeof undefined ) {
            max = parseInt(max);
            const min = parseInt($(this).attr('min'));
            if ($(this).val() > max) {
                _val = max;
            } else if ($(this).val() < min) {
                _val = min;
            }
        }
        $(this).val(_val);
        selector.val(_val);
        selector.attr('data-quantity', _val);
    });

    // ------------------------
    // Quick Add to Cart Plus
    // ------------------------
    $(document).on('click', '.wopb-add-to-cart-plus', function(e){
        e.preventDefault();
        const that = $(this)
        const form = that.closest('form.cart');
        let qtyParent = that.parents('.wopb-qty-wrap:first');
        qtyParent = ! qtyParent.length ? that.parents('.quantity:first') : qtyParent;
        let qty = qtyParent.find('input.wopb-qty');
        qty = ! qty.length ? qtyParent.find('input.qty') : qty;
        let max = qty.attr('max');
        let _val = isNaN(parseInt(qty.val())) ? 0 : parseInt(qty.val());
        if ( max && typeof max !== typeof undefined ) {
            if ( _val < parseInt(max) ) {
                _val = _val + 1;
            }else{
                that.addClass('disable');
            }
        } else {
            _val = _val + 1;
        }
        form.find('.single_add_to_cart_button').attr('data-quantity', _val );
        qty.val( _val );
        $('.wopb-add-to-cart-minus').removeClass('disable');
        $('.wopb-cart-update-btn').removeAttr('disabled');
    });

    // ------------------------
    // Quick Add to Cart Minus
    // ------------------------
    $(document).on('click', '.wopb-add-to-cart-minus', function(e){
        e.preventDefault();
        const that = $(this)
        const form = that.closest('form.cart');
        let qtyParent = that.parents('.wopb-qty-wrap:first');
        qtyParent = ! qtyParent.length ? that.parents('.quantity:first') : qtyParent;
        let qty = qtyParent.find('input.wopb-qty');
        qty = ! qty.length ? qtyParent.find('input.qty') : qty;
        let _val = parseInt(qty.val());
        if ( _val >= 2 ) {
            _val = _val - 1;
        } else {
            $(this).addClass('disable');
        }
        form.find('.single_add_to_cart_button').attr('data-quantity', _val );
        qty.val( _val );
        $('.wopb-add-to-cart-plus').removeClass('disable');
        $('.wopb-cart-update-btn').removeAttr('disabled');
    });

    // ------------------------
    // Reset image when click clear in quick view
    // ------------------------
    $(document).on('reset_data', function(e) {
        if ($(e['target']).closest('.wopb-quick-view-modal').length) {
            $('.wopb-quick-view-modal .wopb-quick-view-image .wopb-thumbnails-new').hide();
            $('.wopb-quick-view-modal .wopb-quick-view-image .wopb-thumbnails').show();
        }
    });

    // ------------------------
    // Cart Redirect URL
    // ------------------------
    $(document).on('click', '.wopb-cart-btn', function(e) {
        const that = $(this);
        if (that.is('[data-redirect]')) {
            setTimeout(function(){ window.location.href = that.data('redirect'); }, 2000);
        }
    });

    // ------------------------
    // Remove Stock HTML from Cart Builder
    // ------------------------
    $('.wopb-builder-cart').find('.stock, .ast-stock-detail, .wopb-stock-progress-bar-section').remove();

    /*
        if ($('.wopb-tooltip-text-top').length > 0) {
            $('.wopb-tooltip-text-top').each(function(i, obj) {
                let html = $(obj).html()
                if (html.indexOf('span') >= 0 ) {
                    const itm1 = $(obj).find('span').eq(0)
                    const itm2 = $(obj).find('span').eq(1)
                    itm1.parents('span').css('left', -1 * (itm1.text().length * 4) + 'px');
                    itm2.parents('span').css('left', -1 * (itm2.text().length * 4) + 'px');
                } else {
                    $(obj).css('left', -1 * ($(obj).text().length * 4) + 'px');
                }
            });
        }
    */

    /*
    * Cart Builder Script
     */
    // Cart Coupon Toggle Button Functionality
    $(document).ready(function() {
        if(($('.wp-block-product-blocks-cart-coupon .wopb-coupon-form').not(':visible'))) {
            $('.wp-block-product-blocks-cart-coupon .wopb-toggle-btn').addClass('wopb-toggle-btn-collapse');
        }
        $(".wopb-coupon-section .wopb-toggle-header").click(function() {
            // let couponBox = $(this).parents('.wopb-coupon-section:first').find('.wopb-coupon-body');
            let couponBox = $(this).parents('.wopb-coupon-section:first').find('.wopb-coupon-form');

            couponBox.slideToggle( "slow", function () {
                if($(this).is(':visible')){
                    $(this).parents('.wopb-coupon-section:first').find('.wopb-toggle-btn').removeClass('wopb-toggle-btn-collapse');
                }else{
                   $(this).parents('.wopb-coupon-section:first').find('.wopb-toggle-btn').addClass('wopb-toggle-btn-collapse');
                }
            } );
        });
    });


    // Empty Cart Check Cart
    $(document).on('click', '.wopb-cart-product-remove, .wopb-cart-update-btn', function(e) {
        const rowCount = $('.woocommerce-cart-form__contents tbody > tr').length;
        if (rowCount == 1) {
            if (e.target.name == 'update_cart') {
                if ($('.input-text.qty').val() == 0) {
                    setTimeout(function() {
                        $('.wp-block-product-blocks-cart-table, .wp-block-product-blocks-free_shipping_progress_bar, .wp-block-product-blocks-cart-total').fadeOut(300, function(){
                            $(this).remove();
                        });
                    }, 300);
                }
            } else {
                $('.wp-block-product-blocks-cart-table').remove();
            }
        }
    });

    /* Update Shipping Charge Content After Update Cart */
    $( document.body ).on( 'updated_cart_totals', function(res){
       $(".wopb-progress-bar").parent('.wopb-product-wrapper').load(location.href + " .wopb-progress-bar");
    });

    /* Change Shipping Method */
    $( document ).on('change', '.wopb-cart-total :input[name^=shipping_method]', function () {
        let shipping_methods = {};

        $(this).parents('.wopb-cart-total:first').removeClass('cart_totals')
        $( '.wopb-cart-total select.shipping_method, .wopb-cart-total :input[name^=shipping_method][type=radio]:checked, .wopb-cart-total :input[name^=shipping_method][type=hidden]' ).each( function() {
            shipping_methods[ $( this ).data( 'index' ) ] = $( this ).val();
        } );

        block( $( 'div.wopb-cart-total' ) );

        let data = {
            security: wc_cart_params.update_shipping_method_nonce,
            shipping_method: shipping_methods
        };

        $.ajax( {
            type:     'post',
            url:      get_url( 'update_shipping_method' ),
            data:     data,
            dataType: 'html',
            success:  function( response ) {
               refreshCartTotal();
            },
            complete: function() {
                $( document.body ).trigger( 'updated_shipping_method' );
            }
        } );
    })


    /*
        * Checkout Login Toggle Handler
    */
    $(document).ready(function() {
        $(".wopb-checkout-login-container .woocommerce-form-login-toggle").click(function() {
            $( '.wopb-checkout-login-container div.login, .wopb-checkout-login-container div.woocommerce-form-login' ).slideToggle("slow");
        });
    })

    $(document).on("click", ".wopb-checkout-login-container .wopb-form-login__submit", function (e) {
        e.preventDefault();
        let parent = $(this).parents(".wopb-checkout-login-container:first");
        let username = parent.find('input[name="username"]').val();
        let password = parent.find('input[name="password"]').val();
        let rememberMe = parent.find('input[name="rememberMe"]').is(":checked");
        parent.find(errorElement).remove();
        block($('body'));
         $.ajax({
                url: wopb_core.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_checkout_login',
                    username: username ,
                    password: password,
                    rememberme: rememberMe,
                    wpnonce: wopb_core.security
                },
                beforeSend: function() {

                },
                success: function(data) {
                     window.location.reload();
                },
                complete:function() {
                    unblock($('body'));
                },
                error: function(xhr) {
                    if(xhr && xhr.responseJSON) {
                        let errors = Object.entries(xhr.responseJSON);
                        errors.forEach(function(error, index) {
                            if(error[1] !== '') {
                                let currentInput = parent.find(`input[name="${error[0]}"]`);
                                if(currentInput.length > 0) {
                                    currentInput.after(errorElement)
                                    currentInput.parents('.form-row:first').find(errorElement).html(error[1]);
                                }else {
                                    let defaultErrorElement = parent.find('.wopb-form-error');
                                    defaultErrorElement.html(errorElement)
                                    defaultErrorElement.find(errorElement).html(error[1])
                                }
                                parent.find(errorElement).fadeIn(200);
                            }
                        });
                    }
                },
            });
    });

    /*
        *FOrm submit prevent, when click another button inside checkout form
     */
    $(document).on("click", ".wopb-builder-container form.checkout button", function (e) {
        if(!$(this).parents('.woocommerce-checkout-payment:first').length && (!$(this).attr('id') || $(this).attr('id') != 'place_order')) {
            return false;
        }
    });

        /*
            * Checkout Coupon Toggle Handler
        */
    $(document).on("click", "div.wopb-coupon-form .wopb-checkout-coupon-submit-btn", function (e) {
        let pageType = $(this).attr('wopbPageType');

        e.preventDefault();
        let thisObject = $(this).parents("div.wopb-coupon-form:first");
        thisObject.refreshCouponForm( 'block' , pageType );
        let $text_field = thisObject.find( '.wopb-coupon-code' );
        let coupon_code = $text_field.val();

        let data = {
            security: pageType=='cart' ? wc_cart_params.apply_coupon_nonce : wc_checkout_params.apply_coupon_nonce,
            coupon_code: coupon_code
        };

        $.ajax( {
            type:     'POST',
            url:      pageType=='cart' ? get_url( 'apply_coupon' ) : get_urlCheckoutCoupon( 'apply_coupon' ),
            data:     data,
            dataType: 'html',
            success: function( response ) {
                $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                thisObject.showCouponNotice( response );
                $( document.body ).trigger( 'applied_coupon_in_checkout', [ coupon_code ] );
                $( document.body ).trigger( 'update_checkout', [ coupon_code ] );

                if(pageType=='cart') {
                    refreshCartTotal();
                    $( document.body ).trigger( 'applied_coupon', [ coupon_code ] );
                }
            },
            complete: function() {
                $text_field.val( '' );
                thisObject.refreshCouponForm( 'unblock' );
            }
        } );
    })

    $(document.body).on('updated_checkout', function (event, data)  {
        let footer = $('.wopb-checkout-review-table tfoot')
        let reviewFooter = $(data.fragments['.woocommerce-checkout-review-order-table']).find('tfoot');
        if( footer.length && reviewFooter.length ) {
            footer.html(reviewFooter.html())
        }
    })

    $.fn.refreshCouponForm = function(blockProcess ,pageType ) {
        if(blockProcess === 'block') {
            block($(this))
            pageType=='cart' ? block($('div.wopb-cart-total')) : block($('.woocommerce-checkout-review-order'))
        }else {
            unblock($(this))
            pageType=='cart' ? unblock($('div.wopb-cart-total')) : unblock($('.woocommerce-checkout-review-order'))
        }
    }
    $.fn.showCouponNotice = function(html_element, $target) {
        let couponSection = $(this).parent('.wopb-coupon-section');
		if ( ! $target ) {
			$target = couponSection.find( '.woocommerce-notices-wrapper:first' ) ||
				couponSection.find( '.cart-empty' ).closest( '.woocommerce' ) ||
				couponSection.find( '.woocommerce-cart-form' );
		}
		$target.prepend( html_element );
	};

    function refreshCartTotal () {
        $(".wopb-cart-total").parent('.wopb-product-wrapper').load(location.href + " .wopb-cart-total");
    }


    let get_urlCheckoutCoupon = function( endpoint ) {
		return wc_checkout_params.wc_ajax_url.toString().replace(
			'%%endpoint%%',
			endpoint
		);
	};

    let get_url = function( endpoint ) {
		return wc_cart_params.wc_ajax_url.toString().replace(
			'%%endpoint%%',
			endpoint
		);
	};
    /*
    * End Cart Builder Script
     */


    /*
     * Cart table Footer Buttons start
    */

    cartTableFooterResponsive();
    $(window).resize( function () {
        cartTableFooterResponsive();
    });

    function cartTableFooterResponsive() {

        let cartTableFirstOption = $(".wopb-cart-form .wopb-cart-table-first-option");
        let cartTableSecondOption = $(".wopb-cart-form .wopb-cart-table-second-option");
        let cartTableWrapper = $( ".wopb-cart-form" );

        if(cartTableWrapper ) {
            if(cartTableWrapper.width() <= 685) {
                if ( ( 520 <= cartTableWrapper.width() &&  cartTableWrapper.width() <= 644 )){
                    if( ( cartTableFirstOption.children().length == 2 && cartTableSecondOption.children().length ==1 ) || ( cartTableFirstOption.children().length == 1 && cartTableSecondOption.children().length ==2 )  ) {
                        $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto", "justify-content": "normal" });
                        $(".wopb-cart-form .wopb-cart-table-option-hidden").css({ "grid-template-columns": "auto" });
                    }else {
                        $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto auto", "justify-content": "space-between" });
                    }
                }
                if( ( cartTableFirstOption.children().length == 2 && cartTableSecondOption.children().length ==2 )) {
                    $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto", "justify-content": "normal" });
                }
                else {
                    $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto auto", "justify-content": "space-between" });
                    $(".wopb-cart-form .wopb-cart-table-first-option").css({ "grid-template-columns": "auto auto"});
                }

                if (( ( cartTableFirstOption.children().length == 2 && cartTableSecondOption.children().length ==1 ) || ( cartTableFirstOption.children().length == 1 && cartTableSecondOption.children().length ==2 ) ) && cartTableWrapper.width() <= 520 ) {
                    $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto", "justify-content": "normal" });
                    $(".wopb-cart-form .wopb-cart-table-first-option").css({ "grid-template-columns": "auto"});
                    $(".wopb-cart-form .wopb-cart-table-option-hidden").css({ "grid-template-columns": "auto" });
                }
                else if(( ( cartTableFirstOption.children().length == 1 && cartTableSecondOption.children().length == 1 ) && ( 520 >= cartTableWrapper.width() && 430 <= cartTableWrapper.width())) ) {
                    $(".wopb-cart-form .wopb-cart-table-option-hidden").css({ "grid-template-columns": "auto" });
                }
                else if(cartTableWrapper.width() <= 520 ) {
                    $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto", "justify-content": "normal" });
                    $(".wopb-cart-form .wopb-cart-table-first-option").css({ "grid-template-columns": "auto"});
                }
            }
            else {
                $(".wopb-cart-form .wopb-cart-table-options").css({ "grid-template-columns": "auto auto", "justify-content": "space-between" });
                $(".wopb-cart-form .wopb-cart-table-first-option").css({ "grid-template-columns": "auto auto"});
            }
        }
    }

    /*
    * Cart table Footer Buttons Ends
    */

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

    //variation form trigger for builder
    $.fn.variationChange = ( variationParam ) => {
        let {form, galleryWrapper, nav, defaultNav, defaultProductImage} = variationParam
        if( form.length ) {
            defaultProductImage.attr({
                "data-backup_alt": defaultProductImage.attr('alt'),
                "data-backup_src": defaultProductImage.attr('src'),
            })
            form.on("found_variation", function (e, variation) {
                let thumbnail = galleryWrapper.find('.slick-active img');
                let attributes = {
                    src: variation.image.full_src,
                };
                let navSlick = nav.find('.slick-active');
                if (navSlick.length > 0) {
                    let navImage = '';
                    navImage = navSlick.find('img[src="' + variation.image.full_src + '"]');
                    if (navImage.length < 1 && variation.image.full_src != '') {
                        // Change variation image for builder
                        if (defaultNav.length > 0) {
                            defaultNav.trigger('click');
                            thumbnail = galleryWrapper.find('.slick-active img');
                        }
                        thumbnail.attr(attributes);
                    } else if (navImage.length < 1) {
                        defaultNav.trigger('click');
                    } else {
                        navImage.parents('.slick-active:first').trigger('click');
                    }
                } else {
                    thumbnail.attr(attributes);
                }
            })
            if( ! form.find('.wopb-variation-selector').length ) {
                form.on("reset_data", function () {
                    let attributes = {
                        src: defaultProductImage.attr('data-backup_src'),
                    };
                    if (defaultNav.length > 0) {
                        defaultNav.trigger('click');
                        if ( defaultProductImage.attr('data-backup_src') !== defaultProductImage.attr('src') ) {
                            defaultProductImage.attr(attributes);
                        }
                    } else {
                        defaultProductImage.attr(attributes);
                    }
                })
            }
        }
    }

    /*
     * Product filter(feature) start
     */
    //filter plus/minus toggle
    $(document).on("click", ".wopb-product-wrapper.wopb-filter-block .wopb-filter-toggle span.dashicons", function (e) {
        let filterPlus = $(this).parent('.wopb-filter-toggle').find('.wopb-filter-plus');
        let filterMinus = $(this).parent('.wopb-filter-toggle').find('.wopb-filter-minus');
        let filterBody = $(this).parents('.wopb-filter-header:first').parent('.wopb-filter-section').find('.wopb-filter-body');

        if($(this).hasClass('wopb-filter-plus')) {
            filterPlus.hide()
            filterMinus.show()
            filterBody.show(600)
        }else if($(this).hasClass('wopb-filter-minus')) {
            filterMinus.hide()
            filterPlus.show()
            filterBody.hide(600)
        }
    })

    $(document).on("click", ".wopb-filter-child-toggle span.dashicons", function (e) {
        let that = $(this)
        let parent = that.parents('.wopb-filter-item:first');
        let rightToggle = parent.find('.wopb-filter-right-toggle');
        let downToggle = parent.find('.wopb-filter-down-toggle');
        let filterChildItem = parent.find('.wopb-filter-check-list:first');
        if(that.hasClass('wopb-filter-right-toggle')) {
            rightToggle.addClass('wopb-d-none');
            downToggle.removeClass('wopb-d-none');
            filterChildItem.removeClass('wopb-d-none');
        }else if(that.hasClass('wopb-filter-down-toggle')) {
            downToggle.addClass('wopb-d-none');
            rightToggle.removeClass('wopb-d-none');
            filterChildItem.addClass('wopb-d-none');
        }
    })

    /*
     * Get data when filter call
     */
    function resetProductFilter() {
        return {
            'search' : {},
            'price' : {},
            'status' : [],
            'rating' : [],
            'tax_relation' : {},
            'product_taxonomy' : [],
            'sorting' : {},
        };
    }
    let productFilters = resetProductFilter();
    let filterSlugValue =  '';
    let filterSlug = '';
    $(document).on("change", ".wopb-filter-block-front-end .wopb-filter-section .wopb-filter-body, " +
        ".wopb-filter-block-front-end .wopb-taxonomy-relation .wopb-filter-body", function (e) {
        let that = $(this);
        let parent = $('.wopb-product-wrapper.wopb-filter-block-front-end');
        if($(e.target).hasClass('wopb-filter-price-input')) {
            let minPriceInput = parent.find('.wopb-min-price');
            let maxPriceInput = parent.find('.wopb-max-price');
            let maxRangeInput = that.find('.wopb-price-range .wopb-max-range');
            if(minPriceInput.val() < 0 || minPriceInput.val() == -0) {
                minPriceInput.val(0)
            }
            if(Number(minPriceInput.val()) > maxRangeInput.attr('max')) {
                minPriceInput.val(maxRangeInput.attr('max'))
            }
            if(maxPriceInput.val() < 0 || Number(maxPriceInput.val()) > maxRangeInput.attr('max') || maxPriceInput.val() == -0) {
                maxPriceInput.val(maxRangeInput.attr('max'))
            }
            let minPriceInputValue = Number(minPriceInput.val());
            let maxPriceInputValue = Number(maxPriceInput.val());
            $(e.target).priceSliderRange(minPriceInputValue, maxPriceInputValue)
        }
        filterSlug = that.find('.wopb-filter-slug').val();
        parent.find('.wopb-active-items .wopb-filter-active-item[data-slug="' + filterSlug + '"]').remove();
        switch(filterSlug) {
            case 'reset':
                productFilters = resetProductFilter();
            break;
            case 'search':
                filterSlugValue = that.find('.wopb-filter-search-input').val();
                if(filterSlugValue){
                    that.addToClearProductFilterSection(('Search:' + filterSlugValue), filterSlugValue)
                }
            break;
            case 'price':
                let minRangeInputValue = Number(that.find('.wopb-input-group .wopb-min-price').val());
                let maxRangeInput = that.find('.wopb-price-range .wopb-max-range');
                let maxRangeInputValue = Number(that.find('.wopb-input-group .wopb-max-price').val());

                if(minRangeInputValue > 0){
                    that.addToClearProductFilterSection(('Min: ' + priceFormat(minRangeInputValue)), 'wopb-min-range')
                }
                if(maxRangeInputValue != maxRangeInput.attr('max') && maxRangeInputValue != 0) {
                    that.addToClearProductFilterSection(('Max: ' + priceFormat(maxRangeInputValue)), 'wopb-max-range')
                }
                filterSlugValue = {'minPrice': minRangeInputValue, 'maxPrice': maxRangeInputValue}
            break;
            case 'category':
                filterSlugValue = [];
                that.find('.wopb-item-content .wopb-filter-category-input:checked').each(function() {
                    filterSlugValue.push($(this).val());
                    that.addToClearProductFilterSection(('Cat: ' + $(this).data('label')), $(this).val())
                });
            break;
            case 'status':
                filterSlugValue = [];
                that.find('.wopb-item-content .wopb-status-input:checked').each(function() {
                    filterSlugValue.push($(this).val());
                    that.addToClearProductFilterSection($(this).val(), $(this).val())
                });
            break;
            case 'rating':
                filterSlugValue = [];
                that.find('.wopb-item-content .wopb-rating-input:checked').each(function() {
                  filterSlugValue.push($(this).val());
                  that.addToClearProductFilterSection(('Rating: ' + $(this).val()), $(this).val())
                });
            break;
            case 'product_taxonomy':
                filterSlugValue = [];
                let taxonomyParent = parent.find('.wopb-item-content .wopb-filter-tax-term-input:checked').parents('.wopb-filter-body');
                taxonomyParent.each(function() {
                    let taxonomy_terms = [];
                    $(this).find('.wopb-item-content .wopb-filter-tax-term-input:checked').each(function() {
                        taxonomy_terms.push($(this).val())
                        that.addToClearProductFilterSection($(this).data('label'), $(this).val())
                    });
                    filterSlugValue.push({
                        taxonomy: $(this).find('.wopb-filter-slug').data('taxonomy'),
                        term_ids: taxonomy_terms
                    })
                });
            break;
            case 'sorting':
                filterSlugValue = that.find('.wopb-filter-sorting-input').val();
            break;
            default:
            break;
        }
        if(filterSlug in productFilters) {
            productFilters[filterSlug] = filterSlugValue;
        }
        productFilters['tax_relation'] = parent.find('.wopb-taxonomy-relation .wopb-filter-tax-relation:checked').val();
        $(this).productFilterCurrentPage();
        filterSlugValue = '';
        if(parent.find('.wopb-active-items .wopb-filter-active-item').length < 1){
            parent.find('.wopb-filter-remove-section').hide(400)
        }
        let blockItemWrapper = $('.wp-block-product-blocks-' + parent.data('block-target')).find('.wopb-wrapper-main-content');
        block(blockItemWrapper);
        wp.apiFetch( {
            path: '/wopb/product-filter',
            method: 'POST',
            data: {
                post_id: parent.data('postid') ? parent.data('postid') : '',
                block_name: ('product-blocks_' + parent.data('block-target')),
                product_filters: productFilters,
                current_url: parent.data('current-url'),
                wpnonce: wopb_core.security
            }
        })
        .then(response => {
            response.blockList.map( function (block) {
                let blockContent = $('.wp-block-product-blocks-' + parent.data('block-target') + '.wopb-block-' + block.blockId).find('.wopb-wrapper-main-content');
                blockContent.html(block.content);
                blockContent.find('.wopb-product-deals').each(function(i, obj) {
                    loopcounter(obj);
                });
            })
            gridProductSlide();
            unblock(blockItemWrapper);
            $(document).trigger('wopbAjaxComplete');
        })
        .catch(error => {
            unblock(blockItemWrapper);
            console.log(error);
        });
    })

    /*
     * Check current page, example: archive, search, taxonomy page
     */
    $.fn.productFilterCurrentPage = function() {
        let currentPage = $(this).parents('.wopb-product-wrapper:first').find('.wopb-filter-reset-section .wopb-filter-current-page');
        if(currentPage.length > 0) {
            if(currentPage.data('slug') == 'product_taxonomy' && currentPage.data('taxonomy') != '') {
                let slugValues = '';
                if(productFilters['product_taxonomy'].findIndex(x => x.taxonomy === currentPage.data('taxonomy')) < 0) {
                     slugValues = [{
                        taxonomy : currentPage.data('taxonomy'),
                        term_ids: [currentPage.val()]
                    }]
                }
                $.merge( productFilters['product_taxonomy'], slugValues )
            }else if(currentPage.data('slug') == 'product_search') {
                productFilters['search'] = currentPage.val();
            }
        }
    }

    /*
     * Add filter item to clear filter item section
     */
    $.fn.addToClearProductFilterSection = function( activeFilterItem, filterSlugValue) {
        let parent = $('.wopb-product-wrapper.wopb-filter-block-front-end');
        let filterSlug = $(this).find('.wopb-filter-slug').val();
        let html = '';
        html += `<span class="wopb-filter-active-item" data-slug="${filterSlug}" data-value="${filterSlugValue}" >`;
            html += `${activeFilterItem} <span class="dashicons dashicons-no-alt wopb-filter-remove-icon"></span>`;
        html += `</span>`;
        parent.find('.wopb-filter-remove-section .wopb-active-items').append(html)
        if(parent.find('.wopb-filter-remove-section:visible').length === 0) {
            parent.find('.wopb-filter-remove-section').show().css('display', 'flex')
        }
    }

    /*
     * Remove Filter Active Item
     */
    $(document).on("click",
        ".wopb-filter-block-front-end .wopb-filter-remove-section .wopb-filter-remove-icon," +
        " .wopb-filter-block-front-end .wopb-filter-remove-section .wopb-filter-remove-all",
        function () {
            let rootBlock = $('.wopb-filter-block-front-end');
            let activeItemParent = $(this).parent('.wopb-filter-active-item');
            let filterSlug = '';
            if($(this).parent('.wopb-filter-remove-all').length > 0 || $(this).hasClass('wopb-filter-remove-all')) { //check if click remove all filter
                filterSlug = 'removeAll';
            }else {
                filterSlug = rootBlock.find('.wopb-filter-body .wopb-filter-slug[value='+ activeItemParent.data('slug') +']'); //check if click single filter for remove
            }

            if((filterSlug !== 'removeAll' && filterSlug.val() === 'search') || filterSlug === 'removeAll') {
                 rootBlock.find('.wopb-filter-body .wopb-filter-search-input').val('');
            }
            if((filterSlug !== 'removeAll' && filterSlug.val() === 'price') || filterSlug === 'removeAll') {
                if(activeItemParent.data('value') === 'wopb-min-range' || filterSlug === 'removeAll') {
                    rootBlock.find('.wopb-filter-body .wopb-min-range').val(0);
                    rootBlock.find('.wopb-filter-body .wopb-min-range').trigger('input');
                }
                if(activeItemParent.data('value') === 'wopb-max-range' || filterSlug === 'removeAll') {
                    let maxPriceInput = rootBlock.find('.wopb-filter-body .wopb-max-range');
                    maxPriceInput.val(maxPriceInput.attr('max'));
                    maxPriceInput.trigger('input');
                }
            }
            if(filterSlug === 'removeAll') {
                rootBlock.find('.wopb-filter-remove-section .wopb-filter-active-item').remove();
                rootBlock.find('.wopb-filter-body .wopb-item-content input').prop('checked', false)
                rootBlock.find('.wopb-filter-body .wopb-filter-sorting-input').val('default')
                rootBlock.find('.wopb-filter-slug-reset').parent('.wopb-filter-body').trigger('change');
            }else {
                let checkBoxFilterItem = filterSlug.parent('.wopb-filter-body').find('.wopb-item-content');
                if(checkBoxFilterItem) {
                    checkBoxFilterItem.find('input[value='+ activeItemParent.data('value') +']').prop('checked', false)
                }
                activeItemParent.remove();
                filterSlug.parent('.wopb-filter-body').trigger('change');
            }
    })

    /*
     * Filter price range slider
     */
    let rangeMinValueFixed = '';
    let rangeMaxValueFixed = '';
     $(document).on("input", ".wopb-filter-block-front-end .wopb-filter-body .wopb-price-range-input", function (e) {
        let that = $(this);
        let parent = $(this).parents('.wopb-price-range-slider:first');
        let minRangeInput = parent.find('.wopb-min-range');
        let maxRangeInput = parent.find('.wopb-max-range');
        let minRangeInputValue = Number(minRangeInput.val());
        let maxRangeInputValue = Number(maxRangeInput.val());
        let {rangeBarGap, rangeBarMinGap} = that.priceSliderRange(minRangeInputValue, maxRangeInputValue);
         if(rangeBarGap > rangeBarMinGap) {
             parent.find('.wopb-min-price').val(minRangeInputValue)
             parent.find('.wopb-max-price').val(maxRangeInputValue)
         }
    })

    /*
     * Filter price range value
     */
    $.fn.priceSliderRange = function (minPrice, maxPrice) {
        let that = $(this);
        let parent = that.parents('.wopb-price-range-slider:first');
        let minRangeInput = parent.find('.wopb-min-range');
        let maxRangeInput = parent.find('.wopb-max-range');

        let rangeBar = parent.find('.wopb-price-range-bar');
        let rangeBarLeft = Math.round((minPrice / maxRangeInput.attr('max')) * 100);
        let rangeBarRight = Math.round((maxPrice / maxRangeInput.attr('max')) * 100);
        let rangeBarGap = Number(rangeBarRight - rangeBarLeft);
        let rangeBarMinGap = 0;
        if(rangeBarGap > rangeBarMinGap) {
            if(that.hasClass('wopb-filter-price-input')) {
                minRangeInput.val(minPrice)
                maxRangeInput.val(maxPrice)
            }
            if(maxPrice <= maxRangeInput.attr('max')) {
                rangeBar.css({'left': rangeBarLeft + '%', 'width': rangeBarGap + '%'});
            }
            rangeMinValueFixed = '';
            rangeMaxValueFixed = '';
        }else {
            if( rangeMinValueFixed === '') {
                rangeMinValueFixed = Number(minPrice - 1);
            }
            if( rangeMaxValueFixed === '') {
                rangeMaxValueFixed = Number(maxPrice + 1);
            }
            if(that.hasClass('wopb-min-range')) {
                minRangeInput.val( rangeMinValueFixed )
            }
            if(that.hasClass('wopb-max-range')) {
                maxRangeInput.val( rangeMaxValueFixed )
            }
        }
        return {
            'rangeBarGap' : rangeBarGap,
            'rangeBarMinGap' : rangeBarMinGap,
        }
    }

    /*
     * Extend/Collapse Filter Item
     */
    $(document).on("click", ".wopb-filter-block-front-end .wopb-filter-section .wopb-filter-extend-control", function () {
        let that = $(this);
        let parent = that.parents('.wopb-filter-body:first');
        let extendedElement = parent.find('.wopb-filter-extended-item');
        let showMoreBtn = parent.find('.wopb-filter-show-more');
        let showLessBtn = parent.find('.wopb-filter-show-less');
        let filterSlug = parent.find('.wopb-filter-slug');
        let filterCheckList = parent.find('.wopb-filter-check-list:first');
        let itemTotalPage = Number(showMoreBtn.attr('data-total-page'));
        let currentPage = 1;
        if(that.hasClass('wopb-filter-show-more')) {
            currentPage = Number(showMoreBtn.attr('data-current-page')) + 1;
            if(itemTotalPage >= currentPage) {
                showMoreBtn.attr('data-current-page', currentPage)
                $.ajax({
                    url: wopb_core.ajax_show_more_filter_item,
                    type: 'POST',
                    data: {
                        action: 'wopb_show_more_filter_item',
                        query: filterSlug.data('query'),
                        attributes: filterSlug.data('attributes'),
                        taxonomy: filterSlug.data('taxonomy'),
                        current_page: currentPage,
                        wpnonce: wopb_core.security
                    },
                    beforeSend: function() {
                        block(filterSlug.parents('.wopb-filter-section:first'));
                    },
                    success: function (response) {
                        if(response) {
                            filterCheckList.append(response)
                            extendedElement.show(500);
                        }
                    },
                    error: function (xhr) {
                    },
                    complete:function() {
                        unblock(filterSlug.parents('.wopb-filter-section:first'));
                    },
                });
            }
            if(itemTotalPage == currentPage || currentPage > itemTotalPage) {
                showMoreBtn.hide();
                 showLessBtn.show();
            }
            extendedElement.show(500);
        }else if(that.hasClass('wopb-filter-show-less')) {
            showLessBtn.hide();
            showMoreBtn.show();
            extendedElement.hide(500);
        }
    })

    /*
     * Price format
     */
    function priceFormat(price) {
        let currencyPosition = wopb_core.currency_position;
        let currencySymbol = wopb_core.currency_symbol;
         return (currencyPosition === 'left' || currencyPosition === 'left_space') ? (currencySymbol + price) : ( price + currencySymbol)
    }
    let filterModal = $('.wopb-filter-modal');
    $('.wopb-filter-block .wopb-filter-title-section').on('click',  function () {
        filterModal.removeClass('wopb-slide-out')
        filterModal.addClass('wopb-slide-in')
    })
    $('.wopb-filter-modal, .wopb-filter-content .wopb-modal-close').on('click', function () {
        if( ! event.target.closest('.wopb-filter-content') || $(this).hasClass('wopb-modal-close') ) {
            filterModal.addClass('wopb-slide-out')
        }
    })
    /*
     * Product filter(feature) end
     */

    /*
     * My Account Page Start
    */
    $(document).on("focus", ".wopb-my-account-container-editor .woocommerce-MyAccount-navigation .woocommerce-MyAccount-navigation-link a", function () {

        $('.wopb-my-account-container-editor .woocommerce-MyAccount-navigation .woocommerce-MyAccount-navigation-link').removeClass('is-active');
        $(this).parent('li.woocommerce-MyAccount-navigation-link').addClass('is-active');
        let id= $(this).attr('id')+'-item';

        $('.wopb-my-account-container-editor .woocommerce-MyAccount-content .wopb-myaccount-item').removeClass('wopb-my-account-container-show');

        let element = $('.wopb-my-account-container-editor .woocommerce-MyAccount-content').find('#'+id);
        element.addClass('wopb-my-account-container-show');
    });


    /*
        * My Account Page End
    */

    /* *************************************
    Social Share window
    ************************************* */
    $(".wopb-share-item a").each(function() {
        $(this).click(function() {
            // For Share window opening
            let share_url = $(this).attr("url");
            let width = 800;
            let height = 500;
            let leftPosition, topPosition;
            //Allow for borders.
            leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
            //Allow for title and status bars.
            topPosition = (window.screen.height / 2) - ((height / 2) + 50);
            let windowFeatures = "height=" + height + ",width=" + width + ",resizable=yes,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition;
            window.open(share_url,'sharer', windowFeatures);
            // For Share count add
            let id = $(this).parent(".wopb-share-item:first").attr("postId");
            let count = $(this).parent(".wopb-share-item:first").attr("count");

            $.ajax({
                url: wopb_core.ajax,
                type: 'POST',
                data: {
                    action: 'wopb_share_count',
                    shareCount:count,
                    postId: id,
                    wpnonce: wopb_core.security
                },
                success: function(res) {
                    $('.wopb-share-count').text(res.data.count)
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });

            return false;
        })
    }); // End

    /*
        * Currency Switcher Block
    */
    $(document).on('click', '.wopb-currency-switcher-container', function(e) {
        $(this).find('.wopb-set-default-currency').slideToggle( "slow");
        const is_open = $(this).hasClass("open");
        if (is_open) {
          $(this).removeClass("open");
        } else {
          $(this).addClass("open");
        }
    });

    $(document).bind('click', function(e) {
        const selector = $(e.target);
        if (selector.parents('.wopb-currency-switcher-container').length < 1 && !selector.hasClass('wopb-currency-switcher-container')) {
            const is_open = $('.wopb-currency-switcher-container').hasClass("open");
            if (is_open) {
                $('.wopb-currency-switcher-container').removeClass('open');
                $('.wopb-currency-switcher-container .wopb-set-default-currency').slideToggle( "slow");
            }
        }
    });
    // End

    /*
     * Custom dropdown script
     */
    $(document).on('click', '.wopb-dropdown-select .wopb-selected-item', function(e) {
        let parent = $(this).parent('.wopb-dropdown-select');
        parent.find('.wopb-select-items').toggle()
    })
    $(document).on('click', '.wopb-dropdown-select .wopb-select-items li', function(e) {
        let parent = $(this).parents('.wopb-dropdown-select:first');
        parent.find('.wopb-selected-item .wopb-selected-text').text($(this).text())
        parent.find('.wopb-selected-item .wopb-selected-text').attr('value', $(this).attr('value'))
        parent.find('.wopb-select-items').toggle()
    })
    /*
     * Custom dropdown script
    */


    /*
     * Get search result from search block
    */
    $(document).on('click', '.wopb-product-search-block .wopb-search-input', function () {
        let SearchResult = $(this).parents('.wopb-product-search-block:first').find('.wopb-search-result')
        if($(this).val() != '' && SearchResult.html() != '' && SearchResult.hasClass('wopb-d-none')) {
            $('.wopb-product-search-block .wopb-search-result').addClass('wopb-d-none');
            SearchResult.removeClass('wopb-d-none');
        }
    })
    $('.wopb-product-search-block form').on('submit', function () {
        if( $(this).data('redirect') ) {
            $(this).searchBlockData({redirect: $(this).data('redirect')});
        }
    })
    $('.wopb-product-search-block').on('input', '.wopb-search-input', function(e) {
      $(this).searchBlockData();
    }).on('click',
        '.wopb-search-category .wopb-select-items li, ' +
        '.wopb-search-icon, ' +
        '.wopb-search-section .wopb-clear',
        function(e) {
            let params = {}
            let form = $(this).parents('form:first')
            if($(this).hasClass('wopb-clear')) {
                form.find('.wopb-search-input').val('');
            }else if( form.data('redirect') && $(this).hasClass('wopb-search-icon') ) {
                params.redirect = form.data('redirect');
            }else if( $(this).parents('.wopb-search-category:first') ) {
                params.category = $(this).attr('value');
            }
            $(this).searchBlockData(params);
        }
    );

    $.fn.searchBlockData =  function (params) {
        let searchBlock = $(this).parents('.wopb-product-search-block:first');
        let search =  searchBlock.find('.wopb-search-input').val();
        let category = params?.category ?? searchBlock.find('.wopb-search-category .wopb-selected-text').attr('value');
        if( ( search || category ) && params?.redirect ) {
            let queryParam = '';
            queryParam += `?s=${search}`;
            queryParam += category ? `&product_cat=${category}` : '';
            window.location.href = params?.redirect + queryParam;
        }
        searchBlock.find('.wopb-clear').addClass('wopb-d-none')
        $('.wopb-product-search-block .wopb-search-result').not(searchBlock.find('.wopb-search-result')).addClass('wopb-d-none');
        if( ( search && search.length >= 3 ) || category ) {
            const post_ID = (searchBlock.parents('.wopb-shortcode').length !== 0) ? searchBlock.parents('.wopb-shortcode').data('postid') : searchBlock.data('postid');
            let widgetBlockId = '';
            let widgetBlock = $(this).parents('.widget_block:first');
            if (widgetBlock.length > 0 && widgetBlock.attr('id')) {
                let widget_items = widgetBlock.attr('id').split("-");
                widgetBlockId = widget_items[widget_items.length - 1]
            }
            searchBlock.find('.wopb-loader-container').addClass('wopb-spin-loader');

            wp.apiFetch( {
                path: '/wopb/product-search',
                method: 'POST',
                data: {
                    search: search,
                    category: category,
                    blockId: searchBlock.data('blockid'),
                    blockName: searchBlock.data('blockname'),
                    postId: post_ID,
                    widgetBlockId: widgetBlockId,
                    wpnonce: wopb_core.security
                }
            })
            .then(response => {
                let searchResult = searchBlock.find('.wopb-search-result');
                searchResult.html(response);
                searchBlock.find('.wopb-search-result').removeClass('wopb-d-none');
                searchBlock.find('.wopb-loader-container').removeClass('wopb-spin-loader');
                if( search ) {
                    searchBlock.find('.wopb-clear').removeClass('wopb-d-none');
                }
                $(document).trigger('wopbAjaxComplete');
            })
            .catch(error => {
                searchBlock.find('.wopb-loader-container').removeClass('wopb-spin-loader');
                if( search ) {
                    searchBlock.find('.wopb-clear').removeClass('wopb-d-none');
                }
            });
        }else {
            searchBlock.find('.wopb-search-result').addClass('wopb-d-none');
            searchBlock.find('.wopb-search-result').html('');
        }
    }

    $(document).on('click', '.wopb-product-search-block .wopb-load-more', function(e) {
        let parents = $(this).parents('.wopb-search-result:first');
        parents.find('.wopb-search-items .wopb-search-item.wopb-extended-item').removeClass('wopb-d-none')
        $(this).addClass('wopb-d-none')
        parents.find('.wopb-less-result').removeClass('wopb-d-none')
    })
    $(document).on('click', '.wopb-product-search-block .wopb-less-result', function(e) {
        let parents = $(this).parents('.wopb-search-result:first');
        parents.find('.wopb-search-items .wopb-search-item.wopb-extended-item').addClass('wopb-d-none')
        $(this).addClass('wopb-d-none')
        parents.find('.wopb-load-more').removeClass('wopb-d-none')
    })
    /*
     * Get search result from search block
    */

    //manage click event when click any place
    $(document).click(function(event) {
        let searchSection = $('.wopb-product-search-block');
        let selectDropdown = $('.wopb-dropdown-select');
        if (!searchSection.is(event.target) && !$(event.target).closest(searchSection).length) {
            $('.wopb-front-block-wrapper.wopb-product-search-block .wopb-search-result').addClass('wopb-d-none');
        }
        if (!selectDropdown.is(event.target) && !$(event.target).closest(selectDropdown).length && !$(event.target).closest('.edit-post-sidebar').length && !$(event.target).closest('.popover-slot').length) {
            $('.wopb-select-items').hide();
        }
    })

    $(document).on('click', '.wopb-banner-link', function(e) {
        if($(this).attr('href')) {
            window.open($(this).attr('href'), $(this).attr('target'));
        }
    });


    // -------------------------------
    // Sales Popup Notifications Addon [START]
    // -------------------------------
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for ( let i = 0; i < ca.length; i++ ) {
            let c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }


    $(window).on('load', function() {
		if ( getCookie('sales_notification') || ! $('body').hasClass('wopb-notification-body') ) {
			return;
		}

        let freequencyTime = 14000; // MS
        let stayTime = 7000;
        const classList = $('body').attr('class').split(/\s+/);
        let _temp = 0;
        classList.forEach(el => {
            if ( el.indexOf('wopb-gap-') != -1 ) {
                _temp = parseInt( el.replace('wopb-gap-', '') ) * 1000;
            }
            if ( el.indexOf('wopb-stay-') != -1 ) {
                stayTime = parseInt( el.replace('wopb-stay-', '') ) * 1000;
            }
            freequencyTime = _temp + stayTime;
        });

        let intervalID;
        const startTime = 2000; // Start Time
        let notificationCount = 1;
        let notificationResponse = 1;
        let notificationTimeout = ''
        function notifications(html, offTime, res) {
            $('body').append( html );
            notificationTimeout = setTimeout(function() {
                $('.wopb-notification').fadeOut(400, function() { $(this).remove(); });
            }, offTime);
            if( res ) {
                notificationCount += 1;
                if (notificationCount >= res.html.length) {
                    notificationCount = 0;
                }
            }
        }
        setTimeout(function() {
            $.ajax({
                url: wopb_core.rest + 'sales/v1/notification',
                type: 'POST',
                success: function(res) {
                    notificationResponse = res;
                    notifications(notificationResponse.html[0], stayTime);
                    intervalID = setInterval(function() {
                        notifications(notificationResponse.html[notificationCount], stayTime, notificationResponse);
                    }, freequencyTime );
                },
                error: function(xhr) {
                    console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
                },
            });
        }, startTime);


        $(document).on('mouseenter mouseleave', '.wopb-notification', function(e){
            if (e.type === 'mouseenter') {
                clearTimeout(notificationTimeout);
                clearInterval(intervalID);
            } else if ( e.type === 'mouseleave' ) {
                setTimeout(function() {
                    $('.wopb-notification').fadeOut(400, function() { $(this).remove(); });
                }, stayTime);
                intervalID = setInterval(function() {
                    notifications(notificationResponse.html[notificationCount], stayTime, notificationResponse);
                }, freequencyTime );
            }
        })

        $(document).on('click', '.wopb-notification-close', function (e) {
            clearInterval(intervalID);
            $('.wopb-notification').fadeOut(400, function() { $(this).remove(); });

			if (!getCookie('sales_notification')) {
				setCookie('sales_notification', 'closed', 1);
			}
        });
    });
    // -------------------------------
    // Sales Popup Notifications Addon [STOP]
    // -------------------------------


    // -------------------------------
    // Product Title Limit Addon [START]
    // -------------------------------
    if ( $('body').hasClass('wopb-title-limit-archive') ) {
        if ( $('.woocommerce-loop-product__title').length > 0 ) {
            $('.woocommerce-loop-product__title').each(function () {
                $(this).attr( 'title', $(this).text() );
            });
        }
        if ( $('.wp-block-post-title a').length > 0 ) {
            $('.wp-block-post-title a').each(function () {
                $(this).attr( 'title', $(this).text() );
            });
        }

    }
    if ( $( 'body' ).hasClass( 'wopb-title-limit-single' ) ) {
        $('.product_title.entry-title').attr( 'title', $('.product_title.entry-title').text() );
        if ( $('.wp-block-post-title').length > 0 ) {
            $('.wp-block-post-title').each(function () {
                $(this).attr( 'title', $(this).text() );
            });
        }
    }
    // -------------------------------
    // Product Title Limit Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Sticky Add to Cart Addon [START]
    // -------------------------------
    if ( $('.wopb-sticky-cart').length > 0 ) {
        const stickyScroll = parseInt( $('.wopb-sticky-cart').data('scroll') );
        $(window).on( 'scroll', function() {
            if ($(window).scrollTop() > stickyScroll) {
                $('.wopb-sticky-cart').show();
            } else {
                $('.wopb-sticky-cart').hide();
            }
        });
        $('.wopb-sticky-add-to-cart .wopb-sticky-slide-up').on('click', function (event) {
            event.preventDefault();
            let that = $(this);
            let scrollView = $( 'form.cart:first' )
            if( !scrollView.length ) {
                scrollView = $( '.woocommerce-product-gallery' );
            }
            if( scrollView ) {
                scrollView[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        })
    }
    $('.wopb-sticky-cart-qty').on('input', function () {
        $('.wopb-sticky-add-to-cart .add_to_cart_button').attr('data-quantity', $(this).val())
    })
    // -------------------------------
    // Sticky Add to Cart Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Name Your Price Addon [START]
    // -------------------------------
    // Check Minimum and Maximum Condition
    $(document).on('input', '.wopb-name-price-field', function() {
        let that = $(this);
        setTimeout(function() {
            let customPrice = parseInt(that.val().trim());
            let priceWrapper = $('.wopb-name-price-wrapper');
            let priceWarning = $('#wopb-price-warning');
            let minPrice = parseInt(priceWrapper.data('min')) ?? 1;
            let maxPrice = parseInt(priceWrapper.data('max'));
            if (isNaN(customPrice)) {
                priceWarning.text(`Minimum Price limit is ${priceFormat(minPrice)}`).show('fast').delay(4000).fadeOut('show');
            } else if (minPrice && customPrice < minPrice) {
                priceWarning.text(`Minimum Price limit is ${priceFormat(minPrice)}`).show('fast').delay(4000).fadeOut('show');
                that.val(minPrice);
            } else if (maxPrice && customPrice > maxPrice) {
                priceWarning.text(`Maximum Price limit is ${priceFormat(maxPrice)}`).show('fast').delay(4000).fadeOut('show');
                that.val(maxPrice);
            }
            // Disable add to cart if price are not added
            if ($('#wopb-name-price-box').length) {
                customPrice = parseInt($('#wopb-name-price-box').val());
                if (customPrice <= 0) {
                    $('.single_add_to_cart_button').prop('disabled', true);
                } else {
                    $('.single_add_to_cart_button').prop('disabled', false);
                }
            }
        }, 1000)
    });

    // Assign value inside the input box from selected item
    $(document).on('change', 'input[type=radio][name=wopb_custom_prices_suggestion]', function(){
        $('.wopb-chunk-price-wrapper > span').removeClass('active');
        $(this).closest('span').addClass('active')
        $('.wopb-name-price-field').val($(this).val().trim());
        if ( $(this).val().trim() > 0 ) {
            $('.single_add_to_cart_button').prop( 'disabled', false );
        }
    });
    // -------------------------------
    // Name Your Price Addon [STOP]
    // -------------------------------


    // -------------------------------
    // Cart Reserved Timer Addon [START]
    // -------------------------------

    const cartReserved = $('.wopb-cart-reserved');
    if ( cartReserved.length > 0 ) {
        const cartClear = cartReserved.data('hides');
        const cartTime = parseInt( cartReserved.data('expire') );
        const cookieValue = parseInt( getCookie('wopb_cart_reserved_timer') );
        const countDownDate = cookieValue + (cartTime * 60000);
        cartTimer();
        const interVal = setInterval(function() {
            cartTimer();
        }, 1000);

        function cartTimer() {
            const currentTime = Date.now();
            const distance = countDownDate - currentTime;
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            $('.wopb-cart-time').html( (hours > 0 ? (hours > 9 ? hours + ':' : '0' + hours + ':') : '')  + (minutes > 9 ? minutes : '0' + minutes) + ':' + (seconds > 9 ? seconds : '0' + seconds));
            // If the count down is over
            if ( distance <= 0 ) {
                document.cookie = "wopb_cart_reserved_timer" + "=" + 0 + ";" + "2023-02-29T08:57:27.344Z" + ";path=/";
                cartReserved.hide();
                clearInterval( interVal );
            }else if(hours === 0 && minutes === 0 && seconds === 0 ){
                if( cartClear === 'hide' ) {
                    cartReserved.hide();
                } else if( cartClear === 'clear' ) {
                    cartReserved.hide();
                    window.location.href = '?wopb-cart-clear';
                }
            }
        }
    }

    // -------------------------------
    // Cart Reserved Timer Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Cart Animation Addon [START]
    // -------------------------------
    let animationCart = $('body.wopb-animation-cart');
    cartAnimation();
    $(document).on('wopbAjaxComplete', function () {
        cartAnimation();
    });
    function cartAnimation() {
        if (animationCart.length > 0) {
            let animName = '',
                animShow = '',
                animInterval = '';
            $(
                '.single_add_to_cart_button, ' +
                '.wopb-anim-cart-btn'
            ).addClass('wopb-animation');
            $('body').attr('class').split(' ').forEach(el => {
                const _temp = el.split('-');
                if (_temp[0] == 'wopb_anim_name') {
                    animName = _temp[1];
                } else if (_temp[0] == 'wopb_anim_show') {
                    animShow = _temp[1];
                } else if (_temp[0] == 'wopb_anim_interval') {
                    animInterval = _temp[1];
                }
            });
            if (animName && animShow && animInterval) {
                let animCartInterval = '';

                function toggleAnimation(animName) {
                    $('.wopb-animation').toggleClass('wopb-anim-' + animName);
                }

                function animAction(animInterval, animName, animShow) {
                    if (animInterval == 'no') {
                        $('.wopb-animation').addClass('wopb-anim-' + animName).css('animation-iteration-count', 'infinite');
                    } else {
                        toggleAnimation(animName);
                        animCartInterval = setInterval(function () {
                            toggleAnimation(animName);
                        }, parseInt(animInterval) * 1000);
                    }
                }

                if (animShow == 'hover') {
                    $('.wopb-animation').addClass('wopb-anim-onhover')
                }
                if (animationCart.hasClass('wopb_anim_show-hover')) {
                    $('body.wopb_anim_show-hover .wopb-animation').hover(function () {
                        animAction(animInterval, animName, animShow);
                    }, function () {
                        $('.wopb-animation').removeClass('wopb-anim-' + animName);
                        clearInterval(animCartInterval)
                    });
                } else {
                    animAction(animInterval, animName, animShow);
                }
            }
        }
    }
    // -------------------------------
    // Cart Animation Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Product Video Addon [START]
    // -------------------------------
    productVideoElement();

    $(document).ready(function() {
        setTimeout(function() {
            productVideoElement();
        }, 100);

        $('.wopb-product-video-wrapper .wopb-custom-video').on('ended', function() {
            let videoWrapper = $(this).parents('.wopb-product-video-wrapper:first');
            let playIcon = videoWrapper.find('.wopb-video-play-icon');
            let videoItem = videoWrapper.find('.wopb-video-item');
            if( playIcon.length ) {
                playIcon.removeClass('wopb-d-none');
                videoItem.addClass('wopb-d-none');
            }
        });
    })
    $(document).on(
        'click',
        '.wopb-product-video-gallery .flex-control-nav li, .wopb-product-video-gallery .wopb-builder-slider-nav .slick-slide',
        function () {
            let slideCurrent = $('.wopb-product-video-section.flex-active-slide');
            if( $(this).parents('.wopb-builder-slider-nav:first').length ) {
                slideCurrent = $('.wopb-product-video-section').parents('.slick-slide.slick-current:first');
            }
            if( ! slideCurrent.length ) {
                let videoItem = $('.wopb-product-video-gallery .wopb-video-item')
                if( videoItem.find('.wopb-custom-video').length ) {
                    videoItem.find('.wopb-custom-video').trigger('pause');
                }else {
                    let iframe = videoItem.find('iframe:first');
                    iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","method":"pause","args":""}', '*');
                }
            }
    })
    function productVideoElement () {
        //Single Product Gallery Thumbnail
        let singleVideoWrapper = $('.wopb-product-video-gallery .wopb-product-video-wrapper');

        //Add Class to Product Video Wrapper Parent
        if( singleVideoWrapper.length ) {
            $('.wopb-product-video-wrapper').each(function (index) {
                let parent = $(this).parents('.wopb-product-video-section:first');
                if( ! parent.length && $(this).parents('.wopb-builder-slider-for:first').length ) {
                    parent = $(this).parents('.slick-slide:first');
                }
                let playIcon = parent.find('.wopb-video-play-icon').clone()
                playIcon.removeClass('wopb-d-none')
                let navItem = $('.wopb-nav-slide.wopb-video-nav');
                if( ! navItem.length ) {
                    navItem = $('.flex-control-nav li').eq(index);
                    navItem.addClass('wopb-video-nav')
                }
                if( !navItem.find('.wopb-video-play-icon').length ) {
                    navItem.append(playIcon)
                }
                parent.find('.zoomImg').remove();

            })
        }

        //Trigger click when click on slider nav play icon
        $('.wopb-product-video-gallery .wopb-video-nav .wopb-video-play-icon').on('click', function () {
            $(this).parents('.wopb-video-nav:first').find('img').trigger('click');
        })

        //Play Video After Click Play Icon
        $('.wopb-product-video-wrapper .wopb-video-play-icon').on('click', function (e) {
            e.preventDefault();
            let that = $(this);
            let item = that.parent('.wopb-product-video-wrapper').find('.wopb-video-item');
            that.addClass('wopb-d-none');
            item.removeClass('wopb-d-none');
            if($('.wopb-product-video-wrapper iframe:first').length) {
                $('.wopb-product-video-wrapper iframe:first')[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","method":"pause","args":""}', '*');
            }
            let iframe = item.find('iframe:first');
            $('.wopb-product-video-wrapper').find('.wopb-custom-video').trigger('pause');
            if(iframe.length > 0) {
                iframe[0].contentWindow.postMessage('{"event":"command","func":"playVideo","method":"play","args":""}', '*');
            }else {
                item.find('.wopb-custom-video').trigger('play');
            }
        })

        $(document).on('click mouseenter mouseleave', '.wopb-product-video-wrapper .wopb-video-item[data-hover="true"]', function(e){
            let that = $(this);
            // let iframe = that.find('iframe:first');
            let video = that.find('.wopb-custom-video')
            if (e.type === 'mouseenter') {
                if( video[0].paused ) {
                    video.trigger('play');
                    that.attr('data-hover-play', 'true')
                }
            } else if ( e.type === 'mouseleave' ) {
                if( that.attr('data-hover-play') === 'true' ) {
                    video.trigger('pause');
                    video.prop('muted', true);
                    that.attr('data-hover-play', 'false')
                }
            }
        })

        //For loop product when block theme
        let loppVideo = $('.wopb-block-loop-video-wrap')
        if( loppVideo.find('img').height() ) {
            loppVideo.find('.wopb-product-video-wrapper').height(loppVideo.find('img').height())
        }
        if( loppVideo.find('img').width() ) {
            loppVideo.find('.wopb-product-video-wrapper').width(loppVideo.find('img').width())
        }
    }
    // -------------------------------
    // Product Video Addon [STOP]
    // -------------------------------

    // -------------------------------
    // Size Chart Addon [START]
    // -------------------------------

    //Size Chart Modal Open or Close
    $("#wopb-chart-modal-btn").click(function(){
        $(".wopb-size-chart-modal").addClass('wopb-active');
    });
    $(".wopb-chart-modal-close").click(function(){
        $(".wopb-size-chart-modal").removeClass('wopb-active');
    });
    $(document).on( 'click', '.wopb-size-chart-modal.wopb-active', function (e) { // Outside Click
        if (
            $(e.target).closest('.wopb-chart-modal-content').length === 0
        ) {
            $(this).removeClass('wopb-active');
        }
    });
    // -------------------------------
    // Size Chart Addon [STOP]
    // -------------------------------

    //Dom Update event
    $(document).ajaxComplete(function(event, xhr, settings) {
        $(document).trigger('wopbAjaxComplete');
    })
    $(document).on('wopbAjaxComplete', function () {
        productVideoElement();
    })

})( jQuery );