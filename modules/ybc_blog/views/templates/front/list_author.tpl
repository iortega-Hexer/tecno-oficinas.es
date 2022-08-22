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
        <div class="ybc_blog_layout_{$blog_layout|escape:'html':'UTF-8'} ybc-blog-wrapper ybc-blog-wrapper-blog-list{if isset($blog_config.YBC_BLOG_AUTO_LOAD) &&$blog_config.YBC_BLOG_AUTO_LOAD} loadmore{/if} ybc-page-auhors">
            {if $authors}
                {if !isset($date_format) || isset($date_format) && !$date_format}{assign var='date_format' value='F jS Y'}{/if}
                <div class="page_blog block ybc_block_author {$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'} page_author">
                    <h4 class="title_blog title_block">{l s='Authors' mod='ybc_blog'}</h4>
                    {assign var='product_row' value=$blog_config.YBC_BLOG_HOME_PER_ROW|intval}
                    <ul class="block_content ybc-blog-list">
                        {foreach from=$authors item='author'}
                            <li> 
                                <div class="ybc-blog-comment-content"> 
                                    {if $author.avata}
                                        <div class="author_avata_show">
                                            <img class="author_avata" src="{$author.avata|escape:'html':'UTF-8'}" />
                                        </div>
                                    {/if}
                                    <div class="author_infor">
                                        <a class="ybc_title_block" href="{$author.link|escape:'html':'UTF-8'}">{$author.information.name|escape:'html':'UTF-8'} ({$author.posts|@count|intval} {if count($author.posts)>1}{l s='posts' mod='ybc_blog'}{else}{l s='post' mod='ybc_blog'}{/if})</a> 
                                        <div class="ybc_author_desc">
                                            {$author.information.description nofilter}
                                        </div>
                                        <a class="view_post" href="{$author.link|escape:'html':'UTF-8'}">
                                            {if count($author.posts)>1}
                                                {l s='View posts' mod='ybc_blog'}
                                            {else}
                                                {l s='View post' mod='ybc_blog'}
                                            {/if}
                                        </a>
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
                </div>
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