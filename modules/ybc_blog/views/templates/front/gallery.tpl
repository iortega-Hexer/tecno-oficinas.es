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
{extends file="page.tpl"}
{block name="left_column"}
    {if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}
        <div id="left-column" class="col-xs-12 col-sm-4 col-md-3">
          {hook h="blogSidebar"}
        </div>
    {/if}  
{/block}
{block name="content_wrapper"}
    <div id="content-wrapper" class="{if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}left-column col-xs-12 col-sm-8 col-md-9{elseif isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='right'}right-column col-xs-12 col-sm-8 col-md-9{/if}">
      {block name="content"}
        <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper ybc-blog-wrapper-gallery">
            <h1 class="page-heading">{l s='Photo gallery' mod='ybc_blog'}</h1>
            {if isset($blog_galleries)}                   
                <ul class="ybc-gallery">
                    {foreach from=$blog_galleries item='gallery'}            
                        <li class="col-xs-12 col-sm-4 col-lg-{12/$per_row|intval}">
                            <a class="gallery_item {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}"  {if $gallery.description} title="{strip_tags($gallery.description)|escape:'html':'UTF-8'}"{/if} rel="prettyPhotoGalleryPage[gallery]" href="{$gallery.image|escape:'html':'UTF-8'}">
                                <img src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$gallery.thumb|escape:'html':'UTF-8'}{/if}" title="{$gallery.title|escape:'html':'UTF-8'}" alt="{$gallery.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$gallery.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                            </a>                    
                        </li>
                    {/foreach}
                </ul>                    
                {if $blog_paggination}
                    <div class="blog-paggination">
                        {$blog_paggination nofilter}
                    </div>
                {/if}
            {else}
                <p class="alert alert-warning">{l s='No item found' mod='ybc_blog'}</p>
            {/if}
        </div>                
      {/block}
    </div>
{/block}
{block name="right_column"}
    {if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='right'}
        <div id="right-column" class="col-xs-12 col-sm-4 col-md-3">
          {hook h="blogSidebar"}
        </div>
    {/if}
{/block}
