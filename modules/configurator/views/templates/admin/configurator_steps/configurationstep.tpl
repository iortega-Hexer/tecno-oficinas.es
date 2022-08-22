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

<div class="tab-pane tab-content">
    <div id="tab-pane-ConfigurationStep" class="tab-pane">
        <!-- CONFIGURATION STEP -->
        <div class="panel configurator-steps-tab">
            <h3 class="tab"> <i class="icon-cog"></i> {l s='Step\'s configuration' mod='configurator'}</h3>

			<h4><i class="icon-info"></i> {l s='General' mod='configurator'}</h4>
			
            <div class="form-group">
                <label class="control-label col-lg-2 required" for="name_{$id_lang|escape:'htmlall':'UTF-8'}">
					<span class="label-tooltip" data-toggle="tooltip" title="{l s='The private name for this Step. Intern and not shown to customers.' mod='configurator'} {l s='Invalid characters:' mod='configurator'} &lt;&gt;;=#{} {l s='By default the group option\'s name is used.' mod='configurator'}">
						{l s='Private name' mod='configurator'}
					</span>
                </label>
                <div class="col-lg-10">
					{include file="./helpers/form/input_text_lang.tpl"
                        languages=$languages
                        input_class=""
                        input_value=$configurator_step->name
                        input_name="name"
                        required=true
					}
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2 required" for="public_name_{$id_lang|escape:'htmlall':'UTF-8'}">
					<span class="label-tooltip" data-toggle="tooltip" title="{l s='The public name for this Step.' mod='configurator'} {l s='Invalid characters:' mod='configurator'} &lt;&gt;;=#{} {l s='By default the group option\'s name is used.' mod='configurator'}">
						{l s='Public name' mod='configurator'}
					</span>
                </label>
                <div class="col-lg-10">
					{include file="./helpers/form/input_text_lang.tpl"
                        languages=$languages
                        input_class=""
                        input_value=$configurator_step->public_name
                        input_name="public_name"
                        required=true
					}
                </div>
            </div>
                
            <div class="form-group">
                <label class="control-label col-lg-2 required" for="invoice_name_{$id_lang|escape:'htmlall':'UTF-8'}">
					<span class="label-tooltip" data-toggle="tooltip" title="{l s='The invoice name for this Step. If empty it takes public name' mod='configurator'} {l s='Invalid characters:' mod='configurator'} &lt;&gt;;=#{}">
						{l s='Invoice name' mod='configurator'}
					</span>
                </label>
                <div class="col-lg-10">
					{include file="./helpers/form/input_text_lang.tpl"
                        languages=$languages
                        input_class=""
                        input_value=$configurator_step->invoice_name
                        input_name="invoice_name"
                        required=true
					}
                </div>
            </div>

            <div class="form-group">
                {if $display === 'edit'}
					<div class="alert alert-warning col-lg-10 col-lg-offset-2" id="alert-change-type" style='display:none;'>
						<button type="button" class="close" data-dismiss="alert">×</button>
						{l s='If you change step\'s type, you will erase all the step settings. Be sure you want to save.' mod='configurator'}
					</div>
                {/if}
				<label class="control-label col-lg-2 required" for="steptype">{l s='Step type' mod='configurator'}</label>
				<div class="col-lg-10">
					<select name="type" id="steptype">
                        {foreach $stepTypes as $key => $type}
                            <option value="{$key|escape:'htmlall':'UTF-8'}" {if $configurator_step->type eq $key}selected="selected"{/if}>
                                {$type|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
					</select>
				</div>
            </div> 
				
            <div step-type='attributes' class="col-lg-10 col-lg-offset-2">
                <div class="alert alert-info">
                    {l s='The option group use the attributes group\'s system of Prestashop. You can manage it in' mod='configurator'} 
                    " <a target="_blank" href="{$link->getAdminLink('AdminAttributesGroups')|escape:'html':'UTF-8'}">{l s='Catalog > Product attributes' mod='configurator'} <i class="icon-external-link-sign"></i></a> "
                </div>
                {if $display === 'edit'}
					<div id="alert-change-group" class="alert alert-warning" style="display:none;">
						<button type="button" class="close" data-dismiss="alert">×</button>
						{l s='If you change the options group, you will erase all the options configuration. Be sure you want to save.' mod='configurator'}
					</div>
                {/if}
            </div>

			<div step-type='attributes' class="form-group">
				<label class="control-label col-lg-2 required" for="id_option_group_attributes">{l s='Attributes group' mod='configurator'}</label>
				<div class="col-lg-10">
					<select name="id_option_group_attributes" id="id_option_group_attributes">
						{foreach $attributesGroup as $group}
							<option value="{$group.id_option_group|escape:'htmlall':'UTF-8'}" {if $configurator_step->id_option_group eq $group.id_option_group}selected="selected"{/if}>
								{$group.name|escape:'htmlall':'UTF-8'}
							</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div step-type='features' class="form-group">
				<label class="control-label col-lg-2 required" for="id_option_group_features">{l s='Features group' mod='configurator'}</label>
				<div class="col-lg-10">
					<select name="id_option_group_features" id="id_option_group_features">
						{foreach $featuresGroup as $group}
							<option value="{$group.id_option_group|escape:'htmlall':'UTF-8'}" {if $configurator_step->id_option_group eq $group.id_option_group}selected="selected"{/if}>
								{$group.name|escape:'htmlall':'UTF-8'}
							</option>
						{/foreach}
					</select>
				</div>
			</div>

			{hook h="configuratorAdminDisplayStepsControllerGroups" configurator_step=$configurator_step}


                    <div class="form-group">
                        <label class="control-label col-lg-2" for="content_{$id_lang|escape:'htmlall':'UTF-8'}">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display helpfull information to the customer about step' mod='configurator'}">
                                    {l s='Help tooltip' mod='configurator'}
                            </span>
                        </label>
                    <div class="col-lg-10">
                                            {include
                            file="./helpers/form/textarea_lang.tpl"
                            languages=$languages
                            input_name='content'
                            class="autoload_rte"
                            input_value=$configurator_step->content
                                            }
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label col-lg-2" for="info_text_{$id_lang|escape:'htmlall':'UTF-8'}">
                        <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display additional text about this steps' mod='configurator'}">
                                {l s='additional text' mod='configurator'}
                        </span>
                    </label>
                    <div class="col-lg-10">
                                            {include
                            file="./helpers/form/textarea_lang.tpl"
                            languages=$languages
                            input_name='info_text'
                            class="autoload_rte"
                            input_value=$configurator_step->info_text
                                            }
                        {if Module::isInstalled('dm_advancedformula') && Module::isEnabled('dm_advancedformula')}
                            <p>{l s='You can use the formulas you defined below by inserting them in this form: {{FORMULA_NAME}}' mod='configurator'}</p>
                        {/if}
                    </div>
                </div>
                
			<div class="row">
				
				<div class="col-xs-12 col-lg-6">
					<h4><i class="icon-eye-open"></i> {l s='Viewing' mod='configurator'}</h4>

					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='When the customer choose an option, it is display in the summary' mod='configurator'}">
								{l s='Choice displayed in the summary' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="displayed_in_preview" id="displayed_in_preview_on" value="1" {if $configurator_step->displayed_in_preview}checked="checked" {/if} />
								<label for="displayed_in_preview_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="displayed_in_preview" id="displayed_in_preview_off" value="0" {if !$configurator_step->displayed_in_preview}checked="checked"{/if} />
								<label for="displayed_in_preview_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Step\'s options will appears to customer when he choose "yes"' mod='configurator'}">
								{l s='Displayed by yes/no choice' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="displayed_by_yes" id="displayed_by_yes_on" value="1" {if $configurator_step->displayed_by_yes}checked="checked" {/if} />
								<label for="displayed_by_yes_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="displayed_by_yes" id="displayed_by_yes_off" value="0" {if !$configurator_step->displayed_by_yes}checked="checked"{/if} />
								<label for="displayed_by_yes_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                                                
					<div class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='You can choose the template (*.tpl format) you want to display. Advanced user.' mod='configurator'}">
								{l s='Use a custom template' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false); configuratorStepHandler.showCustomTemplateForm(true);" type="radio" name="use_custom_template" id="use_custom_template_on" value="1" {if $configurator_step->use_custom_template}checked="checked" {/if} />
								<label for="use_custom_template_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true); configuratorStepHandler.showCustomTemplateForm(false);"  type="radio" name="use_custom_template" id="use_custom_template_off" value="0" {if !$configurator_step->use_custom_template}checked="checked"{/if} />
								<label for="use_custom_template_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                                                
					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Change the default text of a select "Choose a value from the list" ' mod='configurator'}">
								{l s='Default text of a select' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-7">
							{include file="./helpers/form/input_text_lang.tpl"
								languages=$languages
								input_class=""
								input_value=$configurator_step->default_value_select
								input_name="default_value_select"
								required=false
							}
						</div>
					</div>

					<div id="custom_template_block" class='sub-form-group' style="{if empty($configurator_step->use_custom_template)}display: none;{/if}" step-type="attributes,products,features">
						<p class="help-block">{l s='List of template :' mod='configurator'}</p>
						<div class="form-group">
							<label class="control-label col-lg-4 col-lg-offset-1" for="custom_template">{l s='List of template' mod='configurator'}</label>
							<div class="col-lg-7">
								<select name="custom_template" id="custom_template">
									{foreach $custom_template_list as $name}
										<option value="{$name|escape:'htmlall':'UTF-8'}" {if $configurator_step->custom_template eq $name }selected="selected"{/if}>
											{$name|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						</div>    
					</div>

					<div class="form-group">
						<label class="control-label col-lg-4" for="css">
							<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='CSS in the div block (style tag)' mod='configurator'}">
								{l s='CSS in the div block (style tag)' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-7">
							<input value='{$configurator_step->css|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="css" name="css" />
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-lg-4" for="class">
							<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Class in the div block' mod='configurator'}">
								{l s='Class in the div block' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-7">
							<input value='{$configurator_step->class|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="class" name="class" />
						</div>
					</div>

					{assign var='tabsManager' value=$link->getAdminLink('AdminProducts')|escape:'UTF-8'|cat:'&updateproduct&id_product='|cat:$id_product|escape:'html':'UTF-8'|cat:'&key_tab=ModuleConfigurator'}
					{if $configurator_step_tab}
						<div class="col-lg-12">
							<div class="alert alert-info">
								{l s='Tabs can be added in the configuration of the product. You can manage them in' mod='configurator'}
								" <a target="_blank" href="{$tabsManager}">{l s='Products > Product configurator' mod='configurator'} <i class="icon-external-link-sign"></i></a> "
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-4" for="id_configurator_step_tab">
								<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Set a tab where the step will be displayed.' mod='configurator'}">
									{l s='Step\'s tab' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-7">
								<select name="id_configurator_step_tab" id="id_configurator_step_tab">
									<!--<option value="0">{l s='Choose a tab' mod='configurator'}</option>-->
									{foreach $configurator_step_tab as $tab}
										<option value="{$tab->id|escape:'htmlall':'UTF-8'}"
											{if $configurator_step->id_configurator_step_tab eq $tab->id} selected="selected"{/if}
										>{$tab->name[$id_lang]|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{else}
						<div class="col-lg-12">
							<div class="alert alert-warning">
								{l s='No tab available. Add at least one tab before adding one to this step. You can manage them in' mod='configurator'}
								" <a target="_blank" href="{$tabsManager}">{l s='Products > Product configurator' mod='configurator'} <i class="icon-external-link-sign"></i></a> "
							</div>
						</div>
					{/if}

					<div step-type='attributes,products,features'>
						<h4><i class="icon-money"></i> {l s='Price management' mod='configurator'}</h4>

						{*<div step-type='attributes' class="form-group">
							<label class="control-label col-lg-4">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='On this step, customer will be able to know the total price of configuration between the different options. In general you will use this option for the last step.' mod='configurator'}">
									{l s='Display total price' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-8">
								<span class="switch prestashop-switch fixed-width-lg">
									<input onclick="toggleDraftWarning(false);
																			   configuratorStepHandler.updateStatePriceList(true);
																			   configuratorStepHandler.updateStateInputOption(true);"
										   type="radio"
										   name="display_total"
										   id="display_total_on"
										   value="1" {if $configurator_step->display_total}checked="checked"{/if}
										   {if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
									/>
									<label for="display_total_on" class="radioCheck">
										{l s='Yes' mod='configurator'}
									</label>
									<input onclick="toggleDraftWarning(true);
																			   configuratorStepHandler.updateStatePriceList(false);
																			   configuratorStepHandler.updateStateInputOption(false);"
										   type="radio"
										   name="display_total"
										   id="display_total_off"
										   value="0" {if !$configurator_step->display_total}checked="checked"{/if}
										   {if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
									/>
									<label for="display_total_off" class="radioCheck">
										{l s='No' mod='configurator'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>*}

						<div step-type='attributes,products' class="form-group">
							<label class="control-label col-lg-4">
								<span class="label-tooltip" data-toggle='tooltip' title="{l s='When this is activated, the step has only one unique price.' mod='configurator'}">
									{l s='Unique price' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-8">
								<span class="switch prestashop-switch fixed-width-lg">
									<input onclick='toggleDraftWarning(false);
										configuratorStepHandler.updateStatePriceList(true);
										configuratorStepHandler.updateStateInputOption(true);
										configuratorStepHandler.showUniquePrice(true);'
										type='radio'
										name='unique_price'
										id='unique_price_on'
										value='1' {if $configurator_step->unique_price} checked="checked"{/if}
										{if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
									/>
									<label for="unique_price_on" class='radioCheck'>
											{l s='Yes' mod='configurator'}
									</label>
									<input onclick='toggleDraftWarning(true);
										configuratorStepHandler.updateStatePriceList(false);
										configuratorStepHandler.updateStateInputOption(false);
										configuratorStepHandler.showUniquePrice(false);'
										type='radio'
										name='unique_price'
										id='unique_price_off'
										value='0' {if !$configurator_step->unique_price} checked="checked"{/if}
										{if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
									/>
									<label for="unique_price_off" class='radioCheck'>
										{l s='No' mod='configurator'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>

						<div id="unique_price_block" class='sub-form-group' style="{if !$configurator_step->unique_price}display: none;{/if}">
							<p class="help-block">{l s='Available option when you enable unique price:' mod='configurator'}</p>
							<div class="form-group">
								<label class="control-label col-lg-4" >
									<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a price for the current step.' mod='configurator'}">
										{l s='Price' mod='configurator'}
									</span>
								</label>
								<div class="col-lg-7">
									<input value='{$configurator_step->unique_price_value|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="unique_price_value" name="unique_price_value" />
								</div>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='This step determine now the base price used to compute the final price.' mod='configurator'}">
									{l s='Determine base price' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-8">
								<span class="switch prestashop-switch fixed-width-lg">
									<input onclick="toggleDraftWarning(false);" type="radio" name="determine_base_price" id="determine_base_price_on" value="1" {if $configurator_step->determine_base_price}checked="checked" {/if} />
									<label for="determine_base_price_on" class="radioCheck">
										{l s='Yes' mod='configurator'}
									</label>
									<input onclick="toggleDraftWarning(true);"  type="radio" name="determine_base_price" id="determine_base_price_off" value="0" {if !$configurator_step->determine_base_price}checked="checked"{/if} />
									<label for="determine_base_price_off" class="radioCheck">
										{l s='No' mod='configurator'}
									</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
					</div>

					<div step-type='attributes,products,features'>
						<h4><i class="icon-money"></i> {l s='Weight management' mod='configurator'}</h4>
						<div class="form-group">
							<label class="control-label col-lg-4" for="weight">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a weight' mod='configurator'}">
									{l s='Weight' mod='configurator'}
								</span>
							</label>
							<div class="input-group col-lg-7">
								<input
									id="weight"
									class="form-control grey formula_editor"
									type="text"
									name="weight"
									value="{$configurator_step->weight|escape:'htmlall':'UTF-8'}"
								>
								<span class="input-group-addon">
									{Configuration::get('PS_WEIGHT_UNIT')}
								</span>
							</div>
						</div>
					</div>

					<div step-type='attributes,products,features'>
						<h4><i class="icon-money"></i> {l s='Dimensions management' mod='configurator'}</h4>
						<div class="form-group">
							<label class="control-label col-lg-4" for="dimension_width">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set the width of the final product' mod='configurator'}">
									{l s='Width' mod='configurator'}
								</span>
							</label>
							<div class="input-group col-lg-7">
								<input
									id="dimension_width"
									class="form-control grey formula_editor"
									type="text"
									name="dimension_width"
									value="{$configurator_step->dimension_width|escape:'htmlall':'UTF-8'}"
								>
								<span class="input-group-addon">
									{Configuration::get('PS_DIMENSION_UNIT')}
								</span>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4" for="dimension_height">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set the height of the final product' mod='configurator'}">
									{l s='Height' mod='configurator'}
								</span>
							</label>
							<div class="input-group col-lg-7">
								<input
									id="dimension_height"
									class="form-control grey formula_editor"
									type="text"
									name="dimension_height"
									value="{$configurator_step->dimension_height|escape:'htmlall':'UTF-8'}"
								>
								<span class="input-group-addon">
									{Configuration::get('PS_DIMENSION_UNIT')}
								</span>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4" for="dimension_depth">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set the depth of the final product' mod='configurator'}">
									{l s='Depth' mod='configurator'}
								</span>
							</label>
							<div class="input-group col-lg-7">
								<input
									id="dimension_depth"
									class="form-control grey formula_editor"
									type="text"
									name="dimension_depth"
									value="{$configurator_step->dimension_depth|escape:'htmlall':'UTF-8'}"
								>
								<span class="input-group-addon">
									{Configuration::get('PS_DIMENSION_UNIT')}
								</span>
							</div>
						</div>
					</div>

				</div>
					
				<div class="col-xs-12 col-lg-6">
					
					<h4><i class="icon-wrench"></i> {l s='Behavior' mod='configurator'}</h4>

					<div class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Customer must choice step\'s option to validate cart.' mod='configurator'}">
								{l s='Required step' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="required" id="required_on" value="1" {if $configurator_step->required}checked="checked" {/if} />
								<label for="required_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="required" id="required_off" value="0" {if !$configurator_step->required}checked="checked"{/if} />
								<label for="required_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='The step will not appear if it has no options and is ignored.' mod='configurator'}">
								{l s='Ignore step if empty' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="ignored_if_empty" id="ignored_if_empty_on" value="1" {if $configurator_step->ignored_if_empty}checked="checked" {/if} />
								<label for="ignored_if_empty_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="ignored_if_empty" id="ignored_if_empty_off" value="0" {if !$configurator_step->ignored_if_empty}checked="checked"{/if} />
								<label for="ignored_if_empty_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					{if (DMTools::getVersionMajor() >= 17)}
					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='If the user access to the product via a combination link, the default value will be the value find in the URL.' mod='configurator'}">
								{l s='Use combination as default value' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="use_combination_as_default_value" id="use_combination_as_default_value_on" value="1" {if $configurator_step->use_combination_as_default_value}checked="checked" {/if} />
								<label for="use_combination_as_default_value_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="use_combination_as_default_value" id="use_combination_as_default_value_off" value="0" {if !$configurator_step->use_combination_as_default_value}checked="checked"{/if} />
								<label for="use_combination_as_default_value_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					{/if}

					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='If the user access to the product via a custom link with parameters, the default value will be the value find in the URL.' mod='configurator'}">
								{l s='Use url parameters as default value' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="use_url_as_default_value" id="use_url_as_default_value_on" value="1" {if $configurator_step->use_url_as_default_value}checked="checked" {/if} />
								<label for="use_url_as_default_value_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="use_url_as_default_value" id="use_url_as_default_value_off" value="0" {if !$configurator_step->use_url_as_default_value}checked="checked"{/if} />
								<label for="use_url_as_default_value_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div class="form-group" step-type="attributes,products,features,upload">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Customer can choice multiple options.' mod='configurator'}">
								{l s='Multiple choices' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);
                                                                           configuratorStepHandler.showInputMaximumOptions(true);
                                                                           configuratorStepHandler.updateStatePriceList(true);
                                                                           configuratorStepHandler.updateStateInputOption(true);"  
									   type="radio" 
									   name="multiple" 
									   id="multiple_on" 
									   value="1" {if $configurator_step->multiple}checked="checked"{/if} 
									   {if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
                                                                />
								<label for="multiple_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);
                                                                           configuratorStepHandler.showInputMaximumOptions(false);
                                                                           configuratorStepHandler.updateStatePriceList(false);
                                                                           configuratorStepHandler.updateStateInputOption(false);"  
                                                                           type="radio" 
                                                                           name="multiple" 
                                                                           id="multiple_off" 
                                                                           value="0" {if !$configurator_step->multiple}checked="checked"{/if}
									   {if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
                                                                />
								<label for="multiple_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div id="max_options_block" class='sub-form-group' style="{if !$configurator_step->multiple}display: none;{/if}">
							<p class="help-block">{l s='Available options when you enable multiple choices:' mod='configurator'}</p>

							<div class="form-group">
									<label class="control-label col-lg-4" >
											<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a minimum number of choices possible for the customer' mod='configurator'}">
													{l s='Minimum number of choices' mod='configurator'}
											</span>
									</label>
									<div class="col-lg-7">
											<input value='{$configurator_step->min_options|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="min_options" name="min_options" />
									</div>
							</div>

							<div class="form-group">
									<label class="control-label col-lg-4" >
											<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a maximum number of choices possible for the customer. The value must be between 2 and the number of options possible to choose. Value to 0 means unlimited choices.' mod='configurator'}">
													{l s='Maximum number of choices' mod='configurator'}
											</span>
									</label>
									<div class="col-lg-7">
											<input value='{$configurator_step->max_options|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="max_options" name="max_options" />
									</div>
							</div>
					</div>

                                                                
					<div step-type='attributes,products' class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Allow customers to set a quantity for each step\'s options' mod='configurator'}">
								{l s='Enable input quantities' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);
                                                                                configuratorStepHandler.showMaxQtyForm(true);
                                                                                configuratorStepHandler.updateStatePriceList(true);
                                                                                configuratorStepHandler.updateStateInputOption(true);" 
										type="radio" 
										name="use_qty" 
										id="use_qty_on" 
										value="1" {if $configurator_step->use_qty}checked="checked" {/if} 
										{if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
								/>
								<label for="use_qty_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);
                                                                                configuratorStepHandler.showMaxQtyForm(false);
                                                                                configuratorStepHandler.updateStatePriceList(false);
                                                                                configuratorStepHandler.updateStateInputOption(false);"  
                                                                                type="radio" 
                                                                                name="use_qty" 
                                                                                id="use_qty_off" 
                                                                                value="0" 
                                                                                {if !$configurator_step->use_qty}checked="checked"{/if}
										{if !empty($configurator_step->price_list) || $configurator_step->use_input}disabled="disabled"{/if}
                                                                />
								<label for="use_qty_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

                                        {if $configurator_step->max_qty_step_option_id != 0}
                                                {assign var='display_max_qty' value='style="display: none;"'}
                                        {else}
                                                {assign var='display_max_qty' value='style="display: block;"'}
                                        {/if}

                                        <div id="max_qty_block" class='sub-form-group' style="{if !$configurator_step->use_qty}display: none;{/if}">
                                                <p class="help-block">{l s='Available options when you enable input quantities:' mod='configurator'}</p>
                                                <div class="form-group" id="min_qty_group">
                                                        <label class="control-label col-lg-4">
                                                                <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a minimul total quantity. The minimum value is 2. Value to 0 means unlimited quantities. Value 1 is obtain by activate required step' mod='configurator'}">
                                                                        {l s='Minimum total quantity' mod='configurator'}
                                                                </span>
                                                        </label>
                                                        <div class="col-lg-7">
                                                                <input value='{$configurator_step->min_qty|escape:'htmlall':'UTF-8'}' class="form-control formula_editor" type="text" id="min_qty" name="min_qty" />
                                                        </div>
                                                </div>    
                                                <div class="form-group" id="max_qty_group" {$display_max_qty}>
                                                        <label class="control-label col-lg-4">
                                                                <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a maximum total quantity. The minimum value is 2. Value to 0 means unlimited quantities.' mod='configurator'}">
                                                                        {l s='Maximum total quantity' mod='configurator'}
                                                                </span>
                                                        </label>
                                                        <div class="col-lg-7">
                                                                <input value='{$configurator_step->max_qty|escape:'htmlall':'UTF-8'}' class="form-control formula_editor" type="text" id="max_qty" name="max_qty" />
                                                        </div>
                                                </div>
                                                <div class="form-group" id="step_qty_group">
													<label class="control-label col-lg-4">
														<span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a step for the quantity. Value to 0 means step by 1.' mod='configurator'}">
															{l s='Step of quantity' mod='configurator'}
														</span>
													</label>
													<div class="col-lg-7">
														<input value='{$configurator_step->step_qty|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="step_qty" name="step_qty" />
													</div>
                                                </div>
                                                <div class="display_steps_block">
                                                        {assign var=k value=0}
                                                        {foreach $conditions_choices as $block}
                                                                {if empty($block.groups)}
                                                                        {assign var='display_max_qty_steps' value='style="display: none;"'}
                                                                {else}
                                                                        {assign var='display_max_qty_steps' value='style="display: block;"'}
                                                                {/if}
                                                                <div class="form-group" {$display_max_qty_steps}>
                                                                        <label class="control-label col-lg-4">
                                                                                <span class="label-tooltip" data-toggle="tooltip" title="{l s='Select a step and an option from this step from which maximum quantity will be associated. It must be a step with text fields.' mod='configurator'}">
                                                                                        {l s='Step and option to use' mod='configurator'}
                                                                                </span>
                                                                        </label>
                                                                        {foreach $block.groups as $group}
																			{if $group.type === 'select'}
                                                                                <div class="{$group.class|escape:'htmlall':'UTF-8'} chosen" >
                                                                                        {foreach $group.selects as $select}
                                                                                                <select
                                                                                                        {foreach $select.params as $attr => $value}
                                                                                                                {$attr|cat:"="|cat:$value|escape:'htmlall':'UTF-8'} 
                                                                                                        {/foreach}
                                                                                                        {if $select.params.class == 'select_step'}
                                                                                                                onchange="configuratorStepHandler.updateMaxQty(this.selectedIndex === 0)"
                                                                                                        {/if}
                                                                                                        >
                                                                                                        {foreach $select.options as $value => $option}
                                                                                                                {if $select.params.class == 'select_step' && $k == 0}
                                                                                                                        {assign var=k value=1}
                                                                                                                        <option value="0">{l s='Choose a step' mod='configurator'}</option>
                                                                                                                {/if}
                                                                                                                {if ($select.params.class == 'select_step' && $option['attrs']['data-pricelist'] == 1) || ($select.params.class == 'select_option')}
                                                                                                                        <option value="{$value|escape:'htmlall':'UTF-8'}"
                                                                                                                        {if $select.params.class == 'select_step' && $max_qty_step_id eq $value} selected="selected" 
                                                                                                                        {else if $select.params.class == 'select_option' && $configurator_step->max_qty_step_option_id eq $value} selected="selected"{/if}
                                                                                                                        {foreach $option.attrs as $attr => $value}
                                                                                                                                {$attr|cat:"="|cat:$value|escape:'htmlall':'UTF-8'} 
                                                                                                                        {/foreach}
                                                                                                                        >{$option.option|escape:'htmlall':'UTF-8'}</option>
                                                                                                                {/if}
                                                                                                        {/foreach}
                                                                                                </select>
                                                                                        {/foreach}
                                                                                </div>
																			{/if}
                                                                        {/foreach}
                                                                </div>
                                                                {if $configurator_step->max_qty_step_option_id == 0 || empty($block.groups)}
                                                                        {assign var='display_max_qty_coef' value='style="display: none;"'}
                                                                {else}
                                                                        {assign var='display_max_qty_coef' value='style="display: block;"'}
                                                                {/if}
                                                                <div class="form-group" id="max_qty_group_coef" {$display_max_qty_coef}>
                                                                        <label class="control-label col-lg-4">
                                                                                <span class="label-tooltip" data-toggle="tooltip" title="{l s='Set a multiplicator for the quantity associated to the step and the option chosen. A value to 0 is equivalent to a value to 1.' mod='configurator'}">
                                                                                        {l s='Multiplicator coefficient' mod='configurator'}
                                                                                </span>
                                                                        </label>
                                                                        <div class="col-lg-7">
                                                                                <input value='{$configurator_step->max_qty_coef|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="max_qty_coef" name="max_qty_coef" />
                                                                        </div>
                                                                </div>
                                                        {/foreach}                  
                                                </div>
                                        </div>

					<div step-type='attributes' class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='You can upload a price list which will be use for calculate entered values in text fields.' mod='configurator'}">
								{l s='Use a price list' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);
										configuratorStepHandler.updateStateDisplayTotalAndMultipleAndUseQtyOption(true);
										configuratorStepHandler.updateStateUniquePrice(true);
										configuratorStepHandler.updateStateInputOption(true);
										configuratorStepHandler.showPriceListForm(true);
										configuratorStepHandler.showInputSuffixForm(true);
										configuratorStepHandler.updateStateUseShared(true);"
										type="radio"
										name="use_pricelist"
										id="use_pricelist_on"
										value="1" {if !empty($configurator_step->price_list)}checked="checked" {/if}
										{if $configurator_step->display_total || $configurator_step->unique_price || $configurator_step->use_qty || $configurator_step->multiple || $configurator_step->use_input}disabled="disabled"{/if}
								/>
								<label for="use_pricelist_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);
										configuratorStepHandler.updateStateDisplayTotalAndMultipleAndUseQtyOption(false);
										configuratorStepHandler.updateStateUniquePrice(false);
										configuratorStepHandler.updateStateInputOption(false);
										configuratorStepHandler.showPriceListForm(false);
										configuratorStepHandler.showInputSuffixForm(false);
										configuratorStepHandler.updateStateUseShared(false);"
										type="radio" name="use_pricelist"
										id="use_pricelist_off"
										value="0" {if empty($configurator_step->price_list)}checked="checked"{/if}
										{if $configurator_step->display_total || $configurator_step->unique_price || $configurator_step->use_qty || $configurator_step->multiple || $configurator_step->use_input}disabled="disabled"{/if}
								/>
								<label for="use_pricelist_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div id="price_list_block" class='sub-form-group' style="{if empty($configurator_step->price_list)}display: none;{/if}">
						<div class="col-lg-2">&nbsp;</div>
						<p class="help-block">{l s='Available options when you use price list :' mod='configurator'}</p>
						<div class="form-group">
							<label class="control-label col-lg-4 col-lg-offset-1" for="">
								{l s='Import price list' mod='configurator'}
							</label>
							<div class="col-lg-7">
								<input type="hidden" id="price_list" name="price_list" value='{$configurator_step->price_list|escape:'htmlall':'UTF-8'}' />
								<div class="input-group">
									<span class="input-group-addon"><i class="icon-file"></i></span>
									<input id="price_list_file" type="file" name="pricelist_file" />
								</div>
								<p class="help-block">
									{l s='Format:' mod='configurator'} .csv, .txt ({l s='With ";" separator only' mod='configurator'})
									{* @TODO : Mettre un lien vers un fichier exemple *}
								</p>
								{if $configurator_step->price_list neq ''}
									<p>
										<a href="{$price_list_download_link}" target="_blank">
											<i class='icon-download'></i>
											{l s='Download price list file' mod='configurator'}
										</a>
									</p>
									<p>
										<a class="text-danger" onclick="$('#price_list').val('');
												$(this).remove();" href='javascript:void(0);'>
											<i class='icon-remove-sign'></i>
											{l s='Delete price list' mod='configurator'}
										</a>
									</p>
								{/if}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4 col-lg-offset-1" for="price_list_coeff">
								<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Multiplier which will change the value found in the price list' mod='configurator'}">
									{l s='Pricelist\'s coefficient' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-7">
								<input value='{$configurator_step->price_list_coeff|escape:'htmlall':'UTF-8'}' class="form-control" type="text" id="price_list_coeff" name="price_list_coeff" />
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4 col-lg-offset-1" for="price_list_type">{l s='Pricelist\'s impact price' mod='configurator'}</label>
							<div class="col-lg-7">
								<select name="price_list_type" id="price_list_type">
									{foreach $price_list_types as $type_id => $type}
										<option value="{$type_id|escape:'htmlall':'UTF-8'}" {if $configurator_step->price_list_type eq $type_id }selected="selected"{/if}>
											{$type|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-4 col-lg-offset-1" for="price_list_display">{l s='Pricelist\'s input type' mod='configurator'}</label>
							<div class="col-lg-7">
                                                                {* WARNING this.selectedIndex === 2 depends on the order*}
								<select name="price_list_display" id="price_list_display" onchange="configuratorStepHandler.showPriceListHeader(this.selectedIndex === 2)">
									{foreach $price_list_display as $display_id => $display}
										<option value="{$display_id|escape:'htmlall':'UTF-8'}" {if $configurator_step->price_list_display eq $display_id }selected="selected"{/if}>
											{$display|escape:'htmlall':'UTF-8'}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
                                                {*SAME WARNING AS BEFORE*}
                                                {if $configurator_step->price_list_display != 'table'}
                                                        {assign var='display_header' value='style="display: none;"'}
                                                {else}
                                                        {assign var='display_header' value='style="display: block;"'}
                                                {/if}
                                                <div class="form-group" id="header-group" {$display_header}>
                                                        <div style="display:none">
                                                                {include file="./helpers/form/input_text_lang.tpl"
                                                                        languages=$languages
                                                                        input_class=""
                                                                        input_value=$configurator_step->header_names
                                                                        input_name="header_names"
                                                                        required=true
                                                                }
                                                        </div>
                                                        
							<label class="control-label col-lg-4 col-lg-offset-1">{l s='Header' mod='configurator'}</label>
							<div class="col-lg-7" id="price_list_header" style="display: block;">
                                                                <a class="btn btn-primary" id="btn-show-price-list-header">{l s='Add header' mod='configurator'}</a>
							</div>
						</div>
                        <hr>
                        <div class="form-group">
                            <p class="help-block">{l s='You can download this price list files examples :' mod='configurator'}</p>
                            <p>
                                <a href="{$price_list_download_link}&example=1" target="_blank">
                                    <i class='icon-download'></i>
                                    {l s='Download one-dimensional price list file' mod='configurator'}
                                </a>
                            </p>
                            <p>
                                <a href="{$price_list_download_link}&example=2" target="_blank">
                                    <i class='icon-download'></i>
                                    {l s='Download two-dimensional price list file' mod='configurator'}
                                </a>
                            </p>
                        </div>
					</div>

					<div step-type='attributes' class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='Replace choices by text fields. Usefull for getting custom sizes for example.' mod='configurator'}">
								{l s='Use text field instead of choices' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);
										configuratorStepHandler.updateStateDisplayTotalAndMultipleAndUseQtyOption(true);
										configuratorStepHandler.updateStateUniquePrice(true);
										configuratorStepHandler.updateStatePriceList(true);
										configuratorStepHandler.showInputSuffixForm(true);
										configuratorStepHandler.updateStateUseShared(true);"
										type="radio" 
										name="use_input" 
										id="use_input_on" 
										value="1" {if $configurator_step->use_input}checked="checked" {/if} 
										{if $configurator_step->display_total || $configurator_step->unique_price || $configurator_step->use_qty || $configurator_step->multiple || !empty($configurator_step->price_list)}disabled="disabled"{/if}
								/>
								<label for="use_input_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);
										configuratorStepHandler.updateStateDisplayTotalAndMultipleAndUseQtyOption(false);
										configuratorStepHandler.updateStateUniquePrice(false);
										configuratorStepHandler.updateStatePriceList(false);
										configuratorStepHandler.showInputSuffixForm(false);
										configuratorStepHandler.updateStateUseShared(false);"
										type="radio"
										name="use_input"
										id="use_input_off"
										value="0"
										{if !$configurator_step->use_input}checked="checked"{/if}
										{if $configurator_step->display_total || $configurator_step->unique_price || $configurator_step->use_qty || $configurator_step->multiple || !empty($configurator_step->price_list)}disabled="disabled"{/if}
								/>
								<label for="use_input_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div id="text_field_block" class='sub-form-group' style="{if !($configurator_step->use_input || !empty($configurator_step->price_list))}display: none;{/if}">
						<div class="col-lg-2">&nbsp;</div>
						<p class="help-block">{l s='Available options when you use text fields :' mod='configurator'}</p>

						<div class="form-group">
							<label class="control-label col-lg-4" for="input_suffix_{$id_lang|escape:'htmlall':'UTF-8'}">
								<span class="label-tooltip" data-toggle="tooltip" title="{l s='Append suffix to field like "Mile, Inch, Foot, Yard" for example' mod='configurator'}">
									{l s='Input suffix' mod='configurator'}
								</span>
							</label>
							<div class="col-lg-7">
								{include file="./helpers/form/input_text_lang.tpl"
									languages=$languages
									input_class=""
									input_value=$configurator_step->input_suffix
									input_name="input_suffix"
									required=true
								}
							</div>
						</div>
					</div>

					<div class="form-group" step-type="attributes,products,features">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='When the customer choose an option, it is display in the order summary' mod='configurator'}">
								{l s='Choice displayed in the order' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="displayed_in_order" id="displayed_in_order_on" value="1" {if $configurator_step->displayed_in_order}checked="checked" {/if} />
								<label for="displayed_in_order_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="displayed_in_order" id="displayed_in_order_off" value="0" {if !$configurator_step->displayed_in_order}checked="checked"{/if} />
								<label for="displayed_in_order_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                                        
                                        
                    <div step-type='attributes' class="form-group">
						<label class="control-label col-lg-4">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='If you use the distribution, you must have in the previous steps of the options with numerical values in order to allocate the options in this step.' mod='configurator'}">
								{l s='Division' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="use_division" id="use_division_on" value="1" {if $configurator_step->use_division}checked="checked" {/if} />
								<label for="use_division_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="use_division" id="use_division_off" value="0" {if !$configurator_step->use_division}checked="checked"{/if} />
								<label for="use_division_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>

					<div step-type='attributes,products' class="form-group">
						<label class="control-label col-lg-4">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='' mod='configurator'}">
								{l s='Shared step' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);
										configuratorStepHandler.updateStatePriceListAndInputOptions(true);"
									   	type="radio"
									   	name="use_shared"
									   	id="use_shared_on"
									   	value="1"
									   	{if $configurator_step->use_shared}checked="checked" {/if}
								/>
								<label for="use_shared_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);
										configuratorStepHandler.updateStatePriceListAndInputOptions(false);"
									   	type="radio"
									   	name="use_shared"
									   	id="use_shared_off"
									   	value="0"
									   	{if !$configurator_step->use_shared}checked="checked"{/if}
								/>
								<label for="use_shared_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                                         
                                                                
                                                                
                                                                
					<h4 step-type='upload'><i class="icon-file"></i> {l s='File\'s options' mod='configurator'}</h4>

					<div step-type='upload' class="form-group">
						<label class="control-label col-lg-4" for="nb_files">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='The number of files that can be downloaded' mod='configurator'}">
								{l s='Number of files' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-7">
							<input id="nb_files" name="nb_files" type="text" class="form-control" value='{$configurator_step->nb_files|intval}' />
						</div>
					</div>
							
					<div step-type='upload' class="form-group">
						<label class="control-label col-lg-4" for="nb_files">
							{l s='Allowed extensions' mod='configurator'}
						</label>
						<div class="col-lg-7">
							{assign var='used_ext' value=$configurator_step->getUsedExtensions()}
							<select name="extensions[]" id="extensions" multiple class="chosen">
								{foreach $extensions as $ext}
								<option {if in_array($ext, $used_ext)}selected{/if} value="{$ext|escape:'htmlall':'UTF-8'}">{$ext|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
							<p class="help-block">{l s='Let empty if you want to allow any extensions' mod='configurator'}</p>
						</div>
					</div>

					<div step-type='upload' class="form-group">
						<label class="control-label col-lg-4" for="nb_files">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='0 = no limit' mod='configurator'}">
								{l s='Maximum total weight' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-7">
							<div class="input-group">
								<input
									type="text"
									name="max_weight_total"
									id="max_weight_total"
									class="form-control"
									value="{$configurator_step->max_weight_total|intval}"
									placeholder="{l s='0 = no limit' mod='configurator'}"
								>
								<span class="input-group-addon input-group-after">
									{l s='Mb' mod='configurator'}
								</span>
							</div>
						</div>
					</div>
                        
                    <div step-type='upload' class="form-group">
						<label class="control-label col-lg-4">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='The customer can use his webcam to take pictures.' mod='configurator'}">
								{l s='Use camera' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="use_upload_camera" id="use_upload_camera_on" value="1" {if $configurator_step->use_upload_camera}checked="checked" {/if} />
								<label for="use_upload_camera_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="use_upload_camera" id="use_upload_camera_off" value="0" {if !$configurator_step->use_upload_camera}checked="checked"{/if} />
								<label for="use_upload_camera_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                
                    <div step-type='upload' class="form-group">
						<label class="control-label col-lg-4">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Show uploaded image (jpg, png, gif)' mod='configurator'}">
								{l s='Show uploaded image' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="show_upload_image" id="show_upload_image_on" value="1" {if $configurator_step->show_upload_image}checked="checked" {/if} />
								<label for="show_upload_image_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="show_upload_image" id="show_upload_image_off" value="0" {if !$configurator_step->show_upload_image}checked="checked"{/if} />
								<label for="show_upload_image_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
                                                                
                    <div step-type='upload' class="form-group">
						<label class="control-label col-lg-4">
                            <span class="label-tooltip" data-toggle="tooltip" title="{l s='Display progress bar when customer upload his files.' mod='configurator'}">
								{l s='Display progress bar' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<span class="switch prestashop-switch fixed-width-lg">
								<input onclick="toggleDraftWarning(false);" type="radio" name="upload_display_progress" id="upload_display_progress_on" value="1" {if $configurator_step->upload_display_progress}checked="checked" {/if} />
								<label for="upload_display_progress_on" class="radioCheck">
									{l s='Yes' mod='configurator'}
								</label>
								<input onclick="toggleDraftWarning(true);"  type="radio" name="upload_display_progress" id="upload_display_progress_off" value="0" {if !$configurator_step->upload_display_progress}checked="checked"{/if} />
								<label for="upload_display_progress_off" class="radioCheck">
									{l s='No' mod='configurator'}
								</label>
								<a class="slide-button btn"></a>
							</span>
						</div>
					</div>
					
					<!-- STEP GROUP -->
					<h4 step-type='attributes'><i class="icon-th-large"></i> {l s='Step group' mod='configurator'}</h4>

					<div step-type='attributes' class="form-group">
						<label class="control-label col-lg-4">
							<span class="label-tooltip" data-toggle="tooltip" title="{l s='You can link the current step to one or more other steps by selecting them here.' mod='configurator'}">
								{l s='Steps linked to the current step' mod='configurator'}
							</span>
						</label>
						<div class="col-lg-8">
							<select name="stepgroups[]" class="configurator-select2" multiple>
								{foreach $steps_group as $step_group}
									{if $configurator_step->id != $step_group->id}
										<option value="{$step_group->id}" {if in_array($step_group->id, $steps_group_selected)}selected="selected"{/if}>#{$step_group->position+1} - {$step_group->name}</option>
									{/if}
								{/foreach}
							</select>
						</div>
					</div>
					<!-- /STEP GROUP -->
						
				</div>
							
			</div>
			
            <div class="panel-footer">
				<a href="{$link->getAdminLink('AdminConfiguratorSteps')|escape:'html':'UTF-8'}&id_configurator={$id_configurator|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='configurator'}</a>
				<button type="submit" name="submitAddconfigurator_step" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='configurator'}</button>
				<button type="submit" name="submitAddconfigurator_stepAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='configurator'}</button>
            </div>

        </div>
        <!-- DISPLAY CONDITIONS PARAMETERS -->
        <div class="panel configurator-steps-tab">
            <h3 class="tab"> <i class="icon-eye-open"></i> {l s='Display conditions of the step' mod='configurator'}</h3>

            {include file="./conditions_block.tpl"
                type="step"
                id=$configurator_step->id
                choices=$conditions_choices
                values=$conditions_step
            }

            <div class="panel-footer">
				<a href="{$link->getAdminLink('AdminConfiguratorSteps')|escape:'html':'UTF-8'}&id_configurator={$id_configurator|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='configurator'}</a>
				<button type="submit" name="submitAddconfigurator_step" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='configurator'}</button>
				<button type="submit" name="submitAddconfigurator_stepAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='configurator'}</button>
            </div>
        </div>
        <!-- END DISPLAY CONDITIONS PARAMETERS -->

		<!-- FILTERS PARAMETERS -->
		<div class="panel configurator-steps-tab" step-type='products'>
			<h3 class="tab"> <i class="icon-filter"></i> {l s='Filters of the step' mod='configurator'}</h3>

			{include file="./filters_block.tpl" type="step" configurator_step=$configurator_step choices=$filters_choices values=$filters_step}

			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminConfiguratorSteps')|escape:'html':'UTF-8'}&id_configurator={$id_configurator|escape:'htmlall':'UTF-8'}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='configurator'}</a>
				<button type="submit" name="submitAddconfigurator_step" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='configurator'}</button>
				<button type="submit" name="submitAddconfigurator_stepAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='configurator'}</button>
			</div>
		</div>
		<!-- END FILTERS PARAMETERS -->

		{hook h="displayAdminStepConfiguratorAfterForm" configurator_step=$configurator_step}

    </div>
</div>
			
{strip}
	<script>
		$('select.configurator-select2').select2();
	</script>
{/strip}