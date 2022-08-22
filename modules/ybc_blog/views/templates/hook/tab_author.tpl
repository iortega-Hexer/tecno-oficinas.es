{*
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{*
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div id="list_tab_author">
    <ul class="list_tab_author">
        <li class="confi_tab config_tab_author {if $control=='employees'}active{/if}" data-tab-id="all_author"><i class="icon icon-user"></i>&nbsp;{l s='Administrator - Authors' mod='ybc_blog'} &nbsp;<span class="badge">{$totalEmployee|intval}</span></li>
        {if $YBC_BLOG_ALLOW_CUSTOMER_AUTHOR}
            <li class="confi_tab config_tab_author {if $control=='customer'}active{/if}" data-tab-id="all_customer"><i class="icon icon-user"></i>&nbsp;{l s='Community - Authors' mod='ybc_blog'}{if $totalCustomer > 0}&nbsp;<span class="badge">{$totalCustomer|intval}</span>{/if}</li>
        {/if}
        
        <li class="confi_tab config_tab_author {if $control=='author'}active{/if}" data-tab-id="setting"><i class="icon icon-AdminAdmin"></i>&nbsp;{l s='Settings' mod='ybc_blog'}</li> 
    </ul>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        if($('#module_form_1').length)
        {
            $('#module_form_1').before($('#list_tab_author').html());
        }
        else
            $('#module_form').before($('#list_tab_author').html());
        $('#list_tab_author').remove();
        $('.ybc-blog-panel').hide();
        $('.ybc-blog-panel-settings').hide();
        if($('.ybc-blog-panel-employee').length>0)
        {
            $('.ybc-blog-panel-employee').hide();
        }
        if($('.ybc-blog-panel-customer').length>0)
        {
            $('.ybc-blog-panel-customer').hide();
        }
        if($('.config_tab_author.active').attr('data-tab-id')=='all_customer')
        {
            $('.ybc-blog-panel').hide();
            $('.ybc-blog-panel.customer').show();
            if($('.ybc-blog-panel-employee').length>0)
            {
                $('.ybc-blog-panel-employee').hide();
            }
            if($('.ybc-blog-panel-customer').length>0)
            {
                $('.ybc-blog-panel-customer').show();
            }
        }
        else
        {
            if($('.config_tab_author.active').attr('data-tab-id')=='setting')
            {
                $('.ybc-blog-panel-settings').show();
                $('.ybc-blog-panel').hide();
                if($('.ybc-blog-panel-employee').length>0)
                {
                    $('.ybc-blog-panel-employee').hide();
                }
                if($('.ybc-blog-panel-customer').length>0)
                {
                    $('.ybc-blog-panel-customer').hide();
                }
            }
            else
            {
                $('.ybc-blog-panel').hide();
                $('.ybc-blog-panel.employee').show();
                if($('.ybc-blog-panel-employee').length>0)
                {
                    $('.ybc-blog-panel-employee').show();
                }
                if($('.ybc-blog-panel-customer').length>0)
                {
                    $('.ybc-blog-panel-customer').hide();
                }
            }
            
        }
        $(document).on('click','.config_tab_author',function(){
           if(!$(this).hasClass('active'))
           {
                $('.config_tab_author').removeClass('active');
                $(this).addClass('active');
                if($(this).attr('data-tab-id')=='all_author' || $(this).attr('data-tab-id')=='all_customer')
                {
                    $('.ybc-blog-panel-settings').hide();
                    if($(this).attr('data-tab-id')=='all_customer')
                    {
                        $('.ybc-blog-panel').hide();
                        $('.ybc-blog-panel.customer').show();
                        if($('.ybc-blog-panel-employee').length>0)
                        {
                            $('.ybc-blog-panel-employee').hide();
                        }
                        if($('.ybc-blog-panel-customer').length>0)
                        {
                            $('.ybc-blog-panel-customer').show();
                        }
                    }
                    else
                    {
                        $('.ybc-blog-panel').hide();
                        $('.ybc-blog-panel.employee').show();
                        if($('.ybc-blog-panel-employee').length>0)
                        {
                            $('.ybc-blog-panel-employee').show();
                        }
                        if($('.ybc-blog-panel-customer').length>0)
                        {
                            $('.ybc-blog-panel-customer').hide();
                        }
                    }
                }
                else
                {
                    $('.ybc-blog-panel-settings').show();
                    $('.ybc-blog-panel').hide();
                    if($('.ybc-blog-panel-employee').length>0)
                    {
                        $('.ybc-blog-panel-employee').hide();
                    }
                    if($('.ybc-blog-panel-customer').length>0)
                    {
                        $('.ybc-blog-panel-customer').hide();
                    }
                }
           } 
        });
    });
</script>