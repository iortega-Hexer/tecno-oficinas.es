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

{assign var=value value=$option->id_tax_rules_group_product}

<div id="tax_impact_{$option->id|escape:'htmlall':'UTF-8'}" class="price_impact">
    <div class="form-group">
        <label class='col-lg-12'>{l s='Tax impact for :' mod='configurator'} {$option->option.name|escape:'htmlall':'UTF-8'}</label>
    </div>
    <hr />

    <div class="form-group">
        <label class="control-label col-lg-4 required" for="select_taximpact_{$id|escape:'htmlall':'UTF-8'}">{l s='Calculation method' mod='configurator'}</label>
        <div class="col-lg-8">
            <select id="select_taximpact_{$id|escape:'htmlall':'UTF-8'}" name="select_taximpact_{$id|escape:'htmlall':'UTF-8'}" class='select_taximpact chosen' {if $configurator_step->type=="products"}disabled{/if}>
                {foreach $tax_impact_types as $tax}
                    <option value="{$tax.id_tax_rules_group|escape:'htmlall':'UTF-8'}" {if $value eq $tax.id_tax_rules_group}selected='selected'{/if}>{$tax.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>
	
</div>