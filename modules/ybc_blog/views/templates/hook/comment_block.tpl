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
{if $posts}
    {if !isset($date_format) || isset($date_format) && !$date_format}{assign var='date_format' value='F jS Y'}{/if}
    <div class="page_blog block ybc_block_comment {$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'} {if isset($page) && $page}page_{$page|escape:'html':'UTF-8'}{else}page_blog{/if} {if isset($page) && $page=='home'}{if isset($blog_config.YBC_BLOG_HOME_POST_TYPE) && $blog_config.YBC_BLOG_HOME_POST_TYPE=='default' || count($posts)<=1}ybc_block_default{else}ybc_block_slider{/if}{else}{if isset($blog_config.YBC_BLOG_SIDEBAR_POST_TYPE) && $blog_config.YBC_BLOG_SIDEBAR_POST_TYPE=='default' || count($posts)<=1}ybc_block_default{/if}{/if}">
        <h4 class="title_blog title_block">{l s='Latest comments' mod='ybc_blog'}</h4>
        <div class="block_content">
            {assign var='product_row' value=$blog_config.YBC_BLOG_HOME_PER_ROW|intval}
            <ul class="{if count($posts)>1}{if isset($page) && $page=='home' && $blog_config.YBC_BLOG_HOME_POST_TYPE!='default'}owl-carousel{elseif (!isset($page)||(isset($page) && $page!='home')) && $blog_config.YBC_BLOG_SIDEBAR_POST_TYPE!='default'}owl-carousel-new{/if}{/if}">
                {foreach from=$posts item='post'}
                    <li {if $page=='home'}class="col-xs-12 col-sm-4 col-lg-{12/$product_row|intval}"{/if}>
                        <div class="comment_item">
                            {if $post.avata}
                                <div class="author_avata_show">
                                    <img class="author_avata" src="{$post.avata|escape:'html':'UTF-8'}" />
                                </div>
                            {/if}                   
                            <div class="ybc-blog-comment-info">
                                <div class="post-author">
                                {if $post.name}
                                    <span class="post-author-name">{$post.name|escape:'html':'UTF-8'}</span>
                                {/if}
                                {l s='on' mod='ybc_blog'}
                                <a class="ybc_title_block" href="{$post.link|escape:'html':'UTF-8'}">
                                    {$post.subject|escape:'html':'UTF-8'}
                                </a> 
                                </div>
                                <div class="ybc-blog-latest-toolbar">                                         
                                    {if $allow_rating && $post.rating}  
                                        <div itemprop="aggregateRating" title="{l s='Average rating' mod='ybc_blog'}" class="ybc_blog_review">
                                            {assign var='everage_rating' value=$post.rating}
                                            {for $i = 1 to $everage_rating}
                                                {if $i <= $everage_rating}
                                                    <div class="star star_on"></div>
                                                {else}
                                                    <div class="star star_on_{($i-$everage_rating)*10|intval}"></div>
                                                {/if}
                                            {/for}
                                            {if $post.rating<5}
                                                {for $i = $post.rating + 1 to 5}
                                                    <div class="star"></div>
                                                {/for}
                                            {/if}
                                            <meta itemprop="worstRating" content="0"/>
                                            (<span class="ybc-blog-rating-value"  itemprop="ratingValue">{$post.rating|intval}</span>)
                                            <meta itemprop="bestRating" content="5"/>                                                
                                        </div>
                                    {/if}
                                </div>
                            </div>     
                            <div class="ybc-blog-comment-content">                
                                <div class="blogcomment">
                                    {$post.comment|strip_tags:'UTF-8'|truncate:$comment_length:'...'|escape:'html':'UTF-8'}
                                </div>
                                {*<a class="view_post" href="{$post.link|escape:'html':'UTF-8'}">{l s='View post' mod='ybc_blog'}</a>*}
                            </div>
                        </div>
                    </li>
                {/foreach}
            </ul>
            {if isset($all_comment_link)}
                <div class="blog_view_all_button">
                    <a class="blog_view_all" href="{$all_comment_link|escape:'html':'UTF-8'}" title="{l s='View all comments' mod='ybc_blog'}">{l s='View all comments' mod='ybc_blog'}</a>
                </div>
            {/if}
        </div>
        <div class="clear"></div>
    </div>
{/if}