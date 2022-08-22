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

{assign var=id value=$configurator_step->id}

{if !Validate::isLoadedObject($configurator_step)}
	<div class="alert alert-warning">
		{l s='You must save this step before configuring filters.' mod='configurator'}
	</div>
{elseif empty($choices)}
	<div class="alert alert-warning">
		{l s='You can\'t configure filters on the first step.' mod='configurator'}
	</div>
{else}
	<div id="filters_block_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}" class="filters_block filters_{$type|escape:'htmlall':'UTF-8'}_block" data-type="{$type|escape:'htmlall':'UTF-8'}" data-id="{$id|escape:'htmlall':'UTF-8'}">
		<div id="filters_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}">
			<div id="filter_group_list_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}" class="filter_group_list"></div>
		</div>

		<a class="btn btn-default add_filter_group" href="#">
			<i class="icon-plus-sign"></i> {l s='Add a new filter group' mod='configurator'}
		</a>
		<div class="clearfix">&nbsp;</div>

		<div class="panel filters-panel" id="filters-panel_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}" class="filters-panel" style="display:none;">
			<h3><i class="icon-tasks"></i> {l s='Filters' mod='configurator'}</h3>

			<div class="form-group">
				<div class="col-lg-3 col-md-6">
					<label>{l s='Type' mod='configurator'}</label>
					<select class="select_type">
						{foreach $choices.filters_options as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-3 col-md-6">
					<label>{l s='Option' mod='configurator'}</label>
					<select class="select_option">
						{foreach $choices.features_options as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-3 col-md-6">
					<label>{l s='Operator' mod='configurator'}</label>
					<select class="select_operator">
						{foreach $choices.selectors_options as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-3 col-md-6">
					<label>{l s='Step' mod='configurator'}</label>
					<select class="select_target_step">
						{foreach $choices.steps_options as $choice_id => $choice}
							<option value="{$choice->id|escape:'htmlall':'UTF-8'}" data-step-type="{$choice->type|escape:'htmlall':'UTF-8'}">{$choice->name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-1">

				</div>
				<div class="col-lg-3 col-md-6">
					<label>{l s='Type' mod='configurator'}</label>
					<select class="select_target_type">
						{foreach $choices.filters_options as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-3 col-md-6">
					<label>{l s='Option' mod='configurator'}</label>
					<select class="select_target_option">
						{foreach $choices.features_options as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-4 col-md-12">
					<label>{l s='Value' mod='configurator'}</label>
					<select class="select_value">
						{foreach $choices.value_option as $choice_id => $choice_label}
							<option value="{$choice_id|escape:'htmlall':'UTF-8'}">{$choice_label|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-10 col-md-12" style="display:none;">
					<label>{l s='Formula' mod='configurator'}</label>
					<input id="filter_formula" class="formula_editor" type="hidden" />
				</div>
			</div>

			<div class="form-group">
				<div class="col-lg-2 col-lg-offset-5">
					<a class='add_filter btn btn-default' href="#">
						<i class="icon-plus-sign"></i> {l s='Add' mod='configurator'}
					</a>
				</div>
			</div>
		</div>

	</div>
{/if}

<script type="text/javascript">
    (function($) {
        $(function() {
			{if isset($values)}
            filtersHandler.renderFilters('#filters_block_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}', '{$values}'); {* $values is JSON data, no escape necessary *}
			{/if}
        });
    })(jQuery);
</script>