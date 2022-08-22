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
*   Params :
*       $step
*       $option
*   Additionnal :
*       $dimension
*       $min
*       $max
*}

<span class="input-group-addon">{$option->option.name|escape:'html':'UTF-8'} : </span>

<input 
        {* If dimension, min and max are provided, add constraints*}
        {if isset($dimension) && isset($min) && isset($max)}
                data-dimension="{$dimension|escape:'htmlall':'UTF-8'}"
                data-min="{$min|escape:'htmlall':'UTF-8'}" 
                data-max="{$max|escape:'htmlall':'UTF-8'}"
        {/if}
		{if $option->min_value != ""}
                data-min="{$option->min_value|escape:'htmlall':'UTF-8'}"
        {/if}
		{if $option->max_value != ""}
                data-max="{$option->max_value|escape:'htmlall':'UTF-8'}"
        {/if}
        data-step='{$step->id|escape:'htmlall':'UTF-8'}' 
        data-option='{$option->id|escape:'htmlall':'UTF-8'}'
        data-force="{$option->force_value|escape:'htmlall':'UTF-8'}"
        id="option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
        class="form-control grey" 
        type="text" 
        value="" 
/>

{if $step->input_suffix neq ''}
        <span class="input-group-addon">
                {$step->input_suffix|escape:'htmlall':'UTF-8'}
        </span>
{/if}

{if !empty($option->content[$lang_id])}
        {include file='../info.tpl' 
                 title=$option->option.name
                 content=$option->content[$lang_id]}
{/if}

{if $step->price_list eq ''}{include file="../impact_price.tpl"}{/if}