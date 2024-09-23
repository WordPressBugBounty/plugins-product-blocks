jQuery(document).ready( function($) {


    // Edit Template Event
    $('.wopb-edit-template').on('click', function(e) {
        e.preventDefault();
        const that = $(this)
        const url = new URL($(this).attr('href'));
        const post_ID = url.searchParams.get('post');
        saveData(that, 'update&post_id='+post_ID);
    });


    // Add Condition Button in Editor
    if (document.readyState === 'loading') {
        // window.addEventListener( 'load', appendImportButton );
    } else {
        // appendImportButton();
    }
    function appendImportButton() {
        setTimeout(function() {
            let toolbar = document.querySelector( '.edit-post-header__toolbar' );
            if ( ! toolbar ) {
                toolbar = document.querySelector( '.edit-post-header-toolbar' );
                if ( ! toolbar ) {
                    return;
                }
            }
            let buttonDiv = document.createElement( 'div' );
            let html = `<div class="sab-toolbar-insert-layout">`;
            html += '<button id="builder-condition" class="wopb-popup-button"><span class="dashicons dashicons-admin-settings"></span> Conditions</button>';
            html += `</div>`;
            buttonDiv.innerHTML = html;
            toolbar.appendChild( buttonDiv );
            document.getElementById( 'builder-condition' ).addEventListener( 'click', conditionButton );
        }, 0 );
    }


    // Condition Button Event
    function conditionButton() {
        const current = window.location.href;
        const url = new URL(current);
        showCondition( url.searchParams.get("post"), current );
    }


    // Edit Template Action
    $('.wopb-builder-conditions').on('click', function(e) {
        e.preventDefault();
        const current = $(this).attr('href');
        const url = new URL(current);
        initSet();
        showCondition( url.searchParams.get("post"), current );
    });

    
    // Hide Right Sidebar for Single Page
    $('[name=post_filter]').on('change', function() {
        if (this.value == 'archive') {
            $('.wopb-builder-single-wrap').removeClass('active')
            $('.wopb-builder-archive-wrap').addClass('active')
        } else if (this.value == 'shop' || this.value == 'cart' || this.value == 'checkout' || this.value == 'my-account') {
            $('.wopb-builder-single-wrap').removeClass('active')
            $('.wopb-builder-archive-wrap').removeClass('active')
        } else {
            $('.wopb-builder-archive-wrap').removeClass('active')
            $('.wopb-builder-single-wrap').addClass('active')
        }
    });


    // Show Condition Data
    function showCondition( post_id, current ) {
        $('.wopb-edit-template').show();
        $('.wopb-builder, .wopb-edit-template').addClass('active');
        $('.wopb-new-template').removeClass('active');
        $('.wopb-edit-template').attr('href', current);
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            data: {
                action: 'wopb_edit',
                _wpnonce: builder_option.security,
                post_id: post_id
            },
            success: function(data) {
                const retunData = JSON.parse(data);
                if(Object.keys(retunData).length > 0) {
                    if (retunData.title) {
                        $('.wopb-title').val(retunData.title);
                    }
                    if (retunData.type) {
                        $('[name=post_filter]').val(retunData.type);
                        if (retunData.type == 'single-product') {
                            $('.wopb-builder-archive-wrap').removeClass('active')
                            $('.wopb-builder-single-wrap').addClass('active')
                        } else if (retunData.type == 'shop' || retunData.type == 'cart' || retunData.type == 'my-account') {
                            $('.wopb-builder-single-wrap').removeClass('active')
                            $('.wopb-builder-archive-wrap').removeClass('active')
                        } else {
                            $('.wopb-builder-single-wrap').removeClass('active')
                            $('.wopb-builder-archive-wrap').addClass('active')
                        }
                    }
                    if (retunData.conditions) {
                        retunData.conditions.forEach(element => {
                            const data = element.split('/')
                            if(data[0] == 'include') {
                                if (data.length <= 3) {
                                    $('input[name='+data[data.length - 1]+']').prop( "checked", true );
                                }
                            }
                        });
                    }
                    // appendOption(retunData.author_id, 'author');
                    if (retunData.taxonomy) {
                        Object.keys(retunData.taxonomy).forEach( element => {
                            appendOption(retunData.taxonomy[element], element);
                        });
                    }
                }

                //Check all product select or not
                allSingleProductSelect($('#allsingle'))
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }
    

    // Append Option Data
    function appendOption(data, type) {
        if (typeof data != 'undefined') {
            $('.select-'+type).empty();
            $('.select-'+type).closest('.wopb-multi-select').find('.multi-select-action ul').empty();
            $.each(data, function(i,val) {
                $('.select-'+type).append(
                    '<option value="' + val.id + '" selected>' + val.text + '</option>'
                ).trigger('change');

                $('.select-'+type).closest('.wopb-multi-select').find('.multi-select-action ul').append(
                    '<li class="multi-select-single" data-id="' + val.id + '">'+ val.text +' <span class="multi-select-close" data-id="' + val.id + '"> x </span></li>'
                )
            });
        } else {
            $('.select-'+type+' option').remove().trigger('change')
        }
    }


    // Save Data
    function saveData(that, operation) {
        if($('#allsingle').prop('checked')){
            $('.wopb-multi-select').find('.multi-select-data').find("option").remove().trigger('change');
            $('.multi-select-single').remove();
        }
        const submitData =  that.closest('form').serialize();
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            data: submitData+'&operation='+operation,
            beforeSend: function() {
                $('.wopb-new-template').removeClass('active');
            },
            success: function(res) {
                if (operation == 'insert') {
                    $('.wopb-edit-template').show().attr('href', res.data.replace("&amp;", "&"));
                } else {
                    const url = window.location.href;
                    if (url.indexOf('&action=edit') === -1) {
                        window.location.href = res.data.replace("&amp;", "&");
                    } else {
                        $('.wopb-builder, .wopb-new-template, .wopb-edit-template').removeClass('active');
                    }
                }
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }


    // New Template Create Action
    $('.wopb-new-template').on('click', function(e){
        e.preventDefault();
        if ( $('.wopb-title').val().length == 0 ) {
            $('.wopb-message').text('Empty Title !');
        } else {
            saveData($(this), 'insert')
        }
    });


    // New Template Button Popup Action
    $('.page-title-action').on('click', function(e) {
        if ($('.wopb-pro-needed').length < 1) {
        const href = $(this).attr('href')
        $('.wopb-builder')[0].reset();
        $('.wopb-edit-template').hide();
        initSet();
        if(href.indexOf('post_type=wopb_builder') > 0){
            e.preventDefault();
            $('.wopb-builder, .wopb-new-template, .wopb-builder-single-wrap').addClass('active');
            $('.wopb-edit-template').removeClass('active');
        }
        }
    });

    // Builder Notice Popup
    if ($('.wopb-pro-needed').length > 0) {
        $('.page-title-action').addClass('wopb-pro-update-notice');
    }
    $('.wopb-pro-update-notice').on('click', function(e) {
        e.preventDefault();
        $('.wopb-pro-notice').addClass('active');
    });
    $('.wopb-pro-notice-close').on('click', function(e) {
        e.preventDefault();
        $('.wopb-pro-notice').removeClass('active');
    });
    
    

    // Popup Close Action
    $('.wopb-builder-close').on('click', function(e) {
        $('.wopb-builder, .wopb-new-template, .wopb-edit-template').removeClass('active');
    });


    // Template Type
    if( $("select[name=template_type]").length > 0 ) {
        let tabData = '';
        function getActivate(val){
            const url = new URL(window.location.href);
            let type = url.searchParams.get("template_type");
            type = (type == null) ? 'all' : type;
            if (type == val) {
                return 'nav-tab-active';
            } else {
                return '';
            }
        }
        function getURL(val) {
            let url = window.location.href
            url = url.split('template_type=')
            return url[0] + 'template_type=' + (typeof(url[1]) != 'undefined' ? val : 'all' )
        }
        $( "select[name=template_type] option" ).each(function() {
            const value = $(this).val();
            tabData += '<a href="'+getURL(value)+'" class="'+getActivate(value)+' wpxpo-tab-index nav-tab">'+$(this).text()+'</a>';
        });
        $('.wp-header-end').after('<div class="nav-tab-wrapper">'+ tabData +'</div><br/>');
    }


    // Initial Set Value
    function initSet(){
        $(".wopb-single-select").each(function(e) {
            $(this).prop('checked', false);
        });
        $(".wopb-multi-select").each(function(e) {
            $(this).find('.multi-select-data').html('');
            $(this).find('.multi-select-action').html('<ul></ul>');
        });
    }


    // Set Option Value
    function setOption(selector){
        selector = selector.find('.multi-select-data');
        let $html = '<ul>';
        (selector.val()||[]).forEach( item => {
            $html += '<li class="multi-select-single" data-id="'+item+'">'+selector.find('option[value="'+item+'"]').text()+' <span class="multi-select-close" data-id="'+item+'"> x </span></li>';
        });
        $html += '</ul>';
        selector.closest('.wopb-multi-select').find('.multi-select-action').html(selector.val() ? $html : '');
    }


    // Search Keyup
    $('.wopb-item-search').on('change paste keyup', function(e){
        e.preventDefault();
        getAjaxData($(this).closest('.wopb-multi-select'), $(this).val());
    });


    // Search Button Click
    $('.wopb-multi-select-action').on('click', function(e){
        e.preventDefault();
        const dropdown = $(this).closest('.wopb-multi-select').find('.wopb-search-dropdown')
        if(dropdown.hasClass('active')){
            dropdown.removeClass('active')
        }else{
            getAjaxData($(this).closest('.wopb-multi-select'), '');
            dropdown.addClass('active');
        }
    });


    // Single Dropdown Click
    $(document).on('click', '.multi-select-single', function(e){
        e.preventDefault();
        const selector = $(this).closest('.wopb-multi-select').find('.multi-select-data');
        if($.inArray($(this).data('id').toString(), selector.val()) === -1){
            selector.append( '<option value="' + $(this).data('id') + '" selected>' + $(this).text() + '</option>').trigger('change');
            selector.find('option').not(':selected').remove();
        }
        setOption($(this).closest('.wopb-multi-select'));
    });


    // Close Button Click
    $(document).on('click', '.multi-select-close', function(e){
        e.preventDefault();
        e.stopPropagation();
        const selector = $(this).closest('.wopb-multi-select').find('.multi-select-data');
        selector.find("option[value='"+$(this).data('id')+"']").remove().trigger('change');
        $(this).closest('.multi-select-single').remove(); 
    });


    // Get AJAX Search Data
    function getAjaxData(selector, searchTerm){
        const parent = selector.find('.wopb-search-results')
        $.ajax({
            url: builder_option.ajax,
            type: 'POST',
            dataType: 'json',
            data:{
                action: 'wopb_search',
                _wpnonce: builder_option.security,
                type: selector.find('.multi-select-data').data('type'),
                term: searchTerm
            },
            success: function(data) {
                let $html = '<ul>';
                data.forEach( item => {
                    $html += '<li class="multi-select-single" data-id="'+item.id+'">'+item.text+'</li>';
                });
                $html += '</ul>';
                parent.html($html);
                return data;
            },
            error: function(xhr) {
                console.log('Error occured.please try again' + xhr.statusText + xhr.responseText );
            },
        });
    }


    // Outside Search Close
    $(document).mouseup(function(e){
        let container = $(".wopb-search-dropdown");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $('.wopb-search-dropdown').removeClass('active');
        }
    });

    //Check all product select or not
    $('#allsingle').click(function () {
        allSingleProductSelect($(this))
    })
    function allSingleProductSelect(object) {
        let multi_select = object.parent().parent().find('.wopb-multi-select');
        if(object.prop('checked')){
            multi_select.hide()
        }else{
            multi_select.show()
        }
    }

});