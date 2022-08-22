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

<div id="option_settings_{$option->id|escape:'htmlall':'UTF-8'}" class="option_settings" data-step-id="{$option->id_configurator_step|escape:'htmlall':'UTF-8'}">
    <div class="form-group">

        <div class="alert alert-warning col-lg-10 col-lg-offset-2">{l s='Help tooltip is not possible when you use options from a attribute group which use a dropdown list.' mod='configurator'}</div>

        <label class="control-label col-lg-2" for="content_{$id|escape:'htmlall':'UTF-8'}_{$id_lang|escape:'htmlall':'UTF-8'}">
            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display helpfull information to the customer about option' mod='configurator'}">
                {l s='Help tooltip' mod='configurator'}
            </span>
        </label>
        <div class="col-lg-10">
            {include
                file="./helpers/form/textarea_lang.tpl"
                languages=$languages
                input_name='content_'|cat:$id
                class="autoload_rte"
                input_value=$option->content
            }
        </div>
    </div>

    <div class="displayConfiguratorAdminOptionSettings">
        {hook h='displayConfiguratorAdminOptionSettings' configurator_step=$configurator_step option=$option id=$id}
    </div>

    <div class="row">
        <div class="col-xs-12 col-lg-6 {if $configurator_step->type=="products"}hide{/if}">
            <h4>
                <i class="icon-tag"></i> {l s='Reference' mod='configurator'}
            </h4>

            <div class="form-group">
                <label class="control-label col-lg-4" for="reference_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display helpfull information to the customer about option' mod='configurator'}">
                        {l s='Option reference' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <textarea id="reference_{$id|escape:'htmlall':'UTF-8'}" class="form-control formula_editor" name="reference_{$id|escape:'htmlall':'UTF-8'}">{$option->reference|escape:'htmlall':'UTF-8'}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-4" for="reference_position_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display helpfull information to the customer about option' mod='configurator'}">
                        {l s='Reference position' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <select id="reference_position_{$id|escape:'htmlall':'UTF-8'}" class="form-control" name="reference_position_{$id|escape:'htmlall':'UTF-8'}">
                        {for $c=1 to $steps_counter}
                            <option
                                value="{$c|escape:'htmlall':'UTF-8'}"
                                {if $c == $option->reference_position}selected{/if}
                                >
                                {$c|escape:'htmlall':'UTF-8'}
                            </option>
                        {/for}
                    </select>
                </div>
            </div>

            <h4>
                <i class="icon-shopping-cart"></i> {l s='Cart product option' mod='configurator'}
            </h4>

            <div class="form-group">
                <label class="control-label col-lg-4" for="weight_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a weight' mod='configurator'}">
                        {l s='Weight' mod='configurator'}
                    </span>
                </label>
                <div class="input-group col-lg-8">
                    <span class="input-group-addon">
                        {Configuration::get('PS_WEIGHT_UNIT')}
                    </span>
                    <input id="weight_{$id|escape:'htmlall':'UTF-8'}" class="form-control grey formula_editor" type="text" {if $configurator_step->type=="products"}disabled{/if}
                           name="weight_{$id|escape:'htmlall':'UTF-8'}"
                           value="{$option->weight|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-4" for="used_for_dimension_{$id|escape:'htmlall':'UTF-8'}">
                    {l s='Change product dimension' mod='configurator'}
                </label>
                <div class="input-group col-lg-8">
                    <select id="used_for_dimension_{$id|escape:'htmlall':'UTF-8'}" name="used_for_dimension_{$id|escape:'htmlall':'UTF-8'}" class="form-control" {if $configurator_step->type=="products"}disabled{/if}>
                        <option value="">{l s='No change' mod='configurator'}</option>
                        <option value="width" {if $option->used_for_dimension === 'width'}selected{/if}>{l s='Width' mod='configurator'}</option>
                        <option value="height" {if $option->used_for_dimension === 'height'}selected{/if}>{l s='Height' mod='configurator'}</option>
                        <option value="depth" {if $option->used_for_dimension === 'depth'}selected{/if}>{l s='Depth' mod='configurator'}</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-4" for="dimension_coeff_{$id|escape:'htmlall':'UTF-8'}">
                    {l s='Coefficient for the dimension' mod='configurator'}
                </label>
                <div class="input-group col-lg-8">
                    <input id="dimension_coeff_{$id|escape:'htmlall':'UTF-8'}" name="dimension_coeff_{$id|escape:'htmlall':'UTF-8'}" class="form-control" value="{$option->dimension_coeff}">
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-lg-6">
            <h4>
                <i class="icon-wrench"></i> {l s='Behavior' mod='configurator'}
            </h4>
            
            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="default_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a default value and this value will be force. Customer can\'t change this value' mod='configurator'}">
                        {l s='Force value' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);" 
                               type="radio" 
                               name="force_value_{$id|escape:'htmlall':'UTF-8'}" 
                               id="force_value_{$id|escape:'htmlall':'UTF-8'}_on" 
                               value="1" 
                               {if $option->force_value eq 1}checked="checked" {/if}
                               />
                        <label for="force_value_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);
                                            configuratorStepHandler.updateStatePriceListAndInputOptions(false);"  
                               type="radio" 
                               name="force_value_{$id|escape:'htmlall':'UTF-8'}" 
                               id="force_value_{$id|escape:'htmlall':'UTF-8'}_off" 
                               value="0" 
                               {if $option->force_value neq 1}checked="checked" {/if}
                               />
                        <label for="force_value_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            
            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="check_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Check the chekbox' mod='configurator'}">
                        {l s='Cheked' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);" 
                               type="radio" 
                               name="check_value_{$id|escape:'htmlall':'UTF-8'}" 
                               id="check_value_{$id|escape:'htmlall':'UTF-8'}_on" 
                               value="1" 
                               {if $option->check_value eq 1}checked="checked" {/if}
                               />
                        <label for="check_value_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"  
                               type="radio" 
                               name="check_value_{$id|escape:'htmlall':'UTF-8'}" 
                               id="check_value_{$id|escape:'htmlall':'UTF-8'}_off" 
                               value="0" 
                               {if $option->check_value neq 1}checked="checked" {/if}
                               />
                        <label for="check_value_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>            
                        
                        
            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="default_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a default value' mod='configurator'}">
                        {l s='Default value' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <input id="default_value_{$id|escape:'htmlall':'UTF-8'}" class="form-control formula_editor" type="text" name="default_value_{$id|escape:'htmlall':'UTF-8'}" value="{$option->default_value|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group {if $configurator_step->type=="products" || !empty($configurator_step->price_list)}hide{/if}">
                <label class="control-label col-lg-4" for="min_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a minimal value' mod='configurator'}">
                        {l s='Minimal value' mod='configurator'}
                    </span>
                </label>
                {if Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')}
                <div class="col-lg-8">
                    <input id="min_value_{$id|escape:'htmlall':'UTF-8'}" class="form-control formula_editor" type="text" name="min_value_{$id|escape:'htmlall':'UTF-8'}" value="{$option->min_value|escape:'htmlall':'UTF-8'}">
                </div>
                <label class="control-label col-lg-4" for="min_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a minimal value' mod='configurator'}">
                        {l s='Minimal value if null' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <input id="min_value_if_null_{$id|escape:'htmlall':'UTF-8'}" class="form-control" type="text" name="min_value_if_null_{$id|escape:'htmlall':'UTF-8'}" value="{$option->min_value_if_null|escape:'htmlall':'UTF-8'}">
                </div>
                {else}
                <div class="col-lg-8">
                    <input id="min_value_{$id|escape:'htmlall':'UTF-8'}" class="form-control" type="text" name="min_value_{$id|escape:'htmlall':'UTF-8'}" value="{$option->min_value|escape:'htmlall':'UTF-8'}">
                </div>
                {/if}
            </div>

            <div class="form-group {if $configurator_step->type=="products" || !empty($configurator_step->price_list)}hide{/if}">
                <label class="control-label col-lg-4" for="max_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a maximal value' mod='configurator'}">
                        {l s='Maximal value' mod='configurator'}
                    </span>
                </label>
                {if Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')}
                <div class="col-lg-8">
                    <input id="max_value_{$id|escape:'htmlall':'UTF-8'}" class="form-control formula_editor" type="text" name="max_value_{$id|escape:'htmlall':'UTF-8'}" value="{$option->max_value|escape:'htmlall':'UTF-8'}">
                </div>
                <label class="control-label col-lg-4" for="max_value_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a maximal value' mod='configurator'}">
                        {l s='Maximal value if null' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <input id="max_value_if_null_{$id|escape:'htmlall':'UTF-8'}" class="form-control" type="text" name="max_value_if_null_{$id|escape:'htmlall':'UTF-8'}" value="{$option->max_value_if_null|escape:'htmlall':'UTF-8'}">
                </div>
                {else}
                <div class="col-lg-8">
                    <input id="max_value_{$id|escape:'htmlall':'UTF-8'}" class="form-control formula_editor" type="text" name="max_value_{$id|escape:'htmlall':'UTF-8'}" value="{$option->max_value|escape:'htmlall':'UTF-8'}">
                </div>
                {/if}
            </div>

            {if $configurator_step->type=="products"}
                <div class="form-group">
                    <label class="control-label col-lg-4 required" for="select_display_price_calculation_{$id|escape:'htmlall':'UTF-8'}">
                        <span class="label-tooltip" data-toggle="tooltip" title="{l s='Warning when you have a reduction for the configurated product AND products step!' mod='configurator'}">
                            {l s='Display price calculation' mod='configurator'}
                        </span>
                    </label>
                    <div class="col-lg-8">
                        <select id="select_display_price_calculation_{$id|escape:'htmlall':'UTF-8'}" name="display_price_calculation_{$id|escape:'htmlall':'UTF-8'}" class='select_display_price_calculation chosen'>
                            {foreach $price_calculation_types as $price_calculation_type => $price_calculation_name}
                                <option value="{$price_calculation_type|escape:'htmlall':'UTF-8'}" {if $option->display_price_calculation eq $price_calculation_type}selected='selected'{/if}>{$price_calculation_name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-4 required" for="select_price_calculation_{$id|escape:'htmlall':'UTF-8'}">
                        <span class="label-tooltip" data-toggle="tooltip" title="{l s='Warning when you have a reduction for the configurated product AND products step!' mod='configurator'}">
                            {l s='Price calculation' mod='configurator'}
                        </span>
                    </label>
                    <div class="col-lg-8">
                        <select id="select_price_calculation_{$id|escape:'htmlall':'UTF-8'}" name="price_calculation_{$id|escape:'htmlall':'UTF-8'}" class='select_price_calculation chosen'>
                            {foreach $price_calculation_types as $price_calculation_type => $price_calculation_name}
                                <option value="{$price_calculation_type|escape:'htmlall':'UTF-8'}" {if $option->price_calculation eq $price_calculation_type}selected='selected'{/if}>{$price_calculation_name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {else}
                <input type="hidden" name="display_price_calculation_{$id|escape:'htmlall':'UTF-8'}" value="{$price_calculation_default}">
                <input type="hidden" name="price_calculation_{$id|escape:'htmlall':'UTF-8'}" value="{$price_calculation_default}">
            {/if}
            
            <div class="form-group">
                <label class="control-label col-lg-4" for="qty_coeff_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a qty coefficient. It will be not visible but it will multiplicate the current qty for the calculation of the max / min qty of the step' mod='configurator'}">
                        {l s='Qty coeff.' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <input id="qty_coeff_{$id|escape:'htmlall':'UTF-8'}" class="form-control" type="text" name="qty_coeff_{$id|escape:'htmlall':'UTF-8'}" value="{$option->qty_coeff|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group" id="default_qty_group">
                <label class="control-label col-lg-4">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a default quantity. The minimum value is 2. Value to 0 means unlimited quantities.' mod='configurator'}">
                        {l s='Default total quantity' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-7">
                    <input value='{$option->default_qty|escape:'htmlall':'UTF-8'}' class="form-control formula_editor" type="text" id="default_qty" name="default_qty_{$id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
            
            <h4>
                <i class="icon-eye-open"></i> {l s='Display option' mod='configurator'}
            </h4>

            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="slider_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Slider' mod='configurator'}">
                        {l s='Activate a slider' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);" 
                               type="radio" 
                               name="slider_{$id|escape:'htmlall':'UTF-8'}" 
                               id="slider_{$id|escape:'htmlall':'UTF-8'}_on" 
                               value="1" 
                               {if $option->slider eq 1}checked="checked" {/if}
                               />
                        <label for="slider_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"  
                               type="radio" 
                               name="slider_{$id|escape:'htmlall':'UTF-8'}" 
                               id="slider_{$id|escape:'htmlall':'UTF-8'}_off" 
                               value="0" 
                               {if $option->slider neq 1}checked="checked" {/if}
                               />
                        <label for="slider_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
                        
            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="slider_step_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set slide-step value' mod='configurator'}">
                        {l s='Slider step' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <input id="slider_step_{$id|escape:'htmlall':'UTF-8'}" class="form-control" type="text" name="slider_step_{$id|escape:'htmlall':'UTF-8'}" value="{$option->slider_step|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="textarea_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Textarea' mod='configurator'}">
                        {l s='Is a textarea' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);"
                               type="radio"
                               name="textarea_{$id|escape:'htmlall':'UTF-8'}"
                               id="textarea_{$id|escape:'htmlall':'UTF-8'}_on"
                               value="1"
                               {if $option->textarea eq 1}checked="checked" {/if}
                               />
                        <label for="textarea_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"
                               type="radio"
                               name="textarea_{$id|escape:'htmlall':'UTF-8'}"
                               id="textarea_{$id|escape:'htmlall':'UTF-8'}_off"
                               value="0"
                               {if $option->textarea neq 1}checked="checked" {/if}
                               />
                        <label for="textarea_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            <div class="form-group {if $configurator_step->type=="products"}hide{/if}">
                <label class="control-label col-lg-4" for="email_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Email' mod='configurator'}">
                        {l s='Is an email' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);"
                               type="radio"
                               name="email_{$id|escape:'htmlall':'UTF-8'}"
                               id="email_{$id|escape:'htmlall':'UTF-8'}_on"
                               value="1"
                               {if $option->email eq 1}checked="checked" {/if}
                               />
                        <label for="email_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"
                               type="radio"
                               name="email_{$id|escape:'htmlall':'UTF-8'}"
                               id="email_{$id|escape:'htmlall':'UTF-8'}_off"
                               value="0"
                               {if $option->email neq 1}checked="checked" {/if}
                               />
                        <label for="email_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
                        
            <div class="form-group">
                <label class="control-label col-lg-4" for="is_date_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Is an date' mod='configurator'}">
                        {l s='Is an date' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);"
                               type="radio"
                               name="is_date_{$id|escape:'htmlall':'UTF-8'}"
                               id="is_date_{$id|escape:'htmlall':'UTF-8'}_on"
                               value="1"
                               {if $option->is_date eq 1}checked="checked" {/if}
                               />
                        <label for="is_date_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"
                               type="radio"
                               name="is_date_{$id|escape:'htmlall':'UTF-8'}"
                               id="is_date_{$id|escape:'htmlall':'UTF-8'}_off"
                               value="0"
                               {if $option->is_date neq 1}checked="checked" {/if}
                               />
                        <label for="is_date_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
                      
            <h4>
                <i class="icon-eye-open"></i> {l s='RAL group' mod='configurator'}
            </h4>
            
            <div class="form-group">
                <label class="control-label col-lg-4" for="is_ralstep_{$id|escape:'htmlall':'UTF-8'}">
                    <span class="label-tooltip" data-toggle="tooltip" title="{l s='Is an ralstep' mod='configurator'}">
                        {l s='Is an ralstep' mod='configurator'}
                    </span>
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input onclick="toggleDraftWarning(false);"
                               type="radio"
                               name="is_ralstep_{$id|escape:'htmlall':'UTF-8'}"
                               id="is_ralstep_{$id|escape:'htmlall':'UTF-8'}_on"
                               value="1"
                               {if $option->is_ralstep eq 1}checked="checked" {/if}
                               />
                        <label for="is_ralstep_{$id|escape:'htmlall':'UTF-8'}_on" class="radioCheck">
                            {l s='Yes' mod='configurator'}
                        </label>
                        <input onclick="toggleDraftWarning(true);"
                               type="radio"
                               name="is_ralstep_{$id|escape:'htmlall':'UTF-8'}"
                               id="is_ralstep_{$id|escape:'htmlall':'UTF-8'}_off"
                               value="0"
                               {if $option->is_ralstep neq 1}checked="checked" {/if}
                               />
                        <label for="is_ralstep_{$id|escape:'htmlall':'UTF-8'}_off" class="radioCheck">
                            {l s='No' mod='configurator'}
                        </label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
                        
         <div class="form-group">
            <div step-type="attributes">
                <label class="control-label col-lg-4" for="id_atribute_ral_{$id|escape:'htmlall':'UTF-8'}">{l s='Attributes group' mod='configurator'}</label>
                <div class="col-lg-8">
                    <select name="id_atribute_ral_{$id|escape:'htmlall':'UTF-8'}" id="id_atribute_ral_{$id|escape:'htmlall':'UTF-8'}" class="chosen">
                        <option value="0">{l s='No group selected for RAL' mod='configurator'}</option>
                        {foreach $attributesGroup as $group}
                            <option value="{$group.id_option_group|escape:'htmlall':'UTF-8'}"{if $option->id_atribute_ral eq $group.id_option_group}selected="selected"{/if}>
                                {$group.name|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>          
         </div>
                    
                   
        </div>
    </div>
</div>
