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

{literal}
<script id="tmpl_filters_group" type="text/x-handlebars-template">
	{{#separator}}
	<div class="row filter_separator text-center"><strong>{/literal}{l s='OR' mod='configurator'}{literal}</strong></div>
	<div class="clearfix">&nbsp;</div>
	{{/separator}}
	<div data-id="{{id}}" class="panel filter_group">
		<div class="panel-heading">
			<i class="icon-tasks"></i> {/literal}{l s='Filters group' mod='configurator'}{literal} {{id}}
			<a href="#" class="btn-negative_filter" data-id="{{id}}">
				{{#if negative_filter}}
				<i class="icon-remove action-disabled list-action-enable"></i>
				{{else}}
				<i class="icon-check action-enabled list-action-enable"></i>
				{{/if}}
			</a>
		</div>
		<table class="table">
			<thead>
			<tr>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Type' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Option' mod='configurator'}{literal}</span>
				</th>
				<th>
					<span class="title_box">{/literal}{l s='Operator' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Target step' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Target type' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Target option' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='Value' mod='configurator'}{literal}</span>
				</th>
				<th></th>
			</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</script>

<script id="tmpl_filters_row" type="text/x-handlebars-template">
	{{#separator}}
	<tr><td colspan="8" class="text-center"><b>{/literal}{l s='AND' mod='configurator'}{literal}</b></td></tr>
	{{/separator}}
	<tr class="filter_row" data-id="{{id}}" data-values="{{values}}">
		<td>{{type}}</td>
		<td>{{option}}</td>
		<td>{{operator}}</td>
		<td>{{target_step}}</td>
		<td>{{target_type}}</td>
		<td>{{target_option}}</td>
		<td>{{value}}</td>
		<td>
			<a class="btn btn-default delete_filter" href="#">
				<i class="icon-remove"></i> {/literal}{l s='Delete' mod='configurator'}{literal}
			</a>
		</td>
	</tr>
</script>
{/literal}