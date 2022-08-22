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

<div id="price_impact_quantity_block_{$id|escape:'htmlall':'UTF-8'}" class="price_impact_quantity_block" data-id="{$id|escape:'htmlall':'UTF-8'}">
	<hr>
	<div class="form-group">
		<label class="control-label col-lg-4">
			{l s='Multiply the price impact by a quantity?' mod='configurator'}
		</label>
		<div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input onclick="priceImpactHandler.showQuantityForm($(this), true);"
						   type="radio"
						   id="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}_on"
						   name="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}"
						   value="1"
						   {if !empty($option->id_step_impact_qty) || !empty($option->id_step_option_impact_qty)}checked="checked"{/if} />
                    <label for="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                        {l s='Yes' mod='configurator'}
                    </label>
                    <input onclick="priceImpactHandler.showQuantityForm($(this), false);"
						   type="radio"
						   id="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}_off"
						   name="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}"
						   value="0"
						   {if empty($option->id_step_impact_qty) && empty($option->id_step_option_impact_qty)}checked="checked"{/if} />
                    <label for="use_impact_qty_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                        {l s='No' mod='configurator'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
		</div>
	</div>
    <div class="price_impact_quantity_step_block" {if empty($option->id_step_impact_qty) && empty($option->id_step_option_impact_qty)}style="display:none;"{/if}>
		<div class="form-group">
			<label class="control-label col-lg-4">
				<span title=""
					  data-toggle="tooltip"
					  class="label-tooltip"
					  data-original-title="{l s='If you don\'t select options, the quantity used will be the sum of all the options selected in the step.' mod='configurator'}">
					{l s='Choose the quantity step to use' mod='configurator'}
				</span>
			</label>
			<div class="col-lg-4">
				<select id="select_impact_qty_step_{$id|escape:'htmlall':'UTF-8'}" name="select_impact_qty_step_{$id|escape:'htmlall':'UTF-8'}" class='select_impact_qty_step'>
					<option value="">{l s='Choose a step' mod='configurator'}</option>
					{foreach $steps_before as $step_before}
						{if $step_before->use_qty}
							<option value="{$step_before->id|escape:'htmlall':'UTF-8'}"
									{if (int)$option->id_step_impact_qty === (int)$step_before->id}selected='selected'{/if}>
								{$step_before->name|escape:'htmlall':'UTF-8'}
							</option>
						{/if}
					{/foreach}
				</select>
                        </div>
                        <div class="col-lg-4">
				<select id="select_impact_qty_step_option_{$id|escape:'htmlall':'UTF-8'}" name="select_impact_qty_step_option_{$id|escape:'htmlall':'UTF-8'}" class='select_impact_qty_step_option'>
					<option value="0" data-step-id="0">{l s='Choose an option' mod='configurator'}</option>
					{foreach $options_before as $key => $options_temp}
						{foreach $options_temp as $option_before}
							<option data-step-id="{$key|escape:'htmlall':'UTF-8'}"
									value="{$option_before->id|escape:'htmlall':'UTF-8'}"
									{if (int)$option->id_step_option_impact_qty === (int)$option_before->id}selected='selected'{/if}
									{if (int)$option->id_step_impact_qty !== (int)$key}style="display:none;"{/if}>
								{$option_before->option.name|escape:'htmlall':'UTF-8'}
							</option>
						{/foreach}
					{/foreach}
				</select>
			</div>
		</div>
    </div>
</div>