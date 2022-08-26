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
if(typeof PS_ALLOW_ACCENTED_CHARS_URL === 'undefined')
        PS_ALLOW_ACCENTED_CHARS_URL = false;
$(document).ready(function(){
    $('#reply_comwent_text').focus(); 
    if($('input[name="id_post"]').length==0 || ($('input[name="id_post"]').length && !$('input[name="id_post"]').val()))
    {
        $('.ybc-form-group #enabled option[value="-2"]').hide();
    }
    searchCustomerAuthor();
    //$('.ets_sl_slider').attr('checked','checked');
    $(document).on('change','input.title',function(){
        ybc_updateFriendlyURL();
    });
    $(document).on('click','.ets_sl_slider',function(){
        var field= $(this).data('field');
        if($(this).is(':checked'))
        {
            $(this).parent().addClass('active');
            if($('#'+field+'_on').length)
            {
                $('#'+field+'_on').click();
            }
            var value_filed=1;
        }
        else
        {
            $(this).parent().removeClass('active');
            if($('#'+field+'_off').length)
            {
                $('#'+field+'_off').click();
            }
             var value_filed=0;
        }
        $.ajax({
            url: '',
            data: 'action=updateBlock&field='+field+'&value_filed='+value_filed,
            type: 'post',
            dataType: 'json',
            async: true,
			cache: false,
            success: function(json){
                if(json.error)
                    alert(json.error);
                else
                {
                    if(json.message)
                    {
                        showSaveMessage(json.message,json.messageType);
                    }
                }
            },
            error: function(xhr, status, error)
            {
                $('body').removeClass('formloading');
                var err = eval("(" + xhr.responseText + ")");     
                alert(err.Message);               
            }
        });        
    });
    $(document).on('click','.sidebar-positions .setting',function(){
        if(!$(this).hasClass('active'))
        {
            $('.ybc-form-group-sidebar').removeClass('active');
            $('.ybc-form-group-sidebar.'+$(this).attr('data-setting')).addClass('active');
            $(this).addClass('active');
        }
    });
    $(document).on('click','.close-setting-sidebar',function(){
         $('.ybc-form-group-sidebar').removeClass('active');
         $('.sidebar-positions .setting').removeClass('active');
    });
    $(document).mouseup(function (e)
    {
        if($('.ybc-form-group-sidebar.active .ybc-form-group-sidebar-wapper').length)
        {
            var container_sidebar = $('.ybc-form-group-sidebar.active .ybc-form-group-sidebar-wapper');
            if (!container_sidebar.is(e.target)&& container_sidebar.has(e.target).length === 0 && !$('.mce-container').is(e.target) && $('.mce-container').has(e.target).length===0)
            {
                $('.ybc-form-group-sidebar').removeClass('active');
                $('.sidebar-positions .setting').removeClass('active');
                if($('.mce-close').length)
                {
                    $('.mce-close').click();
                }
            }
        }
    });
    $(document).on('click','.module_form_submit_btn_sidebar',function(){
        $('#module_form_submit_btn').click();
    });
    $(document).on('click','.close_choose',function(){
        $('#customer_author').val('');
        $('.customer_author_name_choose').remove();
        $('#customer_autocomplete_input').val('');
    });
    $(document).on('click','input[name="is_customer"]',function(){
       if($(this).val()==1)
       {
            $('.from_admin_author').removeClass('show');
            $('.from_customer_author').addClass('show');
       }
       else
       {
            $('.from_admin_author').addClass('show');
            $('.from_customer_author').removeClass('show');
       } 
    });
    $(document).on('click','.ybc_display_form_author',function(){
        $('.form-group.form_author').toggleClass('show');
        return false;
    });
    $('.message_readed_all').click(function(){
        if (this.checked) {
           $('.message_readed').prop('checked', true);
        } else {
            $('.message_readed').prop('checked', false);
        } 
        displayBulkAction();
    });
    $(document).on('click','#ybc_input_profile_employee_All_tabs',function(){
        if($('#ybc_input_profile_employee_All_tabs').is(':checked'))
        {
            $('input[id^=ybc_input_profile_employee]:not([id$=All_tabs])').closest('li').hide();
        }
        else
            $('input[id^=ybc_input_profile_employee]:not([id$=All_tabs])').closest('li').show();
    });
    if($('#ybc_input_profile_employee_All_tabs').is(':checked'))
    {
        $('input[id^=ybc_input_profile_employee]:not([id$=All_tabs])').closest('li').hide();
    }
    $('.reply_readed_all').click(function(){
        if (this.checked) {
           $('.reply_readed').prop('checked', true);
        } else {
            $('.reply_readed').prop('checked', false);
        } 
        displayBulkAction();
    });
    $(document).on('click','.message_readed,.reply_readed',function(){
        displayBulkAction();
    });
    $(document).on('change','#bulk_action_message',function(){
        $('.alert.alert-success').hide();
        if($('#bulk_action_message').val()=='delete_selected')
        {
            var result = confirm(detele_confirm);
            if(!result)
            {
                $(this).val('');
                return false;
            }
                
        }
        $('body').addClass('formloading');
        var formData = new FormData($(this).parents('form').get(0));
        formData.append('submitBulkActionMessage', 1);
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
                    alert(json.error);
                else
                {
                    if(json.url_reload)
                       window.location.href=json.url_reload; 
                    else
                        location.reload();
                }
            },
            error: function(xhr, status, error)
            {
                $('body').removeClass('formloading');
                var err = eval("(" + xhr.responseText + ")");     
                alert(err.Message);               
            }
        });
    });
    $(document).on('change','#bulk_action_reply',function(){
        $('.alert.alert-success').hide();
        if($('#bulk_action_reply').val()=='delete_selected')
        {
            var result = confirm(detele_confirm);
            if(!result)
            {
                $(this).val('');
                return false;
            }
                
        }
        $('body').addClass('formloading');
        var formData = new FormData($(this).parents('form').get(0));
        formData.append('submitBulkActionReply', 1);
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
                    alert(json.error);
                else
                {
                    if(json.url_reload)
                       window.location.href=json.url_reload; 
                    else
                        location.reload();
                }
            },
            error: function(xhr, status, error)
            {
                $('body').removeClass('formloading');
                var err = eval("(" + xhr.responseText + ")");     
                alert(err.Message);               
            }
        });
    });
    if($('#YBC_BLOC_RSS_DISPLAY_custom_hook').length)
    {
        if($('#YBC_BLOC_RSS_DISPLAY_custom_hook').is(':checked'))
        {
            $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').show();
        }
        else
        {
            $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').hide();
        }
    }
    $(document).on('click','#YBC_BLOC_RSS_DISPLAY_custom_hook',function(){
        if($('#YBC_BLOC_RSS_DISPLAY_custom_hook').is(':checked'))
        {
            $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').show();
        }
        else
        {
            $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').hide();
        }
    });
    $(document).on('change','select[name="id_category"]',function(){
       $('button[name="ybc_submit_ybc_post"]').click(); 
    });
    if($('textarea[name="reply"]').length)
    {
        $('textarea[name="reply"]').after('<label for="send_mail"><input type="checkbox" name="send_mail" id="send_mail" checked="checked"> '+send_mail_label+'</label>');
    }
    if($('#sidebar-positions').length)
    {
        var $mypost = $("#sidebar-positions");
    	$mypost.sortable({
    		opacity: 0.6,
            handle: ".position_number",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updateSidebarOrdering";						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
                        showSaveMessage(jsonData.message,jsonData.messageType);
                        var i=1;
                        $('.sidebar-positions li').each(function(){
                            $(this).find('.position_number').html('<span>'+i+'</span>');
                            i++;
                        });
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
    if($('#list-ybc_post').length)
    {
        var $mypost = $("#list-ybc_post");
    	$mypost.sortable({
    		opacity: 0.6,
            handle: ".dragHandle",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updatePostOrdering&id_category="+$('select[name="id_category"]').val();						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
                        //$('.ybc_blogpost').append('<div class="ets_sussecfull_ajax"><span>'+text_update_position+'</span></div>');
                       showSaveMessage(text_update_position,'success');
                       setTimeout(function(){
                        $('.ets_sussecfull_ajax').remove();
                        }, 1500);
                        var i=1;
                        $('.dragGroup span').each(function(){
                            $(this).html(i+(jsonData.page-1)*20);
                            i++;
                        });
                        
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
    if($('#list-ybc_category').length)
    {
        var $mypost = $("#list-ybc_category");
    	$mypost.sortable({
    		opacity: 0.6,
            handle: ".dragHandle",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updateCategoryOrdering";						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
                        //$('.ybc_blogcategory').append('<div class="ets_sussecfull_ajax"><span>'+text_update_position+'</span></div>');
                        showSaveMessage(text_update_position,'success');
                        setTimeout(function(){
                        $('.ets_sussecfull_ajax').remove();
                        }, 1500);
                        var i=1;
                        $('.dragGroup span').each(function(){
                            $(this).html(i+(jsonData.page-1)*20);
                            i++;
                        });
                        
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
    if($('#list-ybc_slide').length)
    {
        var $mypost = $("#list-ybc_slide");
    	$mypost.sortable({
    		opacity: 0.6,
            handle: ".dragHandle",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updateSliderOrdering";						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
                        //$('.ybc_blogslide').append('<div class="ets_sussecfull_ajax"><span>'+text_update_position+'</span></div>');
                        showSaveMessage(text_update_position,'success');
                        setTimeout(function(){
                        $('.ets_sussecfull_ajax').remove();
                        }, 1500);
                        var i=1;
                        $('.dragGroup span').each(function(){
                            $(this).html(i+(jsonData.page-1)*20);
                            i++;
                        });
                        
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
    if($('#list-ybc_gallery').length)
    {
        var $mypost = $("#list-ybc_gallery");
    	$mypost.sortable({
    		opacity: 0.6,
            handle: ".dragHandle",
    		update: function() {
    			var order = $(this).sortable("serialize") + "&action=updateGalleryOrdering";						
                $.ajax({
        			type: 'POST',
        			headers: { "cache-control": "no-cache" },
        			url: '',
        			async: true,
        			cache: false,
        			dataType : "json",
        			data:order,
        			success: function(jsonData)
        			{
                        //$('.ybc_bloggallery').append('<div class="ets_sussecfull_ajax"><span>'+text_update_position+'</span></div>');
                        showSaveMessage(text_update_position,'success');
                        setTimeout(function(){
                        $('.ets_sussecfull_ajax').remove();
                        }, 1500);
                        var i=1;
                        $('.dragGroup span').each(function(){
                            $(this).html(i+(jsonData.page-1)*20);
                            i++;
                        });
                        
                    }
        		});
    		},
        	stop: function( event, ui ) {
       		}
    	});
    }
    if($('input[name="YBC_BLOG_ENABLE_RSS"]').length)
    {
       $('input[name="YBC_BLOG_ENABLE_RSS"]').click(function(){
            if($('input[name="YBC_BLOG_ENABLE_RSS"]:checked').val()==1)
                $('.rss_setting').show();
            else
                $('.rss_setting').hide();
       }); 
       if($('input[name="YBC_BLOG_ENABLE_RSS"]:checked').val()==1)
            $('.rss_setting').show();
        else
            $('.rss_setting').hide();
    } 
    if($('input[name="YBC_BLOG_ENABLE_SITEMAP"]').length)
    {
       $('input[name="YBC_BLOG_ENABLE_SITEMAP"]').click(function(){
            if($('input[name="YBC_BLOG_ENABLE_SITEMAP"]:checked').val()==1)
                $('.sitemap_setting').show();
            else
                $('.sitemap_setting').hide();
       }); 
       if($('input[name="YBC_BLOG_ENABLE_SITEMAP"]:checked').val()==1)
            $('.sitemap_setting').show();
        else
            $('.sitemap_setting').hide();
    }
    if($('input[name="datetime_active"]').length)
    {
        $('input[name="enabled"]').click(function(){
            if($('input[name="enabled"]:checked').val()==1)
                $('input[name="datetime_active"]').closest('.form-group').hide();
            else
                $('input[name="datetime_active"]').closest('.form-group').show();
       }); 
       if($('input[name="enabled"]:checked').val()==1)
            $('input[name="datetime_active"]').closest('.form-group').hide();
        else
            $('input[name="datetime_active"]').closest('.form-group').show();
    }
    if($('input[name="YBC_BLOG_ALLOW_CUSTOMER_AUTHOR"]').length)
    {
       $('input[name="YBC_BLOG_ALLOW_CUSTOMER_AUTHOR"]').click(function(){
            if($('input[name="YBC_BLOG_ALLOW_CUSTOMER_AUTHOR"]:checked').val()==1)
                $('.setting_customer_author').show();
            else
                $('.setting_customer_author').show();
       }); 
       if($('input[name="YBC_BLOG_ALLOW_CUSTOMER_AUTHOR"]:checked').val()==1)
            $('.setting_customer_author').show();
        else
            $('.setting_customer_author').show();
    }
    if($('#datetime_active').length)
    {
        if($('#enabled').val()==2)
        {
            $('#datetime_active').closest('.form-group').show();
        }
        else
           $('#datetime_active').closest('.form-group').hide(); 
        if($('#enabled').val()==1)
        {
            $('#datetime_added').closest('.form-group').show();
        }
        else
           $('#datetime_added').closest('.form-group').hide(); 
        $('#enabled').change(function(){
            if($('#enabled').val()==2)
            {
                $('#datetime_active').closest('.form-group').show();
            }
            else
               $('#datetime_active').closest('.form-group').hide(); 
            if($('#enabled').val()==1)
            {
                $('#datetime_added').closest('.form-group').show();
            }
            else
               $('#datetime_added').closest('.form-group').hide(); 
        });
    }
    
    $(document).on('click','.checkbox_all input',function(){
        if($(this).is(':checked'))
        {
            $(this).closest('.form-group').find('input').attr('checked','checked');
        }
        else
        {
            $(this).closest('.form-group').find('input').removeAttr('checked');
        }
        if($('#YBC_BLOC_RSS_DISPLAY_custom_hook').length)
        {
            if($('#YBC_BLOC_RSS_DISPLAY_custom_hook').is(':checked'))
            {
                $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').show();
            }
            else
            {
                $('#YBC_BLOC_RSS_DISPLAY_custom_hook').closest('.col-lg-9').find('.help-block').hide();
            }
        }
   });
   $(document).on('click','.checkbox input',function(){
        if($(this).is(':checked'))
        {
            if($(this).closest('.form-group').find('input:checked').length==$(this).closest('.form-group').find('input').length-1)
                 $(this).closest('.form-group').find('.checkbox_all input').attr('checked','checked');
        }
        else
        {
            $(this).closest('.form-group').find('.checkbox_all input').removeAttr('checked');
        } 
   });
    $(document).on('change','select[name="YBC_BLOG_SIDEBAR_POSITION"]',function(){
        if($('select[name="YBC_BLOG_SIDEBAR_POSITION"]').val()=='none')
        {
            $('.form-group.mobile_slidebar').hide();
        }
        else
        {
            $('.form-group.mobile_slidebar').show();
            if($('input[name="YBC_BLOG_SIDEBAR_ON_MOBILE"]:checked').val()==0 && $('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
            {
                $('.form-group.mobile_slidebar_off').show();
            }
            else
            {
                $('.form-group.mobile_slidebar_off').hide();
            }
            if($('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
            {
                $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').show();
            }
            else
                $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').hide();
        }
    });
    $(document).on('change','select[name="YBC_BLOG_DISPLAY_TYPE"]',function(){
        if($('select[name="YBC_BLOG_DISPLAY_TYPE"]').val()=='carousel')
        {
            $('.display_thumb_slider').show();
        }
        else
        {
            $('.display_thumb_slider').hide();
            
        }
    });
    
    if($('select[name="YBC_BLOG_DISPLAY_TYPE"]').val()=='carousel')
        {
            $('.display_thumb_slider').show();
        }
        else
        {
            $('.display_thumb_slider').hide();
            
        }
    $('.category-blog-parent').click(function(){
        $(this).toggleClass('active');
       $(this).next().toggleClass('active'); 
    });
    if($('select[name="YBC_BLOG_SIDEBAR_POSITION"]').val()=='none')
    {
        $('.form-group.mobile_slidebar').hide();
    }
    else
    {
        $('.form-group.mobile_slidebar').show();
        if($('input[name="YBC_BLOG_SIDEBAR_ON_MOBILE"]:checked').val()==0 && $('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
        {
            $('.form-group.mobile_slidebar_off').show();
        }
        else
        {
            $('.form-group.mobile_slidebar_off').hide();
        }
        if($('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
        {
            $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').show();
        }
        else
            $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').hide();
    }
    $(document).on('click','input[name="YBC_BLOG_SIDEBAR_ON_MOBILE"],input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]',function(){
        if($('input[name="YBC_BLOG_SIDEBAR_ON_MOBILE"]:checked').val()==0 && $('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
        {
            $('.form-group.mobile_slidebar_off').show();
        }
        else
        {
            $('.form-group.mobile_slidebar_off').hide();
        }
        if($('input[name="YBC_BLOG_SIDEBAR_ENABLED_ON_MOBILE"]:checked').val()==1)
        {
            $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').show();
        }
        else
            $('#YBC_BLOG_SIDEBAR_ON_MOBILE_on').closest('.form-group').hide();
    });
    $(document).on('change','#YBC_BLOG_ALLOW_COMMENT',function(){
        if($(this).val()!='1')
        {
            $('.form-group.comment').hide();
        }
        else
            $('.form-group.comment').show();
    });
    if($('#YBC_BLOG_ALLOW_COMMENT').val()=='0')
    {
        $('.form-group.comment').hide();
    }
    else
        $('.form-group.comment').show();
    if($('#ybc_input_profile_employee_All_privileges').length>0)
    {
        if($('#ybc_input_profile_employee_All_privileges').is(':checked'))
        {
            $('#ybc_input_profile_employee_All_privileges').parent().parent().find('input').attr('checked','checked');
        }
        $(document).on('click','#ybc_input_profile_employee_All_privileges',function(){
           if($(this).is(':checked'))
           {
                $(this).parent().parent().find('input').attr('checked','checked');
           } 
        });
        $(document).on('click','#ybc_input_profile_employee_Blog,#ybc_input_profile_employee_Comment,#ybc_input_profile_employee_Others',function(){
           if(!$(this).is(':checked'))
           {
                $('#ybc_input_profile_employee_All_privileges').removeAttr('checked');
           } 
        });
    }
    $('.ybc-blog-tab-general').addClass('active'); 
    $('.ybc-blog-tab-basic').addClass('active'); 
    $('.config_tab_general').addClass('active');
    if($('.form-group.parent_category').length>0)
    {
        if(!$('#id_parent option').length)
           $('.form-group.parent_category').hide(); 
    }
    $(this).addClass('active');
    $('.confi_tab').click(function(){
        $('.ybc-form-group').removeClass('active');
        $('.ybc-blog-tab-'+$(this).data('tab-id')).addClass('active');  
        $('.confi_tab').removeClass('active');
        $(this).addClass('active');
        displayFormNext();            
    });
    if($('input[name="YBC_BLOG_ALLOW_TABLE_OF_CONTENT"]:checked').val()==1)
        $('.form-group.table_content').show();
    else
        $('.form-group.table_content').hide();
    $(document).on('click','input[name="YBC_BLOG_ALLOW_TABLE_OF_CONTENT"]',function(){
       if($(this).val()==1)
            $('.form-group.table_content').show();
       else
            $('.form-group.table_content').hide();
    });
    if($('.ybc_fancy').length > 0 || true)
    {
        $('.ybc_fancy').fancybox();
    }
    $('#product_autocomplete_input').autocomplete(ybc_blog_ajax_url,{
		minChars: 1,
		autoFill: true,
		max:20,
		matchContains: true,
		mustMatch:false,
		scroll:false,
		cacheLength:0,
		formatItem: function(item) {
			return '<img src="'+item[2]+'" style="width:24px;"/>'+' - '+item[1]+' - '+item[0];
		}
	}).result(ybcAddAccessory);
    $('#product_autocomplete_input').setOptions({
		extraParams: {
			excludeIds : ybcGetAccessoriesIds()
		}
	});
    $(document).on('click','button[name="send_mail_polls"]',function(){
        if(!$(this).hasClass('active'))
        {
            $(this).addClass('active');
            $.ajax({
                url: '',
                data: {
                    message_email: $('#message_email').val(),
                    send_mail_polls:1,
                    subject_email:$('#subject_email').val(),
                    id_polls: $('#id_polls').val(),
                },
                type: 'post',
                dataType: 'json',                
                success: function(json){ 
                    showSaveMessage(json.message,json.messageType); 
                    $('button[name="send_mail_polls"]').removeClass('active'); 
                    if(json.messageType!='error')
                    {
                        setTimeout(function(){
                            $('.popup-form-send-email-polls').removeClass('show');
                        },10000);
                          
                    }                                 
                },
                error: function(error)
                {                                      
                    $('button[name="send_mail_polls"]').removeClass('active'); 
                    //$('.popup-form-send-email-polls').removeClass('show'); 
                }
            });
        }
        return false;
    });
    $(document).on('click','.send_mail_form',function(){
        if(!$(this).hasClass('active'))
        {
            $this=$(this);
            $this.addClass('active');
            $.ajax({
                url: $(this).attr('href')+'&ajax=1',
                data: {},
                type: 'post',
                dataType: 'json',                
                success: function(json){ 
                    $('.popup-form-send-email-polls').addClass('show');
                    $('.popup-form-send-email-polls-content').html(json.html_form);
                    $this.removeClass('active');                                          
                },
                error: function(error)
                {                                      
                    $this.removeClass('active');
                }
            });
        }
        return false;
    });
    $(document).mouseup(function (e)
    {
        var container_polls = $('.popup-form-send-email-polls-content');
        if (!container_polls.is(e.target)&& container_polls.has(e.target).length === 0)
        {
            $('.popup-form-send-email-polls').removeClass('show');
        }
        
    });
    $(document).keyup(function(e) { 
        if(e.keyCode == 27) {
            if($('.popup-form-send-email-polls').length)
                $('.popup-form-send-email-polls').removeClass('show');
        }
    });
    $(document).on('click','.close_popup',function(){
        $('.popup-form-send-email-polls').removeClass('show');
    });
    $(document).on('click','.list-action',function(){
        if(!$(this).hasClass('disabled'))
        {            
            $(this).addClass('disabled');
            $.ajax({
                url: $(this).attr('href')+'&ajax=1',
                data: {},
                type: 'post',
                dataType: 'json',                
                success: function(json){ 
                    if(json.enabled=='1')
                    {
                        $('.list-item-'+json.listId+'.field-'+json.field).removeClass('action-disabled').addClass('action-enabled');
                        $('.list-item-'+json.listId+'.field-'+json.field).html('<i class="icon-check"></i>');
                    }                        
                    else
                    {
                        $('.list-item-'+json.listId+'.field-'+json.field).removeClass('action-enabled').addClass('action-disabled');
                        $('.list-item-'+json.listId+'.field-'+json.field).html('<i class="icon-remove"></i>');
                    }
                    $('.list-item-'+json.listId+'.field-'+json.field).attr('href',json.href);
                    $('.list-item-'+json.listId+'.field-'+json.field).removeClass('disabled');
                    if(json.title)
                      $('.list-item-'+json.listId+'.field-'+json.field).attr('title',json.title);  
                    if(json.messageType && json.message)  
                        showSaveMessage(json.message,json.messageType);                                             
                },
                error: function(error)
                {                                      
                    $('.list-item-'+json.listId+'.field-'+json.field).removeClass('disabled');
                }
            });
        }
        return false;
    });
    $(document).on('click','.delete_url',function(){
        var delLink = $(this);
        if(!$('#module_form').hasClass('disabled'))
        {
            $('#module_form').addClass('disabled');
            $.ajax({
                url: $(this).attr('href')+'&ajax=1',
                data: {},
                type: 'post',
                dataType: 'json',                
                success: function(json){
                    showSaveMessage(json.message,json.messageType);   
                    if(json.messageType!='error')
                    {
                        if(json.image_default)
                        {
                            $('.uploaded_img_wrapper .ybc_fancy').attr('href',json.image_default);
                            $('.uploaded_img_wrapper .ybc_fancy img').attr('src',json.image_default);
                            $('.uploaded_img_wrapper .delete_url').addClass('hidden');
                        }
                        else
                        {
                            delLink.parents('.uploaded_img_wrapper').eq(0).prev('.uploaded_image_label').eq(0).remove();
                            delLink.parents('.uploaded_img_wrapper').eq(0).remove();
                        }
                        
                    }                 
                    $('#module_form').removeClass('disabled');
                },
                error: function(error)
                {
                    showSaveMessage(error,'error');
                    $('#module_form').removeClass('disabled');
                }
            });
        }
        return false;
    });
    var getFormData = function(form)
    {
        var $inputs = $('input[type="file"]:not([disabled])', form);
        $inputs.each(function(_, input) {
            if (input.files.length > 0)
                return;
            $(input).prop('disabled', true);
        });
        var formData = new FormData(form);
        $inputs.prop('disabled', false);
        return formData;
    }
    var clickedObj = $('#module_form button[type="submit"]');    
    //Process Save
    clickedObj.click(function(){
        if($(this).hasClass('submitExportBlog')|| $(this).hasClass('submitImportBlog') || $(this).hasClass('submitImportBlogWP') || $('input[name="YBC_BLOG_ALLOW_CUSTOMER_AUTHOR"]').length>0)
            return true;
        $('.alert.alert-success').parent().remove();
        var $form=$(this).closest('#module_form');
        if(!$form.hasClass('disabled'))
        {
            if(typeof tinymce !== 'undefined' && tinymce.editors.length > 0)
            {                
                tinyMCE.triggerSave();
            }   
            if($('input.tagify').length > 0)
            {
                $('input.tagify').each(function(){
                    $(this).val($(this).tagify('serialize'));
                });
            }
            $form.addClass('disabled');
            var formData = getFormData($(this).parents('form').get(0));
            if($('.defaultForm.disabled input[type="file"]').length > 0)
            {
                $('.defaultForm.disabled input[type="file"]').each(function(){
                    if (document.getElementById($(this).attr('id')).files.length == 0 ) {
                          formData.delete($(this).attr('id'));
                    }
                });
            } 
            if($(this).attr('name')=='submitSaveAndPreview')
                formData.append('submitSaveAndPreview',1);
            $.ajax({
                url: $form.attr('action')+'&ajax=1',
                data: formData,
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(json){
                    if(json.config_values && json.messageType &&  json.messageType=='success')
                    {
                        Object.keys(json.config_values).forEach(function(k){
                            if($('input[data-field="'+k+'"]').length)
                            {
                                if(json.config_values[k] && !$('input[data-field="'+k+'"]').is(':checked'))
                                {
                                    $('input[data-field="'+k+'"]').attr('checked','checked');
                                    $('input[data-field="'+k+'"]').parent().addClass('active');
                                }
                                if(json.config_values[k]==0 && $('input[data-field="'+k+'"]').is(':checked'))
                                {
                                    $('input[data-field="'+k+'"]').removeAttr('checked');
                                    $('input[data-field="'+k+'"]').parent().removeClass('active');
                                }
                            }
                        });
                        
                    }
                    if(json.link_preview)
                    {
                        var url =json.link_preview;
                        var indexphp = url.indexOf('?');
                        var indexthang = url.indexOf('#');
                        if(indexthang>=0)
                            url = url.substr(0,indexthang);
                        if (indexphp >= 0 && indexphp < url.length) { url += '&preview=1';}
                				else { url += '?preview=1';}
                        var redirectWindow = window.open(url, '_blank');
                        redirectWindow.location;
                        $('#enabled option').removeAttr('selected');
                        $('#enabled option[value="-2"]').attr('selected','selected');
                        $('#enabled').change();
                    }
                    else if(json.messageType=='success' && $('#enabled').val()!=-2) 
                    {
                        $('button[name="submitSaveAndPreview"]').addClass('hide');
                    } 
                    if(json.messageType=='success')
                        $('.ybc-form-group #enabled option[value="-2"]').show();
                    showSaveMessage(json.message,json.messageType);
                    if(json.form_author_post)
                    {
                        $('.ybc_form_author_post').html(json.form_author_post);
                        if(!$('.config_tab_basic').hasClass('active'))
                        {
                            $('.ybc-blog-tab-basic').removeClass('active');
                        }
                        searchCustomerAuthor();
                    }
                        
                    if(json.postUrl)
                    {
                        $form.attr('action',json.postUrl);
                        history.pushState(null, null, json.postUrl);
                    }
                    if(json.images)
                    {
                       $.each(json.images,function(i,item){
                            if($('input[name="'+item.name+'"]').length > 0)
                            {
                                updatePreviewImage(item.name,item.url,item.delete_url);
                            }
                       });
                       
                    }
                    if(json.itemId && json.itemKey)
                    {
                        if($('input[name="'+json.itemKey+'"]').length > 0)
                            $('input[name="'+json.itemKey+'"]').val(json.itemId);
                        else
                        {
                            $form.append('<input name="'+json.itemKey+'" value="'+json.itemId+'" type="hidden"/>')
                        }
                    }
                    if(json.ybc_link_desc)
                    {
                        $('.ybc-link-desc').attr(json.ybc_link_desc);
                        $('.ybc-link-desc').html(json.ybc_link_desc);
                    }
                    $form.removeClass('disabled');
                    if(json.messageType!='error')
                    {
                        if($('input[type="file"]').length)
                        {
                            $('input[type="file"]').val('');
                            $('.dummyfile input[type="text"]').val('');
                        }    
                    }
                },
                error: function(error)
                {
                    showSaveMessage(error,'error');
                    $form.removeClass('disabled');
                }
            });
        }
        return false;
    });

    $('input[type="file"]').change(function(){
        if($(this).attr('name')=='blogdata')
        {
            var fileExtension =['zip'];
        }
        else
        {
            if($(this).attr('name')=='blogdatawordpress')
                var fileExtension =['xml'];
            else
                var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];  
        }
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
            $(this).val('');
            if($(this).parents('.col-lg-9').find('.dummyfile').length > 0)
            {
                $(this).parents('.col-lg-9').find('input[type="text"]').val('');
            }
            if($(this).parents('.col-lg-9').eq(0).find('.preview_img').length > 0)
                $(this).parents('.col-lg-9').eq(0).find('.preview_img').eq(0).remove(); 
            if($(this).parents('.col-lg-9').eq(0).find('.uploaded_image_label').length > 0)
            {
                $(this).parents('.col-lg-9').eq(0).find('.uploaded_image_label').removeClass('hidden');
                $(this).parents('.col-lg-9').eq(0).find('.uploaded_img_wrapper').removeClass('hidden');
            }            
            alert(ybc_blog_invalid_file);
        }
        else
        {
            readURL(this);            
        } 
        //$(this).val($(this).val().replace('C:\\fakepath\\',''));    
    });
    if($('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').length || $('input[name="YBC_BLOG_SHOW_LATEST_NEWS_BLOCK"]').length || $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').length || $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').length ||  $('input[name="YBC_BLOG_SHOW_LATEST_BLOCK_HOME"]').length ||  $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').length)
    {  
        $(document).on('click', 'input[name="YBC_BLOG_ALLOW_REPORT"],input[name="YBC_BLOG_ALLOW_COMMENT"],input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"],input[name="YBC_BLOG_ALLOW_LIKE"],input[name="YBC_BLOG_GALLERY_AUTO_PLAY"],input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"],input[name="YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED"],input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"],input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"],input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"],input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]',function(){
            displayFormNext();
        }); 
        $(document).on('change','select[name="YBC_BLOG_CAPTCHA_TYPE"]',function(){
            displayFormNext();
        }); 
        displayFormNext();
    }
});
$(document).on('click','.del_preview',function(){
    if($(this).parents('.col-lg-9').eq(0).next('.uploaded_image_label').length > 0)
    {
        $(this).parents('.col-lg-9').eq(0).next('.uploaded_image_label').removeClass('hidden');
        $(this).parents('.col-lg-9').eq(0).next('.uploaded_image_label').next('.uploaded_img_wrapper').removeClass('hidden');
    }
    else
    if($(this).parents('.col-lg-9').eq(0).find('.uploaded_image_label').length > 0)
    {
        $(this).parents('.col-lg-9').eq(0).find('.uploaded_image_label').removeClass('hidden');
        $(this).parents('.col-lg-9').eq(0).find('.uploaded_img_wrapper').removeClass('hidden');
    }
    $(this).parents('.col-lg-9').eq(0).find('.dummyfile input[type="text"]').val('');
    if($(this).parents('.col-lg-9').eq(0).find('input[type="file"]').length > 0)
    {
        $(this).parents('.col-lg-9').eq(0).find('input[type="file"]').eq(0).val('');
    }
    $(this).parents('.preview_img').remove();
});
$(document).on('click','.ybc-blog-add-new',function(){
    if(!$('.form-group.parent_category').length)
    {
        clearFieldVal();
        $('#module_form').attr('action',$(this).attr('href'));
        history.pushState(null, null, $(this).attr('href'));
        if($('input[name="post_key"]').length > 0 && $('input[name="post_key"]').val() && $('input[name="'+$('input[name="post_key"]').val()+'"]').length > 0)
        {
            $('input[name="'+$('input[name="post_key"]').val()+'"]').val('');
        }
        $('button[name="submitSaveAndPreview"]').removeClass('hide');
        $('#enabled option').removeAttr('selected');
        $('#enabled option[value="1"]').attr('selected','selected');
        $('#enabled option[value="-2"]').hide();
        $('#enabled').change();
        ybc_blog_is_updating = false;
        $('.help-block .ybc_link_view').remove();
        if($(this).prev('.list-toolbar-btn').length)
          $(this).prev('.list-toolbar-btn').hide();  
        return false;  
    }
});
$(document).on('change','#enabled',function(){
    if($(this).val()==-2 || $('#id_post').length==0 || !$('#id_post').val())
        $('button[name="submitSaveAndPreview"]').removeClass('hide');
    else
       $('button[name="submitSaveAndPreview"]').addClass('hide'); 
});
function clearFieldVal()
{
    $('#module_form input[type="text"],#module_form input[type="file"], #module_form textarea, .rte autoload_rte').val('');
    $('#short_description_1').val('');
    if(typeof tinymce !== 'undefined' && tinymce.editors.length > 0)
    {
        for (var i=length; i>0; i--) {
            tinyMCE.editors[i-1].setContent('');            
        };
        tinyMCE.triggerSave();
    }    
    $('#module_form #divAccessories').html('');
    $('#ajax_choose_product input').val('');
    $('#module_form .tagify-container > span, .uploaded_image_label, .uploaded_img_wrapper,.preview_img').remove();
    $('#module_form input[type="checkbox"]').attr('checked', false);
    $('input[name="sort_order"]').val('1');
    $('input[name="click_number"],input[name="likes"]').val('0');
    
}
function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            if($(input).parents('.col-lg-9').eq(0).find('.preview_img').length <= 0)
            {
                $(input).parents('.col-lg-9').eq(0).append('<div class="preview_img"><img src="'+e.target.result+'"/> <i style="font-size: 20px;" class="process-icon-delete del_preview"></i></div>');
            }
            else
            {
                $(input).parents('.col-lg-9').eq(0).find('.preview_img img').eq(0).attr('src',e.target.result);
            }
            if($(input).parents('.col-lg-9').eq(0).next('.uploaded_image_label').length > 0)
            {
                $(input).parents('.col-lg-9').eq(0).next('.uploaded_image_label').addClass('hidden'); 
                $(input).parents('.col-lg-9').eq(0).next('.uploaded_image_label').next('.uploaded_img_wrapper').addClass('hidden');
            }
            else
            if($(input).parents('.col-lg-9').eq(0).find('.uploaded_image_label').length > 0)
            {
                $(input).parents('.col-lg-9').eq(0).find('.uploaded_image_label').addClass('hidden'); 
                $(input).parents('.col-lg-9').eq(0).find('.uploaded_img_wrapper').addClass('hidden');
            }
                                      
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function updatePreviewImage(name,url,delete_url)
{
    if($('input[name="'+name+'"]').length > 0 && $('input[name="'+name+'"]').parents('.col-lg-9').length > 0)
    {
        if($('input[name="'+name+'"]').parents('.col-lg-9').eq(0).find('.preview_img').length > 0)
           $('input[name="'+name+'"]').parents('.col-lg-9').eq(0).find('.preview_img').eq(0).remove(); 
        if($('input[name="'+name+'"]').parents('.col-lg-9').eq(0).find('.uploaded_image_label').length<=0)
        {
            $('input[name="'+name+'"]').parents('.col-lg-9').eq(0).append('<label class="control-label col-lg-3 uploaded_image_label" style="font-style: italic;">Uploaded image: </label><div class="col-lg-9 uploaded_img_wrapper"><a class="ybc_fancy" href="'+url+'"><img title="Click to see full size image" style="display: inline-block; max-width: 200px;" src="'+url+'"></a>'+(delete_url ? '<a class="delete_url" style="display: inline-block; text-decoration: none!important;" href="'+delete_url+'"><span style="color: #666"><i style="font-size: 20px;" class="process-icon-delete"></i></span></a>' : '')+'</div>');
        }
        else
        {            
            var imageWrapper = $('input[name="'+name+'"]').parents('.col-lg-9').eq(0);
            imageWrapper.find('a.ybc_fancy').eq(0).attr('href',url);
            imageWrapper.find('a.ybc_fancy img').eq(0).attr('src',url);
            if(imageWrapper.find('a.delete_url').length > 0)
                imageWrapper.find('a.delete_url').eq(0).attr('href',delete_url);
            $('input[name="'+name+'"]').parents('.col-lg-9').eq(0).find('.uploaded_image_label').removeClass('hidden');
            $('input[name="'+name+'"]').parents('.col-lg-9').eq(0).find('.uploaded_img_wrapper').removeClass('hidden');            
        }
        $('input[name="'+name+'"]').val('');        
    }
}
function showSaveMessage(message, type)
{
    if($('.ybc_blog_alert').length > 0)
      $('.ybc_blog_alert').remove();  
    if($('.ybc_blog_alert').length <= 0)
    {
        if($('.form_polls').length)
            $('.form_polls').append('<div class="ybc_blog_alert hidden"></div>');
        else if($('.ybc-form-group-sidebar.active').length)
        {
            if(type!='error')
            {
                $('.form-wrapper').append('<div class="ybc_blog_alert hidden"></div>');
                $('.ybc-form-group-sidebar.active').removeClass('active');
                $('.sidebar-positions .setting').removeClass('active');
            }
            else
                $('.ybc-form-group-sidebar.active .ybc-form-group-sidebar-wapper').append('<div class="ybc_blog_alert hidden"></div>');
        }    
        else if($('.blog_center_content').length)
            $('.blog_center_content').append('<div class="ybc_blog_alert hidden"></div>');
        else
            $('.form-wrapper').append('<div class="ybc_blog_alert hidden"></div>');
    }
    $('.ybc_blog_alert').addClass('hidden').removeClass('error').removeClass('success').addClass(type=='error' ? 'error' : 'success').html(message).removeClass('hidden');
    if(type!='error')
    {        
        setTimeout(function(){
            $('.ybc_blog_alert').addClass('hidden');
        },10000);
    }    
}
function ybcGetAccessoriesIds()
{
    if ($('#inputAccessories').val() === undefined)
			return '';
		return $('#inputAccessories').val().replace(/\-/g,',');
}
var ybcAddAccessory = function(event, data, formatted)
{
	if (data == null)
		return false;
	var productId = data[1];
	var productName = data[0];

	var $divAccessories = $('#divAccessories');
	var $inputAccessories = $('#inputAccessories');
	var $nameAccessories = $('#nameAccessories');

	/* delete product from select + add product line to the div, input_name, input_ids elements */
	$divAccessories.html($divAccessories.html() + '<div class="form-control-static form-control-static_'+productId+'"><button type="button" onclick="ybcDelAccessory('+productId+');" class="btn btn-default remove_button" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;<img src="'+data[2]+'" style="width:32px;">&nbsp;'+ productName +'</div>');
	$nameAccessories.val($nameAccessories.val() + productName + '¤');
	$inputAccessories.val($inputAccessories.val() + productId + '-');
	$('#product_autocomplete_input').val('');
	$('#product_autocomplete_input').setOptions({
		extraParams: {excludeIds : ybcGetAccessoriesIds()}
	});
};
var ybcAddAuthor=function(event, data, formatted)
{
    if (data == null)
		return false;
    var customerId= data[0];
    var customerName= data[1];
    var url_customer= data[2];
    $('#customer_author').val(customerId);
    if($('.customer_author_name_choose').length)    
        $('.customer_author_name_choose').html(customerName+'<span class="close_choose">x</span>');
    else
        $('#customer_autocomplete_input').before('<div class="customer_author_name_choose">'+customerName+'<span class="close_choose">x</span></div>');
    $('#customer_autocomplete_input').val(customerName);
    $('#customer_author').val(customerId);
}
function ybcDelAccessory(id)
{
	var div = getE('divAccessories');
	var input = getE('inputAccessories');
	var name = getE('nameAccessories');

	// Cut hidden fields in array
	var inputCut = input.value.split('-');
	var nameCut = name.value.split('¤');

	if (inputCut.length != nameCut.length)
		return jAlert('Bad size');

	// Reset all hidden fields
	input.value = '';
	name.value = '';
    $('.form-control-static_'+id).remove();
	for (i in inputCut)
	{
		// If empty, error, next
		if (!inputCut[i] || !nameCut[i])
			continue ;
		// Add to hidden fields no selected products OR add to select field selected product
		if (inputCut[i] != id)
		{
			input.value += inputCut[i] + '-';
			name.value += nameCut[i] + '¤';
		}
		else
			$('#selectAccessories').append('<option selected="selected" value="' + inputCut[i] + '-' + nameCut[i] + '">' + inputCut[i] + ' - ' + nameCut[i] + '</option>');
	}

	$('#product_autocomplete_input').setOptions({
		extraParams: {excludeIds : ybcGetAccessoriesIds()}
	});
};
function displayFormNext()
{
    if($('#YBC_BLOG_RTL_MODE').length >0)
    {
        $('#YBC_BLOG_RTL_MODE').closest('.ybc-form-group').css('margin-top','30px');
    }
    if($('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').length &&  $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]:checked').val()==1)
    {
        $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_SHOW_RELATED_PRODUCTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').length &&  $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]:checked').val()==1)
    {
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_DISPLAY_RELATED_POSTS"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').length &&  $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]:checked').val()==1)
    {
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
    }
    else
    {
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_CATEGORY_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
    }
    if($('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').length &&  $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]:checked').val()==1)
    {
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').show();
    }
    else
    {
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
        $('input[name="YBC_BLOG_DISPLAY_PRODUCT_PAGE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').hide();
    }
    if($('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').length &&  $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]:checked').val()==1 && $('.config_tab_gallery').hasClass('active'))
    {
        $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        if($('input[name="YBC_BLOG_GALLERY_AUTO_PLAY"]').length &&  $('input[name="YBC_BLOG_GALLERY_AUTO_PLAY"]:checked').val()==1)
            $('input[name="YBC_BLOG_GALLERY_AUTO_PLAY"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        else
            $('input[name="YBC_BLOG_GALLERY_AUTO_PLAY"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_GALLERY_SLIDESHOW_ENABLED"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_ALLOW_REPORT"]').length &&  $('input[name="YBC_BLOG_ALLOW_REPORT"]:checked').val()==1 && $('.config_tab_comment').hasClass('active'))
    {
        $('input[name="YBC_BLOG_ALLOW_REPORT"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_ALLOW_REPORT"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_ALLOW_LIKE"]').length &&  $('input[name="YBC_BLOG_ALLOW_LIKE"]:checked').val()==1 && $('.config_tab_comment').hasClass('active'))
    {
        $('input[name="YBC_BLOG_ALLOW_LIKE"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_ALLOW_LIKE"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').length &&  $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]:checked').val()==1 && $('.config_tab_comment').hasClass('active'))
    {
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_DISPLAY_GDPR_NOTIFICATION"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('input[name="YBC_BLOG_ALLOW_COMMENT"]').length &&  $('input[name="YBC_BLOG_ALLOW_COMMENT"]:checked').val()==1 && $('.config_tab_comment').hasClass('active'))
    {
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('input[name="YBC_BLOG_ALLOW_COMMENT"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('select[name="YBC_BLOG_CAPTCHA_TYPE"]').length && $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').val()=='google' && $('.config_tab_general').hasClass('active'))
    {
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    if($('select[name="YBC_BLOG_CAPTCHA_TYPE"]').length && $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').val()=='google3' && $('.config_tab_general').hasClass('active'))
    {
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').addClass('active');
    }
    else
    {
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
        $('select[name="YBC_BLOG_CAPTCHA_TYPE"]').closest('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').next('.ybc-form-group').removeClass('active');
    }
    $('.help_custom').hide();
    $('.help_custom.'+$('#YBC_BLOG_CAPTCHA_TYPE').val()).show();
}
function displayBulkAction()
{
    if($('.message_readed').length)
    {
        if($('.message_readed:checked').length )
        {
            $('#bulk_action_message').show();
        }
        else
        {
            $('#bulk_action_message').hide();
        }
        if($('.message_readed:checked').length==$('.message_readed[data-viewed="1"]:checked').length)
            $('#bulk_action_message option[value="mark_as_read"]').hide();
        else
            $('#bulk_action_message option[value="mark_as_read"]').show();
        if($('.message_readed:checked').length==$('.message_readed[data-viewed="0"]:checked').length)
            $('#bulk_action_message option[value="mark_as_unread"]').hide();
        else
            $('#bulk_action_message option[value="mark_as_unread"]').show();
        
        if($('.message_readed:checked').length==$('.message_readed[data-approved="1"]:checked').length)
            $('#bulk_action_message option[value="mark_as_approved"]').hide();
        else
            $('#bulk_action_message option[value="mark_as_approved"]').show();
        if($('.message_readed:checked').length==$('.message_readed[data-approved="0"]:checked').length)
            $('#bulk_action_message option[value="mark_as_unapproved"]').hide();
        else
            $('#bulk_action_message option[value="mark_as_unapproved"]').show();
    }
    if($('.reply_readed').length)
    {
        if($('.reply_readed:checked').length )
        {
            $('#bulk_action_reply').show();
        }
        else
        {
            $('#bulk_action_reply').hide();
        }
        if($('.reply_readed:checked').length==$('.reply_readed[data-approved="1"]:checked').length)
            $('#bulk_action_reply option[value="mark_as_approved"]').hide();
        else
            $('#bulk_action_reply option[value="mark_as_approved"]').show();
        if($('.reply_readed:checked').length==$('.reply_readed[data-approved="0"]:checked').length)
            $('#bulk_action_reply option[value="mark_as_unapproved"]').hide();
        else
            $('#bulk_action_reply option[value="mark_as_unapproved"]').show();
    }
}
function searchCustomerAuthor()
{
    $('#customer_autocomplete_input').autocomplete(ybc_blog_author_ajax_url,{
		minChars: 1,
		autoFill: true,
		max:20,
		matchContains: true,
		mustMatch:false,
		scroll:false,
		cacheLength:0,
		formatItem: function(item) {
			return item[1]+' - '+item[2];
		}
	}).result(ybcAddAuthor);
}
function ybc_str2url(str)
{
    var ok=true;
    while(ok)
    {
        var first_char = str.charAt(0);
        if(!isNaN(first_char))
        {
            str =str.slice(1);
        }
        else
            return str;
        
    }
}
function ybc_updateFriendlyURL()
{
        if(!ybc_blog_is_updating)
        {
            $('#url_alias_'+id_language).val(str2url(ybc_str2url($('#title_'+id_language).val()), 'UTF-8')); 
        }        
        else
            if($('#url_alias_'+id_language).val() == '')
                $('#url_alias_'+id_language).val(str2url(ybc_str2url($('#title_'+id_language).val()), 'UTF-8')); 
}