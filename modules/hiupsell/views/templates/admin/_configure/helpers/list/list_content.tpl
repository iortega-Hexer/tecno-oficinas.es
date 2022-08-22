{**
* 2013 - 2017 HiPresta
*
* MODULE Upsell
*
* @author    HiPresta <suren.mikaelyan@gmail.com>
* @copyright HiPresta 2017
* @license   Addons PrestaShop license limitation
* @link      http://www.hipresta.com
*
* NOTICE OF LICENSE
*
* Don't use this module on several shops. The license provided by PrestaShop Addons
* for all its modules is valid only once for a single shop.
*}

{extends file="helpers/list/list_content.tpl"}
{block name="td_content"}
	{if $key == 'active'}
		<a data-id = {$tr.id_block|escape:'htmlall':'UTF-8'} data-status = {$tr.active|escape:'htmlall':'UTF-8'} class="status btn {if $tr.active == '0'}btn-danger{else}btn-success{/if}" 
		href="#" title="{if $tr.active == '0'}{l s='Disabled' mod='hiupsell'}{else}{l s='Enabled' mod='hiupsell'}{/if}">
			<i class="{if $tr.active == '0'}icon-remove {else}icon-check{/if}"></i>
		</a>
	{elseif $key == 'type'}
		{if $tr.products_type == 'products'}
			{l s='Custom Products' mod='hiupsell'}
		{elseif $tr.products_type == 'accessories'}
			{l s='Product Accessories' mod='hiupsell'}
		{elseif $tr.products_type == 'cross_sells'}
			{l s='Cross sells' mod='hiupsell'}
		{elseif $tr.products_type == 'selected_product'}
			{l s='Selected Products' mod='hiupsell'}
		{/if}
	{elseif $key == 'custom_hook'}
		{literal}{{/literal}hook h="hiupsell" id="{$tr['id_block']|escape:'htmlall':'UTF-8'}"{literal}}{/literal}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}




