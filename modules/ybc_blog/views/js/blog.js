/**
 * 2007-2019 ETS-Soft
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
 *  @copyright  2007-2019 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
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
$(document).ready(function(){
    //ybc_loadCaptcha();
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
            if($(this).closest('.upload_form_custom').find('.file_name').length)
            {
                $(this).closest('.upload_form_custom').find('.file_name').html($(this).val());
            }
            else   
                $(this).closest('.upload_form_custom').append('<span class="file_name">'+$(this).val()+'</span>'); 
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
        alert('xx');
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
             
});
function autoLoadBlog()
{
    
    var container = '.ybc-blog-wrapper-blog-list.loadmore';
    if ($(container).length > 0 && $(container+" .blog-paggination a.next").length > 0 && !$(container+" .blog-paggination a.next").hasClass('active') && $(window).scrollTop() + $(window).height() >= $(container).offset().top + $(container).height() ) {
          $(container+" .blog-paggination a.next").addClass('active');
          $('.ets_blog_loading').addClass('active');
          $.ajax({
                url:  $(container+" .blog-paggination a.next").attr('href'),
                data: 'loadajax=1',
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
                    $('.ets_blog_loading').removeClass('active');
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
    if ($('.page_home.ybc_block_slider ul').length > 0) {
        $('.page_home.ybc_block_slider ul').etsowlCarousel({
            items: number_home_posts_per_row,
            responsive: {
                0: {
                    items: 1
                },
                480: {
                    items: 2
                },
                768: {
                    items: 3
                },
                992: {
                    items: 3
                },
                1199: {
                    items: number_home_posts_per_row
                }
            },
            rtl: rtl_blog,
            nav: true,
            dots: true,
            loop: $(".page_home.ybc_block_slider ul li").length > 1,
            rewindNav: false,
            dots: false,
            navText: ['', ''],
            callbacks: true
        });
    }
 
 if ($('.page_home_gallery.ybc_block_slider ul').length > 0)
	$('.page_home_gallery.ybc_block_slider ul').etsowlCarousel({
        items : number_home_posts_per_row,
        navigation : true,
        navigationText : ["",""],
        pagination : false,
        itemsDesktop : [1199,6],
        itemsDesktopSmall : [992,5],
        itemsTablet: [768,4],
        itemsMobile : [480,3],
        rtl: rtl_blog,
        responsive : {
                0 : {
                    items : 3
                },
                480 : {
                    items : 4
                },
                768 : {
                    items : 5
                },
                992 : {
                    items : number_home_posts_per_row
                }
            },
        nav : true,  
        loop: $(".page_home_gallery.ybc_block_slider ul li").length > 1,
        rewindNav : false,
        dots : false,         
        navText: ['', ''],  
        callbacks: true,  
    });
    
 // page blog

    if ( $('.ybc_blog_rtl_mode').length > 0 ){
        var rtl_blog = true;

    } else {
        var rtl_blog = false;
    }
    //alert(rtl_blog);
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