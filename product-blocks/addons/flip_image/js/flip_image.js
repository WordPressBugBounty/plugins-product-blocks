(function($) {
    'use strict';
    let flipTimeOut = '';
    $(document).ready(function() {
        flipClassAdd();
        flipEvent();
    })
    $(document).on('wopbAjaxComplete', function () {
        flipClassAdd();
        flipEvent();
    });
    function flipClassAdd() {
        $(".wopb-flip-image").parent().addClass('wopb-flip-image-section');
    }
    function flipEvent() {
        $('.wopb-flip-image-section').each(function () {
            let that = $(this);
            (that.parent()).on('mouseenter mouseleave', function (e) {

                let mouseEvent = e;
                let flipSection = $(this);
                if(
                    ! flipSection.hasClass('wopb-flip-image-section') &&
                    flipSection.find('.wopb-flip-image-section:first').length
                ) {
                    flipSection = flipSection.find('.wopb-flip-image-section:first');
                } else if( ! flipSection.hasClass('wopb-flip-image-section') ) {
                    flipSection = flipSection.parent().find('.wopb-flip-image-section');
                }
                let image = flipSection.find('img:not(.wopb-flip-image)');
                let animationDuration = (parseFloat($(".wopb-flip-image").css('animation-duration')) * 1000);
                if(mouseEvent.type === 'mouseenter') {
                    flipSection.addClass('wopb-flip-image-hover');
                    flipTimeOut = setTimeout(function() {
                        image.css("opacity", 0);
                    }, (animationDuration * 0.4));
                }else if(mouseEvent.type === 'mouseleave') {
                    clearTimeout(flipTimeOut);
                    flipSection.removeClass('wopb-flip-image-hover');
                    image.css("opacity", 1);
                }
            });
        })
    }
})( jQuery );