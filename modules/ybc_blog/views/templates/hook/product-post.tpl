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
<script type="text/javascript">
    var number_product_related_per_row ={$blog_config.YBC_BLOG_RELATED_PRODUCT_ROW|intval};
    var number_post_related_per_row ={$blog_config.YBC_BLOG_RELATED_POST_ROW_IN_PRODUCT|intval};
</script>
{if $posts}
    <div class="ybc-blog-related-posts on_product ybc_blog_related_posts_type_{if $blog_config.YBC_BLOG_PRODUCT_POST_TYPE}{$blog_config.YBC_BLOG_PRODUCT_POST_TYPE|escape:'html':'UTF-8'}{else}default{/if}">
        <h4 class="title_blog">{l s='Related articles' mod='ybc_blog'}</h4>
        <div class="ybc-blog-related-posts-wrapper">
            {assign var='post_row' value=$blog_config.YBC_BLOG_RELATED_POST_ROW_IN_PRODUCT|intval}
            <ul class="ybc-blog-related-posts-list dt-{$post_row|intval}{if $blog_config.YBC_BLOG_PRODUCT_POST_TYPE=='carousel'} owl-carousel{/if}">
                {foreach from=$posts item='rpost'}                                            
                    <li class="ybc-blog-related-posts-list-li col-xs-12 col-sm-4 col-lg-{12/$post_row|intval} thumbnail-container">
                        {if $rpost.thumb}
                            <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$rpost.link|escape:'html':'UTF-8'}">
                                <img src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$rpost.thumb|escape:'html':'UTF-8'}{/if}" alt="{$rpost.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$rpost.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                    <div class="loader_lady_custom"></div>
                                {/if}
                            </a>   
                                                                             
                        {/if}
                        <a class="ybc_title_block" href="{$rpost.link|escape:'html':'UTF-8'}">{$rpost.title|escape:'html':'UTF-8'}</a>
                        <div class="ybc-blog-sidear-post-meta">
                            {if $rpost.categories}
                                {assign var='ik' value=0}
                                {assign var='totalCat' value=count($rpost.categories)}                        
                                <div class="ybc-blog-categories">
                                    <span class="be-label">{l s='Posted in' mod='ybc_blog'}: </span>
                                    {foreach from=$rpost.categories item='cat'}
                                        {assign var='ik' value=$ik+1}                                        
                                        <a href="{$cat.link|escape:'html':'UTF-8'}">{ucfirst($cat.title)|escape:'html':'UTF-8'}</a>{if $ik < $totalCat}, {/if}
                                    {/foreach}
                                </div>
                            {/if}
                            <span class="post-date">{date($date_format,strtotime($rpost.datetime_added))|escape:'html':'UTF-8'}</span>
                        </div>
                        {if $allowComments || $show_views || $allow_like}
                            <div class="ybc-blog-latest-toolbar">                                         
                                {if $show_views}
                                    <span class="ybc-blog-latest-toolbar-views">
                                        {$rpost.click_number|intval}
                                        {if $rpost.click_number!=1}
                                            <span>{l s='views' mod='ybc_blog'}</span>
                                        {else}
                                            <span>{l s='view' mod='ybc_blog'}</span>
                                        {/if}
                                    </span> 
                                {/if}                       
                                {if $allow_like}
                                    <span class="ybc-blog-like-span ybc-blog-like-span-{$rpost.id_post|intval} {if isset($rpost.liked) && $rpost.liked}active{/if}"  data-id-post="{$rpost.id_post|intval}">                        
                                        <span class="ben_{$rpost.id_post|intval}">{$rpost.likes|intval}</span>
                                        <span class="blog-post-like-text blog-post-like-text-{$rpost.id_post|intval}">
                                            {l s='Liked' mod='ybc_blog'}
                                        </span>
                                    </span>  
                                {/if}
                                {if $allowComments && $rpost.comments_num>0}
                                    <span class="ybc-blog-latest-toolbar-comments">{$rpost.comments_num|intval}
                                        {if $rpost.comments_num!=1}
                                            <span>{l s='comments' mod='ybc_blog'}</span>
                                        {else}
                                            <span>{l s='comment' mod='ybc_blog'}</span>
                                        {/if}
                                    </span> 
                                {/if}
                            </div>
                        {/if} 
                        {if $display_desc}
                            {if $rpost.short_description}
                                <div class="blog_description">{$rpost.short_description|strip_tags:'UTF-8'|truncate:120:'...'|escape:'html':'UTF-8'}</div>
                            {elseif $rpost.description}
                                <div class="blog_description">{$rpost.description|strip_tags:'UTF-8'|truncate:120:'...'|escape:'html':'UTF-8'}</div>
                            {/if}
                        {/if}
                        <a class="read_more" href="{$rpost.link|escape:'html':'UTF-8'}">{if $blog_config.YBC_BLOG_TEXT_READMORE}{$blog_config.YBC_BLOG_TEXT_READMORE|escape:'html':'UTF-8'}{else}{l s='Read More' mod='ybc_blog'}{/if}</a>    
                    </li>
                {/foreach}                        
            </ul>
        </div>
    </div>
{/if}