{**
* Multi Accessories
*
* @author    PrestaMonster
* @copyright PrestaMonster
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if !empty($accessory.combinations)}
    <div class="form-group accessory_combination {if !$is_prestashop16} accessory_combination_15 {/if} show_combinations_{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}">
        <select id="visibility" name="id_accessory_attribute" class="dropdown_combination">
            <option value="{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}_0" data-image="{$accessory.image|escape:'htmlall':'UTF-8'}">
                {$hs_i18n.select_a_combination_optional|escape:'html':'UTF-8'}
            </option>
            {foreach from=$accessory.combinations item=combination}
                <option value="{$group.id_accessory_group|escape:'htmlall':'UTF-8'}_{$accessory.id_accessory|escape:'htmlall':'UTF-8'}_{$combination.id_product_attribute|escape:'htmlall':'UTF-8'}" {if $combination.id_product_attribute == $accessory.id_product_attribute}selected="selected"{/if} data-image="{$combination.image|escape:'htmlall':'UTF-8'}">
                    {$combination.name|escape:'htmlall':'UTF-8'}
                </option>
            {/foreach}
        </select>
    </div>
{/if}