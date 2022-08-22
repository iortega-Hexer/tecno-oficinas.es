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

{assign var='stepInfos' value=$configuratorCartDetail->getStepInfosByIdStep($step->id)}
<div id="step_{$step->id|escape:'htmlall':'UTF-8'}"
     class="step_group form-group {if $stepInfos neq false}info-on-this-step{/if} {$tabClass|escape:'htmlall':'UTF-8'} {$step->class|escape:'htmlall':'UTF-8'} {if $step->dropzone}dmviewer2d-step-dropzone{/if}"
     style="display : none;{$step->css|escape:'htmlall':'UTF-8'}"
>
    <label class="title">
        <span class="step_title">
            {$step->public_name|escape:'html':'UTF-8'}
            {if $step->required}
                <sup>*</sup>
            {/if}
        </span>
        {if $step->content neq ''}
            {include file='./info.tpl'
            title=$step->public_name
            content=$step->content}
        {/if}
    </label>

    {if $display_price}
        {assign var="stepPrice" value=0}
        {foreach $configuratorCartDetail->getDetail() as $id => $stepDetail}
            {if $id == $step->id && isset($stepDetail.display_step_amount)}
                {assign var="stepPrice" value=$stepDetail.display_step_amount}
            {/if}
        {/foreach}
        <div class="display-step-amount">{convertPrice price=$stepPrice}</div>
    {/if}
    <div class="row">
        <div class="col-xs-12 info_text">{if $step->info_text }{$step->info_text nofilter}{* HTML content, no escape necessary *}{/if}</div>
        <div class="col-xs-12">
            {if $step->isType(ConfiguratorStepAbstract::TYPE_STEP_UPLOAD)}
                <p class="text-muted">
                    {l s='You can download a maximum of %d files.' mod='configurator' sprintf=$step->nb_files|intval}
                    {if !empty($step->extensions)}
                        {l s='Allowed extensions:' mod='configurator'}&nbsp;{$step->getDisplayExtensions()|escape:'html':'UTF-8'}
                    {/if}
                </p>
            {/if}
            {if $step->displayed_by_yes}
                <div class="form-group">
                    <div>
                        <input data-toggle="collapse"
                               data-target="#collapse_{$step->id|escape:'htmlall':'UTF-8'}"
                               data-step="{$step->id|escape:'htmlall':'UTF-8'}"
                               type="radio"
                               id="no_radio_{$step->id|escape:'htmlall':'UTF-8'}"
                               class="no_radio"
                               name="yesnoradio[{$step->id|escape:'htmlall':'UTF-8'}][]"
                               checked="checked" />
                        <label for="no_radio_{$step->id|escape:'htmlall':'UTF-8'}">{l s='No' mod='configurator'}</label>
                    </div>

                    <div>
                        <input data-toggle="collapse"
                               data-target="#collapse_{$step->id|escape:'htmlall':'UTF-8'}"
                               type="radio"
                               id="yes_radio_{$step->id|escape:'htmlall':'UTF-8'}"
                               class="yes_radio"
                               name="yesnoradio[{$step->id|escape:'htmlall':'UTF-8'}][]" />
                        <label for="yes_radio_{$step->id|escape:'htmlall':'UTF-8'}">{l s='Yes' mod='configurator'}</label>
                    </div>
                </div>
            {/if}

            {assign var='img_color_exists' value=false}
            {assign var='img_color_not_exists' value=true}
            {foreach $step->options as $option}
                {if Validate::isLoadedObject($option)}
                    {assign var='img_color_exists' value=$img_color_exists or file_exists($col_img_dir|cat:$option->id_option|cat:'.jpg')}
                    {assign var='img_color_not_exists' value=$img_color_not_exists or !file_exists($col_img_dir|cat:$option->id_option|cat:'.jpg')}
                {/if}
            {/foreach}
            <div class="{if $step->displayed_by_yes}collapse{/if} step_options" id="collapse_{$step->id|escape:'htmlall':'UTF-8'}">
                {if $step->displayed_by_yes}<hr/>{/if}
                {include file='./'|cat:{$step->type}|cat:'/default.tpl' step=$step}
            </div>
        </div>

        <div class="col-xs-12 error-step"></div>
        <div class="col-xs-12 info-step">{if $stepInfos neq false}<p>{$stepInfos}{* HTML content, no escape necessary *}</p>{/if}</div>
    </div>

    <hr />
</div>