{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author DMConcept <support@dmconcept.fr>
*  @copyright 2015 DMConcept
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.name == 'id_product'}
        <div id="ajax_choose_product">
            <div class="input-group">
                <input id="product_autocomplete_input" class="ac_input" type="text" name="product_autocomplete_input">
                <span class="input-group-addon"><i class="icon-search"></i></span>
            </div>
        </div>
        <div class="hidden">
            {$smarty.block.parent}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="script"}
    {$smarty.block.parent}
    $('#product_autocomplete_input')
	.autocomplete('ajax_products_list.php', {
	minChars: 1,
	autoFill: true,
	max:20,
	matchContains: true,
	mustMatch:false,
	scroll:false,
	cacheLength:0,
	formatItem: function(item) {
	return item[1]+' - '+item[0];
	}
	}).result(function(event, data, formatted){
	var productId = data[1];
	$('#id_product').val(parseInt(productId));
	});
	$('#product_autocomplete_input').setOptions({
	extraParams: {
	excludeIds : function(){
	if ($('#id_product').val() === '')
	return 9999999;
	return $('#id_product').val();
	}
	}
	});
{/block}