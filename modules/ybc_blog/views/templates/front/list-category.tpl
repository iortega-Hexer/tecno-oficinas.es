{*
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{extends file="page.tpl"}

{block name="content"}
<div class="row">
    {if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}
        <div id="left-column" class="col-xs-12 col-sm-4 col-md-3">
          {hook h="blogSidebar"}
        </div>
    {/if} 
    <div id="content-wrapper" class="{if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}left-column col-xs-12 col-sm-8 col-md-9{elseif isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='right'}right-column col-xs-12 col-sm-8 col-md-9{/if}">
        <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper ybc-blog-wrapper-blog-list{if isset($blog_config.YBC_BLOG_AUTO_LOAD) &&$blog_config.YBC_BLOG_AUTO_LOAD} loadmore{/if}">
            {if $blog_categories}
                <h2 class="page-heading product-listing">{l s='All categories' mod='ybc_blog'}</h2>
                <ul class="ybc-blog-list row">
                    {assign var='first_post' value=true}
                    {foreach from=$blog_categories item='category'}
                        <li class="list_category_item">                         
                            <div class="post-wrapper">
                                {if $first_post && ($blog_layout == 'large_list' || $blog_layout == 'large_grid')}
                                    {if $category.thumb}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$category.link|escape:'html':'UTF-8'}">
                                            <img title="{$category.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ybc_blog/views/img/bg-grey.png")|escape:'html':'UTF-8'}{else}{$category.thumb|escape:'html':'UTF-8'}{/if}" alt="{$category.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$category.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                                <div class="loader_lady_custom"></div>
                                            {/if}
                                        </a> 
                                    {elseif $category.image}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$category.link|escape:'html':'UTF-8'}">
                                            <img title="{$category.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ybc_blog/views/img/bg-grey.png")|escape:'html':'UTF-8'}{else}{$category.image|escape:'html':'UTF-8'}{/if}" alt="{$category.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$category.image|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                                <div class="loader_lady_custom"></div>
                                            {/if}
                                        </a> 
                                     {/if}                              
                                    {assign var='first_post' value=false}
                                {else}
                                    {if $category.thumb}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$category.link|escape:'html':'UTF-8'}">
                                            <img title="{$category.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ybc_blog/views/img/bg-grey.png")|escape:'html':'UTF-8'}{else}{$category.thumb|escape:'html':'UTF-8'}{/if}" alt="{$category.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$category.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                                <div class="loader_lady_custom"></div>
                                            {/if}
                                        </a> 
                                    {elseif $category.image}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$category.link|escape:'html':'UTF-8'}">
                                            <img title="{$category.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$link->getMediaLink("`$smarty.const._MODULE_DIR_`ybc_blog/views/img/bg-grey.png")|escape:'html':'UTF-8'}{else}{$category.image|escape:'html':'UTF-8'}{/if}" alt="{$category.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$category.image|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                                <div class="loader_lady_custom"></div>
                                            {/if}
                                        </a>
                                    {/if}
                                {/if}
                                <div class="ybc-blog-wrapper-content">
                                     <div class="ybc-blog-wrapper-content-main">
                                        <a class="ybc_title_block" href="{$category.link|escape:'html':'UTF-8'}">
                                            {$category.title|escape:'html':'UTF-8'}&nbsp;({$category.count_posts|intval})
                                        </a>
                                        
                                        {if $category.sub_categogires}
                                            <div class="sub_category">
                                                <ul>
                                                    {foreach from=$category.sub_categogires item='sub_category'}
                                                        <li>
                                                            <a href="{$sub_category.link|escape:'html':'UTF-8'}">{$sub_category.title|escape:'html':'UTF-8'}</a>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {/if}
                                        <div class="blog_description">
                                                {if $category.description}
                                                    {$category.description|truncate:500:'...'|escape:'html':'UTF-8' nofilter}
                                                {/if}                                
                                        </div><div class="clearfix"></div>
                                        {if ( $category.count_posts > 0 )}
                                            {if ( $category.count_posts == 1 )}
                                                <a class="view_detail_link blog_view_all" href="{$category.link|escape:'html':'UTF-8'}">
                                                    {l s='View %d post' sprintf=[$category.count_posts] mod='ybc_blog'}
                                                </a>  
                                             {else}
                                                <a class="view_detail_link blog_view_all" href="{$category.link|escape:'html':'UTF-8'}">
                                                    {l s='View %d posts' sprintf=[$category.count_posts] mod='ybc_blog'}
                                                </a> 
                                             {/if}  
                                        {else}
                                            <a class="view_detail_link blog_view_all" href="{$category.link|escape:'html':'UTF-8'}">
                                                {l s='View detail' mod='ybc_blog'}
                                            </a>
                                        {/if}
                                        {*
                                        <a class="view_detail_link read_more" href="{$category.link|escape:'html':'UTF-8'}">{l s='View detail' mod='ybc_blog'}</a>
                                        *}
                                     </div>
                                </div>
                            </div>
                        </li>
                    {/foreach}
                </ul>
                {if $blog_paggination}
                    <div class="blog-paggination">
                        {$blog_paggination nofilter}
                    </div>
                {/if}
                {if isset($blog_config.YBC_BLOG_AUTO_LOAD) &&$blog_config.YBC_BLOG_AUTO_LOAD}
                    <div class="ets_blog_loading">
                        <span id="squaresWaveG">
                            <span id="squaresWaveG_1" class="squaresWaveG"></span>
                            <span id="squaresWaveG_2" class="squaresWaveG"></span>
                            <span id="squaresWaveG_3" class="squaresWaveG"></span>
                            <span id="squaresWaveG_4" class="squaresWaveG"></span>
                            <span id="squaresWaveG_5" class="squaresWaveG"></span>
                        </span>
                    </div>
                    <div class="clearfix"></div>
                {/if}
            {else}
                <p>{l s='No category found' mod='ybc_blog'}</p>
            {/if}
        </div>                
    </div>
    {if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='right'}
        <div id="right-column" class="col-xs-12 col-sm-4 col-md-3">
          {hook h="blogSidebar"}
        </div>
    {/if}
</div>
{/block}
