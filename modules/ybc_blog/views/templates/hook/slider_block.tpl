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
{if $slides}
    <div class="bybc-blog-slider 
    {$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'} 
    {if $blog_config.YBC_BLOG_SLIDER_DISPLAY_THUMBNAIL == false} disable_thumbnail{/if}
    {if isset($blog_config.YBC_BLOG_SLIDER_DISPLAY_CAPTION) && $blog_config.YBC_BLOG_SLIDER_DISPLAY_CAPTION}caption-enabled{else}caption-disabled{/if} 
    {if isset($blog_config.YBC_BLOG_SLIDER_DISPLAY_NAV) && $blog_config.YBC_BLOG_SLIDER_DISPLAY_NAV}nav-enabled{else}nav-disabled{/if}">
        <div class="block_content">
            <div class="ybc-blog-slider loading slider-wrapper theme-{$nivoTheme|escape:'html':'UTF-8'} {$blog_config.YBC_BLOG_DISPLAY_TYPE}">
                {if $blog_config.YBC_BLOG_DISPLAY_TYPE != 'carousel'}
                <div class="loading_img">
                    <img src="{$loading_img|escape:'html':'UTF-8'}" alt="{l s='loading' mod='ybc_blog'}" />
                </div>
                {/if}
                <div id="ybc_slider" class="{$blog_config.YBC_BLOG_DISPLAY_TYPE|escape:'html':'UTF-8'}">                     
                    {foreach from=$slides item='slide'}
                        {if $slide.url}<a href="{$slide.url|escape:'html':'UTF-8'}">{/if}
                            {if $blog_config.YBC_BLOG_DISPLAY_TYPE == 'carousel'}
                            <div class="slider_big_image">
                            {/if}
                                <div class="ybc_slider_image{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}">
                                    {if $blog_config.YBC_BLOG_DISPLAY_TYPE == 'carousel'}
                                        <img src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD) && $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$slide.image|escape:'html':'UTF-8'}{/if}" alt="{$slide.caption|escape:'html':'UTF-8'}" title="{$slide.caption|escape:'html':'UTF-8'}"  {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                        data-original="{$slide.image|escape:'html':'UTF-8'}" class="lazyload"{/if}/>
                                    {else}
                                        <img src="{$slide.image|escape:'html':'UTF-8'}" alt="{$slide.caption|escape:'html':'UTF-8'}" title="{$slide.caption|escape:'html':'UTF-8'}" />
                                    {/if}
                                    
                                    {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                    <div class="loader_lady_custom"></div>
                                    {/if}
                                </div>
                            {if $blog_config.YBC_BLOG_DISPLAY_TYPE == 'carousel'}
                                <div class="nivo-caption">
                                    {$slide.caption|escape:'html':'UTF-8'}
                                </div>
                            </div>
                            {/if}
                        {if $slide.url}</a>{/if}
                        
                    {/foreach}                
                </div>                
            </div>
            {if $blog_config.YBC_BLOG_SLIDER_DISPLAY_THUMBNAIL == true && $blog_config.YBC_BLOG_DISPLAY_TYPE == 'carousel' }
                <div class="ybc-blog-thumbnail-list {$blog_config.YBC_BLOG_DISPLAY_TYPE|escape:'html':'UTF-8'}{if $blog_config.YBC_BLOG_SLIDER_DISPLAY_NAV == true} enable_dots{/if}">
                    <div class="ybc-blog-thumbnail-items">
                        {foreach from=$slides item='slide'}
                            <div class="ybc-blog-thumbnail-item" style="position: related;">
                                <div class="ybc_slider_image{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}">
                                    <img src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD) && $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$slide.image|escape:'html':'UTF-8'}{/if}" alt="{$slide.caption|escape:'html':'UTF-8'}" title="{$slide.caption|escape:'html':'UTF-8'}"  {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} 
                                    data-original="{$slide.image|escape:'html':'UTF-8'}" class="lazyload"{/if}/>
                                    {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                    <div class="loader_lady_custom"></div>
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
        </div>
    </div>
    <script type="text/javascript">
        var sliderAutoPlay = {if $nivoAutoPlay}true{else}false{/if}; 
        var YBC_BLOG_SLIDER_DISPLAY_NAV = {if $blog_config.YBC_BLOG_SLIDER_DISPLAY_NAV = true}true{else}false{/if};      
    </script>
{/if}