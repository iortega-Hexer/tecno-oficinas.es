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
{assign var='first_post' value=false}
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