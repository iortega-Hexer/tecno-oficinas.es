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
{if $authors}
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
{/if}