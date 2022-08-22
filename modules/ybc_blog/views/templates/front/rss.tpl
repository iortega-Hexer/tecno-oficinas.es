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
{/block}
{block name="content_wrapper"}
{if Configuration::get('YBC_BLOG_ENABLE_RSS')}
    <div id="content-wrapper">
      <div id="content">
      <div class="ybc_blog_layout_list ybc-blog-wrapper-rss col-xs-12 col-sm-12">
          {block name="content"}
            <div class=" ybc-blog-wrapper ybc-page-rss">
                {if in_array('latest_posts',$YBC_BLOC_RSS_TYPE) ||in_array('popular_posts',$YBC_BLOC_RSS_TYPE) || in_array('featured_posts',$YBC_BLOC_RSS_TYPE) }
                    <h5 class="title_blog title_block">{l s='Rss feed' mod='ybc_blog'}</h5>
                    <ul class="ybc_block_categories">
                        {if in_array('latest_posts',$YBC_BLOC_RSS_TYPE)}
                            <li class="block_rss block_latest">
                                <a href="{$link_latest_posts|escape:'html':'UTF-8'}">{l s='Latest posts' mod='ybc_blog'}<i class="fa fa-rss"></i></a>
                            </li>
                        {/if}
                        {if in_array('popular_posts',$YBC_BLOC_RSS_TYPE)}
                            <li class="block_rss block_popular">
                                <a href="{$link_popular_posts|escape:'html':'UTF-8'}">{l s='Popular posts' mod='ybc_blog'}<i class="fa fa-rss"></i></a>
                            </li>
                        {/if}
                        {if in_array('featured_posts',$YBC_BLOC_RSS_TYPE)}
                            <li class="block_rss block_featured"> 
                                <a href="{$link_featured_posts|escape:'html':'UTF-8'}">{l s='Featured posts' mod='ybc_blog'}<i class="fa fa-rss"></i></a>
                            </li>
                        {/if}
                    </ul>
                {/if}
                {if in_array('category',$YBC_BLOC_RSS_TYPE)}
                    {hook h='blogRssCategory'}
                {/if}
                {if in_array('authors',$YBC_BLOC_RSS_TYPE)}
                    {hook h='blogRssAuthor'}
                {/if}
            </div>                
          {/block}
      </div>
      </div>
    </div>
{else}
    {l s='Rss is disabled' mod='ybc_blog'}
{/if}
{/block}
{block name="right_column"}
{/block}
