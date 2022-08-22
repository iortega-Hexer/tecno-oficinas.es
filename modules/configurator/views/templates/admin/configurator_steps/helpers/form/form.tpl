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

{extends file="helpers/form/form.tpl"}

{block name="defaultForm"}
    <div class="row">
        <div class="productTabs col-lg-2 col-md-3">
            <div class="list-group">
				{foreach $tabs key=id item=tab}
					<a {if !empty($tab.step_type)}step-type='{$tab.step_type|escape:'htmlall':'UTF-8'}'{/if} data-toggle="tab" class="list-group-item {if $tab.selected eq 1}active{/if}" id="link-{$tab.id|escape:'htmlall':'UTF-8'}" href="#tab-pane-{$tab.id|escape:'htmlall':'UTF-8'}">{$tab.name|escape:'htmlall':'UTF-8'}</a>
				{/foreach}
            </div>
        </div>

        <form id="configurator_step_form" class="form-horizontal col-lg-10 col-md-9" action="{$form_action|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data" name="configurator_step" novalidate>
            <input type="hidden" name="id_configurator" value="{$id_configurator|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="id_configurator_step" value="{$id_configurator_step|escape:'htmlall':'UTF-8'}" />
            {foreach $tabs key=id item=tab}
                {$tab.form_content_html} {* HTML comment, no escape necessary *}
            {/foreach}
        </form>

    </div>

    {include file="../../conditions_block_templates.tpl"}
    {include file="../../filters_block_templates.tpl"}
    {include file="../../price_impact_period_block_templates.tpl"}

	{strip}
	{addJsDef id_lang_default=$id_lang_default}
	{/strip}
	
{/block}

{block name="script"}
    {$smarty.block.parent}
	
	// START COMPATIBILITY
	// Problems with /admin/products.js 
	// on stores who didnt included this file
	tabs_manager = {
		allow_hide_other_languages : true
	};
	if (tabs_manager.allow_hide_other_languages)
		hideOtherLanguage({$default_form_language|escape:'htmlall':'UTF-8'});
	function hideOtherLanguage(id){
		$('.translatable-field').hide();
		$('.lang-' + id).show();
		id_language = id;
	}
	// END COMPATIBILITY
	
    configuratorStepHandler.init();
    $("#id_option_group").on('change', function(){
	configuratorStepHandler.showWarningBlock('alert-change-group');
    });
    $('.list-group a.active').trigger('click');
    $('.list-group a').on('click', function(){
	$('.list-group a').removeClass('active');
	$('.tab-pane').removeClass('active');
	$(this).addClass('active');
    });
	
{/block}