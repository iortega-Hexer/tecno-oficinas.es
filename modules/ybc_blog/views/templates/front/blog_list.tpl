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
    {block name="content_wrapper"}
    {block name="left_column"}
        {if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}
            <div id="left-column" class="col-xs-12 col-sm-4 col-md-3">
              {hook h="blogSidebar"}
            </div>
        {/if}
    {/block}
<div id="content-wrapper" class="{if isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='left'}left-column col-xs-12 col-sm-8 col-md-9{elseif isset($blog_config.YBC_BLOG_SIDEBAR_POSITION) && $blog_config.YBC_BLOG_SIDEBAR_POSITION=='right'}right-column col-xs-12 col-sm-8 col-md-9{/if}">
  {block name="content"}
    <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper ybc-blog-wrapper-blog-list{if isset($blog_config.YBC_BLOG_AUTO_LOAD) &&$blog_config.YBC_BLOG_AUTO_LOAD} loadmore{/if} {if $blog_latest}ybc-page-latest{elseif $blog_category}ybc-page-category{elseif $blog_tag}ybc-page-tag{elseif $blog_search}ybc-page-search{elseif $author}ybc-page-author{else}ybc-page-home{/if}">
        {if $is_main_page}
            {hook h='blogSlidersBlock'}
        {/if}
        {if $blog_category}
            {if isset($blog_category.enabled) && $blog_category.enabled}
                <div class="blog-category {if $blog_category.image}has-blog-image{/if}">
                    {if $blog_category.image}
                        <div class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}">
                            {if $blog_config.YBC_BLOG_CATEGORY_ENABLE_POST_SLIDESHOW}
                                <a href="{$blog_dir|escape:'html':'UTF-8'}views/img/category/{$blog_category.image|escape:'html':'UTF-8'}" class="prettyPhoto">
                            {/if}        
                                    <img src="{$blog_dir|escape:'html':'UTF-8'}views/img/category/{$blog_category.image|escape:'html':'UTF-8'}" alt="{$blog_category.title|escape:'html':'UTF-8'}" title="{$blog_category.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$blog_dir|escape:'html':'UTF-8'}views/img/category/{$blog_category.image|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                              {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_CATEGORY_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_CATEGORY_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                             </svg>
                             {/if}
                             {if $blog_config.YBC_BLOG_CATEGORY_ENABLE_POST_SLIDESHOW}
                                </a>
                             {/if}
                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                <div class="loader_lady_custom"></div>
                            {/if}
                        </div>
                    {/if}
                    <h1 class="page-heading product-listing">{$blog_category.title|escape:'html':'UTF-8'}</h1>            
                    {if $blog_category.description}
                        <div class="blog-category-desc">
                            {$blog_category.description|escape nofilter}
                        </div>
                    {/if}
                </div>
            {else}
                <p class="alert alert-warning">{l s='This category is not available' mod='ybc_blog'}</p>
            {/if}
        {elseif $blog_latest}
           <h1 class="page-heading product-listing">{l s='Latest posts' mod='ybc_blog'}</h1>
        {elseif $blog_popular}
           <h1 class="page-heading product-listing">{l s='Popular posts' mod='ybc_blog'}</h1>
        {elseif $blog_featured}
           <h1 class="page-heading product-listing">{l s='Featured posts' mod='ybc_blog'}</h1>
        {elseif $blog_tag}
            <h1 class="page-heading product-listing">{l s='Tag: ' mod='ybc_blog'}"{ucfirst($blog_tag)|escape:'html':'UTF-8'}"</h1>
        {elseif $blog_search}
            <h1 class="page-heading product-listing">{l s='Search: ' mod='ybc_blog'}"{ucfirst(str_replace('+',' ',$blog_search))|escape:'html':'UTF-8'}"</h1>
        {elseif $author}
            {if isset($author.disabled) && $author.disabled}
                <p class="alert alert-warning">{l s='This author is not available' mod='ybc_blog'}</p>
            {else}
                {if isset($blog_config.YBC_BLOG_AUTHOR_INFORMATION)&& $blog_config.YBC_BLOG_AUTHOR_INFORMATION}
                    
                    {if isset($author.description)&&$author.description}
                        <div class="ybc-block-author ybc-block-author-avata">
                            {if $author.avata}
                                <div class="avata_img">
                                    <img class="avata" src="{$blog_dir|escape:'html':'UTF-8'}views/img/avata/{$author.avata|escape:'html':'UTF-8'}"/>
                                </div>
                            {/if}
                            <div class="ybc-des-and-author">
                                <div class="ybc-author-name">
                                    <h1 class="page-heading product-listing">
                                        <a href="{$author.author_link|escape:'html':'UTF-8'}">
                                            {l s='Author' mod='ybc_blog'}: {$author.name|escape:'html':'UTF-8'}
                                        </a>
                                    </h1>
                                </div>
                                {if isset($author.description)&&$author.description}
                                    <div class="ybc-author-description">
                                        {$author.description nofilter}
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {else}
                        <div class="ybc-author-name">
                            <h1 class="page-heading product-listing">
                                {l s='Author' mod='ybc_blog'}: {$author.name|escape:'html':'UTF-8'}
                            </h1>
                        </div>
                    {/if}
                {else}
                <h1 class="page-heading product-listing">{l s='Author: ' mod='ybc_blog'}"{$author.name|escape:'html':'UTF-8'}"</h1>
                {/if}
            {/if}
        {elseif $month}
            <h1 class="page-heading product-listing">{l s='Posted in : ' mod='ybc_blog'}"{$month|escape:'html':'UTF-8'}"</h1>
        {elseif $year}
            <h1 class="page-heading product-listing">{l s='Posted in: ' mod='ybc_blog'}"{$year|escape:'html':'UTF-8'}"</h1>
        {/if}
        
        {if !($blog_category && (!isset($blog_category.enabled) || isset($blog_category.enabled) && !$blog_category.enabled)) && ($blog_category || $blog_tag || $blog_search || $author || $is_main_page || $blog_latest || $blog_featured || $blog_popular || $month || $year) && (!$author || ($author && !isset($author.disabled)))}
            {if isset($blog_posts) && $blog_posts}
                <ul class="ybc-blog-list row {if $is_main_page}blog-main-page{/if}">
                    {assign var='first_post' value=true}
                    {foreach from=$blog_posts item='post'}            
                        <li>                         
                            <div class="post-wrapper">
                                {if $is_main_page && $first_post && ($blog_layout == 'large_list' || $blog_layout == 'large_grid')}
                                    {if $post.image}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                                            <img title="{$post.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD) && $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$post.image|escape:'html':'UTF-8'}{/if}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.image|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_BLOG_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_BLOG_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                                             </svg>
                                             {/if}
                                        </a>                              
                                    {elseif $post.thumb}
                                        <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                                            <img title="{$post.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD) && $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$post.thumb|escape:'html':'UTF-8'}{/if}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
                                            {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD}
                                             <svg width="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_WIDTH'|escape:'html':'UTF-8')}" height="{Configuration::get('YBC_BLOG_IMAGE_BLOG_THUMB_HEIGHT'|escape:'html':'UTF-8')}" style="width: 100%;height: auto">
                                             </svg>
                                             {/if}
                                        </a>
                                    {/if}
                                    {assign var='first_post' value=false}
                                {elseif $post.thumb}
                                    <a class="ybc_item_img{if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} ybc_item_img_ladyload{/if}" href="{$post.link|escape:'html':'UTF-8'}">
                                        <img title="{$post.title|escape:'html':'UTF-8'}" src="{if isset($blog_config.YBC_BLOG_LAZY_LOAD) && $blog_config.YBC_BLOG_LAZY_LOAD}{$image_folder|escape:'html':'UTF-8'}bg-grey.png{else}{$post.thumb|escape:'html':'UTF-8'}{/if}" alt="{$post.title|escape:'html':'UTF-8'}" {if isset($blog_config.YBC_BLOG_LAZY_LOAD)&& $blog_config.YBC_BLOG_LAZY_LOAD} data-original="{$post.thumb|escape:'html':'UTF-8'}" class="lazyload"{/if} />
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
                                                                <meta itemprop="itemReviewed" content="{$post.title|escape:'html':'UTF-8'}"/>
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
                <p>{l s='No posts found' mod='ybc_blog'}</p>
            {/if}
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