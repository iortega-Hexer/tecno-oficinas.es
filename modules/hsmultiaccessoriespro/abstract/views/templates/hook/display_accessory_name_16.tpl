{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{foreach from=$languages key=k item=language}

    <div class="translatable-field lang-{$language.id_lang|escape:'html':'UTF-8'}" {if $k !=0}style="display: none"{/if}>
        <div class="col-lg-{if $languages|count > 1}8{else}9{/if}">

            <span title="{$hs_i18n.click_to_edit|escape:'html':'UTF-8'}" class="name global_accessory_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}">
                {$accessory.name[$language.id_lang]|escape:'html':'UTF-8'|default:""}
            </span>
            <input type="text" name="edit_name" alt="{$language.id_lang|escape:'html':'UTF-8'}" value="{$accessory.name[$language.id_lang]|escape:'html':'UTF-8'|default:""}" id="name_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" class="edit_name hide global_accessory_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}"/>

        </div>
        {if $languages|count > 1}
            <div class="col-lg-2">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    {$language.iso_code|escape:'html':'UTF-8'}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    {foreach from=$languages item=language}
                        <li>
                            <a href="javascript:hideOtherLanguage({$language.id_lang|escape:'html':'UTF-8'});">{$language.iso_code|escape:'html':'UTF-8'}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/if}
    </div>

{/foreach}
