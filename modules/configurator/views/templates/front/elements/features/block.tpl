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

{foreach $step->options as $option}
    {if $option}
		{assign var='isCustom' value=$img_color_exists and $option->option.texture_image}
                
		{** TODO: col_img_dir **}
		
		<div id="step_option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
			 class="option_block option_group {if $isCustom}custom{else}colortexture {if !$img_color_exists}color{else}texture{/if}{/if}" 
			 style="display:none;"
			 {if !$option->option.texture_image and empty($option->content[$lang_id])}
				data-toggle="popover"
				data-content="{$option->option.name|escape:'html':'UTF-8'} "
			 {elseif !empty($option->content[$lang_id])}
				data-toggle="popover"
				title="{$option->option.name|escape:'html':'UTF-8'}"
				data-content="{$option->content[$lang_id]|escape:'htmlall':'UTF-8'} "
			 {/if} 
		>
			<div class='option_block_content'>
				<span class="configurator-zoom">
                                    {if version_compare($smarty.const._PS_VERSION_, '1.7', '>=')}
                                        <i class="material-icons zoom-in">&#xE8FF;</i>
                                    {else}
                                        <i class="icon-zoom-in"></i>
                                    {/if}
                                </span>
                                
				<div class="option_img" style="background-color: {$option->option.color|escape:'htmlall':'UTF-8'}">
                                    {if $img_color_exists}
                                        <img class="img-responsive" alt="{$option->option.name|escape:'html':'UTF-8'}" src="{$img_col_dir|cat:$option->id_option|cat:'.jpg'}" />
                                    {/if}
				</div>

				<input class="hidden" 
					   data-step='{$step->id|escape:'htmlall':'UTF-8'}' 
					   id="option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
					   type="{if $step->multiple}checkbox{else}radio{/if}" name="step[{$step->id|escape:'htmlall':'UTF-8'}][]" 
					   value="{$option->id|escape:'htmlall':'UTF-8'}"
				/>
				
				{if $img_color_exists and $option->option.texture_image}<span>{$option->option.name|escape:'html':'UTF-8'}</span>{/if}
				{if !$isCustom and !$step->display_total or $isCustom and !$step->use_qty}
					{include file="../impact_price.tpl"}
				{/if}
			</div>
			
			{if $step->use_qty}
				{include file="../quantity.tpl"}
			{/if}
			
			{if $isCustom and $step->use_qty and !$step->display_total}
				{include file="../impact_price.tpl"}
			{/if}
			
			{if $step->display_total}
				{include file="../impact_total_price.tpl"}
			{/if}
		</div>
    {/if}
{/foreach}
<div class="clearfix">&nbsp;</div>