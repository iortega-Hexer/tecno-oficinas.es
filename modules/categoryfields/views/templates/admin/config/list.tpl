{*
* 2007-2017 Musaffar
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
*  @author Musaffar Patel <musaffar.patel@gmail.com>
*  @copyright  2007-2017 Musaffar Patel
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Property of Musaffar Patel
*}

<div class="row">
	<div class="col-sm-6">
		<h4>{l s='Category Fields' mod='categoryfields'}</h4>
		<table class="table">
			<thead>
			<tr>
				<th>{l s='Name' mod='categoryfields'}</th>
				<th>{l s='Action' mod='categoryfields'}</th>
			</tr>
			</thead>
			<tbody>
				{foreach from=$categoryfields item=categoryfield}
					<tr>
						<td>{$categoryfield->name|escape:'htmlall':'UTF-8'}</td>
						<td>
							<i class="cf-categoryfield-edit material-icons"
							   data-id="{$categoryfield->id_categoryfield|escape:'htmlall':'UTF-8'}"
							   data-name="{$categoryfield->name}"
							   data-collapsible="{$categoryfield->collapsible}"
							   style="cursor: pointer;">edit</i>
							<i class="cf-categoryfield-delete material-icons"
							   data-id="{$categoryfield->id_categoryfield|escape:'htmlall':'UTF-8'}" style="cursor: pointer;">delete</i>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>