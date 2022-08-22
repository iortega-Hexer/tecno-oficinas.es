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

{assign var='template_id' value=$option->id}

<div class="col-lg-12">
    <div class="panel">
        <h3 class="tab"><i class="icon-cog"></i> {l s='General settings' mod='configurator'}</h3>
        {include
            file='./option_settings.tpl'
            id=$template_id
            option=$option
            configurator=$configurator
        }
    </div>
</div>

<div class="col-lg-6">
    <div class="panel">
        <h3 class="tab"> <i class="icon-dollar"></i> {l s='Price impact' mod='configurator'}</h3>

        {if $configurator_step->price_list eq ''}

            {include 
                file='./price_impact.tpl' 
                id=$template_id
                option=$option
                currency=$currency
            }

        {else}
            <div class="alert alert-warning">
                {l s='You already use a price list for impact price.' mod='configurator'}
            </div>
        {/if}

    </div>

    <div class="panel">
        <h3 class="tab"> <i class="icon-dollar"></i> {l s='Tax impact' mod='configurator'}</h3>
        {include
            file='./tax_impact.tpl'
            id=$template_id
            option=$option
            currency=$currency
        }
    </div>

    <!-- DIVISION PARAMETERS -->
    {if $configurator_step->use_division}
        <div class="panel">

            <h3 class="tab"> <i class="icon-eye-open"></i> {l s='Division' mod='configurator'}</h3>

            <div id="division_block_{$template_id|escape:'htmlall':'UTF-8'}" class="division_block">
                <div class="form-group">
                    <label class="col-lg-12">{l s='Division for :' mod='configurator'} {$option->option.name|escape:'htmlall':'UTF-8'}</label>
                </div>

                <hr />

                {include file="./division_block.tpl"
                    id=$template_id
                    type="division" 
                    choices=$conditions_choices
                    division_value=$option->id_configurator_step_option_division
                    option=$option
                }
            </div>
        </div>
    {/if}
    <!-- END DIVISION PARAMETERS -->
</div>

<div class="col-lg-6">
    <!-- DISPLAY CONDITIONS PARAMETERS -->
    <div class="panel">
        <h3 class="tab"> <i class="icon-eye-open"></i> {l s='Display conditions of an option' mod='configurator'}</h3>

        <div id="display_conditions_block_{$option->id|escape:'htmlall':'UTF-8'}" class="display_conditions_block">
            <div class="form-group">
                <label class="col-lg-12">{l s='Display conditions for :' mod='configurator'} {$option->option.name|escape:'htmlall':'UTF-8'}</label>
            </div>

            <hr />

            {include file="./conditions_block.tpl"
                type="option"
                id=$template_id
                choices=$conditions_choices
                values=$options_conditions[$template_id]
            }
        </div>
    </div>
    <!-- END DISPLAY CONDITIONS PARAMETERS -->
</div>

<div class="configuratorDisplayAdminOptionSettingsFooter">
    {hook h='configuratorDisplayAdminOptionSettingsFooter' configurator_step=$configurator_step option=$option id=$template_id}
</div>



<script type="text/javascript">
	$(document).ready(function(){
		if(typeof formulaEditor !== 'undefined') {
			formulaEditor.init();
		}
	});
</script>
