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

{*
*   Handling price list according to price_list_display value
*       input: input with data checking
*       select: dropdown element
*       table: pricelist displayed as table
*
*   Parameters :
*       $step       Same as in default.tpl (main dispatcher)
*}


{* Defined first two attributes as default in order to avoid null value later
 * This should of course never happen !
*}
{assign var='attr' value=array()}

{foreach $step->attributes as $attribute}
    {if $attribute.configuratorStepAttribute->id_configurator_step}
        {if count($attr) < 2}
            {append var='attr' value=$attribute}
        {/if}
    {/if}
{/foreach}

{if $step->price_list_display eq 'input'}
        {*Handling price list as inputs*}
        <div class="row">
                {assign var='pricelist' value=$step->price_list|json_decode:true}
                {*
                * Getting keys' min and max
                * X is the first dimension
                * Y is the second one. Same y values no matter X
                * WARNING :
                *   - takes the first and last key
                *   - if strange values and/or not always increasing values may produce
                *       unwanted behaviour
                *}
                {assign var='first_dim'   value=array_keys($pricelist)}
                {assign var='value_x_min' value=reset($first_dim)}
                {assign var='value_x_max' value=end($first_dim)}
                    
                {include file="./price_range_core.tpl" step=$step attribute=$attr[0] min=$value_x_min max=$value_x_max dimension="1"}
                {if isset($attr[1])}
                        {assign var='second_dim'  value=array_keys(reset($pricelist))}
                        {assign var='value_y_min' value=reset($second_dim)}
                        {assign var='value_y_max' value=end($second_dim)}
                        {include file="./price_range_core.tpl" step=$step attribute=$attr[1] min=$value_y_min max=$value_y_max dimension="2"}            
                {/if}
        </div>    
{else if $step->price_list_display eq 'select'}   
        {* Handling price as two selects *}
        <div class="row">
                {assign var='pricelist' value=$step->price_list|json_decode:true}
                
                {include file="./price_select_core.tpl" step=$step attribute=$attr[0] dimension="1" options=$pricelist}
                {if isset($attr[1])}
                    {include file="./price_select_core.tpl" step=$step attribute=$attr[1] dimension="2" options=current($pricelist)}     
                {/if}
        </div>        
{else}
        <div class="row"  id="step_option_{$step->id|escape:'htmlall':'UTF-8'}">
                <table class="table">
                        {assign var='pricelist' value=$step->price_list|json_decode:true}
                        <thead>
                                {if $step->header_names neq ''}
                                        <tr>
                                                {foreach $step->header_names|json_decode as $header}
                                                        <td colspan="{$header->size}">{$header->name}</td>
                                                {/foreach}
                                        </tr>
                                {/if}

                                <tr>
                                        <td>
                                                <span class="row-title">{$attr[1].name|escape:'html':'UTF-8'}</span>
                                                <span class="col-title">{$attr[0].name|escape:'html':'UTF-8'}</span>
                                        </td>
                                        {foreach min($pricelist) as $y => $col}
                                                <td>{$y}{$step->input_suffix}</td>
                                        {/foreach}
                                </tr>
                        </thead>
                        <tbody>
                                {assign var='configStep1' value=$attr[0].configuratorStepAttribute}
                                {assign var='configStep2' value=$attr[1].configuratorStepAttribute}
                                
                                {foreach $step->getPriceListWithTax($configurator->id) as $x => $row}
                                        <tr>
                                                <td>{$x}{$step->input_suffix}</td>
                                                {foreach $row as $y => $col}
                                                        <td
                                                            class="table-cell"
                                                            data-step='{$step->id|escape:'htmlall':'UTF-8'}'
                                                            data-option-1='{$configStep1->id|escape:'htmlall':'UTF-8'}'
                                                            data-value-1='{$x}'
                                                            data-option-2='{$configStep2->id|escape:'htmlall':'UTF-8'}'
                                                            data-value-2='{$y}'
                                                            >
                                                                    {Tools::displayPrice($col)}
                                                        </td>
                                                {/foreach}
                                        </tr>
                                {/foreach}
                        </tbody>
                </table>
        </div>
{/if}


