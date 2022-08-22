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
{if $blockCategTree}
    <div class="block_rss ybc_block_categories {$blog_config.YBC_BLOG_RTL_CLASS|escape:'html':'UTF-8'}">
        <h5 class="title_blog title_block">{l s='Blog categories' mod='ybc_blog'}</h5>    
        <div class="content_block">
            <ul class="rss-categories">
                {foreach from=$blockCategTree[0].children item=child name=blockCategTree}
        			{if $smarty.foreach.blockCategTree.last}
        				{include file="$branche_tpl_path" node=$child last='true'}
        			{else}
        				{include file="$branche_tpl_path" node=$child}
        			{/if}
        		{/foreach}
            </ul>
        </div>    
    </div>
{/if}