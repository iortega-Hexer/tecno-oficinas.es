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

{if !isset($priceDisplayPrecision)}
	{assign var='priceDisplayPrecision' value=2}
{/if}
{if !$priceDisplay || $priceDisplay == 2}
	{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, $priceDisplayPrecision)}
	{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL, $priceDisplayPrecision)}
{elseif $priceDisplay == 1}
	{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, $priceDisplayPrecision)}
	{assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL, $priceDisplayPrecision)}
{/if}

<div id="configurator_preview">
<div id="configurator_preview_buttons" class="box">
    <div class="page-subheading">{l s='Your configuration:' mod='configurator'}</div>
	
	
	{* PROGRESS BAR *}
	{if $DISPLAY_PROGRESS}
	<div id="configurator-progress"><strong></strong></div>
	<hr />
	{/if}
	
	{* PREVIEW PRICES *}
    {if $priceDisplay >= 0 && $priceDisplay <= 2}
		{if $productPrice-$productPriceWithoutReduction neq 0}
		<dl class="dl-horizontal">
			<dt>
			{l s='Base price' mod='configurator'}
			{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
				({if $priceDisplay == 1}{l s='tax excl.' mod='configurator'}{else}{l s='tax incl.' mod='configurator'}{/if})
			{/if}
			</dt>
			<dd id="old_price">{convertPrice price=$productPriceWithoutReduction}</dd>
			{if ($displayReduction !== null)}
				<dt></dt>
				<dd><span class="badge badge-reduction">{$displayReduction}</span></dd>
			{else}
				<dt class="advantage">{l s='After reducing your advantage' mod='configurator'}</dt>
				<dd class="advantage">{convertPrice price=($productPrice-$productPriceWithoutReduction)}</dd>
			{/if}
		</dl>

		<hr />
		{/if}

		{if $configuratorDisplayPrice === 'both' || $configuratorDisplayPrice === 'tax_excl_only' || ($configuratorDisplayPrice !== 'tax_incl_only' && $priceDisplay === 1)}
			<dl id="dl-final-price-tax-excl" class="dl-horizontal dl-final-price">
				<dt>
					{l s='Final price' mod='configurator'}
					{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
						({l s='tax excl.' mod='configurator'})
					{/if}
				</dt>
				<dd id="final_price">{convertPrice price=$productPriceTaxExcl}</dd>
			</dl>
		{/if}
		{if $configuratorDisplayPrice === 'both' || $configuratorDisplayPrice === 'tax_incl_only' || ($configuratorDisplayPrice !== 'tax_excl_only' && $priceDisplay !== 1)}
			<dl id="dl-final-price-tax-incl" class="dl-horizontal dl-final-price">
				<dt>
					{l s='Final price' mod='configurator'}
					{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
						({l s='tax incl.' mod='configurator'})
					{/if}
				</dt>
				<dd id="final_price">{convertPrice price=$productPriceTaxIncl}</dd>
			</dl>
		{/if}
    {/if}

    {* PREVIEW CONTENT *}
    <div class="list-group">
        {foreach $cartDetail as $step}
            {assign var=display value=false}
            {foreach $step.options as $option}
                {if $option.selected || !empty($option.value)}
                    {assign var=display value=true}
                {/if}
            {/foreach}
            {if $step.displayed_in_preview && $display}
                <a href="#step_{$step.id|escape:'htmlall':'UTF-8'}" class="list-group-item">
                    {if $display_name}
                    <strong>{$step.name|escape:'html':'UTF-8'} : <br /></strong>
                    {/if}
                    {assign var=k value=0}
                    {foreach $step.options as $option}{if !empty($option.value) || is_numeric($option.value)}<span class="option_value">{if $k > 0}, {/if}
                                                            {$option.name|escape:'html':'UTF-8'} : {$option.value|escape:'html':'UTF-8'}{$step.input_suffix|escape:'htmlall':'UTF-8'}</span>{assign var=k value=$k+1}{elseif $option.selected}<span class="option">{if $k > 0}, {/if}
								{if $step.use_qty}
								{$option.qty|intval}x&nbsp;
                                                                {/if}
                                                                {$option.name|escape:'html':'UTF-8'}</span>{assign var=k value=$k+1}{/if}{/foreach}
                </a>
            {/if}
        {/foreach}
    </div>

    {* PREVIEW CONTENT *}
    <div class="buttons_container">
		<form 
			action="{if $update_cart}{$link->getProductLink($product)|escape:'html':'UTF-8'}?configurator_update={$id_cart_detail}{else}{$link->getProductLink($base_product)|escape:'html':'UTF-8'}{/if}"
			method="POST"
                        id="form_add_configurator_to_cart"
		>
			
			<input type="hidden" name="add" value="1" />
			<div class="form-group" {if $configurator->hide_qty_product eq 1} style="display: none"{/if} >
				<label for="quantity-configurator">{l s='Quantity :' mod='configurator'}</label>
				<input id="quantity-configurator" class="form-control" type="number" name="qty" value="{$qty}" min="1" />
			</div>

			<button  
				type="submit"
				id="add_configurator_to_cart" 
				class="button btn btn-default"
				{if $disable_addtocart_btn && $progress_value < 100}
					disabled
				{/if}
                {if $nbTabsGroup > 1}
                    style="display:none;"
                {/if}>
				{if $update_cart}
					<span id="current_configurator_to_cart">{l s='Update the cart' mod='configurator'}</span>
					<span style="display:none" id="wait_configurator_to_cart">{l s='Update in progress...' mod='configurator'}</span>
				{else}
					<span id="current_configurator_to_cart">{l s='Add to cart' mod='configurator'}</span>
					<span style="display:none" id="wait_configurator_to_cart">{l s='Add in progress...' mod='configurator'}</span>
				{/if}
			</button>
            {if $nbTabsGroup > 1}
                <a id="tab-action-next-preview" class="btn btn-primary btn-lg btn-block">
                    {l s='Next' mod='configurator'}
                    <i class="icon icon-chevron-right"></i>
                </a>
            {/if}
			{hook h='displayConfiguratorFrontCartPreviewQuantity' id_cart_detail=$id_cart_detail}
		</form>
    </div>
    
    <div class="displayConfiguratorFrontCartPreview">
        {hook h='displayConfiguratorFrontCartPreview' id_cart_detail=$id_cart_detail}
    </div>

</div>
</div>