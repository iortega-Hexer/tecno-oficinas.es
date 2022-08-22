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

{if !isset($id)}
    {assign var=id value=0}
{/if}
{if empty($choices)}
    <div class="alert alert-warning">
        {l s='You must save this step before configuring division.' mod='configurator'}
    </div>
{elseif empty($choices.block_option.groups)}
    <div class="alert alert-warning">
        {l s='You can\'t configure division on the first step.' mod='configurator'}
    </div>
{elseif ! $find_division_step}
    <div class="alert alert-warning">
        {l s='No steps found.' mod='configurator'}
    </div>
{else}
    
    {foreach $choices as $block}
        
        <div id="step_division_block_{$type|escape:'htmlall':'UTF-8'}_{$id|escape:'htmlall':'UTF-8'}" class="step_division_block step_division_{$type|escape:'htmlall':'UTF-8'}_block" data-id="{$id|escape:'htmlall':'UTF-8'}" data-value="{$division_value|escape:'htmlall':'UTF-8'}">
        
            <div class="form-group">
                <label class="control-label col-lg-2">{$block.name|escape:'htmlall':'UTF-8'}</label>
                <div class="col-lg-9">
                    {foreach $block.groups as $key => $group}
                        {if $group.type === 'select'}
                        <div class="{$group.class|escape:'htmlall':'UTF-8'}" >

                            {foreach $group.selects as $select}
                                {if !isset($select.parent_step) || $select.parent_step->use_input || $select.parent_step->price_list }

                                    <select
                                            {foreach $select.params as $attr => $value}
                                                    {$attr|cat:"="|cat:$value|escape:'htmlall':'UTF-8'} 
                                            {/foreach}
                                    >
                                        <option value="0">{l s='No division' mod='configurator'}</option>
                                        {foreach $select.options as $value => $option}
                                            {if $option.classname!="ConfiguratorStepAbstract" || $option.object->use_input || $option.object->price_list }
                                                <option value="{$value|escape:'htmlall':'UTF-8'}"
                                                {foreach $option.attrs as $attr => $value}
                                                    {$attr|cat:"="|cat:$value|escape:'htmlall':'UTF-8'} 
                                                {/foreach}
                                                >{$option.option|escape:'htmlall':'UTF-8'}</option>
                                            {/if}
                                        {/foreach}
                                    </select>

                                {/if}
                            {/foreach}

                        </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
                    
        </div>
        {break}
    {/foreach}
{/if}