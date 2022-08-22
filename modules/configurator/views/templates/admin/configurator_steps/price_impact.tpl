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

{capture assign=floatPrecision}{'%.'|cat:$smarty.const._PS_PRICE_DISPLAY_PRECISION_|cat:'f'|escape:'htmlall':'UTF-8'}{/capture}

{assign var=type value=$option->impact_type}
{assign var=value value=$option->impact_value}

{assign var=impact_multiple_step_id value=$option->impact_multiple_step_id}
{assign var=impact_multiple_step_id value=","|explode:$impact_multiple_step_id}

<div id="price_impact_{$option->id|escape:'htmlall':'UTF-8'}" class="price_impact">
    <div class="form-group">
        <label class='col-lg-12'>{l s='Price impact for :' mod='configurator'} {$option->option.name|escape:'htmlall':'UTF-8'}</label>
    </div>
    <hr />
    <input id="impact_{$id|escape:'htmlall':'UTF-8'}" name="impact_{$id|escape:'htmlall':'UTF-8'}" type="hidden" class="form_value" value="{$type|escape:'htmlall':'UTF-8'},{$value|escape:'htmlall':'UTF-8'}">
    
    {* Principal form of choice of type of impact *}
    <div class="form-group">
        <label class="control-label col-lg-4 required" for="select_impact_{$id|escape:'htmlall':'UTF-8'}">{l s='Calculation method' mod='configurator'}</label>
        <div class="col-lg-8">
            <select id="select_impact_{$id|escape:'htmlall':'UTF-8'}" class='select_impact chosen'  {if $configurator_step->type=="products"}disabled{/if}>
                {foreach $impact_types as $impact_type => $impact_name}
                    <option value="{$impact_type|escape:'htmlall':'UTF-8'}" {if $type eq $impact_type}selected='selected'{/if}>{$impact_name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
	{* X/Y Option *}
	<div class="area-xy">
        <div class="form-group">
            <label class="control-label col-lg-4">
                {l s='Use X/Y values from different steps ?' mod='configurator'}
            </label>
            <div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input onclick="priceImpactHandler.showXYForm($(this), true);" 
                           type="radio" 
                           id="use_step_option_{$id|escape:'htmlall':'UTF-8'}_on"
                           name="use_step_option_{$id|escape:'htmlall':'UTF-8'}"
                           value="1" 
                           {if !empty($option->id_impact_step_option_x) && !empty($option->id_impact_step_option_y)}checked="checked"{/if} />
                    <label for="use_step_option_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                        {l s='Yes' mod='configurator'}
                    </label>
                    <input onclick="priceImpactHandler.showXYForm($(this), false);" 
                           type="radio" 
                           id="use_step_option_{$id|escape:'htmlall':'UTF-8'}_off"
                           name="use_step_option_{$id|escape:'htmlall':'UTF-8'}"
                           value="0" 
                           {if empty($option->id_impact_step_option_x) && empty($option->id_impact_step_option_y)}checked="checked"{/if} />
                    <label for="use_step_option_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                        {l s='No' mod='configurator'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>
        <div class="value-area-xy" {if empty($option->id_impact_step_option_x) && empty($option->id_impact_step_option_y)}style="display:none;"{/if}>
			<div class="form-group">
				<label class="control-label col-lg-2">
					{l s='Option X' mod='configurator'}
				</label>
				<div class="col-lg-5">
					<select id="select_step_x_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" name="select_step_x_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" class='select_step_impact_step_option'>
                        <option value="">{l s='Choose a step' mod='configurator'}</option>
                        {foreach $steps_before as $step_before}
							<option value="{$step_before->id|escape:'htmlall':'UTF-8'}"
									{if isset($matching_step_option[$option->id_impact_step_option_x]) && $matching_step_option[$option->id_impact_step_option_x]==$step_before->id}selected='selected'{/if}>
								{$step_before->name|escape:'htmlall':'UTF-8'}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-5">
					<select id="select_option_x_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" name="select_option_x_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" class='select_option_impact_step_option'>
                        <option value="">{l s='Choose an option' mod='configurator'}</option>
                        {foreach $options_before as $key => $options_temp}
							{foreach $options_temp as $option_before}
								<option data-step-id="{$key|escape:'htmlall':'UTF-8'}" 
										value="{$option_before->id|escape:'htmlall':'UTF-8'}"
										{if $option->id_impact_step_option_x == $option_before->id}selected='selected'{/if}
										{if $option->id_impact_step_option_x != $key}style="display:none;"{/if}>
									{$option_before->option.name|escape:'htmlall':'UTF-8'}
								</option>
							{/foreach}
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-lg-2">
					{l s='Option Y' mod='configurator'}
				</label>
				<div class="col-lg-5">
					<select id="select_step_y_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" name="select_step_y_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" class='select_step_impact_step_option'>
                        <option value="">{l s='Choose a step' mod='configurator'}</option>
                        {foreach $steps_before as $step_before}
							<option value="{$step_before->id|escape:'htmlall':'UTF-8'}"
									{if isset($matching_step_option[$option->id_impact_step_option_y]) && $matching_step_option[$option->id_impact_step_option_y]==$step_before->id}selected='selected'{/if}>
								{$step_before->name|escape:'htmlall':'UTF-8'}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-5">
					<select id="select_option_y_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" name="select_option_y_impact_step_option_{$id|escape:'htmlall':'UTF-8'}" class='select_option_impact_step_option'>
                        <option value="">{l s='Choose an option' mod='configurator'}</option>
                        {foreach $options_before as $key => $options_temp}
							{foreach $options_temp as $option_before}
								<option data-step-id="{$key|escape:'htmlall':'UTF-8'}" 
										value="{$option_before->id|escape:'htmlall':'UTF-8'}"
										{if $option->id_impact_step_option_y == $option_before->id}selected='selected'{/if}
										{if $option->id_impact_step_option_y != $key}style="display:none;"{/if}>
									{$option_before->option.name|escape:'htmlall':'UTF-8'}
								</option>
							{/foreach}
						{/foreach}
					</select>
				</div>
			</div>
        </div>
    </div>
	
    {* Impact with steps: SINGLE STEP SLECTION WITH AREA STEP *}
    <div class="form-group" style='display:none;'>
        <label class="control-label col-lg-4" for="select_step_impact_{$id|escape:'htmlall':'UTF-8'}">
            <span title="" 
                  data-toggle="tooltip" 
                  class="label-tooltip" 
                  data-original-title="{l s='This calculation method need you to choose a step using pricelist.' mod='configurator'}">
                {l s='Step to use' mod='configurator'}
            </span>
        </label>
        <div class="col-lg-8">
            <select id="select_step_impact_{$id|escape:'htmlall':'UTF-8'}" name="select_step_impact_{$id|escape:'htmlall':'UTF-8'}" class="select_step_impact chosen">
                <option value="">{l s='Choose a step' mod='configurator'}</option>
                {foreach $pricelist_steps_withsuffix as $step}
                    <option
                        data-suffix="{$step->input_suffix|escape:'htmlall':'UTF-8'}"
                        value="{$step->id|escape:'htmlall':'UTF-8'}"
                        {if $step->id eq $option->impact_step_id}selected='selected'{/if}
                        data-step-type="{if $step->use_qty}quantity{/if}"
                    >
                        {$step->name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    {* Impact with steps: MULTIPLE STEP SELECTION WITH AREA STEP *}
    <div class="form-group" style='display:none;'>
        <label class="control-label col-lg-4" for="select_step_impact_multiple_{$id|escape:'htmlall':'UTF-8'}">
            <span title="" 
                  data-toggle="tooltip" 
                  class="label-tooltip" 
                  data-original-title="{l s='This calculation method need you to choose a step using pricelist.' mod='configurator'}">
                {l s='Step to use' mod='configurator'}
            </span>
        </label>
        <div class="col-lg-8">
            <select id="select_step_impact_multiple_{$id|escape:'htmlall':'UTF-8'}" name="select_step_impact_multiple_{$id|escape:'htmlall':'UTF-8'}[]" class='select_step_impact_multiple chosen' multiple="multiple">
                {foreach $pricelist_steps_withsuffix as $step}
                    <option data-suffix="{$step->input_suffix|escape:'htmlall':'UTF-8'}" 
                            value="{$step->id|escape:'htmlall':'UTF-8'}" 
                            {if $step->id|in_array:$impact_multiple_step_id}selected='selected'{/if}>
                        {$step->name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
    
    {* Impact with steps: SINGLE STEP SELECTION WITH AREA BUT ONLY ONE DIMENSION *}
    <div class="form-group" style='display:none;'>
        <label class="control-label col-lg-4" for="select_step_impact_singleinput_{$id|escape:'htmlall':'UTF-8'}">
            <span title="" 
                  data-toggle="tooltip" 
                  class="label-tooltip" 
                  data-original-title="{l s='This calculation method need you to choose a step which use only one input textfield.' mod='configurator'}">
                {l s='Step to use' mod='configurator'}
            </span>
        </label>
        <div class="col-lg-8">
            <select id="select_step_impact_{$id|escape:'htmlall':'UTF-8'}" name="select_step_impact_singleinput_{$id|escape:'htmlall':'UTF-8'}" class='select_step_impact_singleinput chosen'>
                <option value="">{l s='Choose a step' mod='configurator'}</option>
                {foreach $pricelist_steps_singleinput as $step}
                    <option value="{$step->id|escape:'htmlall':'UTF-8'}" 
                            {if $step->id eq $option->impact_step_id}selected='selected'{/if}>
                        {$step->name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
            
    {* Impact with steps: SINGLE STEP SELECTION WITH ALL STEP *}
    <div class="form-group" style='display:none;'>
        <label class="control-label col-lg-4" for="select_step_impact_all_{$id|escape:'htmlall':'UTF-8'}">
            <span title="" 
                  data-toggle="tooltip" 
                  class="label-tooltip" 
                  data-original-title="{l s='This calculation method need you to choose a step. To have result please choose a step that have a price' mod='configurator'}">
                {l s='Step to use' mod='configurator'}
            </span>
        </label>
        <div class="col-lg-8">
            <select id="select_step_impact_all_{$id|escape:'htmlall':'UTF-8'}" name="select_step_impact_all_{$id|escape:'htmlall':'UTF-8'}" class="select_step_impact_all chosen">
                <option value="">{l s='Choose a step' mod='configurator'}</option>
                {foreach $steps_all as $step}
                    <option value="{$step->id|escape:'htmlall':'UTF-8'}" 
                            {if $step->id eq $option->impact_step_id}selected='selected'{/if}>
                        {$step->name|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>
            
   {* Impact with steps: INFORMATIONS ABOUT SELECTION *}
    <div class="form-group" style='display:none;'>
        <div class="alert alert-warning alert-area" style="display:none;">
            {l s='Previous step\'s price list\'s suffix should use distance measuring units to be correct when displayed.' mod='configurator'}
        </div>
        <div class="alert alert-info info-multiplier" style="display:none;">
            {l s='This calculation method will take the value written by the customer and multiply it by the value. The result will be the new amount added to the configuration.' mod='configurator'}
        </div>
        <label class="control-label col-lg-4 required">{l s='Value' mod='configurator'}</label>
        <div class="col-lg-8">
            <div class="input-group">
                <span class="input-group-addon">
                    <span class="devise" style='display:none;'>
                        {$currency->sign|escape:'htmlall':'UTF-8'}
                    </span>
                    <span class="percent">
                        %
                    </span>
                    <span class="multiplier" style='display:none;'>
                        x
                    </span>
                </span>
                <input id="value_{$id|escape:'htmlall':'UTF-8'}" type="text" class="form-control impact_value" value="{$value|escape:'htmlall':'UTF-8'}" onchange="this.value = this.value.replace(/,/g, '.');" {if $configurator_step->type=="products"}disabled{/if}>
                <span class="input-group-addon input-group-after" {if $option and $option->impact_type neq 'area'}style='display:none;'{/if}>
                    /&nbsp;<span class="suffix"></span>²
                </span>
            </div>
        </div>
    </div>
    
    {* Impact with steps: AREA INFORMATIONS *}
    <div class="area-extension">
        <div class="form-group">
            <label class="control-label col-lg-4">
                {l s='Value displayed differently' mod='configurator'}
            </label>
            <div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input onclick="priceImpactHandler.showUnityForm($(this), true);" 
                           type="radio" 
                           id="display_differently_{$id|escape:'htmlall':'UTF-8'}_on" 
                           name="display_differently_{$id|escape:'htmlall':'UTF-8'}"
                           value="1" 
                           {if !empty($option->unity)}checked="checked"{/if} />
                    <label for="display_differently_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                        {l s='Yes' mod='configurator'}
                    </label>
                    <input onclick="priceImpactHandler.showUnityForm($(this), false);" 
                           type="radio" 
                           id="display_differently_{$id|escape:'htmlall':'UTF-8'}_off" 
                           name="display_differently_{$id|escape:'htmlall':'UTF-8'}"
                           value="0" 
                           {if empty($option->unity)}checked="checked"{/if} />
                    <label for="display_differently_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                        {l s='No' mod='configurator'}
                    </label>
                    <a class="slide-button btn"></a>
                </span>
            </div>
        </div>

        <div class="value-displayed-options" {if empty($option->unity)}style="display:none;"{/if}>
            <div class="form-group">
                <label class="control-label col-lg-6" for="unity_{$id|escape:'htmlall':'UTF-8'}">
                    {l s='Unity' mod='configurator'}
                </label>
                <div class="col-lg-6">
                    <input value='{$option->unity|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="unity_{$id|escape:'htmlall':'UTF-8'}" name="unity_{$id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-6" for="conversion_factor_{$id|escape:'htmlall':'UTF-8'}">
                    {l s='Conversion Factor' mod='configurator'}
                </label>
                <div class="col-lg-6">
                    <input value='{$option->conversion_factor|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="conversion_factor_{$id|escape:'htmlall':'UTF-8'}" name="conversion_factor_{$id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
        </div>
    </div>

    {* IMPORT PRICELIST AREA *}
    <div class="form-group" {if $option and $option->impact_type neq 'pricelist'}style='display:none;'{/if}>
        <div class="alert alert-warning">
            {l s='Your price list need to have the same dimension (only one row/multiple columns or multiple rows/multiple columns) and the same header values than price list of the previous step to work properly.' mod='configurator'}
        </div>
        <label class="control-label col-lg-4" for="">
            {l s='Import price list' mod='configurator'}
        </label>
        <div class="col-lg-8">
            <input type="hidden" id="price_list_{$id|escape:'htmlall':'UTF-8'}" name="price_list_{$id|escape:'htmlall':'UTF-8'}" value='{$option->price_list|escape:'htmlall':'UTF-8'}' class='pricelist_input' />
            <div class="input-group">
                <span class="input-group-addon"><i class="icon-file"></i></span>
                <input id="price_list_file_{$id|escape:'htmlall':'UTF-8'}" type="file" name="pricelist_file_{$id|escape:'htmlall':'UTF-8'}" />
            </div>
            <p class="help-block">
                {l s='Format:' mod='configurator'} .csv, .txt 
            </p>
            {if $option->price_list neq ''}
                <p>
                    <a onclick="$('#price_list_{$id|escape:'htmlall':'UTF-8'}').val('');
                                    $(this).remove();" href='javascript:void(0);'><i class='icon-remove-sign'></i> {l s='Delete price list' mod='configurator'}</a>
                </p>
            {/if}
        </div>
    </div>

    {* IMPACT VALUE PERIOD *}
    {include
        file="./price_impact_period.tpl"
        option=$option
    }

    {* IMPACT VALUE WITH QUANTITY *}
    {include
        file="./price_impact_quantity.tpl"
        option=$option
    }
		
	{hook h="configuratorAdminDisplayStepsControllerPriceImpact" configuratorStepOption=$option id=$id}
	
</div>