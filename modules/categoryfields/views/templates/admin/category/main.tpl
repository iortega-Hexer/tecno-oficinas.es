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

<div id="categoryfields" style="border-top:1px solid #ccc; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
    <div class="form-group">
        <div class="col-lg-3 control-label"></div>
        <div class="col-lg-9">
            <h2>{l s='Category Fields' mod='categoryfields'}</h2>
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-3 control-label"></div>
        <div class="col-lg-9">
            <select name="id_categoryfield" id="id_categoryfield">
                <option value="">{l s='Select a field' mod='categoryfields'}</option>
                {foreach from=$category_fields item=category_field}
                    <option value="{$category_field->id_categoryfield|escape:'htmlall':'UTF-8'}">{$category_field->name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-3 control-label"></div>
        <div class="col-lg-9">
            <h4>{l s='Content' mod='categoryfields'}</h4>
            {foreach from=$languages item=language}
                <div class="form-group translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" style="{if $language.id_lang eq $id_lang_default}display: block;{else}display:none;{/if}">
                    <div class="col-lg-9">
                        <textarea name="categoryfield_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="categoryfield_content_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="rte autoload_rte cf_autoload_rte"></textarea>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                            {$language.iso_code|escape:'htmlall':'UTF-8'}
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-backdrop"></div>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language_dropdown}
                                <li>
                                    <a href="javascript:hideOtherLanguage({$language_dropdown.id_lang|escape:'htmlall':'UTF-8'});">{$language_dropdown.name|escape:'htmlall':'UTF-8'}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/foreach}
            <h4>{l s='Excerpt' mod='categoryfields'}</h4>
            {foreach from=$languages item=language}
                <div class="form-group translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" style="{if $language.id_lang eq $id_lang_default}display: block;{else}display:none;{/if}">
                    <div class="col-lg-9">
                        <textarea name="categoryfield_excerpt_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="categoryfield_excerpt_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="rte autoload_rte cf_autoload_rte"></textarea>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
                            {$language.iso_code|escape:'htmlall':'UTF-8'}
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-backdrop"></div>
                        <ul class="dropdown-menu">
                            {foreach from=$languages item=language_dropdown}
                                <li>
                                    <a href="javascript:hideOtherLanguage({$language_dropdown.id_lang|escape:'htmlall':'UTF-8'});">{$language_dropdown.name|escape:'htmlall':'UTF-8'}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>

    <div style="display: none">
        {foreach from=$category_field_content item=content}
            {if !empty($content.content) || !empty($content.excerpt)}
                <textarea id="cf_content_{$content.id_categoryfield}_{$content.id_lang}" name="cf_content_{$content.id_categoryfield}_{$content.id_lang}">{$content.content nofilter}</textarea>
                <textarea id="cf_excerpt_{$content.id_categoryfield}_{$content.id_lang}" name="cf_excerpt_{$content.id_categoryfield}_{$content.id_lang}">{$content.excerpt nofilter}</textarea>
			{/if}
        {/foreach}
    </div>

</div>

<script>
    $(document).ready(function () {

        languages = {$languages_json nofilter};

        setTimeout(function() {
            cf_admin_category_main_controller = new CFAdminCategoryMainController('#categoryfields');
        }, 2000);
    });
</script>