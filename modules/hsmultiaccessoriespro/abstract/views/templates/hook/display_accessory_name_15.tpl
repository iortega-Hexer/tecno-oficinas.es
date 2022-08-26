{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="margin-form translatable">
    {foreach $languages as $language}
        <div class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'} language_15" style="display: {if $language.id_lang == $default_form_language}block{else}none{/if}; float: left;">
            <span title="{$hs_i18n.click_to_edit|escape:'htmlall':'UTF-8'}" class="name global_accessory_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}">
                {$accessory.name[$language.id_lang]|escape:'html':'UTF-8'|default:""}
            </span>
            <input type="text" name="edit_name" alt="{$language.id_lang|intval}" value="{$accessory.name[$language.id_lang]|escape:'html':'UTF-8'|default:""}" id="name_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" class="edit_name hide global_accessory_{$accessory.id_accessory|escape:'html':'UTF-8'}_{$accessory.id_product_attribute|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" />
        </div>
    {/foreach}
</div>
