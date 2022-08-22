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

{if isset($step)}
    {assign var="first_option" value=current($step->options)}
    {if $step->use_custom_template eq 1}
        {assign var='custom_tpl' value='../custom/'|cat:$step->custom_template}
        {include file=$custom_tpl step=$step}
    {elseif $step->price_list neq ''}
        {include file='./price_list.tpl' step=$step}
    {elseif $step->use_input}
        {include file='./input.tpl' step=$step}
    {elseif $step->option_group->is_color_group and ($first_option->option['color'] neq '' OR $img_color_exists)}
        {include file='./block.tpl' step=$step}
    {elseif !$step->multiple and $step->option_group->group_type eq 'select'}
        {include file='./select.tpl' step=$step}
    {else}
        {include file='./option.tpl' step=$step}
    {/if}
{/if}