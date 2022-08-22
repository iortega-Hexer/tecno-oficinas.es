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
        <div id="step_option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
             class="option_block option_group custom"
             style="display:none;"
                {if !empty($option->content[$lang_id])}
                    data-toggle="popover"
                    title="{$option->option.name|escape:'html':'UTF-8'}"
                    data-content="{$option->content[$lang_id]|escape:'htmlall':'UTF-8'} "
                {/if}
        >

            <div class='option_block_content'>
                <span class="configurator-zoom"><i class="icon-zoom-in"></i></span>
                <div class="option_img">
                    {if isset($option->layers) and count($option->layers) > 0}
                        {foreach $option->layers as $layer}
                            <img src="{$layer->layer_path}">
                        {/foreach}
                    {else}
                        {assign var='image' value=Image::getCover($option->id_option)}
                        <img class="img-responsive" alt="{$option->option.name|escape:'html':'UTF-8'}" src="{$link->getImageLink($option->id_option, $image['id_image'], 'home_default')}" />
                    {/if}
                </div>

                <input class="hidden"
                       data-step='{$step->id|escape:'htmlall':'UTF-8'}'
                       id="option_{$step->id|escape:'htmlall':'UTF-8'}_{$option->id|escape:'htmlall':'UTF-8'}"
                       type="{if $step->multiple}checkbox{else}radio{/if}" name="step[{$step->id|escape:'htmlall':'UTF-8'}][]"
                       value="{$option->id|escape:'htmlall':'UTF-8'}"
                />

                <span class="product-name">{$option->option.name|escape:'html':'UTF-8'}</span>
                {if !$step->use_qty}
                    {include file="../impact_price.tpl"}
                {/if}
            </div>

            {if $step->use_qty}
                {include file="../quantity.tpl"}
            {/if}

            {if $step->use_qty and !$step->display_total}
                {include file="../impact_price.tpl"}
            {/if}

            {if $step->display_total}
                {include file="../impact_total_price.tpl"}
            {/if}
        </div>
    {/if}
{/foreach}
<div class="clearfix">&nbsp;</div>
