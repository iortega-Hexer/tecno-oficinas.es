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
<div class="block {$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'} ybc_block_gallery {if isset($page) && $page}page_{$page|escape:'html':'UTF-8'}_gallery{else}page_blog_gallery{/if} {if isset($blog_config.YBC_BLOG_GALLERY_BLOCK_HOME_SLIDER_ENABLED) && $blog_config.YBC_BLOG_GALLERY_BLOCK_HOME_SLIDER_ENABLED && $page=='home' || isset($blog_config.YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED) && $blog_config.YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED && $page!='home'}ybc_block_slider{else}ybc_block_default{/if}">
    <h4 class="title_blog title_block">
        <a href="{$gallery_link|escape:'html':'UTF-8'}">
            {l s='Photo gallery' mod='ybc_blog'}
        </a>
    </h4> 
    {if $galleries}
        <div class="block_content">
            {assign var='product_row' value=$blog_config.YBC_BLOG_HOME_PER_ROW|intval}
            <ul class="{if isset($blog_config.YBC_BLOG_GALLERY_BLOCK_HOME_SLIDER_ENABLED) && $blog_config.YBC_BLOG_GALLERY_BLOCK_HOME_SLIDER_ENABLED && $page=='home' || isset($blog_config.YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED) && $blog_config.YBC_BLOG_GALLERY_BLOCK_SIDEBAR_SLIDER_ENABLED && $page!='home'}owl-carousel{/if}">
                {foreach from=$galleries item='gallery'}  
                    <li {if $page=='home'}class="col-xs-12 col-sm-4 col-lg-{12/$product_row|intval}"{/if}>
                        {if isset($blog_config.YBC_BLOG_GALLERY_SLIDESHOW_ENABLED) && $blog_config.YBC_BLOG_GALLERY_SLIDESHOW_ENABLED}
                        <a {if $gallery.description}title="{strip_tags($gallery.description)|escape:'html':'UTF-8'}"{/if}  rel="prettyPhotoBlock[galleryblock]" class="gallery_block_slider gallery_item" href="{$gallery.image|escape:'html':'UTF-8'}">
                            <img src="{$gallery.thumb|escape:'html':'UTF-8'}" title="{$gallery.title|escape:'html':'UTF-8'}"  alt="{$gallery.title|escape:'html':'UTF-8'}"  />
                        </a>
                        {else}
                            <img src="{$gallery.thumb|escape:'html':'UTF-8'}" title="{$gallery.title|escape:'html':'UTF-8'}"  alt="{$gallery.title|escape:'html':'UTF-8'}"  />
                        {/if}   
                        <h3 class="ybc_title_block">{if strlen($gallery.title) > 50}{substr($gallery.title,0,49)|escape:'html':'UTF-8'}...{else}{$gallery.title|escape:'html':'UTF-8'}{/if}</h3>                                           
                    </li>
                {/foreach}            
            </ul>  
            {if $blog_config.YBC_BLOG_DISPLAY_BUTTON_ALL_HOMEPAGE || $page!='home'}
                <div class="blog_view_all_button">
                    <a class="view_all_link" href="{$gallery_link|escape:'html':'UTF-8'}">{l s='View all Photos' mod='ybc_blog'}</a>
                </div>
            {/if}
        </div>      
    {else}
        <div class="block_content">
            <p>{l s='No featured images' mod='ybc_blog'}</p>
            <div class="cleafix"></div>
        </div>
    {/if}
     <div class="clear"></div>
</div>