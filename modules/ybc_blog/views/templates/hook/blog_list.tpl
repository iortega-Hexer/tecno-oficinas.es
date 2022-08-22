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
{if isset($blog_posts) && $blog_posts}
    {foreach from=$blog_posts item='post'}            
        <li>                         
            <div class="post-wrapper">
                {if $is_main_page && $first_post && ($blog_layout == 'large_list' || $blog_layout == 'large_grid')}
                    {if $post.image}
                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                            <img title="{$post.title|escape:'html':'UTF-8'}" src="{$post.image|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.image|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                             </svg>
                             {/if}
                        </a>                              
                    {elseif $post.thumb}
                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                            <img title="{$post.title|escape:'html':'UTF-8'}" src="{$post.thumb|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                             </svg>
                             {/if}
                        </a>
                    {/if}
                    {assign var='first_post' value=false}
                {elseif $post.thumb}
                    <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                        <img title="{$post.title|escape:'html':'UTF-8'}" src="{$post.thumb|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                        {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                             </svg>
                             {/if}
                    </a>
                {/if}
                <div class="ybc-blog-wrapper-content">
                <div class="ybc-blog-wrapper-content-main">
                    <a class="ybc_title_block" href="{$post.link|escape:'html':'UTF-8'}">{$post.title|escape:'html':'UTF-8'}</a>
                    {if $show_categories && $post.categories}
                        <div class="ybc-blog-sidear-post-meta"> 
                            {if !$date_format}{assign var='date_format' value='F jS Y'}{/if}
                            {if $show_categories && $post.categories}
                                <div class="ybc-blog-categories">
                                    {assign var='ik' value=0}
                                    {assign var='totalCat' value=count($post.categories)}
                                    <span class="be-label">{l s='Posted in' mod='ybc_blog'}: </span>
                                    {foreach from=$post.categories item='cat'}
                                        {assign var='ik' value=$ik+1}                                        
                                        <a href="{$cat.link|escape:'html':'UTF-8'}">{ucfirst($cat.title)|escape:'html':'UTF-8'}</a>{if $ik < $totalCat}, {/if}
                                    {/foreach}
                                </div>
                            {/if}
                        </div> 
                    {/if}
                    <div class="ybc-blog-latest-toolbar">	
						{if $show_views}                    
                                <span class="ybc-blog-latest-toolbar-views" title="{l s='Page views' mod='ybc_blog'}">
                                    {$post.click_number|intval}
                                    {if $post.click_number !=1}<span>
                                        {l s='Views' mod='ybc_blog'}</span>
                                    {else}
                                        <span>{l s='View' mod='ybc_blog'}</span>
                                    {/if}
                                </span>
                        {/if} 
                        {if $allow_rating && $post.total_review}
                             <div class="blog_rating_wrapper" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                 <span class="total_views" itemprop="reviewCount">{$post.total_review|intval}</span>
                                 <span>
                                    {if $post.total_review != 1}
                                        {l s='Comments' mod='ybc_blog'}
                                    {else}
                                        {l s='Comment' mod='ybc_blog'}
                                    {/if}
                                </span>
                                {if $allow_rating && isset($post.everage_rating) && $post.everage_rating}
                                    {assign var='everage_rating' value=$post.everage_rating}
                                    <div class="blog-extra-item be-rating-block item">
                                        <div class="blog_rating_wrapper">
                                            <div class="ybc_blog_review" title="{l s='Average rating' mod='ybc_blog'}">
                                                {for $i = 1 to $everage_rating}
                                                    {if $i <= $everage_rating}
                                                        <div class="star star_on"></div>
                                                    {else}
                                                        <div class="star star_on_{($i-$everage_rating)*10|intval}"></div>
                                                    {/if}
                                                {/for}
                                                {if $everage_rating<5}
                                                    {for $i = $everage_rating + 1 to 5}
                                                        <div class="star"></div>
                                                    {/for}
                                                {/if}
                                                <meta itemprop="worstRating" content="0"/>
                                                (<span class="ybc-blog-rating-value"  itemprop="ratingValue">{number_format((float)$everage_rating, 1, '.', '')|escape:'html':'UTF-8'}</span>)
                                                <meta itemprop="bestRating" content="5"/>
                                            </div>
                                        </div>
                                    </div>
                                {/if} 
                             </div>
                        {/if}
                        {if $allow_like}
                            <span title="{if $post.liked}{l s='Liked' mod='ybc_blog'}{else}{l s='Like this post' mod='ybc_blog'}{/if}" class="item ybc-blog-like-span ybc-blog-like-span-{$post.id_post|escape:'html':'UTF-8'} {if $post.liked}active{/if}"  data-id-post="{$post.id_post|escape:'html':'UTF-8'}">                        
                                <span class="blog-post-total-like ben_{$post.id_post|escape:'html':'UTF-8'}">{$post.likes|escape:'html':'UTF-8'}</span>
                                <span class="blog-post-like-text blog-post-like-text-{$post.id_post|escape:'html':'UTF-8'}"><span>{l s='Liked' mod='ybc_blog'}</span></span>
                            </span> 
                        {/if}                     
                        
                    </div>
                    <div class="blog_description">
                         {if $post.short_description}
                            <p>{$post.short_description|strip_tags:'UTF-8'|truncate:500:'...'|escape:'html':'UTF-8'}</p>
                        {elseif $post.description}
                            <p>{$post.description|strip_tags:'UTF-8'|truncate:500:'...'|escape:'html':'UTF-8'}</p>
                        {/if}                                  
                    </div>
                    <a class="read_more" href="{$post.link|escape:'html':'UTF-8'}">{if $blog_config.YBC_BLOG_TEXT_READMORE}{$blog_config.YBC_BLOG_TEXT_READMORE|escape:'html':'UTF-8'}{else}{l s='Read More' mod='ybc_blog'}{/if}</a>
                  </div>
                </div>
            </div>
        </li>
    {/foreach}
{/if}