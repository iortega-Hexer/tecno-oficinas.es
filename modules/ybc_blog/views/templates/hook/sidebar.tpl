{*
* 2007-2022 ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
* 
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2022 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="ybc-left-panel col-lg-2">
    <div class="list-group">
        {if $list}
            {foreach from=$list item='tab'}
                {if $tab.hasAccess}
                    {if $tab.id!='ybc_tab_other_modules'}
                    <a class="{if $active == $tab.id || ($tab.id=='ybc_tab_comment' && $active=='ybc_tab_comment_reply') || ($tab.id=='ybc_tab_employees' && ($active=='ybc_tab_customer' || $active=='ybc_tab_author')) }active{/if} list-group-item" href="{$tab.url|escape:'html':'UTF-8'}" id="{$tab.id|escape:'html':'UTF-8'}">{if isset($tab.icon)}<i class="{$tab.icon|escape:'html':'UTF-8'}"></i> {/if}{$tab.label|escape:'html':'UTF-8'}{if isset($tab.total_result) && $tab.total_result} ({$tab.total_result|intval}){/if}</a>
                    {else}
                        <div class="li_othermodules">
                            <a class="{if isset($tab.hasRefs) && $tab.hasRefs}refs_othermodules{else}link_othermodules{/if}" href="{$tab.url|escape:'html':'UTF-8'}" {if isset($tab.hasRefs) && $tab.hasRefs}target="_blank"{/if}>
                                <span class="tab-title">{$tab.label|escape:'html':'UTF-8'}</span>
                                <span class="tab-sub-title">{$tab.subtitle|escape:'html':'UTF-8'}</span>
                            </a>
                        </div>
                    {/if}
                {else}
                    <style>
                    {literal}
                        #subtab-{/literal}{$tab.controller|escape:'html':'UTF-8'}{literal}{
                            display:none;
                        }
                    {/literal}
                    </style>
                {/if}
                
            {/foreach}
        {/if}
    </div>
</div>