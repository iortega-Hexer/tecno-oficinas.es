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

<!-- CONFIGURATOR -->
<div id="configurator_block" class="configurator_block tabs">
    {*<div class="">*}
        <h1 class="h1" itemprop="name">
            {*l s='Configure your' mod='configurator'*} {$productObject->name|escape:'html':'UTF-8'}
            <span class="pull-right required"><sup>*</sup>{l s='Required step' mod='configurator'}</span>
        </h1>
        {block name='product_description_short'}
            <div id="product-description-short-{$productObject->id}" itemprop="description">
                {$productObject->description_short nofilter}{* HTML comment, no escape necessary *}
            </div>
        {/block}
    {*</div>*}
    {*<div class="col-xs-12 col-sm-12 col-md-12">*}
        {if $configurator->tab_type === 'tab'}
            <div id="configurator-tabs">
                {assign var='k' value='0'}
                <ul>
                    {foreach $configuratorStepTab as $tab}
                        <li href="javascript:void(0);"
                            id="tab_{$tab->id|escape:'htmlall':'UTF-8'}"
                            class="configurator-tab-page"
                            {if $k eq '0'}data-selected="true"{else}data-selected="false"{/if}
                            data-block="configurator-tab-{$tab->id|escape:'htmlall':'UTF-8'}"
                            style="width: {math equation='100 / nbTabs' nbTabs=$nbTabsGroup }%"
                        >
                            {$tab->name[$id_lang]|escape:'htmlall':'UTF-8'}
                        </li>
                       {assign var='k' value=$k+1}
                    {/foreach}
                </ul>
            </div>
        {/if}
        <div class="step_list">
            {assign var='tabClass' value=''}
            {if $configurator->tab_type === 'accordion'}
                {assign var='k' value='0'}
                <div class="accordion" id="configurator-accordions">
                    {foreach $configuratorStepTab as $tab}
                        <div class="card configurator-card {if $k eq '0'}accordion-opened{/if}">
                            <div class="card-header configurator-tab-page"
                                 data-toggle="collapse"
                                 data-target="#accordion_{$tab->id|escape:'htmlall':'UTF-8'}"
                                 aria-expanded="{if $k eq '0'}true{else}false{/if}"
                            >
                                {$tab->name[$id_lang]|escape:'htmlall':'UTF-8'}
                                <i class="material-icons">navigate_next</i>
                            </div>
                            <div id="accordion_{$tab->id|escape:'htmlall':'UTF-8'}" class="panel-collapse collapse {if $k eq '0'}in{/if}" data-parent="#configurator-accordions">
                                <div class="card-body">
                                    {foreach $configurator->steps as $step}
                                        {if $step->id_configurator_step_tab eq $tab->id}
                                            {assign var='tabClass' value='configurator-tab-'|cat:$tab->id}
                                            <!-- STEP -->
                                            {include file='./configurator_step_17.tpl' step=$step configuratorCartDetail=$configuratorCartDetail tabClass=$tabClass}
                                            <!-- END STEP -->
                                        {/if}
                                    {/foreach}

                                    <!-- TABS ACTIONS -->
                                    {if $nbTabsGroup > 1}
                                        <div class="configurator-accordions-actions text-center">
                                            {if !($k eq '0')}
                                                <a class="btn btn-default btn-lg accordion-action-previous">
                                                    <i class="icon icon-chevron-left"></i>
                                                    {l s='Previous' mod='configurator'}
                                                </a>
                                            {/if}
                                            {if ($k + 1) < $nbTabsGroup}
                                                <a class="btn btn-primary btn-lg accordion-action-next">
                                                    {l s='Next' mod='configurator'}
                                                    <i class="icon icon-chevron-right"></i>
                                                </a>
                                            {/if}
                                            {if ($k + 1) >= $nbTabsGroup}
                                                <a class="btn btn-success btn-lg accordion-action-add-to-cart">
                                                    {l s='Add to cart' mod='configurator'}
                                                    <i class="icon icon-shopping-cart"></i>
                                                </a>
                                            {/if}
                                        </div>
                                    {/if}

                                </div>
                            </div>
                        </div>
                        {assign var='k' value=$k+1}
                    {/foreach}
                </div>
            {else}
                {foreach $configurator->steps as $step}
                    {foreach $configuratorStepTab as $tab}
                        {if $step->id_configurator_step_tab eq $tab->id}
                            {assign var='tabClass' value='configurator-tab-'|cat:$tab->id}
                        {/if}
                    {/foreach}
                    <!-- STEP -->
                    {include file='./configurator_step_17.tpl' step=$step configuratorCartDetail=$configuratorCartDetail tabClass=$tabClass}
                    <!-- END STEP -->
                {/foreach}

                <!-- TABS ACTIONS -->
                {if $nbTabsGroup > 1}
                    <div class="configurator-tabs-actions text-center">
                        <a id="tab-action-previous" class="btn btn-default btn-lg" style="display:none;">
                        <span><i class="icon icon-chevron-left"></i>
                            {l s='Previous' mod='configurator'}</span>
                        </a>
                        <a id="tab-action-next" class="btn btn-primary btn-lg">
                            {l s='Next' mod='configurator'}
                            <i class="icon icon-chevron-right"></i>
                        </a>
                        <a id="tab-action-add-to-cart" class="btn btn-success btn-lg" style="display:none;">
                            {l s='Add to cart' mod='configurator'}
                            <i class="icon icon-shopping-cart"></i>
                        </a>
                    </div>
                {/if}
            {/if}
        </div>
    {*</div>*}
    <div id='configurator_preview_clear'></div>
    {*<div class="col-xs-12 col-sm-12 col-md-12">*}
        <div id="configurator_preview_container">
            {$previewHtml nofilter}{* HTML comment, no escape necessary *}
        </div>
    {*</div>
    <div class="clearfix"></div>*}
</div>
<!-- END CONFIGURATOR -->

<script>
    {strip}
    var configurator_floating_preview = {$CONFIGURATOR_FLOATING_PREVIEW|intval};
    {/strip}
</script>