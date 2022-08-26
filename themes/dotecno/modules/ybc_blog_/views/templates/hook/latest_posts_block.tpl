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
    <div class="block ybc_block_latest {*$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'*} {if isset($page) && $page}page_{$page|escape:'html':'UTF-8'}{else}page_blog{/if} {if isset($page) && $page=='home'}{if isset($blog_config.YBC_BLOG_HOME_POST_TYPE) && $blog_config.YBC_BLOG_HOME_POST_TYPE=='default' || count($posts)<=1} ybc_blog_default{else} ybc_block_slider{/if}{else}{if isset($blog_config.YBC_BLOG_SIDEBAR_POST_TYPE) && $blog_config.YBC_BLOG_SIDEBAR_POST_TYPE=='default' || count($posts)<=1} ybc_block_default{else} ybc_block_slider{/if}{/if}">
        <div class="h2 tit_blog title_block">{l s='Our [1]proyects[/1]' d='Shop.Theme.Actions' sprintf=[ '[1]' => '<span>', '[/1]' => '</span>' ]}</div>
        {assign var='product_row' value=$blog_config.YBC_BLOG_HOME_PER_ROW|intval}
        {assign var='orden' value=0}
        <div class="block_content">
            <ul class="owl-rtl-grid {if count($posts)>1}{if isset($page) && $page=='home' && $blog_config.YBC_BLOG_HOME_POST_TYPE!='default'}owl-carousel{elseif (!isset($page)||(isset($page) && $page!='home')) && $blog_config.YBC_BLOG_SIDEBAR_POST_TYPE!='default'}owl-carousel{/if}{/if}">
                {foreach from=$posts item='post'}
                    {assign var='orden' value=$orden+1}
                    <li class="item-grid">
                        {if $post.thumb}
                            <a class="ybc_item_img" href="{$post.link|escape:'html':'UTF-8'}">
                                <img src="{$post.thumb|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" title="{$post.title|escape:'html':'UTF-8'}" />
                            </a>
                        {/if}
                        {if $orden==3}
                          <div class="hover-post">
                            <div class="h3">
                              {l s='Usability' d='Shop.Theme.Actions'}
                            </div>
                          </div>
                        {elseif $orden==4}
                          <div class="hover-post opaco">
                            <div class="h4">
                              {l s='Find out more here' d='Shop.Theme.Actions'}
                            </div>
                            {if $post.short_description}
                                <div class="blog_description">
                                  <p>
                                    {if isset($blog_config.YBC_BLOG_POST_EXCERPT_LENGTH) && (int)$blog_config.YBC_BLOG_POST_EXCERPT_LENGTH>0}
                                        {$post.short_description|strip_tags:'UTF-8'|truncate:(int)$blog_config.YBC_BLOG_POST_EXCERPT_LENGTH:'...'|escape:'html':'UTF-8'}
                                    {else}
                                        {$post.short_description|strip_tags:'UTF-8'|escape:'html':'UTF-8'}
                                    {/if}
                                  </p>
                                </div>
                            {/if}
                            <a class="btn-secondary" href="{$post.link|escape:'html':'UTF-8'}" title="Ver más sobre nuestro trabajo">
                              {l s='View more' d='Shop.Theme.Actions'}
                            </a>
                          </div>
                        {elseif $orden==7}
                          <div class="hover-post">
                            <div class="h3">
                              {l s='Confort' d='Shop.Theme.Actions'}
                            </div>
                          </div>
                        {/if}
                    </li>
                {/foreach}
            </ul>
            {if $blog_config.YBC_BLOG_DISPLAY_BUTTON_ALL_HOMEPAGE || $page!='home'}
                <div class="blog_view_all_button">
                    <a href="{$latest_link|escape:'html':'UTF-8'}" class="view_all_link">{l s='View all latest posts' d='Shop.Theme.Actions'}</a>
                </div>
            {/if}
        </div>
        <div class="clear"></div>
    </div>

{/if}
