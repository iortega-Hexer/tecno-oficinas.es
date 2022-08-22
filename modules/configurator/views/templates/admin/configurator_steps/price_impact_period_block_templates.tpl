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
<script id="tmpl_impact_value_period_group" type="text/x-handlebars-template">
	<div data-id="{{id}}" class="panel impact_value_period_group">
		<div class="panel-heading">
			<i class="icon-tasks"></i> {/literal}{l s='Your specific periods' mod='configurator'}{literal}
		</div>
		<table class="table">
			<thead>
			<tr>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='From' mod='configurator'}{literal}</span>
				</th>
				<th class="fixed-width-md">
					<span class="title_box">{/literal}{l s='To' mod='configurator'}{literal}</span>
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

<script id="tmpl_impact_value_period_row" type="text/x-handlebars-template">
	<tr class="impact_value_period_row" data-id="{{id}}" data-values="{{values_json}}">
		<td>{{values.date_start}}</td>
		<td>{{values.date_end}}</td>
		<td>{{values.specific_value}} {/literal}{Context::getContext()->currency->sign}{literal}</td>
		<td>
			<a class="btn btn-default delete_impact_value_period" href="#">
				<i class="icon-remove"></i> {/literal}{l s='Delete' mod='configurator'}{literal}
			</a>
		</td>
	</tr>
</script>
{/literal}