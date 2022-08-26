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
<div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper ybc-blog-wrapper-blog-list{if isset($blog_config.YBC_BLOG_AUTO_LOAD) &&$blog_config.YBC_BLOG_AUTO_LOAD} loadmore{/if}">
    {if $posts}
        <h2 class="page-heading product-listing">{l s='All Comments' mod='ybc_blog'}</h2>
        <ul class="ybc-blog-list row">
            {assign var='first_post' value=true}
            {foreach from=$posts item='post'}
                <li class="list_post_item">                         
                    <div class="post-wrapper">
                        {if $first_post && ($blog_layout == 'large_list' || $blog_layout == 'large_grid')}
                            {if $post.avata}
                                <div class="author_avata_show">
                                    <img class="author_avata" src="{$post.avata|escape:'html':'UTF-8'}" />
                                </div>
                            {/if}                               
                            {assign var='first_post' value=false}
                        {elseif $post.avata}
                            <div class="author_avata_show">
                                <img class="author_avata" src="{$post.avata|escape:'html':'UTF-8'}" />
                            </div>
                        {/if}
                        <div class="ybc-blog-wrapper-content">
                             <div class="ybc-blog-wrapper-content-main">
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
                                            <div title="{l s='Average rating' mod='ybc_blog'}" class="ybc_blog_review">
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
                                                <meta itemprop="bestRating" content="5"/>                                                
                                            </div>
                                        {/if}
                                    </div>
                                    <span class="comment-time"><span>{l s='On' mod='ybc_blog'} </span>{date($date_format,strtotime($post.datetime_added))|escape:'html':'UTF-8'}</span>
                                </div>     
                                <div class="ybc-blog-comment-content">                
                                    <div class="blogcomment">
                                        {$post.comment|strip_tags:'UTF-8'|truncate:$comment_length:'...'|escape:'html':'UTF-8'}
                                    </div>
                                    {*<a class="view_post" href="{$post.link|escape:'html':'UTF-8'}">{l s='View post' mod='ybc_blog'}</a>*}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            {/foreach}
        </ul>
        {if $comment_paggination}
            <div class="blog-paggination">
                {$comment_paggination nofilter}
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