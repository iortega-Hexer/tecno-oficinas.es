/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2022 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
 var height_index_heading = 100;
function refreshCapchaPolls()
{
    if($('#ybc-blog-polls-capcha-img').length)
    {
        originalCapcha = $('#ybc-blog-polls-capcha-img').attr('src');
        originalCode = $('#ybc-blog-polls-capcha-img').attr('rel');
        newCode = Math.random();
        $('#ybc-blog-polls-capcha-img').attr('src', originalCapcha.replace(originalCode,newCode));
        $('#ybc-blog-polls-capcha-img').attr('rel', newCode);
        $('input[name="polls_capcha_code"]').val('');
    }
    if($('.ybc_blog_g_recaptcha').length)
    {
        grecaptcha.reset(
            ybc_blog_polls_g_recaptcha
        ); 
    }
    else
    if($('#ybc_blog_polls_g_recaptcha').length > 0)
    {
        ybc_polls_lonloadCallback(); 
    }
}
function changePosition_tablecontent(){
    if ( $('#wrapper > .container').length > 0){
        var button_position_nav = ( $(window).width() - $('#wrapper > .container').width() ) / 2;
    } else if ( $('#columns.container').length > 0 ){
        var button_position_nav = ( $(window).width() - $('#columns.container').width() ) / 2;
    }

    if ( button_position_nav > 120){
        $('.box_table_content_scroll').css({"left": button_position_nav, "margin-left": "-100px", "right": button_position_nav, "margin-right": "-100px"});
    } else {
        $('.box_table_content_scroll').css({"left": "20px", "margin": '0', "right": '20px'});
    }
    console.log(button_position_nav);

}
$(document).ready(function(){
    //ybc_loadCaptcha();
    ybc_blog_create_table_content();
    if($('.bybc-blog-slider .lazyload').length)
    {
        $(document).scrollTop(100);
    }
    $(window).resize(function(e){
        changePosition_tablecontent();
        ybc_blog_display_content_heading();
    });
    if($('.ets_blog_autoload_rte').length)
    {
        tinymce.init({
              selector: '.ets_blog_autoload_rte',
              plugins: "align link image media code",
              browser_spellcheck: true,
              themes: "modern",         
            toolbar1: "code,colorpicker,bold,italic,underline,strikethrough,blockquote,link,align,bulli,numlist,table,image,media,formatselect",   
            convert_urls: false
        });
    }
    $(document).on('click','#check_gpdr',function(){
       if($(this).is(':checked'))
       {
            $('input[name="bcsubmit"]').removeAttr('disabled');
       } 
       else
       {
            $('input[name="bcsubmit"]').attr('disabled','disabled');
       }
    });
    $(document).on('click','input[name="polls_post"]',function(){
        $('.form-polls-body').removeClass('hidden');
        $('.form-group.polls-title').removeClass('noactive');
        $('.form-group.polls-title label').removeClass('checked');
        $(this).parents('label').addClass('checked');
        if($('#polls_name').val()=='')
            $('#polls_name').focus();
        else
            $('#polls_feedback').focus();
        if($('#ybc_blog_polls_g_recaptcha').val())
            refreshCapchaPolls();
    });
    $(document).on('click','button[name="polls_cancel"]',function(){
        $('.form-polls-body').addClass('hidden');
        $('.form-group.polls-title').addClass('noactive');
        if($('.form-polls > .bootstrap').length)
            $('.form-polls > .bootstrap').remove();
        return false;
    });
    $(document).on('click','button[name="polls_submit"]',function(){
        var formData = new FormData($(this).parents('form').get(0));
        $('body').addClass('formloading');
        $('.form-polls >.bootstrap').remove();
        $.ajax({
            url: '',
            data: formData,
            type: 'post',
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(json){
                $('body').removeClass('formloading');
                if(json.error)
                {
                    if($('.form-polls > .bootstrap').length)
                        $('.form-polls > .bootstrap').remove();
                    $('.form-polls-body').after(json.error);
                }
                else
                {
                    $('.form-polls-body').after(json.sussec);
                    $('#polls_post_helpful_no').html('('+json.polls_post_helpful_no+')');
                    $('#polls_post_helpful_yes').html('('+json.polls_post_helpful_yes+')');
                    $('.form-polls-body').addClass('hidden');
                    $('.form-group.polls-title').addClass('noactive');
                    $('input[name="polls_post"]').removeAttr('disabled');
                    $('input[name="polls_post"]:checked').attr('disabled','disabled');
                    $('#polls_feedback').val('');
                }
                refreshCapchaPolls();
            },
            error: function(xhr, status, error)
            {
                $('body').removeClass('formloading');
                var err = eval("(" + xhr.responseText + ")");     
                alert(err.Message); 
                refreshCapchaPolls();              
            }
        });
        return false;
    });
    $(document).on('change','#form_blog input[type="file"],.blog-managament-information input[type="file"]',function(){
        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            alert(ybc_blog_invalid_file);
        }
        else
        {
            var file_name = $(this).val().replace('C:\\fakepath\\','');
            if($(this).closest('.upload_form_custom').find('.file_name').length)
            {
                $(this).closest('.upload_form_custom').find('.file_name').html(file_name);
            }
            else   
                $(this).closest('.upload_form_custom').append('<span class="file_name">'+file_name+'</span>'); 
            readURL(this);            
        }
        if($(this).next('.file_name').length>0)
        {
           $(this).next('.file_name').html($(this).val().replace('C:\\fakepath\\','')) 
        }

    });
    if ( $('.ybc-blog-rtl').length > 0 ){
        var rtl_blog = true;

    } else {
        var rtl_blog = false;
    }

    function loadLazi(){
        if($('img.lazyload').length>0)
        {
            $('img.lazyload').lazyload({
                load : function () {
                    $(this).parent().removeClass('ybc_item_img_ladyload');
                    $(this).parent().parent().removeClass('ybc_item_img_ladyload');
                    $(this).addClass('isload');
                },
                threshold: 100,
            });
        }
    }
    $( window ).load(function() {
      loadLazi();
    });
    loadLazi();
    $(document).on('click','.axpand_button',function(){
       if($(this).hasClass('closed'))
       {
            $(this).next('.list-months').removeClass('hidden');
            $(this).removeClass('closed').addClass('opened');
       }
       else
       {
            $(this).next('.list-months').addClass('hidden');
            $(this).removeClass('opened').addClass('closed');
       } 
    });
    $(document).on('click','.owl-next',function(){
        if($('img.lazyload').length>0)
        {
            $('img.lazyload').lazyload();
        }
    });
    $(window).scroll(function(){
         autoLoadBlog();
    });
    $(document).on('click','.ybc-navigation-blog',function(){
       $(this).toggleClass('active'); 
       $('.ybc-navigation-blog-content').toggleClass('show');   
    });
    runowl();
    autoLoadBlog();
    displayListBlogCategory();
    $(document).on('click','.category-blog-parent',function(){
        $(this).next().toggleClass('show');
        $(this).toggleClass('active');
    });
    $('#ybc-blog-capcha-refesh').click(function(){
        originalCapcha = $('#ybc-blog-capcha-img').attr('src');
        originalCode = $('#ybc-blog-capcha-img').attr('rel');
        newCode = Math.random();
        $('#ybc-blog-capcha-img').attr('src', originalCapcha.replace(originalCode,newCode));
        $('#ybc-blog-capcha-img').attr('rel', newCode);
    });
    $('#ybc-blog-polls-capcha-refesh').click(function(){
        refreshCapchaPolls();
    });
    $('.blog_rating_star').click(function(){
        var rating = parseInt($(this).attr('rel'));
        $('.blog_rating_star').removeClass('star_on');
        $('#blog_rating').val(rating);
        for(i = 1; i<= rating; i++)
        {
            $('.blog_rating_star_'+i).addClass('star_on');
        }
    });
    $(document).on('click','.ybc-block-comment-report',function()
    {
        if(!confirm(ybc_blog_report_warning))
            return false;
        btnObj = $(this);
        btnObj.addClass('active');
        $.ajax({
            url : ybc_blog_report_url,
            data : {
                id_comment : btnObj.attr('rel')
            },
            dataType: 'json',
            type : 'post',
            success: function(json){
                if(json['success'])
                {
                    btnObj.remove();
                    alert(json['success']);
                }   
                else
                {
                    alert(json['error']);
                }
                btnObj.removeClass('active'); 
            },
            error: function(){
                alert(ybc_blog_error);
                btnObj.removeClass('active'); 
            }                       
        });
    });
    $(document).on('click','.ybc-block-comment-reply',function(){
        if ( $(this).hasClass('active') ){
            $('.form_reply_comment').remove();
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
            $(this).closest('.ybc-blog-detail-comment').append('<form class="form_reply_comment" action="#blog_comment_line_'+$(this).attr('rel')+'" method="post"> <input type="hidden" name="replyCommentsave" value="'+$(this).attr('rel')+'"> <textarea name="reply_comwent_text" placeholder="'+placeholder_reply+'"></textarea> <input type="submit" value="Send"> </form>');
            $('textarea[name="reply_comwent_text"]').focus();
        }
        
    });
    $(document).on('click','.ybc-block-comment-delete',function(){
        btnObj = $(this);
        var conf= confirm(ybc_blog_delete_comment);
        if(!btnObj.hasClass('active') && conf)
        {
            btnObj.addClass('active');
            $.ajax({
                url : btnObj.attr('href'),
                data : {
                    ajax : 1
                },
                dataType: 'json',
                type : 'post',
                success: function(json){
                    if(json['error'])
                    {
                        alert(json['error']);
                    }
                    else
                    {
                        btnObj.closest('li').remove();
                        alert(json['success']);
                    }
                    btnObj.removeClass('active'); 
                },
                error: function(){
                    alert(ybc_blog_error);
                    btnObj.removeClass('active'); 
                }                       
            });
        }
        return false;
    });
    $(document).on('click','.ybc-blog-like-span',function()
    {       
        btnObj = $(this);        
        if(!btnObj.hasClass('active2'))
        {
            $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).addClass('active2');
            $.ajax({
                url : ybc_blog_like_url,
                data : {
                    id_post : btnObj.attr('data-id-post')
                },
                dataType: 'json',
                type : 'post',
                success: function(json){
                    if(json['success'])
                    {    
                        $('.ben_'+btnObj.attr('data-id-post')).text(json['likes']);  
                        if(json['liked'])
                        {
                            $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).addClass('active');
                            $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).attr('title',unlike_text);
                        } 
                        else
                        {
                            $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).removeClass('active');
                            $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).attr('title',like_text);
                        }    
                    }                       
                    else
                    {
                        alert(json['error']);
                    } 
                    $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).removeClass('active2');                  
                },
                error: function(){                    
                    $('.ybc-blog-like-span-'+btnObj.attr('data-id-post')).removeClass('active2'); 
                    alert(ybc_like_error);             
                }                       
            });   
        }        
    });    
    
    	
     //Nivo slider
     if($('#ybc_slider.nivo').length > 0){
        $(window).load(function() {
            $('#ybc_slider.nivo').nivoSlider({
                manualAdvance : !sliderAutoPlay,
                effect: 'random',
                pauseTime: YBC_BLOG_SLIDER_SPEED,
                afterLoad: function(){   
                    $('.ybc-blog-slider').removeClass('loading');
                }
            });
        });
     }
     

     if ( $('.ybc-blog-thumbnail-list').length > 0 && $('#ybc_slider.carousel').length > 0 ){
        $('#ybc_slider.carousel').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: true,
          fade: true,
          rtl: rtl_blog,
          dots: YBC_BLOG_SLIDER_DISPLAY_NAV,
          autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 5 : false,
          asNavFor: '.ybc-blog-thumbnail-items',
          responsive: [
            {
                breakpoint: 1200,
                settings: {
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 4 : false,
                }
            }, 
            {
                breakpoint: 992,
                settings: {
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 3 : false,
                }
            }, 
            {
                breakpoint: 768,
                settings: {
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 3 : false,
                }
            },
            {
                breakpoint: 400,
                settings: {
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 2 : false,
                }
            }
          ]
        });
        $('#ybc_slider.carousel').on('afterChange', function(event, slick, currentSlide, nextSlide,slickPrev){
            addLazyLoadAfterSlider();
            });
        $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-items').slick({
          slidesToShow: 5,
          slidesToScroll: 1,
          asNavFor: '#ybc_slider.carousel',
          arrows: true,
          infinite: true,
          rtl: rtl_blog,
          autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 5 : false,
          autoplaySpeed: YBC_BLOG_SLIDER_SPEED,
          focusOnSelect: true,
          responsive: [
            {
                breakpoint: 1200,
                settings: {
                    slidesToShow: 4,
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 4 : false,
                }
            }, 
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 3,
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 3 : false,
                }
            }, 
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 3 : false,
                }
            },
            {
                breakpoint: 400,
                settings: {
                    slidesToShow: 2,
                    autoplay: sliderAutoPlay ? $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-item').length > 2 : false,
                }
            }
          ]
        });
        $(window).load(function(){
             $('.ybc-blog-thumbnail-list .ybc-blog-thumbnail-items').on('afterChange', function(event, slick, currentSlide, nextSlide,slickPrev){   
             addLazyLoadAfterSlider();
            });
            $('.ybc-blog-slider').removeClass('loading');
        });
     } else {
        if ( $('.ybc-blog-thumbnail-list').length <= 0 && $('#ybc_slider.carousel').length > 0 ){
            $('#ybc_slider.carousel').slick({
              slidesToShow: 1,
              slidesToScroll: 1,
              arrows: YBC_BLOG_SLIDER_DISPLAY_NAV,
              fade: true,
              dots: YBC_BLOG_SLIDER_DISPLAY_NAV,
              rtl: rtl_blog,
              infinite: true,
              autoplay: sliderAutoPlay,
              autoplaySpeed: YBC_BLOG_SLIDER_SPEED,
              adaptiveHeight: true
            });
        }
        $(window).load(function(){
            $('.ybc-blog-slider').removeClass('loading');
        });
     }
     $(document).on('change','select[name="ybc_sort_by_posts"]',function(){
        $('.ets_blog_loading.sort').addClass('active').parents('.ybc-blog-wrapper').addClass('loading_sort');
        $.ajax({
                url:  '',
                data: 'loadajax=1&ybc_sort_by_posts='+$('select[name="ybc_sort_by_posts"]').val(),
                type: 'post',
                dataType: 'json',                
                success: function(json){
                    $('.ybc-blog-list').html(json.list_blog);
                    $('.blog-paggination').html(json.blog_paggination);
                    if($('img.lazyload').length>0)
                    {
                        $('img.lazyload').lazyload({
                            load : function () {
                                $(this).parent().removeClass('ybc_item_img_ladyload');
                                $(this).parent().parent().removeClass('ybc_item_img_ladyload');
                            },
                            threshold: 100,
                        });
                    }
                    $('.ets_blog_loading.sort').removeClass('active').parents('.ybc-blog-wrapper').removeClass('loading_sort');
                },
                error: function(error)
                {
                   
                }
        });
     });       
});
function autoLoadBlog()
{
    var container = '.ybc-blog-wrapper-blog-list.loadmore';
    if ($(container).length > 0 && $(container+" .blog-paggination a.next").length > 0 && !$(container+" .blog-paggination a.next").hasClass('active') && $(window).scrollTop() + $(window).height() >= $(container).offset().top + $(container).height() ) {
          $(container+" .blog-paggination a.next").addClass('active');
          $('.ets_blog_loading.autoload').addClass('active');
          $.ajax({
                url:  $(container+" .blog-paggination a.next").attr('href'),
                data: 'loadajax=1'+($('select[name="ybc_sort_by_posts"]').length ? '&ybc_sort_by_posts='+$('select[name="ybc_sort_by_posts"]').val():''),
                type: 'post',
                dataType: 'json',                
                success: function(json){
                    $('.ybc-blog-list').append(json.list_blog);
                    $('.blog-paggination').html(json.blog_paggination);
                    if($('img.lazyload').length>0)
                    {
                        $('img.lazyload').lazyload({
                            load : function () {
                                $(this).parent().removeClass('ybc_item_img_ladyload');
                                $(this).parent().parent().removeClass('ybc_item_img_ladyload');
                            },
                            threshold: 100,
                        });
                    }
                    $('.ets_blog_loading.autoload').removeClass('active');
                },
                error: function(error)
                {
                   
                }
        });         
    } 
}
function runowl()
{
    if ( $('.ybc_blog_rtl_mode').length > 0 ){
        var rtl_blog = true;

    } else {
        var rtl_blog = false;
    }
 if ($('.page_blog.ybc_block_slider ul').length > 0)
	$('.page_blog.ybc_block_slider ul').etsowlCarousel({
        items : 1,
        nav : true,  
        navigation : true,
        navigationText : ["",""],
        pagination : false,
        loop: $(".page_blog.ybc_block_slider ul li").length > 1,
        rewindNav : false,
        dots : false,        
        navText: ['', ''],  
        callbacks: true,
        rtl: rtl_blog,
    });
 
 if ($('.page_blog_gallery.ybc_block_slider ul').length > 0)
	$('.page_blog_gallery.ybc_block_slider ul').etsowlCarousel({
        items : 3,
        navigation : true,
        navigationText : ["",""],
        pagination : false,
        nav : true,  
        loop: $(".page_blog_gallery.ybc_block_slider ul li").length > 1,
        rewindNav : false,
        dots : false,         
        navText: ['', ''],  
        callbacks: true,
        rtl: rtl_blog,
    });
    if ($('.ybc-blog-related-products-wrapper .ybc_related_products_type_carousel').length > 0)
    {
        $('.ybc-blog-related-products-wrapper .ybc_related_products_type_carousel').etsowlCarousel({
            items : number_product_related_per_row,
            navigation : true,
            navigationText : ["",""],
            pagination : false,
            itemsDesktop : [1199,number_product_related_per_row],
            itemsDesktopSmall : [992,number_product_related_per_row],
            itemsTablet: [768,2],
            itemsMobile : [480,1],
            rtl: rtl_blog,
            responsive : {
                // breakpoint from 0 up
                0 : {
                    items : 1
                },
                370 : {
                    items : 2
                },
                992 : {
                    items : number_product_related_per_row
                },
            },
            nav : true,  
            navRewind : false,
            //Pagination
            dots : false,    
            navText : [ , ],
        });
        $('.ybc-blog-related-products-wrapper .ybc_related_products_type_carousel').on('changed.etsowl.carousel', function(event) {
            if($('img.lazyload').length>0)
            {
                $('img.lazyload').lazyload();
            }
        });
    }
    if ($('.ybc_blog_related_posts_type_carousel ul').length > 0)
    {
        $('.ybc_blog_related_posts_type_carousel ul').etsowlCarousel({
            items : number_post_related_per_row,
            navigation : true,
            navigationText : ["",""],
            pagination : false,
            itemsDesktopSmall : [992,number_post_related_per_row],
            itemsTablet: [768,2],
            itemsMobile : [480,1],
            rtl: rtl_blog,
            responsive : {
                // breakpoint from 0 up
                0 : {
                    items : 1
                },
                480 : {
                    items : 2
                },
                768 : {
                    items : 2
                },
                992 : {
                    items : number_post_related_per_row
                },
            },
            nav : true,  
            navRewind : false,
            //Pagination
            dots : false,    
            navText : [ , ],
        });
        $('.ybc_blog_related_posts_type_carousel ul').on('changed.etsowl.carousel', function(event) {
            if($('img.lazyload').length>0)
            {
                $('img.lazyload').lazyload();
            }
        });
    }
}
function displayListBlogCategory()
{
    if($('.ybc_block_categories li.active').length>0)
    {
        $('.ybc_block_categories li.active').closest('.children').addClass('show');
        $('.ybc_block_categories li.active').closest('.children').parent().find('.category-blog-parent').addClass('active');
    }
}
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            
            if($(input).closest('.col-md-9').find('.thumb_post').length <= 0)
            {
                $(input).closest('.col-md-9').append('<div class="thumb_post"><img src="'+e.target.result+'"/> </div>');
            }
            else
            {
                $(input).closest('.col-md-9').find('.thumb_post img').eq(0).attr('src',e.target.result);
            }
                                      
        }
        reader.readAsDataURL(input.files[0]);
    }
}
function ybc_loadCaptcha()  {
    var img = $('.ybc-captcha-img-data:not(.loaded)').first();
    if (img.length > 0)
    {
        img.load(function() {
            if (!img.hasClass('loaded'))
            {
                ybc_refreshCaptcha(img);
            }
            if (img[0].complete && img.hasClass('loaded'))
            {
                ybc_loadCaptcha();
            }
        }).filter(function() {
            return this.complete;
        }).load();
    }
}
function ybc_refreshCaptcha (img) {
    if (img.length && !img.hasClass('loaded'))
    {
        var orgLink = img.attr('src');
        var orgCode = img.attr('rel');
        var rand = Math.random();
        img.attr('src', orgLink.replace(orgCode, rand));
        img.attr('rel', rand);
        if (!img.hasClass('loaded')) {
            img.addClass('loaded');
        }
    }
}

function addLazyLoadAfterSlider(){
    if( $('.slick-cloned .ybc_item_img_ladyload .isload').length ){
        $('.slick-cloned .ybc_item_img_ladyload .isload').parent().removeClass('ybc_item_img_ladyload');
    }
    $('img.lazyload').each(function(){
        $(this).lazyload({
        threshold: 100,
        afterLoad      : function (element) {
			$(this).parent().removeClass('ybc_item_img_ladyload');
            $(this).parent().parent().removeClass('ybc_item_img_ladyload');
            $(this).addClass('isload');
		}
    });
    });
}
function ybc_blog_create_table_content()
{
    if($('.ybc_create_table_content').length)
    {
        $('.ybc_create_table_content h2,.ybc_create_table_content h3,.ybc_create_table_content h4,.ybc_create_table_content h5,.ybc_create_table_content h6').addClass('ybc_heading');
        if($('.ybc_create_table_content .ybc_heading').length)
        {
            $('.ybc_create_table_content').prepend('<div class="ybc_indexing_box"><div class="ybc_indexing_box_title">'+YBC_BLOG_LABEL_TABLE_OF_CONTENT+' <span class="close_open_heading opened"></span></div><div class="ybc_indexing_content_post"></div></div>');
            if($('.ybc_create_table_content h2').length)
            {
                var arr = {2:0,3:0,4:0,5:0,6:0};
            }
            else if($('.ybc_create_table_content h3').length)
            {
                var arr = {3:0,4:0,5:0,6:0};
            }
            else if($('.ybc_create_table_content h4').length)
            {
                var arr = {4:0,5:0,6:0};
            }
            else if($('.ybc_create_table_content h5').length)
            {
                var arr = {5:0,6:0};
            }
            else if($('.ybc_create_table_content h6').length)
            {
                var arr = {6:0};
            }
            else
                return true;
            var count=1;
            $('.ybc_create_table_content .ybc_heading').each(function(){
                if($(this).text().trim()!='')
                {
                    var tagName = $(this).prop("tagName").toLowerCase();
                    var nbTag = parseInt(tagName.replace('h', ''));
                    var idIndex ='';
                    for(i=2;i<=6;i++)
                    {
                        if(typeof arr[i] !='undefined')
                        {
                            if(i<nbTag)
                            {
                                idIndex += arr[i]+'.';
                            }
                            if(i==nbTag)
                            {
                                arr[i] ++;
                                idIndex += arr[i]+'.';
                            }    
                            if(i>nbTag)
                                arr[i]=0;
                        }
                    }
                    $(this).attr('id','ybc_heading_'+tagName+'_'+count);
                    $('.ybc_indexing_content_post').append('<div class="ybc_indexing index_'+tagName+'"><a href="#ybc_heading_'+tagName+'_'+count+'">'+idIndex+ ' '+$(this).text()+'</a></div>');
                    count++;
                }
            });


            $('.ybc-blog-wrapper-content').append('<div class="box_table_content_scroll"><button class="ybc_btn_show_table_content"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M88 48C101.3 48 112 58.75 112 72V120C112 133.3 101.3 144 88 144H40C26.75 144 16 133.3 16 120V72C16 58.75 26.75 48 40 48H88zM480 64C497.7 64 512 78.33 512 96C512 113.7 497.7 128 480 128H192C174.3 128 160 113.7 160 96C160 78.33 174.3 64 192 64H480zM480 224C497.7 224 512 238.3 512 256C512 273.7 497.7 288 480 288H192C174.3 288 160 273.7 160 256C160 238.3 174.3 224 192 224H480zM480 384C497.7 384 512 398.3 512 416C512 433.7 497.7 448 480 448H192C174.3 448 160 433.7 160 416C160 398.3 174.3 384 192 384H480zM16 232C16 218.7 26.75 208 40 208H88C101.3 208 112 218.7 112 232V280C112 293.3 101.3 304 88 304H40C26.75 304 16 293.3 16 280V232zM88 368C101.3 368 112 378.7 112 392V440C112 453.3 101.3 464 88 464H40C26.75 464 16 453.3 16 440V392C16 378.7 26.75 368 40 368H88z"/></svg></button><div>');
            changePosition_tablecontent();
            ybc_blog_display_button_heading();
        }
        $(document).on('click','.close_open_heading',function(){
            $(this).toggleClass('closed').toggleClass('opened');
            $('.ybc_indexing_content_post').toggleClass('hidden');
            ybc_blog_display_button_heading();
        });
        $(document).on('click','.ybc_indexing_content_post .ybc_indexing a,.table_content .ybc_indexing a',function(e){
            e.preventDefault();
            var index_content = $(this).attr('href');
            $([document.documentElement, document.body]).animate({
                scrollTop: $(index_content).offset().top-100
            }, 'normal');
        });
        $(document).on('click','.ybc_btn_show_table_content',function(e){
           e.preventDefault();
           if($('.box_table_content_scroll .table_content').length==0)
           {
                $('.box_table_content_scroll').append('<div class="table_content show"><div class="table-title">'+YBC_BLOG_LABEL_TABLE_OF_CONTENT+'<div class="btn_close_table_content">Close</div></div>'+$('.ybc_indexing_content_post').html()+'<div>');
           } 
           else
               $('.box_table_content_scroll .table_content').toggleClass('show').parents('.box_table_content_scroll').toggleClass('show_content');
           ybc_blog_display_content_heading();
        });
        $(document).on('click','.btn_close_table_content',function(e){
            $('.box_table_content_scroll .table_content').removeClass('show');
        });
        $(window).scroll(function(){
            ybc_blog_display_button_heading();
        });
    }
}
function ybc_blog_display_content_heading(){
    if ( $('#wrapper > .container').length > 0){
        var button_position_nav = ( $(window).width() - $('#wrapper > .container').width() ) / 2;
    } else if ( $('#columns.container').length > 0 ){
        var button_position_nav = ( $(window).width() - $('#columns.container').width() ) / 2;
    }
    var box_table_content_width = $('.box_table_content_scroll .table_content.show').width();
    if ( button_position_nav < box_table_content_width + 100){
        var change_content_align = (box_table_content_width + 100) - button_position_nav;
        $('.box_table_content_scroll .table_content').css({"margin-left": "-"+change_content_align+"px", "margin-right": "-"+change_content_align+"px"});
    }else{
        $('.box_table_content_scroll .table_content').css({"margin-left": "", "margin-right": ""});
    }

    if ( button_position_nav > 120){
        $('.box_table_content_scroll').removeClass('change_position_content');
    } else {
        $('.box_table_content_scroll').addClass('change_position_content');
    }
    console.log(button_position_nav);
}
function ybc_blog_display_button_heading(){
    var begin_container = '.ets_begin_heading_table';
    var end_container = '.ets_end_heading_table';
    var height_begin = $(begin_container).offset().top + 10;

    if ($(begin_container).length > 0 && $(window).scrollTop() > height_begin && $(window).scrollTop() + $(window).height()/2 < $(end_container).offset().top  ) {
        $('.box_table_content_scroll').addClass('show');
        ybc_blog_display_content_heading();
    }
    else
        $('.box_table_content_scroll').removeClass('show');
}
