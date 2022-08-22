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
*   Precondition: $step->price_list eq ''
*   Generate input's HTML for all options
*   If no constraints are given, generates a simple input.
*   If constraints (dimension, min and max) are given, adds them in input's parameters
*
*   Parameters :
*       $step       Same as in dispatcher
*       
*}
<div class="row">
    {foreach $step->options as $option}
        {if $option and $option->slider eq true}
            {include file='./slider.tpl' option=$option step=$step dimension="1" lang_id=$lang_id}

        {elseif $option->textarea eq true}
            {include file='./textarea.tpl' option=$option step=$step dimension="1" lang_id=$lang_id}

        {elseif $option->email eq true}
            {include file='./email.tpl' option=$option step=$step dimension="1" lang_id=$lang_id}
            
        {elseif $option->is_date eq true}
            {include file='./date.tpl' option=$option step=$step dimension="1" lang_id=$lang_id }
            
       {elseif $option->is_ralstep eq true}
            {include file='./ralstep.tpl' option=$option step=$step dimension="1" lang_id=$lang_id }
  
        {else}
            <div id="step_option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
                 class="option_input option_group col-md-6 form-group" style="display:none;">
                <div class="input-group">
                    {include file='./input_core.tpl' option=$option step=$step dimension="1"}
                </div>

                {if $option->min_value || $option->max_value}
                    <small>{l s='Available values:' mod='configurator'}</small>
                    <small class="label_min" style="display: {if !$option->max_value}inline{else}none{/if}">
                        > <span class="option_min_value">{$option->min_value|escape:'htmlall':'UTF-8'}</span>{$step->input_suffix|escape:'htmlall':'UTF-8'}
                    </small>
                    <small class="label_max" style="display: {if !$option->min_value}inline{else}none{/if}">
                        < <span class="option_max_value">{$option->max_value|escape:'htmlall':'UTF-8'}</span>{$step->input_suffix|escape:'htmlall':'UTF-8'}
                    </small>
                    <small class="label_min_max" style="display: {if $option->max_value && $option->min_value}inline{else}none{/if}">
                        <span class="option_min_value">{$option->min_value|escape:'htmlall':'UTF-8'}</span>{$step->input_suffix|escape:'htmlall':'UTF-8'} - <span class="option_max_value">{$option->max_value|escape:'htmlall':'UTF-8'}</span>{$step->input_suffix|escape:'htmlall':'UTF-8'}
                    </small>
                {/if}

            </div>
        {/if}
    {/foreach}
</div>