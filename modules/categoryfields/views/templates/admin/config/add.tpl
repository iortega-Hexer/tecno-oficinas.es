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

<div class="panel-heading">
	<h3>{l s='Add a new field' mod='categoryfields'}</h3>
</div>

<div class="form-wrapper row">
	<div class="alert alert-danger mp-errors" style="display: none"></div>

	<div class="col-lg-12">
		<div class="form-group row">
			<label class="control-label col-lg-2">
				{l s='Name:' mod='categoryfields'}
			</label>

			<div class="col-lg-10">
				<input id="id_categoryfield" type="hidden" class="form-control" name="id_categoryfield" value=""/>
				<input id="name" type="text" class="form-control" name="name" value="" data-required="required" />
			</div>
		</div>

		<div class="form-group row">
			<div class="col-lg-2 control-label"></div>
			<div class="col-lg-10">
				{l s='Collapsible?' mod='categoryfields'}
				<input type="checkbox" value="1" name="collapsible" id="collapsible" class="collapsible"/>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xs-12">
		<button type="button" id="btn-cf-field-save" class="btn btn-primary btn-lg">{l s='Save' mod='categoryfields'}</button>
	</div>
</div>