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

{if !empty($upsell_products)}
    {foreach from=$upsell_products item=products_info name=info_products}
        {if !empty($products_info['products'])}
        	{if $products_info.block_layout == 'list'}
        		{if $psv < 1.7}
            		{include file="{$module_tpl_dir}/list.tpl" all_products=$products_info['products']}
        		{else}
        			{include file="{$module_tpl_dir}/list1.7.tpl" all_products=$products_info['products']}
        		{/if}
            {else}
            	{if $psv < 1.7}
            		{include file="{$module_tpl_dir}/grid.tpl" all_products=$products_info['products']}
        		{else}
        			{include file="{$module_tpl_dir}/grid1.7.tpl" all_products=$products_info['products']}
        		{/if}
			{/if}
        {/if}
    {/foreach}
{/if}
