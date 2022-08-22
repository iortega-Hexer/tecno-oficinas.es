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

{if !isset($showlabel)}
	{assign var=showlabel value=false}
{/if}
{if !isset($unique)}
	{assign var=unique value=false}
{/if}

{if isset($step) && $step->use_qty}
    <div {if $unique}unique-qty{/if} class="quantity_wanted form-group">
        {if $showlabel}
            <label>{l s='Quantity:' mod='configurator'}</label>
        {/if}
        <div class="input-group">
            <div class="input-group-btn">
                <button class="btn btn-configurator configurator-quantity-minus" type="button" {if $step->step_qty > 0}data-step="{{$step->step_qty}}"{/if}>-</button>
            </div>
            <input class="form-control qty {if $showlabel}qty-inline{/if}" type="text" value="0" {if $step->step_qty > 0}step="{{$step->step_qty}}"{/if}>
            <div class="input-group-btn">
                <button class="btn btn-configurator configurator-quantity-plus" type="button" {if $step->step_qty > 0}data-step="{{$step->step_qty}}"{/if}>+</button>
            </div>
        </div>
    </div>
{/if}
