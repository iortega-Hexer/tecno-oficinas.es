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
{if ((isset($blog_config.YBC_BLOG_SHOW_CATEGORIES_BLOCK) && $blog_config.YBC_BLOG_SHOW_CATEGORIES_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_SEARCH_BLOCK) && $blog_config.YBC_BLOG_SHOW_SEARCH_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_LATEST_NEWS_BLOCK) && $blog_config.YBC_BLOG_SHOW_LATEST_NEWS_BLOCK) ||(isset($blog_config.YBC_BLOG_SHOW_POPULAR_POST_BLOCK) && $blog_config.YBC_BLOG_SHOW_POPULAR_POST_BLOCK) || (isset($blog_config.YBC_BLOG_SHOW_FEATURED_BLOCK) && $blog_config.YBC_BLOG_SHOW_FEATURED_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_TAGS_BLOCK) && $blog_config.YBC_BLOG_SHOW_TAGS_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_GALLERY_BLOCK) && $blog_config.YBC_BLOG_SHOW_GALLERY_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_ARCHIVES_BLOCK) && $blog_config.YBC_BLOG_SHOW_ARCHIVES_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_COMMENT_BLOCK) && $blog_config.YBC_BLOG_SHOW_COMMENT_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_AUTHOR_BLOCK) && $blog_config.YBC_BLOG_SHOW_AUTHOR_BLOCK)||(isset($blog_config.YBC_BLOG_ENABLE_RSS) && $blog_config.YBC_BLOG_ENABLE_RSS && isset($blog_config.YBC_BLOC_RSS_DISPLAY) && $blog_config.YBC_BLOC_RSS_DISPLAY && in_array('side_bar',$blog_config.YBC_BLOC_RSS_DISPLAY))) && !$blog_config.YBC_BLOG_SIDEBAR_ON_MOBILE }
<div class="ybc-navigation-blog">{if $blog_config.YBC_BLOG_NAVIGATION_TITLE}{$blog_config.YBC_BLOG_NAVIGATION_TITLE|escape:'html':'UTF-8'}{else}{l s='Blog navigation' mod='ybc_blog'}{/if}</div>
<div class="ybc-navigation-blog-content">
{/if}
{foreach from=$sidebars_postion item='position'}
    {$sidebars.$position nofilter}
{/foreach}
{if ((isset($blog_config.YBC_BLOG_SHOW_CATEGORIES_BLOCK) && $blog_config.YBC_BLOG_SHOW_CATEGORIES_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_SEARCH_BLOCK) && $blog_config.YBC_BLOG_SHOW_SEARCH_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_LATEST_NEWS_BLOCK) && $blog_config.YBC_BLOG_SHOW_LATEST_NEWS_BLOCK) ||(isset($blog_config.YBC_BLOG_SHOW_POPULAR_POST_BLOCK) && $blog_config.YBC_BLOG_SHOW_POPULAR_POST_BLOCK) || (isset($blog_config.YBC_BLOG_SHOW_FEATURED_BLOCK) && $blog_config.YBC_BLOG_SHOW_FEATURED_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_TAGS_BLOCK) && $blog_config.YBC_BLOG_SHOW_TAGS_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_GALLERY_BLOCK) && $blog_config.YBC_BLOG_SHOW_GALLERY_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_ARCHIVES_BLOCK) && $blog_config.YBC_BLOG_SHOW_ARCHIVES_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_COMMENT_BLOCK) && $blog_config.YBC_BLOG_SHOW_COMMENT_BLOCK)||(isset($blog_config.YBC_BLOG_SHOW_AUTHOR_BLOCK) && $blog_config.YBC_BLOG_SHOW_AUTHOR_BLOCK)||(isset($blog_config.YBC_BLOG_ENABLE_RSS) && $blog_config.YBC_BLOG_ENABLE_RSS && isset($blog_config.YBC_BLOC_RSS_DISPLAY) && $blog_config.YBC_BLOC_RSS_DISPLAY && in_array('side_bar',$blog_config.YBC_BLOC_RSS_DISPLAY))) && !$blog_config.YBC_BLOG_SIDEBAR_ON_MOBILE }
</div>
{/if}