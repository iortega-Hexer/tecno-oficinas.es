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
<li class="category_{$node.id_category|intval}{if isset($last) && $last == 'true'} last{/if}">
	<a href="{$node.link_rss|escape:'html':'UTF-8'}">{$node.title|escape:'html':'UTF-8'}<i class="fa fa-rss"></i></a>
	{if $node.children|@count > 0}
        <span class="category-blog-parent">click</span>
		<ul class="rss-children">
    		{foreach from=$node.children item=child name=categoryTreeBranch}
    			{if $smarty.foreach.categoryTreeBranch.last}
    				{include file="$branche_tpl_path" node=$child last='true'}
    			{else}
    				{include file="$branche_tpl_path" node=$child last='false'}
    			{/if}
    		{/foreach}
		</ul>
	{/if}
</li>