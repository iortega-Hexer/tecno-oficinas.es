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

{if isset($cf_content)}
    {if $collapsible eq 1}
        <div class='categoryextrafield'>
            <div class='excerpt'>
                {$cf_excerpt nofilter}
                <a class='read-more'>{l s='read more' mod='categoryfields'} <i class="material-icons">keyboard_arrow_down</i></a>
            </div>
            <div class='content' style='display:none'>
                {$cf_content nofilter}
                <a class='read-less'>{l s='read less' mod='categoryfields'} <i class="material-icons">keyboard_arrow_up</i></a>
            </div>
        </div>
    {else}
        <div class='categoryextrafield'>
            <div class='content'>
                {$cf_content nofilter}
            </div>
        </div>
    {/if}
{/if}