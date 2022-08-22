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
*   Create an input with min max constraints
*   Parameters :
*       $step       Same as in dispatcher
*       $option  Current element's option
*       $dimension  
*       $min        Input's minimum value   (int)
*       $max        Input's maximum value   (int)
*
*}

<div id="step_option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
            class="option_input option_group col-md-6 form-group" {*style="display:none;"*}>
        <div class="input-group"> 
                {include file='./input_core.tpl' dimension="{$dimension}" min=$min max=$max}
        </div>
        {assign var='min' value=($option->min_value)?$option->min_value:$min}
        {assign var='max' value=($option->max_value)?$option->max_value:$max}
        {if $min || $max}
                <small>
                        {l s='Available values:' mod='configurator'}
                        {if $min && $max}
                                {$min|escape:'htmlall':'UTF-8'}{$step->input_suffix|escape:'htmlall':'UTF-8'} - {$max|escape:'htmlall':'UTF-8'}{$step->input_suffix|escape:'htmlall':'UTF-8'}
                        {else if $min}
                                > {$min|escape:'htmlall':'UTF-8'}{$step->input_suffix|escape:'htmlall':'UTF-8'}
                        {else if $max}
                                < {$max|escape:'htmlall':'UTF-8'}{$step->input_suffix|escape:'htmlall':'UTF-8'}
                        {/if}
                </small>
        {/if}
</div>
